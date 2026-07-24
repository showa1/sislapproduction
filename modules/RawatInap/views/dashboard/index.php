<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard Laporan Length of Stay (LOS)';
$this->params['breadcrumbs'][] = ['label' => 'Rawat Inap', 'url' => ['/rawatinap/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

// Calculate percentages for vs last month
$calcDiff = function($curr, $prev) {
    if ($prev == 0) return $curr > 0 ? 100 : 0;
    return round((($curr - $prev) / $prev) * 100, 1);
};

$losDiff = $calcDiff($currData['rata_rata_los'], $prevData['rata_rata_los']);
$pasienDiff = $currData['jumlah_pasien'] - $prevData['jumlah_pasien'];

$targetJam = 40000;
$currJam = $currData['total_jam'];
$jamProgress = min(100, $currJam > 0 ? ($currJam / $targetJam) * 100 : 0);

$months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$lblBulanIni = $months[$currentMonth - 1] . " " . $year;

$labelsJson = json_encode($months);
$avgLosArr = array_map(function($jam) { return round($jam / 24, 1); }, array_column($trendByMonth, 'rata_rata_los'));
$pasienArr = array_column($trendByMonth, 'jumlah_pasien');
$avgLosJson = json_encode(array_values($avgLosArr));
$pasienJson = json_encode(array_values($pasienArr));
$sparklineJson = json_encode($sparklineData);

// Helpers for view
$formatLos = function($jam) {
    return round($jam / 24, 1) . " Hari";
};

$formatDiffBadge = function($diff, $isLos = true) {
    // For LOS: decrease is good (green), increase is bad (red)
    // For Pasien: increase is good (green), decrease is bad (red)
    $isGood = $isLos ? ($diff <= 0) : ($diff >= 0);
    $color = $isGood ? 'success' : 'danger';
    $icon = $diff > 0 ? 'bi-caret-up-fill' : ($diff < 0 ? 'bi-caret-down-fill' : 'bi-dash');
    $sign = $diff > 0 ? '+' : '';
    $val = $isLos ? abs($diff).'%' : abs($diff).' Pasien';
    return "<span class=\"badge bg-{$color} bg-opacity-10 text-{$color} fw-bold me-2 px-2 py-1\"><i class=\"bi {$icon}\"></i> {$val}</span>";
};
?>
<div class="rawatinap-dashboard">
    <!-- Filter form inline -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-3">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-center']) ?>
                <div class="col-auto">
                    <span class="text-muted small fw-bold me-2"><i class="bi bi-funnel"></i> FILTER</span>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">TAHUN</span>
                        <select name="year" class="form-select border-start-0 shadow-none">
                            <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">BULAN</span>
                        <select name="month" class="form-select border-start-0 shadow-none">
                            <?php 
                            $bulanList = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];
                            foreach($bulanList as $num => $name): ?>
                                <option value="<?= $num ?>" <?= $currentMonth == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">CARA BAYAR</span>
                        <select name="cara_bayar" class="form-select border-start-0 shadow-none">
                            <option value="Semua">Semua</option>
                            <?php foreach($optCaraBayar as $opt): ?>
                                <option value="<?= Html::encode($opt) ?>" <?= $caraBayar == $opt ? 'selected' : '' ?>><?= Html::encode($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">RUANGAN</span>
                        <select name="ruangan" class="form-select border-start-0 shadow-none">
                            <option value="Semua">Semua</option>
                            <?php foreach($optRuangan as $opt): ?>
                                <option value="<?= Html::encode($opt) ?>" <?= $ruangan == $opt ? 'selected' : '' ?>><?= Html::encode($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-auto ms-auto text-end">
                    <span class="text-muted small d-none d-md-inline me-3">Data per: <?= $lblBulanIni ?></span>
                    <button type="submit" class="btn btn-sm btn-info text-white fw-bold px-4">APPLY</button>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Cards Row -->
    <div class="row g-4 mb-4">
        <!-- RATA-RATA LOS -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-start border-info border-4">
                <div class="card-body p-4 position-relative">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">RATA-RATA LOS BULAN INI</h6>
                    <h2 class="fw-bold mb-1 d-flex align-items-baseline" id="losDisplayVal">
                        <?= round($currData['rata_rata_los'] / 24, 1) ?> <span class="fs-6 text-muted ms-2 fw-normal" id="losDisplayUnit">Hari</span>
                    </h2>
                    <div class="mt-3 mb-4 d-flex align-items-center">
                        <?= $formatDiffBadge($losDiff, true) ?>
                        <span class="small text-muted">vs Bulan Lalu</span>
                    </div>
                    
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 position-absolute bottom-0 start-0 ms-4 mb-3" style="font-size: 0.8rem;" id="btnToggleLos" onclick="toggleLosView()">
                        <i class="bi bi-arrow-repeat"></i> Convert ke Jam
                    </button>
                </div>
            </div>
        </div>

        <!-- TOTAL PASIEN PULANG -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-start border-success border-4">
                <div class="card-body p-4 position-relative">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">TOTAL PASIEN PULANG (KRS)</h6>
                    <h2 class="fw-bold mb-1 d-flex align-items-baseline">
                        <?= number_format($currData['jumlah_pasien']) ?> <span class="fs-6 text-muted ms-2 fw-normal">Pasien</span>
                    </h2>
                    <div class="mt-3 d-flex align-items-center">
                        <?= $formatDiffBadge($pasienDiff, false) ?>
                        <span class="small text-muted">vs Bulan Lalu</span>
                    </div>
                    
                    <div class="mt-4 pt-2 position-absolute bottom-0 start-0 w-100 px-4 mb-3">
                        <div style="height: 65px; width: 100%;">
                            <canvas id="sparklineChart"></canvas>
                        </div>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">6 bulan terakhir</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOTAL JAM RANAP -->
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 rounded-4 border-start border-primary border-4">
                <div class="card-body p-4">
                    <h6 class="text-muted text-uppercase small fw-bold mb-3">TOTAL JAM RANAP BULAN INI</h6>
                    <h2 class="fw-bold mb-1 d-flex align-items-baseline">
                        <?= number_format($currData['total_jam'], 0, ',', '.') ?> <span class="fs-6 text-muted ms-2 fw-normal">Jam</span>
                    </h2>
                    <div class="mt-1 small text-muted">
                        <?= number_format(floor($currData['total_jam'] / 24), 0, ',', '.') ?> Hari
                    </div>
                    
                    <div class="mt-4 pt-3">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: <?= $jamProgress ?>%" aria-valuenow="<?= $jamProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small text-muted" style="font-size: 0.75rem;">
                            <span>0</span>
                            <span>Target: <?= number_format($targetJam, 0, ',', '.') ?> Jam</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Main Mixed Chart -->
        <div class="col-lg-8">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">TREN LOS BULANAN</h6>
                            <h5 class="fw-bold text-dark">Rata-rata LOS & Jumlah Pasien &mdash; <?= Html::encode($year) ?></h5>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="mainMixedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown List -->
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    <h6 class="text-muted text-uppercase small fw-bold mb-4">BREAKDOWN</h6>
                    <h5 class="fw-bold text-dark mb-4">Cara Pembayaran</h5>
                    
                    <div class="breakdown-list" style="max-height: 290px; overflow-y: auto; padding-right: 10px;">
                        <?php 
                        $maxPasien = 0;
                        if (!empty($dataBreakdown)) {
                            $maxPasien = max(array_column($dataBreakdown, 'jumlah_pasien'));
                        }
                        
                        $colors = ['#0dcaf0', '#20c997', '#fd7e14', '#6f42c1', '#d63384'];
                        $cIdx = 0;
                        ?>
                        
                        <?php if(empty($dataBreakdown)): ?>
                            <div class="text-center text-muted small py-4">Tidak ada data.</div>
                        <?php endif; ?>
                        
                        <?php foreach($dataBreakdown as $bd): ?>
                            <?php 
                                $pct = $maxPasien > 0 ? ($bd['jumlah_pasien'] / $maxPasien) * 100 : 0;
                                $color = $colors[$cIdx % count($colors)];
                                $avgJam = $bd['jumlah_pasien'] > 0 ? $bd['total_jam'] / $bd['jumlah_pasien'] : 0;
                                $avgDisplayBd = $formatLos($avgJam);
                                $cIdx++;
                            ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <span class="fw-bold text-dark d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2" style="width: 8px; height: 8px; background-color: <?= $color ?>;"></span>
                                        <?= Html::encode($bd['carabayar_nama']) ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px; background-color: #f1f5f9;">
                                    <div class="progress-bar rounded-pill" style="width: <?= $pct ?>%; background-color: <?= $color ?>;"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2 small text-muted" style="font-size: 0.75rem;">
                                    <span>Rata-rata LOS: <?= $avgDisplayBd ?></span>
                                    <span><?= round($avgJam, 1) ?> Jam</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$rawAvgJam = round($currData['rata_rata_los']);
$valHari = round($currData['rata_rata_los'] / 24, 1);

$this->registerJs("
    // Toggle Logic
    let showingHours = false;
    const valHours = '{$rawAvgJam}';
    const unitHours = 'Jam';
    
    const valDays = '{$valHari}';
    const unitDays = 'Hari';
    
    window.toggleLosView = function() {
        const displayVal = document.getElementById('losDisplayVal');
        const btn = document.getElementById('btnToggleLos');
        
        if (showingHours) {
            displayVal.innerHTML = valDays + ' <span class=\"fs-6 text-muted ms-2 fw-normal\" id=\"losDisplayUnit\">' + unitDays + '</span>';
            btn.innerHTML = '<i class=\"bi bi-arrow-repeat\"></i> Convert ke Jam';
            showingHours = false;
        } else {
            displayVal.innerHTML = valHours + ' <span class=\"fs-6 text-muted ms-2 fw-normal\" id=\"losDisplayUnit\">' + unitHours + '</span>';
            btn.innerHTML = '<i class=\"bi bi-arrow-repeat\"></i> Convert ke Hari';
            showingHours = true;
        }
    };

    // Sparkline Chart (6 Months)
    const ctxSparkline = document.getElementById('sparklineChart').getContext('2d');
    const sparklineLabelsArr = " . json_encode($sparklineLabels) . ";
    new Chart(ctxSparkline, {
        type: 'bar',
        data: {
            labels: sparklineLabelsArr,
            datasets: [{
                data: {$sparklineJson},
                backgroundColor: 'rgba(25, 135, 84, 0.5)',
                borderRadius: 2,
                barPercentage: 0.9,
                categoryPercentage: 1.0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false }, 
                tooltip: { 
                    enabled: true,
                    callbacks: {
                        label: function(context) { return context.raw + ' Pasien'; }
                    }
                } 
            },
            scales: { 
                x: { 
                    display: true, 
                    grid: { display: false },
                    ticks: { font: { size: 9 }, color: '#888', maxRotation: 0 }
                }, 
                y: { display: false, beginAtZero: true } 
            },
            layout: { padding: 0 }
        }
    });

    // Main Mixed Chart
    const ctxMain = document.getElementById('mainMixedChart').getContext('2d');
    new Chart(ctxMain, {
        type: 'bar',
        data: {
            labels: {$labelsJson},
            datasets: [
                {
                    label: 'Batas Toleransi (Hari)',
                    data: Array(12).fill(2.8),
                    type: 'line',
                    borderColor: '#dc3545',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Pasien',
                    data: {$pasienJson},
                    type: 'line',
                    borderColor: '#fd7e14',
                    backgroundColor: '#fd7e14',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y1'
                },
                {
                    label: 'Avg LOS (Hari)',
                    data: {$avgLosJson},
                    type: 'bar',
                    backgroundColor: function(context) {
                        const val = context.dataset.data[context.dataIndex];
                        return val > 2.8 ? '#dc3545' : '#0dcaf0';
                    },
                    borderRadius: 4,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, boxWidth: 8, font: {size: 11} }
                },
                tooltip: {
                    backgroundColor: 'rgba(255,255,255,0.95)',
                    titleColor: '#000',
                    bodyColor: '#333',
                    borderColor: '#ddd',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Waktu (Hari)', font: {size: 10} },
                    grid: { borderDash: [4, 4], color: '#f1f5f9' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Jumlah Pasien', font: {size: 10} },
                    grid: { drawOnChartArea: false }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
");
?>
