<?php
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../views/login.php'); exit; }
verify_csrf($_POST['csrf'] ?? null);

$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

$stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  header('Location: ../views/login.php?err=Invalid+credentials');
  exit;
}

login_user($user);
header('Location: ../index.php');
