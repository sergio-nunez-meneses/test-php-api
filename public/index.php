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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $personGateway = new PersonGateway($dbConnection);
  $response = $personGateway->findAll();

  if ($response['body']) {
    echo $response['body'];
  }
}
