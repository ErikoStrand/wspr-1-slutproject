<?php
if (!defined("DB_USER")) {
  define('DB_HOST', 'localhost');
  define('DB_USER', 'erik');
  define('DB_PASS', 'ewqewq1');
  define('DB_NAME', 'php_dev');
  
  // Create connection
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  
  // Check connection
  if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
  }
}


// echo 'Connected successfully';
