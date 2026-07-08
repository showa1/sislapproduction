<?php
$db = new PDO("sqlite:" . __DIR__ . "/runtime/dashboard.db");
$r = $db->query("SELECT MIN(tanggal) as min_tgl, MAX(tanggal) as max_tgl, COUNT(*) as total FROM transaksi_layanan")->fetch(PDO::FETCH_ASSOC);
echo "Data dari  : " . $r["min_tgl"] . PHP_EOL;
echo "Sampai     : " . $r["max_tgl"] . PHP_EOL;
echo "Total rows : " . $r["total"] . PHP_EOL;
echo PHP_EOL . "--- Per bulan ---" . PHP_EOL;
$rows = $db->query("SELECT strftime(\"%Y-%m\", tanggal) as bln, COUNT(*) as n FROM transaksi_layanan GROUP BY bln ORDER BY bln")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) { echo $row["bln"] . ": " . $row["n"] . " rows" . PHP_EOL; }
