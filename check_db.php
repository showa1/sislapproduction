<?php
$db = require __DIR__ . '/config/db.php';
$pdo = new PDO($db['dsn'], $db['username'], $db['password']);
$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'sislap_revenuereport'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
