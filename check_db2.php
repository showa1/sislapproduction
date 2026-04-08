<?php
$db = require __DIR__ . '/config/db.php';
$pdo = new PDO($db['dsn'], $db['username'], $db['password']);

// We will fetch one row from the sislap_revenuereport view to see its array keys
$stmt = $pdo->query("SELECT * FROM sislap_revenuereport LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo "Columns available in sislap_revenuereport:\n";
    foreach (array_keys($row) as $key) {
        echo "- $key\n";
    }
} else {
    echo "No rows found in sislap_revenuereport.\n";
}
