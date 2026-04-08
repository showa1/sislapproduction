<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Sistem Informasi Eksekutif';

// Register Chart.js and Moment.js/Adapter for advanced time/heatmap if needed (but we will just use basic Chart.js)
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

// Additional plugin for heatmap (Matrix) or we can just build a simple HTML table heatmap
$this->registerCss("
    body { background-color: #F4F7FE; }
    .content-wrapper { background-color: #F4F7FE !important; }
    
    .card-kpi {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 45, 114, 0.05);
        background: #ffffff;
        padding: 20px;
        transition: transform 0.2s ease;
        height: 100%;
    }
    .card-kpi:hover { transform: translateY(-3px); }
    
    .kpi-title { font-family: 'Inter', sans-serif; font-size: 0.85rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-value { font-family: 'Inter', sans-serif; font-size: 1.8rem; color: #002D72; font-weight: 800; margin-top: 5px; }
    
    .card-rj .kpi-icon-box { background: rgba(0, 123, 255, 0.15); color: #007bff; }
    .card-igd .kpi-icon-box { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
    .card-ri .kpi-icon-box { background: rgba(40, 167, 69, 0.15); color: #28a745; }
    .card-lab .kpi-icon-box { background: rgba(111, 66, 193, 0.15); color: #6f42c1; }
    .card-rad .kpi-icon-box { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }

    .card-chart {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        background: #ffffff;
        padding: 20px;
        margin-bottom: 24px;
        height: 100%;
    }
    .chart-title { font-family: 'Inter', sans-serif; font-size: 1.1rem; color: #2d3748; font-weight: 700; margin-bottom: 20px; }

    .table-minimal { width: 100%; border-collapse: collapse; }
    .table-minimal th { 
        text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; 
        color: #a0aec0; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; 
    }
    .table-minimal td { 
        padding: 15px 12px; border-bottom: 1px solid #edf2f7; 
        color: #4a5568; font-size: 0.95rem; font-weight: 500;
    }
    .table-minimal tr:last-child td { border-bottom: none; }
    
    .badge-growth {
        font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;
    }
    .badge-growth.up { color: #28a745; }
    .badge-growth.down { color: #dc3545; }
    .badge-growth.neutral { color: #6c757d; }
    
    .heatmap-cell {
        min-width: 30px; height: 30px; border-radius: 4px; border: 1px solid rgba(0,0,0,0.05); text-align: center;
        font-size: 0.7rem; color: transparent; transition: all 0.2s ease; cursor: crosshair;
    }
    .heatmap-cell:hover { color: #fff; transform: scale(1.1); font-weight: bold; border-color: rgba(0,0,0,0.2); }
    .table-heatmap { width: 100%; font-size: 0.8rem; }
    .table-heatmap th, .table-heatmap td { padding: 2px; text-align: center; border: none; }
    .table-heatmap th { color: #a0aec0; font-weight: 500; }
    .table-heatmap td.hour-label { font-weight: 600; color: #718096; text-align: right; padding-right: 10px; width: 40px;}
");

// Helper function to render growth
function renderGrowth($value) {
    if ($value > 0) return "<span class='badge-growth up'><i class='bi bi-arrow-up-right'></i> +{$value}%</span>";
    if ($value < 0) return "<span class='badge-growth down'><i class='bi bi-arrow-down-right'></i> {$value}%</span>";
    return "<span class='badge-growth neutral'><i class='bi bi-dash'></i> 0%</span>";
}
function renderGrowthRev($value) { // For times when lower is better, although usually count higher is better for revenue, but maybe we just use standard.
    if ($value > 0) return "<span class='badge-growth up'><i class='bi bi-arrow-up-right'></i> +{$value}%</span>";
    if ($value < 0) return "<span class='badge-growth down'><i class='bi bi-arrow-down-right'></i> {$value}%</span>";
    return "<span class='badge-growth neutral'><i class='bi bi-dash'></i> 0%</span>";
}
// For IGD specifically, a spike might be bad, but in terms of hospital capacity, it's just traffic. We'll use warning red for spike.
function renderWarningGrowth($value) {
    if ($value >= 20) return "<span class='badge-growth down' title='Lonjakan Pasien'><i class='bi bi-exclamation-triangle'></i> +{$value}%</span>";
    if ($value > 0) return "<span class='badge-growth neutral'><i class='bi bi-arrow-up-right'></i> +{$value}%</span>";
    if ($value < 0) return "<span class='badge-growth up'><i class='bi bi-arrow-down-right'></i> {$value}%</span>";
    return "<span class='badge-growth neutral'><i class='bi bi-dash'></i> 0%</span>";
}
?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h3 style="color: #002D72; font-weight: 800; font-family: 'Inter', sans-serif; margin-bottom: 5px;">Ringkasan Eksekutif</h3>
        <p class="text-muted" style="font-size: 0.9rem;">Periode: Bulan <?= date('F Y', mktime(0, 0, 0, $bulanIni, 10, $tahunIni)) ?></p>
    </div>
</div>

<!-- Quick Overview KPIs (5 Cards) -->
<div class="row mb-4">
    <!-- Rawat Jalan -->
    <div class="col px-2 mb-3">
        <div class="card-kpi card-rj d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <div class="kpi-title">Rawat Jalan</div>
                <div class="kpi-icon-box px-2 rounded"><i class="bi bi-stethoscope"></i></div>
            </div>
            <div class="kpi-value"><?= number_format((int)$rj_curr, 0, ',', '.') ?></div>
            <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                <?= renderGrowth($rj_growth) ?> vs bln lalu
                <div class="mt-1"><strong><?= $rj_baru_pct ?>%</strong> Pasien Baru</div>
            </div>
        </div>
    </div>
    
    <!-- IGD -->
    <div class="col px-2 mb-3">
        <div class="card-kpi card-igd d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <div class="kpi-title">IGD</div>
                <div class="kpi-icon-box px-2 rounded"><i class="bi bi-truck-front"></i></div>
            </div>
            <div class="kpi-value text-danger"><?= number_format((int)$igd_curr, 0, ',', '.') ?></div>
            <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                <?= renderWarningGrowth($igd_growth) ?> vs bln lalu
                <div class="mt-1">Kunjungan Darurat</div>
            </div>
        </div>
    </div>
    
    <!-- Rawat Inap -->
    <div class="col px-2 mb-3">
        <div class="card-kpi card-ri d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <div class="kpi-title">Rawat Inap</div>
                <div class="kpi-icon-box px-2 rounded"><i class="bi bi-hospital-bed"></i></div>
            </div>
            <div class="kpi-value text-success"><?= number_format((int)$inap_curr, 0, ',', '.') ?></div>
            <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                <?= renderGrowth($inap_growth) ?> vs bln lalu
                <div class="mt-1"><strong><?= $bor_pct ?>%</strong> Bed Occupancy Rate</div>
            </div>
        </div>
    </div>
    
    <!-- Laboratorium & Rad -->
    <!-- Gabung, atau pisah jika ada metrik tersendiri. Kita pisah saja untuk estetika -->
    <div class="col px-2 mb-3">
        <div class="card-kpi card-lab d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <div class="kpi-title">Lab & Rad</div>
                <div class="kpi-icon-box px-2 rounded"><i class="bi bi-virus"></i></div>
            </div>
            <div class="kpi-value" style="color:#6f42c1;"><?= number_format((int)$labrad_curr, 0, ',', '.') ?></div>
            <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                <?= renderGrowth($labrad_growth) ?> vs bln lalu
                <div class="mt-1">Total Pemeriksaan Penunjang</div>
            </div>
        </div>
    </div>
    
    <!-- Dominasi Bayar (As per old layout, keeping it as 5th card) -->
    <div class="col px-2 mb-3">
        <div class="card-kpi card-rad d-flex flex-column justify-content-between">
            <div class="d-flex justify-content-between">
                <div class="kpi-title">Dominasi Bayar</div>
                <div class="kpi-icon-box px-2 rounded"><i class="bi bi-wallet2"></i></div>
            </div>
            <div class="kpi-value" style="font-size: 1.2rem; margin-top: 15px;"><?= $topCaraBayar ? Html::encode($topCaraBayar['carabayar_nama']) : '-' ?></div>
            <div class="mt-2 text-muted" style="font-size: 0.8rem;">
                Top Asuransi/Bayar
            </div>
        </div>
    </div>
</div>

<!-- Main Visualization Row -->
<div class="row">
    <!-- Health Service Funnel (Stacked Area/Bar) -->
    <div class="col-md-7 mb-4">
        <div class="card-chart">
            <div class="chart-title">Engine Utama Kunjungan (Trend 12 Bulan) <span style="font-size: 0.8rem; font-weight: normal; color: #888; float:right;">Health Service Funnel</span></div>
            <div style="height: 350px;">
                <canvas id="funnelChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Heatmap Waktu Kunjungan (RJ & IGD) -->
    <div class="col-md-5 mb-4">
        <div class="card-chart text-center" style="overflow-x: auto;">
            <div class="chart-title text-start">Heatmap Kepadatan Pasien <span style="font-size: 0.8rem; font-weight: normal; color: #888;">(Poli & IGD)</span></div>
            <table class="table-heatmap">
                <thead>
                    <tr>
                        <th>Jam</th>
                        <?php foreach($hari_labels as $day): ?>
                            <th><?= substr($day, 0, 3) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    for ($hour = 6; $hour <= 22; $hour++): 
                        $displayHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                    ?>
                    <tr>
                        <td class="hour-label"><?= $displayHour ?></td>
                        <?php foreach($hari_labels as $day): 
                            $val = isset($heatmapMatrix[$day][$hour]) ? $heatmapMatrix[$day][$hour] : 0;
                            
                            // Calculate color intensity (Blue)
                            // Max could be derived from PHP, but we'll use a fixed threshold or relative (approx max = 50)
                            $intensity = min(1, $val / 30); // scale 0 to 1 based on 30 visits/hr max expected
                            
                            // Map to red if very high, otherwise blue
                            if ($intensity > 0.8) {
                                $bgColor = "rgba(220, 53, 69, " . ($intensity) . ")"; // Red for extremely busy
                            } else {
                                $bgColor = "rgba(0, 123, 255, " . ($intensity > 0.1 ? $intensity : 0.05) . ")";
                            }
                            
                            $title = "{$day} jam {$displayHour}: {$val} Pasien";
                        ?>
                        <td>
                            <div class="heatmap-cell" style="background-color: <?= $bgColor ?>;" title="<?= $title ?>">
                                <?= $val > 0 ? $val : '' ?>
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <div class="text-start mt-2" style="font-size: 0.75rem; color: #aaa;">
                <span style="display:inline-block; width:12px; height:12px; background:rgba(0,123,255,0.1); border-radius:3px;"></span> Sepi
                <span style="display:inline-block; width:12px; height:12px; background:rgba(0,123,255,0.6); border-radius:3px; margin-left:10px;"></span> Normal
                <span style="display:inline-block; width:12px; height:12px; background:rgba(220,53,69,0.8); border-radius:3px; margin-left:10px;"></span> Sibuk / Padat
            </div>
        </div>
    </div>
</div>

<!-- Secondary Insights Row -->
<div class="row">
    <!-- Matriks Asal & Cara Bayar -->
    <div class="col-md-6 mb-4">
        <div class="card-chart">
            <div class="chart-title">Matriks Asal Pasien & Cara Bayar</div>
            <div style="height: 300px;">
                <canvas id="matriksChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Operational Efficiency (Top 5 Lab/Rad) -->
    <div class="col-md-6 mb-4">
        <div class="card-chart" style="overflow-y: auto;">
            <div class="chart-title">Efisiensi Operasional (Top 5 Penunjang)</div>
            <table class="table-minimal">
                <thead>
                    <tr>
                        <th>Jenis Pemeriksaan / Unit</th>
                        <th style="text-align: right;">Total Permintaan</th>
                        <th style="width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($topPemeriksaan)): ?>
                        <?php foreach($topPemeriksaan as $idx => $pem): 
                            // Render small progress bar relative to the top 1
                            $max = (int) ($topPemeriksaan[0]['total'] ?: 1);
                            $pct = round(((int)$pem['total'] / $max) * 100);
                            $barColor = $idx == 0 ? '#6f42c1' : '#a0aec0';
                        ?>
                        <tr>
                            <td>
                                <strong><?= Html::encode($pem['nama'] ?: 'Tidak Diketahui') ?></strong>
                                <div class="mt-1 bg-light rounded" style="height: 6px; width: 100%;">
                                    <div style="height: 100%; border-radius: 4px; background: <?= $barColor ?>; width: <?= $pct ?>%;"></div>
                                </div>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #2d3748; font-size: 1.1rem;">
                                <?= number_format((int)$pem['total'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="border: 1px solid #ddd;">High Vol</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted">Data belum tersedia</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart JS Initialization -->
<?php
// Prepare Data for Matriks Asal Bayar (Stacked)
$matriksLabels = [];
$matriksDatasets = [];
$colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E83E8C'];
$colorIndex = 0;

$tempData = [];
foreach ($matriksBayar as $mb) {
    if (!in_array($mb['statuspasien'], $matriksLabels)) {
        $matriksLabels[] = $mb['statuspasien'] ?: 'Tidak Diketahui';
    }
    $tempData[$mb['carabayar_nama']][$mb['statuspasien'] ?: 'Tidak Diketahui'] = (int)$mb['total'];
}

foreach ($tempData as $cb => $values) {
    $data = [];
    foreach ($matriksLabels as $label) {
        $data[] = isset($values[$label]) ? $values[$label] : 0;
    }
    $matriksDatasets[] = [
        'label' => $cb,
        'data' => $data,
        'backgroundColor' => isset($colors[$colorIndex]) ? $colors[$colorIndex] : '#ccc',
        'borderWidth' => 0
    ];
    $colorIndex++;
}

$matriksLabelsJson = json_encode($matriksLabels);
$matriksDatasetsJson = json_encode($matriksDatasets);

$trendRJJson = json_encode($trendRJ);
$trendIGDJson = json_encode($trendIGD);
$trendRIJson = json_encode($trendRI);
$trendLabRadJson = json_encode($trendLabRad);

$this->registerJs("
    // Default Font
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#718096';

    // 1. Health Funnel Chart (Stacked Bar)
    const ctxFunnel = document.getElementById('funnelChart').getContext('2d');
    new Chart(ctxFunnel, {
        type: 'bar', // Grouped Stacked Bar Chart
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [
                {
                    label: 'Rawat Jalan',
                    data: $trendRJJson,
                    backgroundColor: 'rgba(0, 123, 255, 0.8)', // Primary Blue
                    stack: 'Stack 0',
                },
                {
                    label: 'IGD',
                    data: $trendIGDJson,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)', // Danger Red
                    stack: 'Stack 0',
                },
                {
                    label: 'Rawat Inap (Admisi)',
                    data: $trendRIJson,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)', // Success Green
                    stack: 'Stack 0',
                },
                {
                    label: 'Penunjang',
                    data: $trendLabRadJson,
                    backgroundColor: 'rgba(111, 66, 193, 0.8)', // Purple
                    stack: 'Stack 0',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { weight: '600' } } },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { stacked: true, beginAtZero: true, grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false } },
                x: { stacked: true, grid: { display: false, drawBorder: false } }
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false }
        }
    });

    // 2. Matriks Asal & Cara Bayar (Stacked Bar)
    const ctxMatriks = document.getElementById('matriksChart').getContext('2d');
    new Chart(ctxMatriks, {
        type: 'bar',
        data: {
            labels: $matriksLabelsJson,
            datasets: $matriksDatasetsJson
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8, font: {size: 10} } }
            },
            scales: {
                x: { stacked: true, grid: { display: false, drawBorder: false } },
                y: { stacked: true, beginAtZero: true, grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false } }
            }
        }
    });
");
?>
