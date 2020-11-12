<?php
require('../tools/okta.php');

// functions create_public_key, url_base64_decode and encode_length, and token verification process based on https://github.com/dragosgaftoneanu/okta-simple-jwt-verifier/blob/master/src/SimpleJWTVerifier.php#L130

class TokenController
{
  public function authenticate()
  {
    // check for token in HTTP headers
    switch (true)
    {
      case array_key_exists('HTTP_AUTHORIZATION', $_SERVER):
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
        break;

      case array_key_exists('Authorization', $_SERVER):
        $auth_header = $_SERVER['Authorization'];
        break;

      default:
        $auth_header = null;
        break;
    }

    if ($auth_header === null)
    {
      throw new \Exception('Authorization headers not found.');
    }

    preg_match('/Bearer\s(\S+)/', $auth_header, $matches);

    if (!isset($matches[1]))
    {
      throw new \Exception('Token not found.');
    }

    if (!stristr($matches[1], '.'))
    {
      throw new \Exception("Token doesn't contain expected delimiter.");
    }

    if (count(explode('.', $matches[1])) !== 3)
    {
      throw new \Exception("Token doesn't contain expected structure.");
    }

    // deconstruct and decode token structure
    list($header, $payload, $signature) = explode('.', $matches[1]);
    $decoded_header = $this->decode_token_structure($header);
    // echo "\n\nHeader:\n\n";
    // var_dump($decoded_header);
    $decoded_payload = $this->decode_token_structure($payload);
    // echo "\n\nPayload:\n\n";
    // var_dump($decoded_payload);

    if ($decoded_header['alg'] !== 'RS256')
    {
      throw new \Exception('Token was generated through an unsupported algorithm.');
    }

    if ($decoded_payload['iat'] > time())
    {
      throw new \Exception('Token was issued in the future (well played Jonas Kahnwald).');
    }

    if ($decoded_payload['exp'] < time())
    {
      throw new \Exception('Token expired.');
    }

    if (OKTAAUDIENCE !== '' && $decoded_payload['aud'] !== '')
    {
      if (OKTAAUDIENCE !== $decoded_payload['aud'])
      {
        throw new \Exception("Token doesn't contain expected audience.");
      }
    }

    if (OKTACLIENTID !== '' && $decoded_payload['cid'] !== '')
    {
      if (OKTACLIENTID !== $decoded_payload['cid'])
      {
        throw new \Exception("Token doesn't contain expected client ID.");
      }
    }

    if (OKTAISSUER !== $decoded_payload['iss'])
    {
      throw new \Exception("Token doesn't contain expected issuer.");
    }

    $url = $decoded_payload['iss'] . '/.well-known/oauth-authorization-server';
    $keys = (new TokenModel)->get_keys($url);
    // echo "\n\nKeys:\n\n";
    // var_dump($keys);

    if (empty($keys))
    {
      throw new \Exception("Token's keys not found at /keys endpoint.");
    }

    foreach($keys as $key)
    {
      if ($key['kid'] !== $decoded_header['kid'])
      {
        throw new \Exception("Token's signing key not found.");
      }
      else
      {
        $public_key = $this->create_public_key($key['n'], $key['e']);
      }
    }

    // echo "\nPublic key generated:\n\n";
    // var_dump($public_key);

    if (openssl_verify("$header.$payload", $this->url_base64_decode($signature), $public_key, OPENSSL_ALGO_SHA256))
    {
      // echo "Token's signature verified!";
      return true;
    }
    else
    {
      throw new \Exception("Token's signature couldn't be verified.");
      return false;
    }
  }

  public function decode_token_structure($array)
  {
    return json_decode(base64_decode($array), true);
  }

  public function create_public_key($n, $e)
  {
    $modulus = $this->url_base64_decode($n);
    $exponent = $this->url_base64_decode($e);
    $components = [
        'modulus' => pack('Ca*a*', 2, $this->encode_length(strlen($modulus)), $modulus),
        'publicExponent' => pack('Ca*a*', 2, $this->encode_length(strlen($exponent)), $exponent)
    ];

    $RSAPublicKey = pack(
        'Ca*a*a*',
        48,
        $this->encode_length(strlen($components['modulus']) + strlen($components['publicExponent'])),
        $components['modulus'],
        $components['publicExponent']
    );

    $rsaOID = pack('H*', '300d06092a864886f70d0101010500');

    $RSAPublicKey = chr(0) . $RSAPublicKey;
    $RSAPublicKey = chr(3) . $this->encode_length(strlen($RSAPublicKey)) . $RSAPublicKey;
    $RSAPublicKey = pack(
        'Ca*a*',
        48,
        $this->encode_length(strlen($rsaOID . $RSAPublicKey)),
        $rsaOID . $RSAPublicKey
    );
    $RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
      chunk_split(base64_encode($RSAPublicKey), 64) .
      '-----END PUBLIC KEY-----';

    return $RSAPublicKey;
  }

  public function url_base64_decode($input)
  {
    $remainder = strlen($input) % 4;

    if ($remainder)
    {
      $padlen = 4 - $remainder;
      $input .= str_repeat('=', $padlen);
    }

    return base64_decode(strtr($input, '-_', '+/'));
  }

  public function encode_length($length)
  {
    if ($length <= 0x7F)
    {
      return chr($length);
    }

    $temp = ltrim(pack('N', $length), chr(0));
    return pack('Ca*', 0x80 | strlen($temp), $temp);
  }
}
