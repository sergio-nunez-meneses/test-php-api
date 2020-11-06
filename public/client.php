<?php

require('../tools/okta.php');

$client_id = OKTACLIENTID;
$client_secret = OKTASECRET;
$scope = SCOPE;
$issuer = OKTAISSUER;

$get_token = obtain_token($issuer, $client_id, $client_secret, $scope);

// test requests
get_all_users($get_token);
get_user($get_token, 1);

function obtain_token($issuer, $client_id, $client_secret, $scope) {
  echo "Obtaining token...\n";

  // set up request
  $uri = $issuer . '/v1/token';
  $token = base64_encode("$client_id:$client_secret");
  $payload = http_build_query([
    'grant_type' => 'client_credentials',
    'scope' => $scope
  ]);

  // build curl request
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $uri);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    "Authorization: Basic $token"
  ]);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  // process and return response
  $response = curl_exec($ch);
  $response = json_decode($response, true);

  if (!isset($response['access_token']) || !isset($response['token_type'])) {
    exit("Failed, exiting.\n");
  }

  echo "Success!\n ";
  return $response['token_type'] . ' ' . $response['access_token'];
}

function get_all_users($token) {
  echo 'Getting all users...';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/person');
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: $token"
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);

  var_dump($response);
}

function get_user($token, $id) {
  echo "Getting user id#$id...";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:8000/person/$id");
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: $token"
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);

  var_dump($response);
}
