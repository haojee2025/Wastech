<?php
function h($str) {
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrf_input() {
  $t = csrf_token();
  echo '<input type="hidden" name="csrf" value="'.h($t).'">';
}

function verify_csrf($token) {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token ?? '')) {
    http_response_code(419);
    echo "CSRF token mismatch";
    exit;
  }
}

function status_color($pct) {
  if ($pct >= 100) return 'full';
  if ($pct >= 70) return 'warn';
  return 'ok';
}
