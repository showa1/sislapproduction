<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Dashboard Laboratorium';
$this->registerJsFile(Yii::$app->request->baseUrl . '/template/vendors/chart.js/chart.umd.js', ['position' => \yii\web\View::POS_HEAD]);

$this->registerCss("
    body, .content-wrapper { background-color: #F4F7FE !important; }
    .card-kpi {
        border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,45,114,0.05);
        background: #ffffff; padding: 20px; transition: transform 0.2s ease; height: 100%; cursor: pointer;
    }
    .card-kpi:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,45,114,0.1); }
    .kpi-title { font-family: 'Inter', sans-serif; font-size: 0.78rem; color: #718096; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-value { font-family: 'Inter', sans-serif; font-size: 1.7rem; color: #002D72; font-weight: 800; margin-top: 5px; }
    .kpi-value.sm { font-size: 1.5rem; }
    .kpi-sub  { font-size: 0.75rem; color: #a0aec0; margin-top: 2px; }
    .kpi-icon-box { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; font-size:1.2rem; }
    
    .card-blue  .kpi-icon-box { background:rgba(0,123,255,.12); color:#007bff; }
    .card-green .kpi-icon-box { background:rgba(40,167,69,.12); color:#28a745; }
    .card-yellow .kpi-icon-box { background:rgba(255,193,7,.15); color:#d97706; }
    .card-red   .kpi-icon-box { background:rgba(220,53,69,.12); color:#dc3545; }
    .card-purple .kpi-icon-box { background:rgba(102,16,242,.12); color:#6610f2; }
    .card-cyan .kpi-icon-box { background:rgba(23,162,184,.12); color:#17a2b8; }

    .section-card { border:none; border-radius:15px; box-shadow:0 4px 20px rgba(0,0,0,0.03); background:#fff; padding:22px; margin-bottom:24px; height:100%; }
    .section-title { font-family:'Inter',sans-serif; font-size:1rem; color:#2d3748; font-weight:700; margin-bottom:18px; }
    .section-subtitle { font-size:0.8rem; color:#a0aec0; font-weight:500; }

    .badge-growth { font-size:0.78rem; font-weight:700; display:inline-flex; align-items:center; gap:3px; }
    .badge-growth.up { color:#28a745; } .badge-growth.down { color:#dc3545; } .badge-growth.neutral { color:#6c757d; }

    .tbl { width:100%; border-collapse:collapse; }
    .tbl th { text-align:left; padding:10px 12px; border-bottom:2px solid #e2e8f0; color:#a0aec0; font-weight:600; font-size:0.78rem; text-transform:uppercase; }
    .tbl td { padding:12px; border-bottom:1px solid #f0f4f8; color:#4a5568; font-size:0.9rem; }
    .tbl tr:last-child td { border-bottom:none; }

    .activity-badge { display:inline-block; padding:3px 8px; border-radius:20px; font-size:0.72rem; font-weight:700; }
    .badge-in  { background:#dcfce7; color:#16a34a; }
    .badge-out { background:#fee2e2; color:#dc2626; }
    .badge-warn { background:#fef3c7; color:#d97706; }

    .progress-mini { height:6px; border-radius:3px; background:#e2e8f0; margin-top:5px; }
    .progress-mini .fill { height:100%; border-radius:3px; }

    .row-label { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#a0aec0; margin-bottom:10px; padding-left:4px; }
    .border-top-section { border-top:2px solid #f0f4f8; margin-top:8px; padding-top:18px; }
    
    .filter-btn {
        background: #fff; border: 1px solid #e2e8f0; color: #4a5568; font-size: 0.85rem; font-weight: 600;
        padding: 5px 12px; border-radius: 6px; transition: all 0.2s;
    }
    .filter-btn:hover, .filter-btn.active { background: #002D72; color: #fff; border-color: #002D72; }
");

function growthBadge($v, $invertSla = false) {
    if ($v > 0) {
        $colorClass = $invertSla ? 'down' : 'up';
        return "<span class='badge-growth {$colorClass}'><i class='bi bi-arrow-up-right-circle-fill'></i> +" . $v . "%</span>";
    }
    if ($v < 0) {
        $colorClass = $invertSla ? 'up' : 'down';
        return "<span class='badge-growth {$colorClass}'><i class='bi bi-arrow-down-right-circle-fill'></i> " . $v . "%</span>";
    }
    return "<span class='badge-growth neutral'><i class='bi bi-dash-circle-fill'></i> 0%</span>";
}
function rp($n) { return 'Rp '.number_format($n, 0, ',', '.'); }
function num($n) { return number_format((float)$n, 0, ',', '.'); }

?>

<div class="row mb-3 align-items-center">
    <div class="col-md-6">
        <h3 style="color:#002D72;font-weight:800;font-family:'Inter',sans-serif;margin-bottom:3px;">Dashboard Laboratorium</h3>
        <p class="text-muted mb-0" style="font-size:0.88rem;">Data Real-time · Periode: Bulan <?= date('F Y', mktime(0,0,0,$bulanIni,10,$tahunIni)) ?></p>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0 d-flex justify-content-md-end gap-2">
        <button class="filter-btn active">Bulan Ini</button>
        <button class="filter-btn">Bulan Lalu</button>
        <input type="date" class="form-control d-inline-block w-auto" style="font-size:0.85rem; padding:4px 10px; height:auto; border-radius:6px;" value="<?= date('Y-m-d') ?>">
    </div>
</div>

<!-- ==================== ROW 1: KPI Cards ==================== -->
<div class="row-label">Performa Layanan Laboratorium</div>
<div class="row mb-3">
    <!-- Total Pemeriksaan -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Total Pemeriksaan <span class="badge bg-secondary" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Simulasi</span></div>
                <div class="kpi-icon-box"><i class="bi bi-droplet-fill"></i></div>
            </div>
            <div class="kpi-value text-primary"><?= num($kpi['total_pemeriksaan']) ?> <span style="font-size:1rem;color:#718096;font-weight:500;">Sampel</span></div>
            <div class="mt-2"><?= growthBadge($kpi['pertumbuhan_pemeriksaan']) ?> <span class="kpi-sub">vs bulan lalu</span></div>
        </div>
    </div>
    
    <!-- Rata-rata TAT -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi <?= $kpi['sla_terpenuhi'] ? 'card-green' : 'card-red' ?>">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Rata-rata TAT <span class="badge bg-secondary" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Simulasi</span></div>
                <div class="kpi-icon-box"><i class="bi bi-stopwatch"></i></div>
            </div>
            <div class="kpi-value <?= $kpi['sla_terpenuhi'] ? 'text-success' : 'text-danger' ?>"><?= $kpi['rata_tat'] ?> <span style="font-size:1rem;color:#718096;font-weight:500;">Menit</span></div>
            <div class="mt-2 text-truncate">
                <?= growthBadge($kpi['pertumbuhan_tat'], true) ?> <span class="kpi-sub">vs bulan lalu</span>
                <?= $kpi['sla_terpenuhi'] ? '<span class="ms-1 badge bg-success" style="font-size:0.6rem;">SLA OK</span>' : '<span class="ms-1 badge bg-danger" style="font-size:0.6rem;">OVER SLA</span>' ?>
            </div>
        </div>
    </div>

    <!-- Pendapatan Lab -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-cyan">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Pendapatan Lab <span class="badge bg-secondary" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Simulasi</span></div>
                <div class="kpi-icon-box"><i class="bi bi-cash-stack"></i></div>
            </div>
            <div class="kpi-value kpi-value sm text-info"><?= rp($kpi['pendapatan_lab']) ?></div>
            <div class="mt-2"><?= growthBadge($kpi['pertumbuhan_pendapatan']) ?> <span class="kpi-sub">vs bulan lalu</span></div>
        </div>
    </div>

    <!-- Jenis Tes Terbanyak -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-purple">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Tes Terbanyak <span class="badge bg-secondary" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Simulasi</span></div>
                <div class="kpi-icon-box"><i class="bi bi-bar-chart-fill"></i></div>
            </div>
            <div class="kpi-value text-truncate" style="color:#6610f2; font-size:1.3rem; margin-top:12px;" title="<?= $kpi['tes_terbanyak_nama'] ?>"><?= $kpi['tes_terbanyak_nama'] ?></div>
            <div class="kpi-sub mt-2"><strong><?= num($kpi['tes_terbanyak_qty']) ?></strong> tes dilakukan (<?= growthBadge($kpi['pertumbuhan_tes']) ?>)</div>
        </div>
    </div>
</div>

<!-- ==================== ROW 2: Charts ==================== -->
<div class="row-label border-top-section">Visualisasi Tren & Utilitas</div>
<div class="row">
    <!-- Line Chart TAT -->
    <div class="col-md-7 mb-4">
        <div class="section-card">
            <div class="section-title">Tren TAT Harian <span class="section-subtitle float-end">Rata-rata Menit Penyelesaian</span></div>
            <div style="height:320px;"><canvas id="trendTATChart"></canvas></div>
        </div>
    </div>
    <!-- Combo Chart Kapasitas -->
    <div class="col-md-5 mb-4">
        <div class="section-card">
            <div class="section-title">Volume vs Kapasitas Alkes <span class="section-subtitle float-end">7 Hari Terakhir</span></div>
            <div style="height:320px;display:flex;align-items:center;justify-content:center;">
                <canvas id="comboChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ==================== ROW 3: Tables ==================== -->
<div class="row-label border-top-section">Performa Unit & Logistik</div>
<div class="row">
    <!-- Top Dokter -->
    <div class="col-md-6 mb-4">
        <div class="section-card" style="overflow-y:auto;max-height:420px;">
            <div class="section-title">Top 5 Dokter Perujuk Terbanyak <span class="section-subtitle float-end">Bulan Ini</span></div>
            <table class="tbl">
                <thead><tr><th>Nama Dokter</th><th>Poli/Spesialis</th><th style="text-align:right;">Jumlah Rujukan</th></tr></thead>
                <tbody>
                    <?php foreach ($topDokter as $idx => $dok):
                        $max = (int)($topDokter[0]['jumlah'] ?: 1);
                        $pct = round(((int)$dok['jumlah'] / $max) * 100);
                        $color = $idx < 3 ? '#007bff' : '#94a3b8';
                    ?>
                    <tr>
                        <td>
                            <strong><?= Html::encode($dok['nama']) ?></strong>
                            <div class="progress-mini"><div class="fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div></div>
                        </td>
                        <td style="color:#718096;font-size:0.8rem;"><?= Html::encode($dok['spesialis']) ?></td>
                        <td style="text-align:right;font-weight:700;color:#2d3748;"><?= num($dok['jumlah']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Stok Kritis -->
    <div class="col-md-6 mb-4">
        <div class="section-card">
            <div class="section-title">Status Stok Reagen Kritis <span class="section-subtitle float-end">Perhatian</span></div>
            <table class="tbl">
                <thead><tr><th>Reagen</th><th>Kategori</th><th style="text-align:center;">Stok / Min</th><th style="text-align:center;">Status</th></tr></thead>
                <tbody>
                    <?php foreach ($stokKritis as $stok): 
                        $statusBadge = '';
                        if ($stok['status'] == 'Sangat Kritis') $statusBadge = '<span class="activity-badge badge-out">Sangat Kritis</span>';
                        else if ($stok['status'] == 'Kritis') $statusBadge = '<span class="activity-badge badge-out" style="background:#fecdd3;color:#e11d48">Kritis</span>';
                        else $statusBadge = '<span class="activity-badge badge-warn">Perhatian</span>';
                    ?>
                    <tr>
                        <td><strong><?= Html::encode($stok['reagen']) ?></strong></td>
                        <td style="color:#718096;font-size:0.8rem;"><?= Html::encode($stok['kategori']) ?></td>
                        <td style="text-align:center;font-weight:700;">
                            <span style="color:<?= $stok['status']=='Sangat Kritis'?'#dc2626':'#d97706' ?>"><?= $stok['stok'] ?></span> / <span style="color:#a0aec0;font-weight:500;"><?= $stok['minimal'] ?></span>
                        </td>
                        <td style="text-align:center;"><?= $statusBadge ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================= JAVASCRIPT ========================= -->
<?php
$trendDatesJson  = json_encode($trendDates);
$trendTATJson    = json_encode($trendTAT);

$volDates = ["H-6", "H-5", "H-4", "H-3", "H-2", "Kemarin", "Hari Ini"];
$volDatesJson = json_encode($volDates);
$volDataJson = json_encode($volumeData);
$kapasitasJson = json_encode($kapasitasData);

$this->registerJs("
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#718096';

// --- Trend TAT Line Chart ---
const ctxTAT = document.getElementById('trendTATChart').getContext('2d');
let gradTAT = ctxTAT.createLinearGradient(0, 0, 0, 300);
gradTAT.addColorStop(0, 'rgba(40,167,69,0.3)');
gradTAT.addColorStop(1, 'rgba(40,167,69,0)');
new Chart(ctxTAT, {
    type: 'line',
    data: {
        labels: $trendDatesJson,
        datasets: [{
            label: 'Rata-rata TAT (Menit)',
            data: $trendTATJson,
            borderColor: '#28a745',
            backgroundColor: gradTAT,
            tension: 0.4, fill: true, borderWidth: 3,
            pointRadius: 4, pointBackgroundColor: '#fff', pointBorderWidth: 2, pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: {weight:'600'} } }, 
            tooltip: { mode: 'index', intersect: false } 
        },
        scales: { 
            y: { 
                beginAtZero: true, 
                grid: { borderDash:[4,4], color:'#e2e8f0', drawBorder:false },
                ticks: { stepSize: 15 } 
            }, 
            x: { grid: { display: false } } 
        }
    }
});

// --- Volume vs kapasitas Combo Chart ---
const ctxCombo = document.getElementById('comboChart').getContext('2d');
new Chart(ctxCombo, {
    type: 'bar', // base type is bar
    data: {
        labels: $volDatesJson,
        datasets: [
            {
                type: 'line',
                label: 'Kapasitas Maksimal',
                data: $kapasitasJson,
                borderColor: '#dc3545',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                pointRadius: 0
            },
            {
                type: 'bar',
                label: 'Volume Tes Dilakukan',
                data: $volDataJson,
                backgroundColor: 'rgba(0,123,255,0.85)',
                borderRadius: 4,
                barPercentage: 0.6
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 10, font: {size:11} } },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: { 
            y: { beginAtZero: true, grid: { borderDash:[4,4], color:'#e2e8f0' } },
            x: { grid: { display: false } }
        }
    }
});
");
?>
