<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Analisis Kunjungan Closing';

// Tailwind CSS via CDN (khusus dashboard ini agar tidak konflik global)
$this->registerJsFile('https://cdn.tailwindcss.com', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);

// Custom Config Tailwind untuk warna PMC
$this->registerJs("
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          pmc: {
            navy: '#002D72',
            green: '#6DC536',
          }
        },
        fontFamily: {
          sans: ['Inter', 'sans-serif'],
        }
      }
    }
  }
", \yii\web\View::POS_HEAD);

// Some reset CSS to prevent Yii's bootstrap from messing up Tailwind cards
$this->registerCss("
    body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
    .content-wrapper { background-color: #f8fafc !important; }
    a:hover { text-decoration: none; }
");

$chartJsParams = json_encode($chartData);
?>

<div class="tw-wrapper">
    <!-- Header Area -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-pmc-navy mb-1">Analisis Kunjungan Closing</h1>
            <p class="text-slate-500 text-sm">Pemantauan Tren Pasien & Komparasi Antar Periode</p>
        </div>
        
        <!-- Filter Controls -->
        <div class="mt-4 md:mt-0 flex flex-col items-end gap-3">
            <form id="filterForm" method="GET" action="<?= Url::to(['/keuangan/analisis-kunjungan/index']) ?>" class="flex items-center gap-2 bg-white p-2 rounded-lg shadow-sm border border-slate-200">
                <input type="hidden" name="r" value="keuangan/analisis-kunjungan/index">
                <input type="hidden" name="filter_type" id="filterType" value="<?= Html::encode($filterType) ?>">
                <div class="flex items-center">
                    <input type="date" name="start_date" id="startDate" value="<?= Html::encode($startDate) ?>" class="text-sm bg-slate-50 border border-slate-200 rounded px-2 py-1.5 outline-none focus:border-pmc-navy text-slate-700">
                    <span class="mx-2 text-slate-400 text-sm">s/d</span>
                    <input type="date" name="end_date" id="endDate" value="<?= Html::encode($endDate) ?>" class="text-sm bg-slate-50 border border-slate-200 rounded px-2 py-1.5 outline-none focus:border-pmc-navy text-slate-700">
                </div>
                <button type="submit" class="bg-pmc-navy hover:bg-blue-900 text-white px-3 py-1.5 rounded text-sm transition-colors duration-200">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
            </form>
            
            <!-- Quick Filters (Tips Tambahan Direksi) -->
            <div class="flex gap-2 text-xs">
                <button type="button" onclick="setQuickFilter('first_10')" class="px-3 py-1 rounded-full border transition-all <?= $filterType == 'first_10' ? 'bg-pmc-navy text-white border-pmc-navy' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' ?>">
                    10 Hari Pertama
                </button>
                <button type="button" onclick="setQuickFilter('mtd')" class="px-3 py-1 rounded-full border transition-all <?= $filterType == 'mtd' ? 'bg-pmc-navy text-white border-pmc-navy' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' ?>">
                    Bulan Ini (MTD)
                </button>
                <button type="button" onclick="setQuickFilter('last_month')" class="px-3 py-1 rounded-full border transition-all <?= $filterType == 'last_month' ? 'bg-pmc-navy text-white border-pmc-navy' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' ?>">
                    Bulan Lalu
                </button>
            </div>
        </div>
    </div>

    <!-- 3 Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Kartu 1: Bulan Lalu -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 flex items-start gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-12 h-12 rounded-lg bg-orange-100 text-orange-500 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="bi bi-calendar-minus"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Periode Sebelumnya</p>
                <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight leading-none mb-2"><?= number_format($kpi['bulan_lalu'], 0, ',', '.') ?> <span class="text-sm font-medium text-slate-500">Pasien</span></h3>
                <p class="text-xs text-slate-500"><?= Html::encode($kpi['label_lalu']) ?></p>
            </div>
        </div>

        <!-- Kartu 2: Bulan Ini -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 flex items-start gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-2xl flex-shrink-0">
                <i class="bi bi-calendar-plus"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Periode Pemantauan</p>
                <h3 class="text-3xl font-extrabold text-pmc-navy tracking-tight leading-none mb-2"><?= number_format($kpi['bulan_ini'], 0, ',', '.') ?> <span class="text-sm font-medium text-slate-500">Pasien</span></h3>
                <p class="text-xs text-slate-500"><?= Html::encode($kpi['label_sekarang']) ?></p>
            </div>
        </div>

        <!-- Kartu 3: GAP / Selisih -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 flex items-start gap-4 transition-transform hover:-translate-y-1 duration-300 relative overflow-hidden">
            <!-- Decorative background blob -->
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-<?= $kpi['warna'] ?>-50 opacity-50"></div>
            
            <div class="w-12 h-12 rounded-lg bg-<?= $kpi['warna'] ?>-100 text-<?= $kpi['warna'] ?>-600 flex items-center justify-center text-2xl flex-shrink-0 z-10">
                <i class="bi <?= $kpi['selisih'] >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' ?>"></i>
            </div>
            <div class="z-10">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Selisih (GAP)</p>
                <div class="flex items-baseline gap-2 mb-2">
                    <h3 class="text-3xl font-extrabold text-<?= $kpi['warna'] ?>-600 tracking-tight leading-none">
                        <?= $kpi['selisih'] > 0 ? '+' : '' ?><?= number_format($kpi['selisih'], 0, ',', '.') ?>
                    </h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-<?= $kpi['warna'] ?>-100 text-<?= $kpi['warna'] ?>-800">
                        <?= $kpi['selisih'] > 0 ? '+' : '' ?><?= $kpi['persentase'] ?>%
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Narasi Data Row -->
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-4 flex items-start gap-3">
        <i class="bi bi-info-circle-fill text-blue-500 text-lg mt-0.5"></i>
        <p class="text-slate-700 text-sm leading-relaxed">
            <strong>Kesimpulan Otomatis:</strong> <?= $kpi['narasi'] ?>
        </p>
    </div>

    <?php if ($kpi['belum_closing'] > 0): ?>
    <!-- Peringatan GAP Pendaftaran vs Closing -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-8 flex items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-amber-500 text-lg mt-0.5"></i>
        <div class="text-slate-700 text-sm leading-relaxed">
            <strong class="text-amber-700">Insight Operasional:</strong> Terdapat 
            <span class="font-bold text-amber-600 px-1 text-base"><?= number_format($kpi['belum_closing'], 0, ',', '.') ?></span> 
            pasien yang telah terdaftar pada periode ini namun <strong>belum melakukan tahapan Closing Kasir</strong>. 
            <p class="text-xs text-slate-500 mt-1">Ini bisa merepresentasikan tagihan asuransi yang sedang diproses, pasien batal periksa, atau pembayaran tertunda.</p>
        </div>
    </div>
    <?php else: ?>
    <div class="mb-8"></div>
    <?php endif; ?>

    <!-- Chart Area -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Perbandingan Tren Harian</h3>
        
        <div class="relative h-[400px] w-full">
            <canvas id="komparasiChart"></canvas>
        </div>
    </div>
</div>

<?php 
$js = <<<JS
    // 1. Inisialisasi Chart.js
    const chartParams = $chartJsParams;
    const ctx = document.getElementById('komparasiChart').getContext('2d');
    
    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#64748b'; // slate-500

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartParams.labels,
            datasets: [
                {
                    label: chartParams.label_sekarang,
                    data: chartParams.data_sekarang,
                    borderColor: '#002D72', // pmc-navy
                    backgroundColor: 'rgba(0, 45, 114, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#002D72',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    order: 1 // Drawn on top
                },
                {
                    label: chartParams.label_lalu,
                    data: chartParams.data_lalu,
                    borderColor: '#cbd5e1', // slate-300
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    pointBackgroundColor: '#cbd5e1',
                    pointBorderColor: '#ffffff',
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    order: 2 // Drawn behind
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
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        font: { weight: '600', size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { maxRotation: 45, minRotation: 45 }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9', // slate-100
                        borderDash: [5, 5],
                        drawBorder: false
                    },
                    border: { display: false }
                }
            }
        }
    });

    // 2. Fungsi Quick Filter Buttons
    function setQuickFilter(type) {
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const filterTypeInput = document.getElementById('filterType');
        
        let today = new Date();
        let yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        
        if (type === 'first_10') {
            startDateInput.value = yyyy + '-' + mm + '-01';
            endDateInput.value = yyyy + '-' + mm + '-10';
        } else if (type === 'mtd') {
            let dd = String(today.getDate()).padStart(2, '0');
            startDateInput.value = yyyy + '-' + mm + '-01';
            endDateInput.value = yyyy + '-' + mm + '-' + dd;
        } else if (type === 'last_month') {
            let lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            let lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
            
            let lm_yyyy = lastMonth.getFullYear();
            let lm_mm = String(lastMonth.getMonth() + 1).padStart(2, '0');
            
            startDateInput.value = lm_yyyy + '-' + lm_mm + '-01';
            endDateInput.value = lastMonthEnd.getFullYear() + '-' + String(lastMonthEnd.getMonth() + 1).padStart(2, '0') + '-' + String(lastMonthEnd.getDate()).padStart(2, '0');
        }
        
        filterTypeInput.value = type;
        document.getElementById('filterForm').submit();
    }
    
    // Attach to global window object so onclicks work
    window.setQuickFilter = setQuickFilter;
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
