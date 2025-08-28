<?php
require_once __DIR__.'/../../lib/auth.php';
require_once __DIR__.'/../../lib/db.php';
require_once __DIR__.'/../../lib/helpers.php';
require_login(); require_role('collector');
verify_csrf($_POST['csrf'] ?? null);

$collector = current_user();
$machine_id = (int)($_POST['machine_id'] ?? 0);
$total_w = (float)($_POST['total_weight_kg'] ?? 0);
$total_v = (float)($_POST['total_volume_l'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

$photoPath = null;
if (!empty($_FILES['photo']['name'])) {
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  $type = mime_content_type($_FILES['photo']['tmp_name']);
  if (isset($allowed[$type]) && $_FILES['photo']['size'] <= 5*1024*1024) {
    $ext = $allowed[$type];
    $dir = __DIR__.'/../../uploads';
    if (!is_dir($dir)) mkdir($dir,0775,true);
    $name = 'pickup_'.time().'_'.bin2hex(random_bytes(6)).'.'.$ext;
    $dest = $dir.'/'.$name;
    move_uploaded_file($_FILES['photo']['tmp_name'], $dest);
    $photoPath = '/wastech/uploads/'.$name;
  }
}

$db = db();
$db->beginTransaction();
try {
  // Insert pickup
  $ins = $db->prepare("INSERT INTO pickups (machine_id, collector_id, total_weight_kg, total_volume_l, photo_path, notes)
                       VALUES (?, ?, ?, ?, ?, ?)");
  $ins->execute([$machine_id, $collector['id'], $total_w, $total_v, $photoPath, $notes]);

  // Reset machine totals
  $upd = $db->prepare("UPDATE machines SET current_weight_kg=0, current_volume_l=0, last_pickup_at=NOW() WHERE id=?");
  $upd->execute([$machine_id]);

  $db->commit();
  header('Location: ../views/collector-machine-detail.php?machine_id='.$machine_id.'&msg='.urlencode('Pickup recorded and bin reset'));
} catch (Throwable $e) {
  $db->rollBack();
  http_response_code(500);
  echo "Error: ".$e->getMessage();
}
