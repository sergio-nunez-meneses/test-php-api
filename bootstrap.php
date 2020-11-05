<?php
require 'vendor/autoload.php';

// import classes
use Dotenv\Dotenv;
use Src\System\DatabaseConnector;

$dotenv = new DotEnv(__DIR__);
$dotenv->load(); // load all environment variables

$dbConnection = (new DatabaseConnector())->getConnection();

// test code, should output:
// api://default
// when you run $ php bootstrap.php
// echo getenv('OKTAAUDIENCE');
