<?php
$db = require __DIR__ . '/config/db.php';
$pdo = new PDO($db['dsn'], $db['username'], $db['password']);

$stmt = $pdo->query("SELECT * FROM sislap_revenuereport LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$columns = array_keys($row);
file_put_contents('schema_sislap_revenuereport.json', json_encode($columns, JSON_PRETTY_PRINT));
echo "Schema written to schema_sislap_revenuereport.json\n";
