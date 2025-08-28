<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';
require_login(); require_role('recycler');

$machines = db()->query("SELECT * FROM machines WHERE active=1 ORDER BY location, name")->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Machines — Recycler</title>
  <link rel="stylesheet" href="../assets/css/app.css">
  <script defer src="../assets/js/bin.js"></script>
</head>
<body>
  <div class="container">
    <header class="top">
      <h1>Choose a Machine</h1>
      <nav><a href="../controllers/auth-logout.php">Logout</a> • <a href="recycler-history.php">My History</a></nav>
    </header>

    <div class="grid">
      <?php foreach ($machines as $m):
        $wPct = $m['max_weight_kg'] > 0 ? min(100, round($m['current_weight_kg'] / $m['max_weight_kg'] * 100)) : 0;
        $vPct = $m['max_volume_l'] > 0 ? min(100, round($m['current_volume_l'] / $m['max_volume_l'] * 100)) : 0;
      ?>
      <div class="card">
        <div class="bin <?= h(status_color(max($wPct,$vPct))) ?>">
          <div class="bin-head">
            <div class="title"><?= h($m['name']) ?></div>
            <div class="sub"><?= h($m['location']) ?></div>
          </div>
          <div class="bars">
            <div class="bar"><span style="width: <?= $wPct ?>%"></span></div>
            <div class="meta">Weight: <?= $wPct ?>%</div>
            <div class="bar"><span style="width: <?= $vPct ?>%"></span></div>
            <div class="meta">Volume: <?= $vPct ?>%</div>
          </div>
          <div class="status <?= h(status_color(max($wPct,$vPct))) ?>">
            <?= ($wPct>=100 || $vPct>=100) ? 'Full' : (($wPct>=70 || $vPct>=70) ? 'Near Full' : 'OK') ?>
          </div>
        </div>
        <a class="btn" href="recycler-drop.php?machine_id=<?= (int)$m['id'] ?>">Use this machine</a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
