<?php
require('../tools/okta.php');

$client_id = OKTACLIENTID;
$client_secret = OKTASECRET;
$scope = SCOPE;
$issuer = OKTAISSUER;
$curl_options = [];
$token = get_token($issuer, $client_id, $client_secret, $scope);

function curl_request($token, $url) {
  $curl_opts = [
    CURLOPT_URL => $url,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      "Authorization: $token"
    ],
    CURLOPT_RETURNTRANSFER => true
  ];

  try {
    $ch = curl_init();

    if ($ch === false) {
      throw new \Exception("\nFailed to initialize request.\n");
    }

    curl_setopt_array($ch, $curl_opts);
    $response = curl_exec($ch);
    curl_close($ch);

    var_dump($response);
  } catch (\Exception $e) {
    echo $e->getMessage() . "\n\n";
  }
}

function get_token($issuer, $client_id, $client_secret, $scope) {
  echo "\nObtaining token...\n";

  // set up request
  $uri = $issuer . '/v1/token';
  $generate_token = base64_encode("$client_id:$client_secret");
  $payload = http_build_query([
    'grant_type' => 'client_credentials',
    'scope' => $scope
  ]);
  $curl_opts = [
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Basic $generate_token"
    ],
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false, // fixed bug 'Curl failed with error #60'
    CURLOPT_VERBOSE => TRUE
  ];

  // build curl request
  try {
    $ch = curl_init($uri);

    if ($ch === false) {
      throw new \Exception("\nFailed to initialize request.\n");
    }

    curl_setopt_array($ch, $curl_opts);
    $response = curl_exec($ch); // process and return response

    if ($response === false) {
      throw new Exception(curl_error($ch), curl_errno($ch));
    }

    $response = json_decode($response, true);

    if (!isset($response['access_token']) || !isset($response['token_type'])) {
      exit("\nFailed, exiting.\n");
    }

    echo "\nSuccess!\n";
    curl_close($ch);
    return $response['token_type'] . ' ' . $response['access_token'];
  } catch (\Exception $e) {
    trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()
      ), E_USER_ERROR);
  }
}

function get_all_users($token) {
  echo "\n\nGetting all users...\n\n";
  curl_request($token, 'http://127.0.0.1:8000/person');
}

function get_user($token, $id) {
  echo "\n\nGetting user id#$id...\n\n";
  curl_request($token, "http://127.0.0.1:8000/person/$id");
}

// test requests
get_all_users($token);
get_user($token, 3);
