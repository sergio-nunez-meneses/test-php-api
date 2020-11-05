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
$user_id = null;

// route requests
if ($uri[1] === 'person') {
  $personGateway = new PersonGateway($dbConnection);

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($uri[2])) {
      $user_id = $uri[2];
      $response = $personGateway->findOne($user_id);
    } else {
      $response = $personGateway->findAll();
    }
  } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = $personGateway->create($_POST);
  } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $user_id = $uri[2];
    parse_str(file_get_contents('php://input') , $output);
    $response = $personGateway->update($user_id, $output);
  } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $user_id = $uri[2];
    $response = $personGateway->delete($user_id);
  }

  if (isset($response) && $response['body']) {
    header($response['status_code_header']);
    echo $response['body'];
  }
} else {
  header('HTTP/1.1 404 Not Found');
  exit();
}
