<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';
require_login(); require_role('recycler');
verify_csrf($_POST['csrf'] ?? null);

$user = current_user();
$machine_id = (int)($_POST['machine_id'] ?? 0);
$item_type_id = (int)($_POST['item_type_id'] ?? 0);
$est_w = (float)($_POST['est_weight_kg'] ?? 0);
$est_v = (float)($_POST['est_volume_l'] ?? 0);
$note = trim($_POST['note'] ?? '');

if ($est_w < 0 || $est_v < 0) { $est_w = 0; $est_v = 0; }

$db = db();
$db->beginTransaction();

try {
  // Lock machine row to avoid race conditions
  $stmt = $db->prepare("SELECT * FROM machines WHERE id=? AND active=1 FOR UPDATE");
  $stmt->execute([$machine_id]);
  $m = $stmt->fetch();
  if (!$m) throw new Exception("Machine not found");

  // Capacity pre-check
  $will_w = $m['current_weight_kg'] + $est_w;
  $will_v = $m['current_volume_l'] + $est_v;

  if ($will_w > $m['max_weight_kg'] || $will_v > $m['max_volume_l']) {
    // Reject because full
    $ins = $db->prepare("INSERT INTO drop_events (user_id, machine_id, item_type_id, est_weight_kg, est_volume_l, note, status, reason)
                         VALUES (?, ?, ?, ?, ?, ?, 'rejected', 'full')");
    $ins->execute([$user['id'], $m['id'], $item_type_id, $est_w, $est_v, $note]);
    $db->commit();
    header('Location: ../views/recycler-drop.php?machine_id='.$m['id'].'&msg='.urlencode('Container full — try another location'));
    exit;
  }

  // MACHINE AUTO-VERIFICATION (simplified):
  // For MVP we accept all within capacity. You can extend with per-item rules.
  $accepted = true;

  if ($accepted) {
    // Insert accepted drop
    $ins = $db->prepare("INSERT INTO drop_events (user_id, machine_id, item_type_id, est_weight_kg, est_volume_l, note, status)
                         VALUES (?, ?, ?, ?, ?, ?, 'accepted')");
    $ins->execute([$user['id'], $m['id'], $item_type_id, $est_w, $est_v, $note]);

    // Update machine totals
    $upd = $db->prepare("UPDATE machines SET current_weight_kg=?, current_volume_l=? WHERE id=?");
    $upd->execute([$will_w, $will_v, $m['id']]);

    $db->commit();
    header('Location: ../views/recycler-history.php?msg='.urlencode('Drop accepted and recorded'));
  } else {
    $ins = $db->prepare("INSERT INTO drop_events (user_id, machine_id, item_type_id, est_weight_kg, est_volume_l, note, status, reason)
                         VALUES (?, ?, ?, ?, ?, ?, 'rejected', 'invalid')");
    $ins->execute([$user['id'], $m['id'], $item_type_id, $est_w, $est_v, $note]);
    $db->commit();
    header('Location: ../views/recycler-history.php?msg='.urlencode('Drop rejected by machine'));
  }
} catch (Throwable $e) {
  $db->rollBack();
  http_response_code(500);
  echo "Error: ".$e->getMessage();
}
