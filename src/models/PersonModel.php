<?php

class PersonModel extends DatabaseModel
{

  public function to_json($data)
  {
    if (count($data) > 0) {
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode($data);
      return $response;
    }
  }

  public function findAll()
  {
    $sql = "SELECT * FROM person";
    $res = $this->run_query($sql);
    return $this->to_json($res);
  }

  public function findOne($id)
  {
    $sql = "SELECT * FROM person WHERE id =:id";
    $res = $this->run_query($sql, ['id' => $id])->fetch();
    return $this->to_json($res);
  }

  public function create($input = [])
  {
    $sql = "INSERT INTO person (firstname, lastname, firstparent_id, secondparent_id) VALUES (:firstname, :lastname, :firstparent_id, :secondparent_id)";
    $placeholders = [
      'firstname' => $input['firstname'],
      'lastname' => $input['lastname'],
      'firstparent_id' => $input['firstparent_id'] ?? null,
      'secondparent_id' => $input['secondparent_id'] ?? null
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      $response['status_code_header'] = 'HTTP/1.1 201 Created';
      $response['body'] = json_encode(['user' => 'created']);
      return $response;
    }
  }

  public function update($id, $input = [])
  {
    $sql = "UPDATE person SET firstname = :firstname, lastname  = :lastname, firstparent_id = :firstparent_id, secondparent_id = :secondparent_id WHERE id = :id";
    $placeholders = [
      'id' => $id,
      'firstname' => $input['firstname'],
      'lastname' => $input['lastname'],
      'firstparent_id' => $input['firstparent_id'] ?? null,
      'secondparent_id' => $input['secondparent_id'] ?? null
    ];
    $res = $this->run_query($sql, $placeholders)->rowCount();

    if ($res > 0)
    {
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode(['user' => 'updated']);
      return $response;
    }
  }

  public function delete($id)
  {
    $sql = "DELETE FROM person WHERE id = :id";
    $res = $this->run_query($sql, ['id' => $id])->rowCount();

    if ($res > 0)
    {
      $response['status_code_header'] = 'HTTP/1.1 200 OK';
      $response['body'] = json_encode(['user' => 'deleted']);
      return $response;
    }
  }
}
