<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Dashboard Keuangan';

// Register Chart.js
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

$this->registerCss("
    body { background-color: #F8FAFC; }
    .content-wrapper { background-color: #F8FAFC !important; }
    
    .finance-header { margin-bottom: 24px; }
    .finance-title { color: #002D72; font-weight: 800; font-family: 'Inter', sans-serif; margin-bottom: 5px; }
    
    /* Search Bar */
    .search-container { position: relative; max-width: 350px; }
    .search-input { width: 100%; padding: 10px 20px 10px 40px; border-radius: 30px; border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); font-size: 0.9rem; transition: all 0.3s ease; }
    .search-input:focus { border-color: #002D72; box-shadow: 0 4px 20px rgba(0, 45, 114, 0.1); outline: none; }
    .search-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #a0aec0; font-size: 1rem; }

    /* KPI Cards */
    .kpi-row { margin-bottom: 24px; }
    .card-kpi { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04); background: #ffffff; padding: 20px; transition: transform 0.2s ease; border: 1px solid #edf2f7; }
    .card-kpi:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0, 45, 114, 0.06); }
    .kpi-title { font-family: 'Inter', sans-serif; font-size: 0.8rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .kpi-val { font-family: 'Inter', sans-serif; font-size: 1.6rem; color: #002D72; font-weight: 800; line-height: 1.2; }
    .kpi-sub { font-size: 0.8rem; display: flex; align-items: center; margin-top: 5px; }
    
    .kpi-icon-box { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .box-blue { background: rgba(0, 123, 255, 0.1); color: #007bff; }
    .box-green { background: rgba(32, 201, 151, 0.1); color: #20c997; }
    .box-orange { background: rgba(253, 126, 20, 0.1); color: #fd7e14; }
    .box-purple { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }

    /* Chart Cards */
    .card-chart { border: none; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); background: #ffffff; padding: 20px; margin-bottom: 24px; height: 100%; border: 1px solid #edf2f7; }
    .chart-title { font-family: 'Inter', sans-serif; font-size: 1rem; color: #2d3748; font-weight: 700; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }

    /* Menu Grid */
    .menu-section-title { font-family: 'Inter', sans-serif; font-size: 1.1rem; color: #002D72; font-weight: 700; margin-bottom: 15px; margin-top: 10px; }
    .finance-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
    .finance-card { background: #ffffff; border-radius: 12px; padding: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); display: flex; align-items: center; text-decoration: none !important; transition: all 0.3s ease; border: 1px solid #edf2f7; }
    .finance-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0, 45, 114, 0.08); border-color: rgba(0, 45, 114, 0.1); }
    .finance-card .icon-box { width: 40px; height: 40px; border-radius: 10px; background: rgba(0, 45, 114, 0.06); color: #002D72; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 15px; transition: all 0.3s ease; }
    .finance-card:hover .icon-box { background: #002D72; color: #ffffff; }
    
    .card-content h3 { color: #2d3748; font-family: 'Inter', sans-serif; font-size: 0.95rem; font-weight: 700; margin-bottom: 4px; margin-top: 0; }
    .badge-info { display: inline-block; background: #F1F5F9; color: #475569; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; border: 1px solid #E2E8F0; }
    
    .empty-state { grid-column: 1 / -1; text-align: center; padding: 30px; color: #a0aec0; display: none; }
");

// Convert PHP arrays to JSON for Chart.js
$trendJs = json_encode($trendBulanan);
$penjaminJs = json_encode($komposisiPenjamin);
$unitJs = json_encode($topUnit);
$dokterJs = json_encode($tagihanDokter);
?>

<!-- Header -->
<div class="finance-header d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h2 class="finance-title">Dashboard Keuangan</h2>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Ringkasan Finansial Eksekutif - <?= date('F Y') ?></p>
    </div>
    
    <div class="search-container mt-3 mt-md-0">
        <i class="bi bi-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Pencarian cepat menu laporan...">
    </div>
</div>

<!-- KPI Row -->
<div class="row kpi-row">
    <!-- Revenue -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi d-flex justify-content-between align-items-start">
            <div>
                <div class="kpi-title">Total Pendapatan <span class="badge bg-success" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Real</span></div>
                <div class="kpi-val text-break">Rp <?= number_format($kpi['revenue_bulan_ini'] / 1000000, 1, ',', '.') ?>M</div>
                <div class="kpi-sub <?= $kpi['growth'] >= 0 ? 'text-success' : 'text-danger' ?>">
                    <i class="bi <?= $kpi['growth'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' ?> me-1"></i>
                    <?= abs($kpi['growth']) ?>% dari bulan lalu
                </div>
            </div>
            <div class="kpi-icon-box box-blue"><i class="bi bi-cash-coin"></i></div>
        </div>
    </div>
    <!-- Arus Kas -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi d-flex justify-content-between align-items-start">
            <div>
                <div class="kpi-title">Net Arus Kas <span class="badge bg-warning text-dark" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Proxy</span></div>
                <div class="kpi-val text-break text-success"><?= $kpi['arus_kas'] ?></div>
                <div class="kpi-sub text-muted">
                    <i class="bi bi-info-circle me-1"></i> Positif (Sehat)
                </div>
            </div>
            <div class="kpi-icon-box box-green"><i class="bi bi-graph-up-arrow"></i></div>
        </div>
    </div>
    <!-- Piutang -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi d-flex justify-content-between align-items-start">
            <div>
                <div class="kpi-title">Total Piutang (A/R) <span class="badge bg-warning text-dark" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Proxy</span></div>
                <div class="kpi-val text-break">Rp <?= number_format($kpi['piutang'] / 1000000, 1, ',', '.') ?>M</div>
                <div class="kpi-sub <?= $kpi['piutang_status'] == 'warning' ? 'text-warning' : 'text-danger' ?>">
                    <i class="bi bi-exclamation-triangle me-1"></i> Butuh difollow-up
                </div>
            </div>
            <div class="kpi-icon-box box-orange"><i class="bi bi-file-earmark-medical"></i></div>
        </div>
    </div>
    <!-- Margin -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi d-flex justify-content-between align-items-start">
            <div>
                <div class="kpi-title">Margin Operasional <span class="badge bg-danger" style="font-size: 0.6rem; vertical-align: text-top; margin-left: 4px;">Dummy</span></div>
                <div class="kpi-val text-break">24.5%</div>
                <div class="kpi-sub text-success">
                    <i class="bi bi-arrow-up-right me-1"></i> +1.2% thd target
                </div>
            </div>
            <div class="kpi-icon-box box-purple"><i class="bi bi-pie-chart"></i></div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row">
    <!-- Trend Bulanan -->
    <div class="col-md-8 mb-4">
        <div class="card-chart">
            <div class="chart-title">
                <span>Tren Pendapatan Bulanan (<?= date('Y') ?>)</span>
                <span class="badge bg-light text-dark border">Dalam Juta Rupiah</span>
            </div>
            <div style="height: 280px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Komposisi Penjamin -->
    <div class="col-md-4 mb-4">
        <div class="card-chart">
            <div class="chart-title">Komposisi Pendapatan</div>
            <div style="height: 280px; position: relative;">
                <canvas id="donutChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row">
    <!-- Top Unit -->
    <div class="col-md-6 mb-4">
        <div class="card-chart">
            <div class="chart-title">Top 5 Unit Revenue Generator</div>
            <div style="height: 250px;">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
    <!-- Tagihan Dokter -->
    <div class="col-md-6 mb-4">
        <div class="card-chart">
            <div class="chart-title">Status Tagihan Jasa Medis (Spesialis)</div>
            <div style="height: 250px;">
                <canvas id="stackedChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Menu Navigasi Laporan -->
<h4 class="menu-section-title">Akses Laporan Keuangan</h4>
<div class="finance-grid" id="financeMenuGrid">
    
    <a href="<?= Url::to(['/keuangan/pendapatan-pasien/index']) ?>" class="finance-card">
        <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
        <div class="card-content">
            <h3 class="menu-name">Pendapatan Pasien</h3>
            <span class="badge-info"><i class="bi bi-graph-up text-success"></i> <?= Html::encode($summaries['pendapatan_pasien']) ?></span>
        </div>
    </a>
    
    <a href="<?= Url::to(['/keuangan/revenue/index']) ?>" class="finance-card">
        <div class="icon-box" style="color:#20c997; background:rgba(32, 201, 151, 0.1);"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="card-content">
            <h3 class="menu-name">Laporan Revenue</h3>
            <span class="badge-info text-success"><i class="bi bi-check-circle"></i> <?= Html::encode($summaries['revenue']) ?></span>
        </div>
    </a>
    
    <a href="<?= Url::to(['/keuangan/jasa-dokter/index']) ?>" class="finance-card">
        <div class="icon-box" style="color:#29ABE2; background:rgba(41, 171, 226, 0.1);"><i class="bi bi-person-badge"></i></div>
        <div class="card-content">
            <h3 class="menu-name">Tagihan Jasa Dokter</h3>
            <span class="badge-info text-warning"><i class="bi bi-hourglass-split"></i> <?= Html::encode($summaries['jasa_dokter']) ?></span>
        </div>
    </a>
    
    <a href="<?= Url::to(['/keuangan/neraca-saldo/index']) ?>" class="finance-card">
        <div class="icon-box" style="color:#fd7e14; background:rgba(253, 126, 20, 0.1);"><i class="bi bi-scales"></i></div>
        <div class="card-content">
            <h3 class="menu-name">Neraca Saldo</h3>
            <span class="badge-info"><i class="bi bi-list-check text-primary"></i> <?= Html::encode($summaries['neraca_saldo']) ?></span>
        </div>
    </a>
    
    <a href="<?= Url::to(['/keuangan/buku-besar/index']) ?>" class="finance-card">
        <div class="icon-box" style="color:#805AD5; background:rgba(128, 90, 213, 0.1);"><i class="bi bi-journal-text"></i></div>
        <div class="card-content">
            <h3 class="menu-name">Buku Besar</h3>
            <span class="badge-info"><i class="bi bi-hdd-stack text-purple"></i> <?= Html::encode($summaries['buku_besar']) ?></span>
        </div>
    </a>
    
    <div class="empty-state" id="emptyState">
        <i class="bi bi-search" style="font-size: 2rem; opacity: 0.5;"></i>
        <h6 class="mt-2">Menu tidak ditemukan</h6>
    </div>
</div>

<?php
$this->registerJs("
    // Default Font
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#718096';
    
    const trendData = $trendJs;
    const penjaminData = $penjaminJs;
    const unitData = $unitJs;
    const dokterData = $dokterJs;

    // 1. Trend Chart (Line)
    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Revenue (Juta Rp)',
                data: trendData.data,
                borderColor: '#002D72',
                backgroundColor: 'rgba(0, 45, 114, 0.08)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false } },
                x: { grid: { display: false, drawBorder: false } }
            }
        }
    });

    // 2. Komposisi Penjamin (Donut)
    new Chart(document.getElementById('donutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: penjaminData.labels,
            datasets: [{
                data: penjaminData.data,
                backgroundColor: ['#20c997', '#007bff', '#fd7e14', '#e83e8c'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 15, font: {size: 11} } }
            }
        }
    });

    // 3. Top Unit (Horizontal Bar)
    new Chart(document.getElementById('barChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: unitData.labels,
            datasets: [{
                label: 'Revenue (Juta Rp)',
                data: unitData.data,
                backgroundColor: 'rgba(41, 171, 226, 0.85)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false } },
                y: { grid: { display: false, drawBorder: false }, ticks: { font: {size: 10} } }
            }
        }
    });

    // 4. Tagihan Dokter (Stacked Bar)
    new Chart(document.getElementById('stackedChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: dokterData.labels,
            datasets: [
                {
                    label: 'Terbayar',
                    data: dokterData.terbayar,
                    backgroundColor: '#6DC536',
                    borderRadius: 4
                },
                {
                    label: 'Dalam Proses',
                    data: dokterData.proses,
                    backgroundColor: '#F5A623',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } } },
            scales: {
                x: { stacked: true, grid: { display: false, drawBorder: false }, ticks: { font: {size: 10} } },
                y: { stacked: true, beginAtZero: true, grid: { borderDash: [4, 4], color: '#e2e8f0', drawBorder: false } }
            }
        }
    });

    // Quick Search Script
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let keyword = this.value.toLowerCase();
        let cards = document.querySelectorAll('.finance-card');
        let hasVisible = false;
        
        cards.forEach(card => {
            let menuName = card.querySelector('.menu-name').textContent.toLowerCase();
            if (menuName.includes(keyword)) {
                card.style.display = 'flex';
                hasVisible = true;
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('emptyState').style.display = hasVisible ? 'none' : 'block';
    });
");
?>
