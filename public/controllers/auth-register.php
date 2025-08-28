<?php
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../views/login.php'); exit; }
verify_csrf($_POST['csrf'] ?? null);

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

if (!$name || !$email || !$pass) {
  header('Location: ../views/login.php?err=Missing+fields'); exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = db()->prepare("INSERT INTO users (name, email, role, password_hash) VALUES (?, ?, 'recycler', ?)");
try {
  $stmt->execute([$name, $email, $hash]);
} catch (Throwable $e) {
  header('Location: ../views/login.php?err='.urlencode('Email already registered'));
  exit;
}

header('Location: ../views/login.php?ok=Account+created,+please+login');
