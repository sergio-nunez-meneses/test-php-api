<?php
require_once('../include/class_autoloader.php');
require('../tools/okta.php');

// handle Cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *'); // allow all requests from all origins
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE'); // allow only declared request methods
header('Access-Control-Max-Age: 3600'); // add max time
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$user_id = null;
$request_method = $_SERVER['REQUEST_METHOD'];

if ($uri[1] !== 'person') {
  header('HTTP/1.1 404 Not Found');
  exit();
}

if (isset($uri[2])) {
  $user_id = $uri[2];
}

// request authentication with okta
if (!authenticate()) {
  header('HTTP/1.1 401 Unauthorized');
  exit('Unauthorized');
}

PersonController::route_request($request_method, $user_id);

function decode_token_structure($array) {
  return json_decode(base64_decode($array), true);
}

function base64_url_encode($input) {
  return str_replace(
    ['+', '/', '='],
    ['-', '_', ''],
    base64_encode($input)
  );
}

// create a middleware
function authenticate() {
  try {
    switch (true) {
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

    preg_match('/Bearer\s(\S+)/', $auth_header, $matches);

    if (!isset($matches[1])) {
      throw new \Exception('No Bearer Token found.');
    }

    if (!stristr($matches[1], '.')) {
      throw new \Exception("Token doesn't contain expected delimiter.");
    }

    list($header, $payload, $signature) = explode('.', $matches[1]);
    $decoded_header = decode_token_structure($header);
    echo "\n\nHeader:\n\n";
    var_dump($decoded_header);
    $decoded_payload = decode_token_structure($payload);
    echo "\n\nPayload:\n\n";
    var_dump($decoded_payload);

    if ($decoded_header['alg'] != 'RS256') {
      throw new \Exception('Token was generated through an unsupported algorithm.');
    }

		if ($decoded_payload['iat'] > time()) {
      throw new \Exception('Token was issued in the future (well played Jonas Kahnwald).');
    }

		if ($decoded_payload['exp'] < time()) {
      throw new \Exception('Token expired.');
    }

    if (OKTAAUDIENCE !== "" && $decoded_payload['aud'] !== "") {
      if (OKTAAUDIENCE !== $decoded_payload['aud']) {
        throw new \Exception("Token doesn't contain expected audience.");
      }
    }

    if (OKTACLIENTID !== "" && $decoded_payload['cid'] !== "") {
      if (OKTACLIENTID !== $decoded_payload['cid']) {
        throw new \Exception("Token doesn't contain expected client ID.");
      }
    }

    if (OKTAISSUER !== $decoded_payload['iss']) {
      throw new \Exception("Token doesn't contain expected issuer.");
    }

    // get token id
    // $kid = json_decode($plainHeader, true);
    // validate it
    // if ($kid['kid'] not empty && $kid['kid'] is equal to key in OKTAISSUER . '/v1/keys') allow access

    return true;
  } catch (\Exception $e) {
    return false;
  }
}
