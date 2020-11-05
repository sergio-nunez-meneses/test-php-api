<?php
namespace Src\TableGateways;

class PersonGateway
{

  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function to_json($result)
  {
    if (is_array($result)) {
      if (count($result) > 0) {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
      }
    } else {
      $response['status_code_header'] = 'HTTP/1.1 201 Created';
      $response['body'] = json_encode(['user' => 'created']);
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

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->execute(['id' => $id]);
      $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      return $this->to_json($res);
    }
    catch (\PDOException $e)
    {
      exit($e->getMessage());
    }

  }

  public function insert(Array $input)
  {
    $sql = "INSERT INTO person (firstname, lastname, firstparent_id, secondparent_id) VALUES (:firstname, :lastname, :firstparent_id, :secondparent_id)";

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        'firstname' => $input['firstname'],
        'lastname' => $input['lastname'],
        'firstparent_id' => $input['firstparent_id'] ?? null,
        'secondparent_id' => $input['secondparent_id'] ?? null
      ]);
      $res = $stmt->rowCount();

      if ($res > 0)
      {
        return $this->to_json($res);
      }
    }
    catch (\PDOException $e)
    {
      exit($e->getMessage());
    }
  }

  public function update($id, Array $input)
  {
    $sql = "UPDATE person SET firstname = :firstname, lastname  = :lastname, firstparent_id = :firstparent_id, secondparent_id = :secondparent_id WHERE id = :id";

    try
    {
      $stmt = $this->db->prepare($sql);
      $stmt->execute([
        'id' => $id,
        'firstname' => $input['firstname'],
        'lastname' => $input['lastname'],
        'firstparent_id' => $input['firstparent_id'] ?? null,
        'secondparent_id' => $input['secondparent_id'] ?? null
      ]);
      $res = $stmt->rowCount();

      if ($res > 0)
      {
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode(['user' => 'updated']);
        return $response;
      }
    }
    catch (\PDOException $e)
    {
      exit($e->getMessage());
    }

  }
}
