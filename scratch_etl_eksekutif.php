<?php
/**
 * ETL: Sinkronisasi data dari PostgreSQL ke SQLite dashboard.db
 * Jalankan: php scratch_etl_eksekutif.php [--from=YYYY-MM-DD] [--to=YYYY-MM-DD]
 */
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);

$pgDb = Yii::$app->db;
$sqDb = Yii::$app->db_dashboard;

$opts     = getopt("", ["from:", "to:"]);
$dateFrom = $opts["from"] ?? date("Y-m-01", strtotime("-2 months"));
$dateTo   = $opts["to"]   ?? date("Y-m-d");

echo "=== ETL Eksekutif Dashboard ===\n";
echo "Periode: {$dateFrom} s/d {$dateTo}\n\n";

// ---- Mapping instalasi_id -> unit_bisnis ----
$instalasiMap = [
    2  => "Rajal",
    3  => "IGD",
    4  => "Ranap",
    5  => "Laboratorium",
    6  => "Radiologi",
    7  => "IBS",
    9  => "Farmasi",
    20 => "ICU/PICU",
    38 => "Ranap",
    72 => "Fisioterapi",
    73 => "Hemodialisa",
    74 => "Fisioterapi",
    76 => "ICU/PICU",
    81 => "IBS",
    82 => "MCU",
];

function mapCaraBayar(string $cb): string {
    $cb = strtoupper(trim($cb));
    if (strpos($cb, "BPJS KES") !== false) return "BPJS Kesehatan";
    if (strpos($cb, "JASA RAH") !== false) return "Jasa Raharja";
    if (strpos($cb, "ASURANSI") !== false) return "Asuransi Komersial";
    return "Umum";
}

function jamSlot(string $jamNum): string {
    $h = (int)$jamNum;
    if ($h < 7)  return "07:00";
    if ($h > 21) return "21:00";
    return str_pad($h, 2, "0", STR_PAD_LEFT) . ":00";
}

$hariMap = [
    "MONDAY"    => "Senin",   "TUESDAY"  => "Selasa",
    "WEDNESDAY" => "Rabu",    "THURSDAY" => "Kamis",
    "FRIDAY"    => "Jumat",   "SATURDAY" => "Sabtu",
    "SUNDAY"    => "Minggu",
];

$insIds = implode(",", array_keys($instalasiMap));

echo "Menarik data dari PostgreSQL...\n";
try {
    $rows = $pgDb->createCommand("
        SELECT
            p.tgl_pendaftaran::date                 AS tanggal,
            rm.instalasi_id,
            COALESCE(cb.carabayar_nama, 'PRIBADI')  AS cara_bayar,
            CASE WHEN p.kunjungan = 'Baru' THEN 'Baru' ELSE 'Lama' END AS jenis_pasien,
            TRIM(TO_CHAR(p.tgl_pendaftaran, 'Day')) AS hari_raw,
            TO_CHAR(p.tgl_pendaftaran, 'HH24')      AS jam_num,
            COALESCE(tp.total_pendapatan, 0)         AS pendapatan
        FROM pendaftaran_t p
        JOIN ruangan_m rm ON rm.ruangan_id = p.ruangan_id
        LEFT JOIN carabayar_m cb ON cb.carabayar_id = p.carabayar_id
        LEFT JOIN (
            SELECT pendaftaran_id, SUM(total_tarifakhir) AS total_pendapatan
            FROM tindakanpelayanan_t GROUP BY pendaftaran_id
        ) tp ON tp.pendaftaran_id = p.pendaftaran_id
        WHERE p.tgl_pendaftaran::date BETWEEN :df AND :dt
          AND p.pasienbatalperiksa_id IS NULL
          AND rm.instalasi_id IN ({$insIds})
        ORDER BY p.tgl_pendaftaran
    ", [":df" => $dateFrom, ":dt" => $dateTo])->queryAll();
} catch (Exception $e) {
    echo "ERROR query: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Ditemukan " . count($rows) . " pendaftaran.\n";
if (empty($rows)) { echo "Tidak ada data. Selesai.\n"; exit; }

// ---- Agregasi ----
$agg = [];
foreach ($rows as $row) {
    $insId = (int)$row["instalasi_id"];
    if (!isset($instalasiMap[$insId])) continue;

    $unit    = $instalasiMap[$insId];
    $cb      = mapCaraBayar((string)$row["cara_bayar"]);
    $jenis   = $row["jenis_pasien"];
    $tanggal = $row["tanggal"];
    $hari    = $hariMap[strtoupper($row["hari_raw"])] ?? "Senin";
    $jam     = jamSlot($row["jam_num"]);
    $pend    = (float)$row["pendapatan"];

    $key = "{$tanggal}|{$unit}|{$cb}|{$jenis}|{$hari}|{$jam}";
    if (!isset($agg[$key])) {
        $agg[$key] = ["tanggal"=>$tanggal,"unit"=>$unit,"cb"=>$cb,"jenis"=>$jenis,
                      "hari"=>$hari,"jam"=>$jam,"pasien"=>0,"pendapatan"=>0.0];
    }
    $agg[$key]["pasien"]++;
    $agg[$key]["pendapatan"] += $pend;
}

echo "Agregasi: " . count($agg) . " baris unik.\n";

// ---- Hapus periode lama di SQLite ----
$sqDb->createCommand("DELETE FROM transaksi_layanan WHERE tanggal BETWEEN :df AND :dt",
    [":df" => $dateFrom, ":dt" => $dateTo])->execute();
echo "Data lama dihapus.\n";

// ---- Insert baru ----
$stmt = $sqDb->pdo->prepare("
    INSERT INTO transaksi_layanan (tanggal, unit_bisnis, cara_bayar, jenis_pasien, hari, jam, jumlah_pasien, pendapatan)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$sqDb->pdo->beginTransaction();
foreach ($agg as $r) {
    $stmt->execute([$r["tanggal"],$r["unit"],$r["cb"],$r["jenis"],
                    $r["hari"],$r["jam"],(int)$r["pasien"],round($r["pendapatan"],2)]);
}
$sqDb->pdo->commit();
echo "Insert " . count($agg) . " rows selesai.\n\n";

// ---- Verifikasi ----
$verify = $sqDb->createCommand("
    SELECT strftime('%Y-%m', tanggal) as bln, SUM(jumlah_pasien) as pasien, COUNT(*) as rows
    FROM transaksi_layanan WHERE tanggal BETWEEN :df AND :dt
    GROUP BY bln ORDER BY bln
", [":df" => $dateFrom, ":dt" => $dateTo])->queryAll();

echo "=== Verifikasi ===\n";
foreach ($verify as $v) echo "  {$v['bln']}: {$v['rows']} rows, {$v['pasien']} pasien\n";
echo "\nETL selesai!\n";
