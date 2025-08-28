<?php
// init_db.php — one-time DB bootstrap for Wastech (vanilla PHP + MySQL)
// Run via: CLI -> php init_db.php   OR   Browser -> http://localhost/wastech/init_db.php
// Delete this file after success. Stay safe. 💾

header('Content-Type: text/plain; charset=utf-8');

const DB_HOST = '127.0.0.1';
const DB_NAME = 'wastech';
const DB_USER = 'root';
const DB_PASS = ''; // ← change if needed

$root = __DIR__;
$sqlSchema = $root . '/sql/001_schema.sql';
$sqlSeedItems = $root . '/sql/010_seed_item_types.sql';

try {
  // 1) Connect to server (no DB), create DB if missing
  $serverDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
  $server = new PDO($serverDsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  $server->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
  echo "✓ Database ensured: " . DB_NAME . PHP_EOL;

  // 2) Connect to DB
  $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
  $db = new PDO($dsn, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  // Helpers
  $tableExists = function (PDO $db, string $table): bool {
    $q = $db->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema=? AND table_name=? LIMIT 1");
    $q->execute([DB_NAME, $table]);
    return (bool)$q->fetch();
  };

  $countTable = function (PDO $db, string $table): int {
    $r = $db->query("SELECT COUNT(*) AS c FROM `$table`")->fetch();
    return (int)($r['c'] ?? 0);
  };

  $execFile = function (PDO $db, string $path) {
    if (!is_file($path)) throw new RuntimeException("Missing SQL file: $path");
    $sql = trim(file_get_contents($path));
    if ($sql === '') return;
    // PDO::exec can run multi-statements in MySQL; this is fine for bootstrap.
    $db->exec($sql);
  };

  // 3) Apply schema if tables not present
  if (!$tableExists($db, 'users')) {
    $execFile($db, $sqlSchema);
    echo "✓ Schema applied from sql/001_schema.sql" . PHP_EOL;
  } else {
    echo "• Schema tables already exist — skipping." . PHP_EOL;
  }

  // 4) Seed item_types
  if ($tableExists($db, 'item_types') && $countTable($db, 'item_types') === 0) {
    $execFile($db, $sqlSeedItems);
    echo "✓ Seeded item_types (and sample machines)" . PHP_EOL;
  } else {
    echo "• item_types already seeded — skipping." . PHP_EOL;
  }

  // 5) Ensure at least one Collector user
  $collectorEmail = 'collector@wastech.local';
  $q = $db->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
  $q->execute([$collectorEmail]);
  if (!$q->fetch()) {
    $hash = password_hash('collector123', PASSWORD_DEFAULT);
    $ins = $db->prepare("INSERT INTO users (name,email,phone,role,password_hash) VALUES (?,?,?,?,?)");
    $ins->execute(['Collector One', $collectorEmail, null, 'collector', $hash]);
    echo "✓ Created default collector account: {$collectorEmail} / collector123" . PHP_EOL;
  } else {
    echo "• Collector user already exists — skipping." . PHP_EOL;
  }

  // 6) Ensure uploads/ dir
  $uploadsDir = $root . '/uploads';
  if (!is_dir($uploadsDir)) {
    if (!mkdir($uploadsDir, 0775, true) && !is_dir($uploadsDir)) {
      throw new RuntimeException("Failed to create uploads dir: $uploadsDir");
    }
  }
  echo "✓ uploads/ directory ready" . PHP_EOL;

  echo PHP_EOL . "🚀 All set. You can log in now:" . PHP_EOL;
  echo "   Recycler -> create via Register form" . PHP_EOL;
  echo "   Collector -> {$collectorEmail} / collector123" . PHP_EOL;
  echo PHP_EOL . "➡️  Open: http://localhost/wastech/public" . PHP_EOL;
  echo "⚠️  Security: delete init_db.php after bootstrap." . PHP_EOL;

} catch (Throwable $e) {
  http_response_code(500);
  echo "❌ Init error: " . $e->getMessage() . PHP_EOL;
  echo "Tip: Check MySQL creds in init_db.php and /lib/db.php" . PHP_EOL;
  exit(1);
}
