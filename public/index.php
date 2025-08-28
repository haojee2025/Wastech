<?php
require_once __DIR__.'/../lib/auth.php';
$u = current_user();
if ($u) {
  if ($u['role'] === 'recycler') {
    header('Location: /wastech/public/views/recycler-machines.php');
  } else {
    header('Location: /wastech/public/views/collector-machines.php');
  }
  exit;
}
header('Location: /wastech/public/views/login.php');
