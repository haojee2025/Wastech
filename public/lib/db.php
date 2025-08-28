<?php
// PDO connection (edit credentials for your env)
function db() : PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $dsn = "mysql:host=127.0.0.1;dbname=wastech;charset=utf8mb4";
  $username = "root";
  $password = "";

  $pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
