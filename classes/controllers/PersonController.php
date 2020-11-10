<?php

class PersonController
{

  public static function route_request($request_method, $user_id)
  {
    $person_model = new PersonModel();

    switch ($request_method)
    {
      case 'GET':
        if ($user_id)
        {
          $response = $person_model->findOne($user_id);
        }
        else
        {
          $response = $person_model->findAll();
        };
        break;

      case 'POST':
        $response = $person_model->create($_POST);
        break;

      case 'PUT':
      parse_str(file_get_contents('php://input') , $output);
      $response = $person_model->update($user_id, $output);
          break;

      case 'DELETE':
          $response = $person_model->delete($user_id);
          break;

      default:
          header('HTTP/1.1 404 Not Found');
          break;

    }
    if (isset($response) && $response['body'])
    {
      header($response['status_code_header']);
      echo $response['body'];
    }
  }
}
