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
      throw new \Exception('No Bearer Token');
    }

    list($header, $payload, $signature) = explode('.', $matches[1]);
    $plainHeader = base64_decode($header);
    echo "Header:\n$plainHeader\n\n";
    $plainPayload = base64_decode($payload);
    echo "Payload:\n$plainPayload\n\n";
    $plainSignature = base64_decode($signature);
    echo "Signature:\n$signature\n\n";

    // get token id
    // $kid = json_decode($plainHeader, true);
    // validate it
    // if ($kid['kid'] not empty && $kid['kid'] is equal to key in OKTAISSUER . '/v1/keys') allow access

    return true;
  } catch (\Exception $e) {
    return false;
  }
}
