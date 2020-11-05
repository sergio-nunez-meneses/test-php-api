<?php
require '../bootstrap.php';
use Src\TableGateways\PersonGateway;

// handle Cross origin resource sharing (CORS)
header('Access-Control-Allow-Origin: *'); // allow all requests from all origins
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE'); // allow only declared request methods
header('Access-Control-Max-Age: 3600'); // add max time
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
$userId = null;

// route requests
if ($uri[1] === 'person') {
  $personGateway = new PersonGateway($dbConnection);

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($uri[2])) {
      $response = $personGateway->findOne(2);
    } else {
      $response = $personGateway->findAll();
    }
  }

  if ($response['body']) {
    echo $response['body'];
  }
} else {
  header('HTTP/1.1 404 Not Found');
  exit();
}
