<?php
namespace Src\TableGateways;

class PersonGateway
{

  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function to_json($array = [])
  {
    if (count($array) > 0) {
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($array);
      return $response;
    }
  }

  public function findAll()
  {
    $sql = "SELECT * FROM person";

    try
    {
      $stmt = $this->db->query($sql);
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $this->to_json($res);
    }
    catch (\PDOException $e)
    {
      exit($e->getMessage());
    }
  }

  public function findOne($id)
  {
    $sql = "SELECT * FROM person WHERE id =:id";

    try {
      $stmt = $this->db->prepare($sql);
      $stmt->execute(['id' => $id]);
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $this->to_json($res);
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }

  }
}
