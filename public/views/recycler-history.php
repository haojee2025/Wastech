<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';
require_login(); require_role('recycler');

$user = current_user();
$stmt = db()->prepare("
  SELECT d.*, m.name AS machine_name, m.location, i.name AS item_name
  FROM drop_events d
  JOIN machines m ON m.id=d.machine_id
  JOIN item_types i ON i.id=d.item_type_id
  WHERE d.user_id=?
  ORDER BY d.created_at DESC
");
$stmt->execute([$user['id']]);
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>My History</title>
  <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
  <div class="container">
    <header class="top">
      <h1>My Drop History</h1>
      <nav><a href="recycler-machines.php">Machines</a> • <a href="../controllers/auth-logout.php">Logout</a></nav>
    </header>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert"><?= h($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="table">
      <div class="thead">
        <span>Date</span><span>Machine</span><span>Location</span><span>Item</span><span>W (kg)</span><span>V (L)</span><span>Status</span>
      </div>
      <?php foreach ($rows as $r): ?>
        <div class="trow">
          <span><?= h($r['created_at']) ?></span>
          <span><?= h($r['machine_name']) ?></span>
          <span><?= h($r['location']) ?></span>
          <span><?= h($r['item_name']) ?></span>
          <span><?= h($r['est_weight_kg']) ?></span>
          <span><?= h($r['est_volume_l']) ?></span>
          <span class="chip <?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
