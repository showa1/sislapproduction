<?php
/**
 * Seed data dummy Juli 2026 ke dashboard.db
 * Menyalin pola distribusi dari Juni 2026 dengan sedikit variasi acak
 */
$db = new PDO("sqlite:" . __DIR__ . "/runtime/dashboard.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ambil semua kombinasi unit_bisnis + cara_bayar + jenis_pasien yang ada di Juni 2026
$rows = $db->query("
    SELECT unit_bisnis, cara_bayar, jenis_pasien, hari, jam,
           ROUND(AVG(jumlah_pasien)) as avg_pasien,
           ROUND(AVG(pendapatan)) as avg_pendapatan
    FROM transaksi_layanan
    WHERE tanggal BETWEEN '2026-06-01' AND '2026-06-30'
    GROUP BY unit_bisnis, cara_bayar, jenis_pasien, hari, jam
")->fetchAll(PDO::FETCH_ASSOC);

$days = [
    1  => "Selasa",  2  => "Rabu",   3  => "Kamis",  4  => "Jumat",
    5  => "Sabtu",   6  => "Minggu", 7  => "Senin",   8  => "Selasa",
    9  => "Rabu",    10 => "Kamis",  11 => "Jumat",   12 => "Sabtu",
    13 => "Minggu",  14 => "Senin",  15 => "Selasa",  16 => "Rabu",
    17 => "Kamis",   18 => "Jumat",  19 => "Sabtu",   20 => "Minggu",
    21 => "Senin",   22 => "Selasa", 23 => "Rabu",    24 => "Kamis",
    25 => "Jumat",   26 => "Sabtu",  27 => "Minggu",  28 => "Senin",
    29 => "Selasa",  30 => "Rabu",   31 => "Kamis",
];

echo "Seeding Juli 2026...\n";
$stmt = $db->prepare("INSERT OR IGNORE INTO transaksi_layanan
    (tanggal, unit_bisnis, cara_bayar, jenis_pasien, hari, jam, jumlah_pasien, pendapatan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$db->beginTransaction();
$inserted = 0;

// Hanya isi hari kerja (bukan Sabtu/Minggu untuk pola realistis)
// Tapi tetap isi semua hari sesuai pola
foreach ($days as $day => $hariNama) {
    $tanggal = sprintf("2026-07-%02d", $day);
    
    // Cari baris yang hari-nya cocok
    foreach ($rows as $row) {
        if ($row["hari"] !== $hariNama) continue;
        
        // Variasi ±15% dari rata-rata Juni
        $variance = 1 + (rand(-15, 20) / 100);
        $pasien = max(0, (int)round($row["avg_pasien"] * $variance));
        $pendapatan = max(0, (int)round($row["avg_pendapatan"] * $variance));
        
        if ($pasien <= 0) continue;
        
        $stmt->execute([
            $tanggal,
            $row["unit_bisnis"],
            $row["cara_bayar"],
            $row["jenis_pasien"],
            $row["hari"],
            $row["jam"],
            $pasien,
            $pendapatan
        ]);
        $inserted++;
    }
}

$db->commit();
echo "Selesai! Inserted {$inserted} rows untuk Juli 2026.\n";

// Verifikasi
$check = $db->query("SELECT COUNT(*) as n, SUM(jumlah_pasien) as total_pasien FROM transaksi_layanan WHERE tanggal BETWEEN '2026-07-01' AND '2026-07-31'")->fetch(PDO::FETCH_ASSOC);
echo "Juli 2026: {$check['n']} rows, total pasien: {$check['total_pasien']}\n";
