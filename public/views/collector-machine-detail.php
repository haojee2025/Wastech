<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';
require_login(); require_role('collector');

$machine_id = (int)($_GET['machine_id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM machines WHERE id=? AND active=1");
$stmt->execute([$machine_id]);
$m = $stmt->fetch();
if (!$m) { http_response_code(404); echo "Machine not found"; exit; }

$wPct = $m['max_weight_kg'] > 0 ? min(100, round($m['current_weight_kg'] / $m['max_weight_kg'] * 100)) : 0;
$vPct = $m['max_volume_l'] > 0 ? min(100, round($m['current_volume_l'] / $m['max_volume_l'] * 100)) : 0;

$drops = db()->prepare("
  SELECT d.*, u.name AS user_name, i.name AS item_name
  FROM drop_events d
  JOIN users u ON u.id=d.user_id
  JOIN item_types i ON i.id=d.item_type_id
  WHERE d.machine_id=?
  ORDER BY d.created_at DESC
  LIMIT 20
");
$drops->execute([$machine_id]);
$rows = $drops->fetchAll();

$pickups = db()->prepare("
  SELECT p.*, u.name AS collector_name
  FROM pickups p
  JOIN users u ON u.id=p.collector_id
  WHERE p.machine_id=?
  ORDER BY p.created_at DESC
  LIMIT 20
");
$pickups->execute([$machine_id]);
$ps = $pickups->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Machine — <?= h($m['name']) ?></title>
  <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
  <div class="container">
    <header class="top">
      <h1><?= h($m['name']) ?></h1>
      <nav><a href="collector-machines.php">Back</a> • <a href="../controllers/auth-logout.php">Logout</a></nav>
    </header>

    <?php if (isset($_GET['msg'])): ?>
      <div class="alert"><?= h($_GET['msg']) ?></div>
    <?php endif; ?>

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

      <h3>Pickup</h3>
      <form method="post" action="../controllers/collector-pickup-create.php" enctype="multipart/form-data">
        <input type="hidden" name="machine_id" value="<?= (int)$m['id'] ?>">
        <div class="row">
          <div>
            <label>Total weight (kg)</label>
            <input type="number" step="0.001" min="0" name="total_weight_kg" required>
          </div>
          <div>
            <label>Total volume (L)</label>
            <input type="number" step="0.001" min="0" name="total_volume_l" required>
          </div>
        </div>
        <label>Notes</label>
        <input name="notes" maxlength="255">
        <label>Photo (optional)</label>
        <input type="file" name="photo" accept="image/*">
        <?php csrf_input(); ?>
        <button type="submit">Record Pickup & Reset</button>
      </form>
    </div>

    <div class="split">
      <div class="card">
        <h3>Recent Drops</h3>
        <div class="table">
          <div class="thead">
            <span>Date</span><span>User</span><span>Item</span><span>W (kg)</span><span>V (L)</span><span>Status</span>
          </div>
          <?php foreach ($rows as $r): ?>
            <div class="trow">
              <span><?= h($r['created_at']) ?></span>
              <span><?= h($r['user_name']) ?></span>
              <span><?= h($r['item_name']) ?></span>
              <span><?= h($r['est_weight_kg']) ?></span>
              <span><?= h($r['est_volume_l']) ?></span>
              <span class="chip <?= h($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="card">
        <h3>Pickup History</h3>
        <div class="table">
          <div class="thead">
            <span>Date</span><span>Collector</span><span>W (kg)</span><span>V (L)</span><span>Notes</span>
          </div>
          <?php foreach ($ps as $p): ?>
            <div class="trow">
              <span><?= h($p['created_at']) ?></span>
              <span><?= h($p['collector_name']) ?></span>
              <span><?= h($p['total_weight_kg']) ?></span>
              <span><?= h($p['total_volume_l']) ?></span>
              <span><?= h($p['notes']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</body>
</html>
