<?php
require_once __DIR__ . '/db.php';

function start_session_once() {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
}

function current_user() {
  start_session_once();
  return $_SESSION['user'] ?? null;
}

function require_login() {
  if (!current_user()) {
    header('Location: /wastech/public/views/login.php');
    exit;
  }
}

function require_role($role) {
  $u = current_user();
  if (!$u || $u['role'] !== $role) {
    http_response_code(403);
    echo "Forbidden";
    exit;
  }
}

function login_user($userRow) {
  start_session_once();
  session_regenerate_id(true);
  $_SESSION['user'] = [
    'id' => $userRow['id'],
    'name' => $userRow['name'],
    'email' => $userRow['email'],
    'role' => $userRow['role'],
  ];
}

function logout_user() {
  start_session_once();
  $_SESSION = [];
  session_destroy();
}
