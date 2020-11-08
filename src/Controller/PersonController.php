<?php
namespace Src\Controller;
use Src\TableGateways\PersonGateway;

class PersonController
{

  public static function route_request($db, $request_method, $user_id)
  {
    $personGateway = new PersonGateway($db);

    switch ($request_method)
    {
      case 'GET':
        if ($user_id)
        {
          $response = $personGateway->findOne($user_id);
        }
        else
        {
          $response = $personGateway->findAll();
        };
        break;

      case 'POST':
        $response = $personGateway->create($_POST);
        break;

      case 'PUT':
      parse_str(file_get_contents('php://input') , $output);
      $response = $personGateway->update($user_id, $output);
          break;

      case 'DELETE':
          $response = $personGateway->delete($user_id);
          break;

      default:
          $response = PersonController::error_response();
          header('HTTP/1.1 404 Not Found');
          break;

    }
    if (isset($response) && $response['body'])
    {
      header($response['status_code_header']);
      echo $response['body'];
    }
  }

  public static function error_response()
  {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = json_encode(['page' => 'not found']);
    return $response;
  }
}
