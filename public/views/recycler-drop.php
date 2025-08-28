<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';
require_login(); require_role('recycler');

$machine_id = (int)($_GET['machine_id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM machines WHERE id=? AND active=1");
$stmt->execute([$machine_id]);
$machine = $stmt->fetch();
if (!$machine) { http_response_code(404); echo "Machine not found"; exit; }

$items = db()->query("SELECT * FROM item_types ORDER BY name")->fetchAll();

$wPct = $machine['max_weight_kg'] > 0 ? min(100, round($machine['current_weight_kg'] / $machine['max_weight_kg'] * 100)) : 0;
$vPct = $machine['max_volume_l'] > 0 ? min(100, round($machine['current_volume_l'] / $machine['max_volume_l'] * 100)) : 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Drop Item — <?= h($machine['name']) ?></title>
  <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
  <div class="container">
    <header class="top">
      <h1>Drop Item</h1>
      <nav><a href="recycler-machines.php">Back</a> • <a href="recycler-history.php">My History</a> • <a href="../controllers/auth-logout.php">Logout</a></nav>
    </header>

    <div class="card">
      <div class="bin <?= h(status_color(max($wPct,$vPct))) ?>">
        <div class="bin-head">
          <div class="title"><?= h($machine['name']) ?></div>
          <div class="sub"><?= h($machine['location']) ?></div>
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

      <?php if (isset($_GET['msg'])): ?>
        <div class="alert"><?= h($_GET['msg']) ?></div>
      <?php endif; ?>

      <form method="post" action="../controllers/recycler-drop-create.php">
        <input type="hidden" name="machine_id" value="<?= (int)$machine['id'] ?>">
        <label>Item type</label>
        <select name="item_type_id" required>
          <?php foreach ($items as $it): ?>
            <option value="<?= (int)$it['id'] ?>"><?= h($it['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <div class="row">
          <div>
            <label>Estimated weight (kg)</label>
            <input type="number" name="est_weight_kg" min="0" step="0.001" placeholder="0.000" required>
          </div>
          <div>
            <label>Estimated volume (L)</label>
            <input type="number" name="est_volume_l" min="0" step="0.001" placeholder="0.000" required>
          </div>
        </div>

        <label>Notes (optional)</label>
        <input name="note" maxlength="255" placeholder="brand/condition/serial…">

        <?php csrf_input(); ?>
        <button type="submit">Submit Drop</button>
      </form>
    </div>
  </div>
</body>
</html>
