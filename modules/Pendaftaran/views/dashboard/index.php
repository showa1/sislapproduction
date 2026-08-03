<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard Pendaftaran MJKN vs Onsite';

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

?>

<div class="row">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">Dashboard Analitik Pendaftaran</h2>

        <!-- Filter Range Tanggal -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body px-4 py-3">
                <?= Html::beginForm(['index'], 'get', ['id' => 'filterForm']) ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                            <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Terapkan</button>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="<?= Url::to(['index']) ?>" class="btn btn-light border text-muted w-100"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                        </div>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mt-4">
            <!-- 1. Total Pendaftaran -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card card-img-holder text-white" style="background: linear-gradient(135deg, #002D72, #0056b3); border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0, 45, 114, 0.2);">
                    <div class="card-body p-4">
                        <h5 class="font-weight-normal mb-3">Total Pendaftaran <i class="bi bi-people mdi-24px float-right"></i></h5>
                        <h2 class="mb-4 fw-bold"><?= number_format($totalPendaftaran, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: normal;">Pasien</span></h2>
                        <h6 class="card-text small opacity-75">Total seluruh pasien terdaftar</h6>
                    </div>
                </div>
            </div>
            
            <!-- 2. Pasien Mobile JKN -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card card-img-holder text-white" style="background: linear-gradient(135deg, #10B981, #059669); border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);">
                    <div class="card-body p-4">
                        <h5 class="font-weight-normal mb-3">Pasien Mobile JKN <i class="bi bi-phone mdi-24px float-right"></i></h5>
                        <h2 class="mb-4 fw-bold"><?= number_format($totalMjkn, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: normal;">Pasien</span></h2>
                        <h6 class="card-text small opacity-100 fw-bold">(<?= number_format($pctMjkn, 1) ?>% dari Total)</h6>
                    </div>
                </div>
            </div>

            <!-- 3. Pasien Onsite -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card card-img-holder text-white" style="background: linear-gradient(135deg, #64748b, #475569); border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(100, 116, 139, 0.2);">
                    <div class="card-body p-4">
                        <h5 class="font-weight-normal mb-3">Pasien Onsite <i class="bi bi-person-badge mdi-24px float-right"></i></h5>
                        <h2 class="mb-4 fw-bold"><?= number_format($totalOnsite, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: normal;">Pasien</span></h2>
                        <h6 class="card-text small opacity-100 fw-bold">(<?= number_format($pctOnsite, 1) ?>% dari Total)</h6>
                    </div>
                </div>
            </div>

            <!-- 4. Check-In Rate MJKN -->
            <div class="col-md-3 stretch-card grid-margin">
                <div class="card card-img-holder text-white" style="background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);">
                    <div class="card-body p-4">
                        <h5 class="font-weight-normal mb-3">Check-In MJKN <i class="bi bi-check2-circle mdi-24px float-right"></i></h5>
                        <h2 class="mb-4 fw-bold"><?= number_format($totalCheckinMjkn, 0, ',', '.') ?> <span style="font-size: 1rem; font-weight: normal;">/ <?= $totalMjkn ?></span></h2>
                        <h6 class="card-text small opacity-100 fw-bold">(<?= number_format($pctCheckin, 1) ?>% Tingkat Kehadiran)</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row mt-3">
            <!-- Pie Chart -->
            <div class="col-md-4 grid-margin stretch-card mb-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-dark fw-bold mb-4">Rasio MJKN vs Onsite</h5>
                        <div style="height: 300px; display: flex; justify-content: center;">
                            <canvas id="ratioChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Chart -->
            <div class="col-md-8 grid-margin stretch-card mb-4">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-dark fw-bold mb-4">Tren Pendaftaran Harian</h5>
                        <div style="height: 300px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Stacked Bar Chart -->
            <div class="col-md-12 grid-margin stretch-card mb-5">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-dark fw-bold mb-4">Distribusi Cara Daftar per Poliklinik</h5>
                        <div style="height: 400px;">
                            <canvas id="poliChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    // Common Chart.js options
    Chart.defaults.font.family = \"'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif\";
    Chart.defaults.color = '#64748b';

    // 1. Ratio Pie Chart
    const ctxRatio = document.getElementById('ratioChart').getContext('2d');
    new Chart(ctxRatio, {
        type: 'doughnut',
        data: {
            labels: ['Mobile JKN', 'Onsite / Regular'],
            datasets: [{
                data: [" . $totalMjkn . ", " . $totalOnsite . "],
                backgroundColor: ['#10B981', '#64748b'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw;
                            let total = context.chart._metasets[context.datasetIndex].total;
                            let percentage = Math.round((value / total) * 100) + '%';
                            return label + ': ' + percentage + ' (' + value + ' Pasien)';
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // 2. Daily Trend Line Chart
    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: " . $chartLabels . ",
            datasets: [
                {
                    label: 'Mobile JKN',
                    data: " . $chartDataMjkn . ",
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                },
                {
                    label: 'Onsite / Regular',
                    data: " . $chartDataOnsite . ",
                    borderColor: '#64748b',
                    backgroundColor: 'rgba(100, 116, 139, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 3. Polyclinic Stacked Bar Chart
    const ctxPoli = document.getElementById('poliChart').getContext('2d');
    new Chart(ctxPoli, {
        type: 'bar',
        data: {
            labels: " . $poliLabels . ",
            datasets: [
                {
                    label: 'Mobile JKN',
                    data: " . $poliDataMjkn . ",
                    backgroundColor: '#10B981',
                    borderRadius: 4
                },
                {
                    label: 'Onsite / Regular',
                    data: " . $poliDataOnsite . ",
                    backgroundColor: '#64748b',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: { 
                    stacked: true, 
                    grid: { display: false },
                    ticks: { maxRotation: 45, minRotation: 45 }
                },
                y: { 
                    stacked: true, 
                    beginAtZero: true, 
                    grid: { borderDash: [2, 4] } 
                }
            }
        }
    });
");
?>
