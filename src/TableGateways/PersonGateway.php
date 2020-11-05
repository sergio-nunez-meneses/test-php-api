<?php
namespace Src\TableGateways;

class PersonGateway
{

  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function findAll()
  {
    $sql = "SELECT * FROM person";

    try
    {
      $stmt = $this->db->query($sql);
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($res);
      return $response;
    }
    catch (\PDOException $e)
    {
      exit($e->getMessage());
    }
  }
}
