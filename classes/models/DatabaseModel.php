<?php
require('../tools/sql.php');

abstract class DatabaseModel
{

  private $pdo;
  protected $table;
  protected $columns;

  protected function connection()
  {
    $this->pdo = new PDO(
      'mysql:host=' . DB_HOST. ';port=' . DB_PORT. ';charset=' . DB_CHAR . ';dbname=' . DB_NAME,
      DB_USER,
      DB_PWD,
      PDO_OPTIONS
    );

    if (!empty($this->pdo))
    {
      // echo 'connected to ' . getenv('DB_DATABASE') . '.<br>'; // for debugging
      return TRUE;
    }
    else
    {
      // echo "connection failed. <br>";
      return FALSE;
    }
  }

  protected function run_query($sql, $placeholders = [])
  {
    if ($this->connection() === TRUE)
    {
      if (empty($placeholders))
      {
        return $this->pdo->query($sql)->fetchAll();
      }
      else
      {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($placeholders);
        return $stmt;
      }
    }
  }

  protected function create_database()
  {
    $this->pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHAR, DB_USER, DB_PWD);

    if (empty($this->pdo) === FALSE)
    {
      $this->pdo->exec('CREATE DATABASE IF NOT EXISTS' . DB_NAME);
      $result = $this->pdo->exec('use' . DB_NAME);
      // echo 'database ' . DB_DATABASE . 'created.<br>'; // for debugging
      return TRUE;
    }
    else
    {
      echo 'failed creating database.<br>';
      return FALSE;
    }
  }

  protected function create_table()
  {
    if ($this->connection() === TRUE)
    {
      $this->table = 'person';
      $this->table_columns = "(
          id INT NOT NULL AUTO_INCREMENT,
          firstname VARCHAR(100) NOT NULL,
          lastname VARCHAR(100) NOT NULL,
          firstparent_id INT DEFAULT NULL,
          secondparent_id INT DEFAULT NULL,
          PRIMARY KEY (id),
          FOREIGN KEY (firstparent_id)
              REFERENCES person(id)
              ON DELETE SET NULL,
          FOREIGN KEY (secondparent_id)
              REFERENCES person(id)
              ON DELETE SET NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

      INSERT INTO person
          (id, firstname, lastname, firstparent_id, secondparent_id)
      VALUES
          (1, 'Krasimir', 'Hristozov', null, null),
          (2, 'Maria', 'Hristozova', null, null),
          (3, 'Masha', 'Hristozova', 1, 2),
          (4, 'Jane', 'Smith', null, null),
          (5, 'John', 'Smith', null, null),
          (6, 'Richard', 'Smith', 4, 5),
          (7, 'Donna', 'Smith', 4, 5),
          (8, 'Josh', 'Harrelson', null, null),
          (9, 'Anna', 'Harrelson', 7, 8);
      ";
      $this->pdo->exec("DROP TABLE IF EXISTS $this->table");
      $this->pdo->exec("CREATE TABLE IF NOT EXISTS $this->table $this->table_columns");
      // echo "created table $this->table and columns $this->columns.<br>"; // for debugging
      return TRUE;
    }
    else
    {
      echo 'failed creating table and columns.<br>';
      return FALSE;
    }
  }

  protected function run_database()
  {
    $this->create_database();
    $this->create_table();
  }
}
