<?php
require __DIR__ . "/vendor/autoload.php";
require __DIR__ . "/vendor/yiisoft/yii2/Yii.php";
$config = require __DIR__ . "/config/web.php";
new yii\web\Application($config);
$db = Yii::$app->db;
$df = "2026-07-01"; $dt = "2026-07-08";

try {
    $c = $db->createCommand("
        SELECT SUM(tp.tarif_tindakan)
            FROM pasienmasukpenunjang_t pp
            JOIN pendaftaran_t pd ON pd.pendaftaran_id = pp.pendaftaran_id
            JOIN pasien_m v ON v.pasien_id = pp.pasien_id
            JOIN carabayar_m cb ON cb.carabayar_id = pd.carabayar_id
            JOIN penjaminpasien_m penj ON penj.penjamin_id = pd.penjamin_id
            JOIN ruangan_m r_lab ON r_lab.ruangan_id = pp.ruangan_id
            LEFT JOIN rujukan_t r ON r.rujukan_id = pd.rujukan_id
            LEFT JOIN asalrujukan_m ar ON ar.asalrujukan_id = r.asalrujukan_id
            LEFT JOIN tindakanpelayanan_t tp ON tp.pasienmasukpenunjang_id = pp.pasienmasukpenunjang_id
            LEFT JOIN daftartindakan_m dt ON dt.daftartindakan_id = tp.daftartindakan_id
            
            LEFT JOIN (
                SELECT pl.daftartindakan_id, MIN(jp.jenispemeriksaanlab_nama) as jenispemeriksaanlab_nama
                FROM pemeriksaanlab_m pl
                JOIN jenispemeriksaanlab_m jp ON jp.jenispemeriksaanlab_id = pl.jenispemeriksaanlab_id
                GROUP BY pl.daftartindakan_id
            ) jp ON jp.daftartindakan_id = tp.daftartindakan_id

            LEFT JOIN pegawai_m peg ON peg.pegawai_id = pp.pegawai_id
            LEFT JOIN gelarbelakang_m gb ON gb.gelarbelakang_id = peg.gelarbelakang_id
            LEFT JOIN kelaspelayanan_m kp ON kp.kelaspelayanan_id = pp.kelaspelayanan_id
            LEFT JOIN asuransipasien_m asur ON asur.asuransipasien_id = pd.asuransipasien_id
            WHERE r_lab.ruangan_nama ILIKE '%laborat%'
        AND pp.tglmasukpenunjang::date BETWEEN :df AND :dt
        AND pd.pasienbatalperiksa_id IS NULL
    ", [":df"=>$df,":dt"=>$dt])->queryScalar();
    echo "Total Tarif Lab Fixed: " . $c . "\n";
} catch(Exception $e) { echo $e->getMessage(); }
