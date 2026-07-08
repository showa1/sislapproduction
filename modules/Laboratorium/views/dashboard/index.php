<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dateFrom string */
/* @var $dateTo string */
/* @var $kunjunganCurr int */  /* @var $kunjunganGrowth float */
/* @var $itemCurr int */       /* @var $itemGrowth float */
/* @var $pendapatanCurr float */ /* @var $pendapatanGrowth float */
/* @var $topPemeriksaan array */
/* @var $pasienBaru int */     /* @var $pasienBaruPct float */
/* @var $rujukanDist array */  /* @var $penjaminDist array */
/* @var $trendHarian array */  /* @var $topJenisPemeriksaan array */
/* @var $statRujukan array */

$this->title = 'Dashboard Laboratorium';

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

$this->registerCss("
body, .content-wrapper { font-family: 'Inter', sans-serif; background: #F4F7FE !important; }
.page-header-title { color: #0F172A; font-weight: 800; font-size: 1.6rem; letter-spacing: -0.5px; margin: 0; }
.page-header-sub   { color: #64748B; font-size: 0.88rem; margin: 2px 0 0; }

/* Filter Bar */
.filter-bar { background:#fff; border-radius:14px; border:1px solid #E8EDF5; box-shadow:0 2px 12px rgba(0,0,0,0.03); padding:16px 20px; margin-bottom:24px; }
.filter-bar .form-control, .filter-bar .form-select { border-radius:8px; border:1px solid #E2E8F0; font-size:.87rem; height:38px; padding:0 12px; }
.btn-filter { background:#002D72; color:#fff; border:none; border-radius:8px; padding:8px 20px; font-weight:700; font-size:.87rem; }
.btn-filter:hover { background:#1D4ED8; color:#fff; }
.btn-laporan { background:#fff; color:#002D72; border:2px solid #002D72; border-radius:8px; padding:6px 18px; font-weight:700; font-size:.85rem; text-decoration:none; }
.btn-laporan:hover { background:#002D72; color:#fff; }

/* KPI Cards */
.kpi-card {
    background:#fff; border-radius:16px; border:1px solid #F1F5F9;
    box-shadow:0 6px 24px rgba(15,23,42,0.04); padding:22px 24px;
    transition:all .25s ease; height:100%; position:relative; overflow:hidden;
}
.kpi-card:hover { transform:translateY(-3px); box-shadow:0 14px 32px rgba(15,23,42,0.08); }
.kpi-label { font-size:.7rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:1px; }
.kpi-value { font-size:2rem; font-weight:800; color:#0F172A; line-height:1.2; margin:8px 0 5px; }
.kpi-value.sm { font-size:1.3rem; }
.kpi-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
.trend-badge { font-size:.8rem; font-weight:700; display:inline-flex; align-items:center; gap:3px; }
.trend-badge.up   { color:#10B981; } .trend-badge.down { color:#EF4444; } .trend-badge.neutral { color:#94A3B8; }
.kpi-sub { font-size:.75rem; color:#94A3B8; margin-top:3px; }

.kpi-blue   .kpi-icon { background:rgba(59,130,246,.1);  color:#3B82F6; }
.kpi-green  .kpi-icon { background:rgba(16,185,129,.1);  color:#10B981; }
.kpi-purple .kpi-icon { background:rgba(139,92,246,.1);  color:#8B5CF6; }
.kpi-amber  .kpi-icon { background:rgba(245,158,11,.1);  color:#F59E0B; }
.kpi-cyan   .kpi-icon { background:rgba(14,165,233,.1);  color:#0EA5E9; }

/* Chart Cards */
.chart-card {
    background:#fff; border-radius:16px; border:1px solid #F1F5F9;
    box-shadow:0 6px 24px rgba(15,23,42,0.04); padding:24px; margin-bottom:24px; height:100%;
}
.chart-title { font-size:1rem; font-weight:700; color:#0F172A; }
.chart-sub   { font-size:.78rem; color:#94A3B8; margin-top:2px; }

/* Table in cards */
.tbl-lab { width:100%; border-collapse:collapse; font-size:.88rem; }
.tbl-lab thead th {
    padding:10px 12px; border-bottom:2px solid #E2E8F0;
    font-size:.7rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.8px; color:#94A3B8; white-space:nowrap;
}
.tbl-lab tbody td { padding:12px; border-bottom:1px solid #F8FAFF; color:#475569; vertical-align:middle; }
.tbl-lab tbody tr:last-child td { border-bottom:none; }
.tbl-lab tbody tr:hover td { background:#F8FAFF; }
.rank-badge { width:24px; height:24px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; }
.rank-1 { background:#FEF3C7; color:#D97706; }
.rank-2 { background:#F1F5F9; color:#475569; }
.rank-3 { background:#FEF3C7; color:#92400E; }
.rank-n { background:#F8FAFF; color:#94A3B8; }

/* Stat Row */
.stat-chip { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:20px; font-size:.8rem; font-weight:600; background:#F8FAFF; border:1px solid #E2E8F0; color:#475569; }
.stat-chip .dot { width:8px; height:8px; border-radius:50%; }

/* Section Label */
.section-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:#94A3B8; margin-bottom:14px; padding-left:4px; margin-top:8px; }
.divider-section { border-top:1px solid #F1F5F9; margin:20px 0; }
");

function labGrowth($v) {
    if ($v > 0) return "<span class='trend-badge up'><i class='bi bi-arrow-up'></i> +{$v}%</span>";
    if ($v < 0) return "<span class='trend-badge down'><i class='bi bi-arrow-down'></i> {$v}%</span>";
    return "<span class='trend-badge neutral'><i class='bi bi-dash'></i> 0%</span>";
}
function fmtRp($n) {
    if ($n >= 1_000_000_000) return 'Rp ' . number_format($n/1_000_000_000, 2, '.', ',') . ' M';
    return 'Rp ' . number_format($n/1_000_000, 0, ',', '.') . ' jt';
}
function fmtNum($n) { return number_format((int)$n, 0, ',', '.'); }
?>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid p-0">

    <!-- ======== HEADER ======== -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-7">
            <h1 class="page-header-title"><i class="bi bi-droplet-half text-primary" style="font-size:1.4rem;"></i> Dashboard Laboratorium</h1>
            <p class="page-header-sub">Data real-time dari sistem SIM RS &middot; <?= Html::encode($dateFrom) ?> s/d <?= Html::encode($dateTo) ?></p>
        </div>
        <div class="col-md-5 text-md-right mt-2 mt-md-0">
            <a href="<?= Url::to(['/laboratorium/dashboard/laporan', 'date_from' => $dateFrom, 'date_to' => $dateTo]) ?>" class="btn-laporan mr-2">
                <i class="bi bi-table"></i> Laporan Detail
            </a>
        </div>
    </div>

    <!-- ======== FILTER BAR ======== -->
    <div class="filter-bar">
        <form method="get" action="<?= Url::to(['/laboratorium/dashboard/index']) ?>" class="row align-items-end" style="margin:0;">
            <input type="hidden" name="r" value="laboratorium/dashboard/index">
            <div class="col-md-4 pr-2">
                <label class="small font-weight-bold text-muted d-block mb-1">Tanggal Mulai</label>
                <input type="date" name="date_from" class="form-control" value="<?= Html::encode($dateFrom) ?>">
            </div>
            <div class="col-md-4 pr-2">
                <label class="small font-weight-bold text-muted d-block mb-1">Tanggal Akhir</label>
                <input type="date" name="date_to" class="form-control" value="<?= Html::encode($dateTo) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-filter w-100">
                    <i class="bi bi-funnel-fill"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- ======== KPI CARDS (Row 1) ======== -->
    <div class="section-label">Performa Layanan Laboratorium</div>
    <div class="row mb-3">
        <!-- Total Kunjungan -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-blue">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="kpi-label">Total Kunjungan Lab</span>
                    <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
                </div>
                <div class="kpi-value"><?= fmtNum($kunjunganCurr) ?></div>
                <div><?= labGrowth($kunjunganGrowth) ?> <span class="kpi-sub">vs bln lalu</span></div>
                <div class="kpi-sub mt-1"><strong><?= $pasienBaruPct ?>%</strong> Pasien Baru (<?= fmtNum($pasienBaru) ?>)</div>
            </div>
        </div>
        <!-- Total Item -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-purple">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="kpi-label">Total Item Pemeriksaan</span>
                    <div class="kpi-icon"><i class="bi bi-clipboard2-pulse-fill"></i></div>
                </div>
                <div class="kpi-value"><?= fmtNum($itemCurr) ?></div>
                <div><?= labGrowth($itemGrowth) ?> <span class="kpi-sub">vs bln lalu</span></div>
                <div class="kpi-sub mt-1">Rata-rata <?= $kunjunganCurr > 0 ? round($itemCurr/$kunjunganCurr, 1) : 0 ?> item/kunjungan</div>
            </div>
        </div>
        <!-- Pendapatan -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-green">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="kpi-label">Pendapatan Laboratorium</span>
                    <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
                </div>
                <div class="kpi-value sm"><?= fmtRp($pendapatanCurr) ?></div>
                <div><?= labGrowth($pendapatanGrowth) ?> <span class="kpi-sub">vs bln lalu</span></div>
                <div class="kpi-sub mt-1">Rata-rata <?= $kunjunganCurr > 0 ? fmtRp($pendapatanCurr/$kunjunganCurr) : 'Rp 0' ?>/kunjungan</div>
            </div>
        </div>
        <!-- Top Pemeriksaan -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="kpi-card kpi-amber">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="kpi-label">Pemeriksaan Terbanyak</span>
                    <div class="kpi-icon"><i class="bi bi-trophy-fill"></i></div>
                </div>
                <?php if ($topPemeriksaan): ?>
                <div class="kpi-value sm mt-2" style="color:#D97706;" title="<?= Html::encode($topPemeriksaan['nama'] ?? '') ?>">
                    <?= Html::encode(strlen($topPemeriksaan['nama'] ?? '') > 22 ? substr($topPemeriksaan['nama'],0,22).'…' : $topPemeriksaan['nama']) ?>
                </div>
                <div class="kpi-sub mt-1"><strong><?= fmtNum($topPemeriksaan['total'] ?? 0) ?></strong> permintaan</div>
                <?php else: ?>
                <div class="kpi-sub mt-3">Belum ada data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ======== CHARTS ROW ======== -->
    <div class="divider-section"></div>
    <div class="section-label">Visualisasi &amp; Distribusi</div>
    <div class="row">
        <!-- Trend Harian -->
        <div class="col-lg-8 mb-4">
            <div class="chart-card">
                <div class="chart-title">Tren Kunjungan &amp; Pendapatan Harian</div>
                <div class="chart-sub">Jumlah kunjungan dan pendapatan per hari dalam periode terpilih</div>
                <div id="chart-trend" style="min-height:300px;margin-top:12px;"></div>
            </div>
        </div>
        <!-- Donut Cara Bayar -->
        <div class="col-lg-4 mb-4">
            <div class="chart-card">
                <div class="chart-title">Distribusi Cara Bayar</div>
                <div class="chart-sub">Komposisi penjamin pasien lab periode ini</div>
                <div id="chart-penjamin" style="min-height:300px;margin-top:12px;"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Asal Rujukan Bar -->
        <div class="col-lg-5 mb-4">
            <div class="chart-card">
                <div class="chart-title">Asal Rujukan Pasien</div>
                <div class="chart-sub">Distribusi institusi perujuk ke laboratorium</div>
                <div id="chart-rujukan" style="min-height:280px;margin-top:12px;"></div>
            </div>
        </div>
        <!-- Top Jenis Pemeriksaan -->
        <div class="col-lg-7 mb-4">
            <div class="chart-card">
                <div class="chart-title">Top 10 Pemeriksaan Terbanyak</div>
                <div class="chart-sub">Berdasarkan jumlah permintaan dalam periode ini</div>
                <div class="table-responsive mt-3">
                    <table class="tbl-lab">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pemeriksaan</th>
                                <th>Jenis</th>
                                <th class="text-right">Kunjungan</th>
                                <th class="text-right">Item</th>
                                <th class="text-right">Tarif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topJenisPemeriksaan as $i => $row): ?>
                            <tr>
                                <td><span class="rank-badge rank-<?= $i < 3 ? ($i+1) : 'n' ?>"><?= $i+1 ?></span></td>
                                <td style="font-weight:600;color:#0F172A;"><?= Html::encode($row['nama_pemeriksaan'] ?? '-') ?></td>
                                <td><span class="stat-chip" style="padding:3px 8px;font-size:.72rem;"><?= Html::encode($row['jenis'] ?? '-') ?></span></td>
                                <td class="text-right" style="font-weight:600;"><?= fmtNum($row['total_kunjungan']) ?></td>
                                <td class="text-right"><?= fmtNum($row['total_item']) ?></td>
                                <td class="text-right" style="color:#10B981;font-weight:600;"><?= fmtRp($row['total_tarif']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topJenisPemeriksaan)): ?>
                            <tr><td colspan="6" class="text-center" style="color:#94A3B8;padding:20px;">Belum ada data pemeriksaan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ======== STATISTIK RUJUKAN × CARA BAYAR ======== -->
    <div class="divider-section"></div>
    <div class="section-label">Matriks Asal Rujukan × Penjamin</div>
    <div class="chart-card">
        <div class="chart-title">Rincian Kunjungan per Asal Rujukan &amp; Cara Bayar</div>
        <div class="chart-sub">Tampilkan volume dan tarif berdasarkan kombinasi institusi perujuk dan penjamin</div>
        <div class="table-responsive mt-3">
            <table class="tbl-lab">
                <thead>
                    <tr>
                        <th>Asal Rujukan</th>
                        <th>Cara Bayar / Penjamin</th>
                        <th class="text-right">Kunjungan</th>
                        <th class="text-right">Item Periksa</th>
                        <th class="text-right">Total Tarif</th>
                        <th style="min-width:100px;">% Kunjungan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalKunj = array_sum(array_column($statRujukan, 'jumlah_kunjungan')) ?: 1;
                    $cbColors = ['BPJS Kesehatan'=>'#1D4ED8','PRIBADI'=>'#7C3AED','ASURANSI'=>'#D97706','BPJS Ketenagakerjaan'=>'#0D9488'];
                    foreach ($statRujukan as $s):
                        $pct = round(($s['jumlah_kunjungan']/$totalKunj)*100, 1);
                        $color = $cbColors[$s['cara_bayar']] ?? '#64748B';
                    ?>
                    <tr>
                        <td style="font-weight:600;color:#0F172A;"><?= Html::encode($s['asal_rujukan'] ?? '-') ?></td>
                        <td>
                            <span class="stat-chip" style="border-color:<?= $color ?>;color:<?= $color ?>;">
                                <span class="dot" style="background:<?= $color ?>;"></span>
                                <?= Html::encode($s['cara_bayar'] ?? '-') ?>
                            </span>
                        </td>
                        <td class="text-right font-weight-bold"><?= fmtNum($s['jumlah_kunjungan']) ?></td>
                        <td class="text-right"><?= fmtNum($s['jumlah_item']) ?></td>
                        <td class="text-right" style="color:#10B981;font-weight:600;"><?= fmtRp($s['total_tarif']) ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;height:6px;background:#E2E8F0;border-radius:9px;">
                                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $color ?>;border-radius:9px;"></div>
                                </div>
                                <span style="font-size:.78rem;font-weight:700;color:#475569;white-space:nowrap;"><?= $pct ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($statRujukan)): ?>
                    <tr><td colspan="6" class="text-center" style="color:#94A3B8;padding:20px;">Belum ada data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
// ---- Prepare JS data ----
$trendLabels   = json_encode(array_column($trendHarian, 'tgl'));
$trendKunj     = json_encode(array_map('intval', array_column($trendHarian, 'total')));
$trendPend     = json_encode(array_map(fn($v) => round($v/1_000_000, 1), array_column($trendHarian, 'pendapatan')));

$penjLabels    = json_encode(array_column($penjaminDist, 'label'));
$penjValues    = json_encode(array_map('intval', array_column($penjaminDist, 'total')));

$rujLabels     = json_encode(array_column($rujukanDist, 'label'));
$rujValues     = json_encode(array_map('intval', array_column($rujukanDist, 'total')));

$this->registerJs("
(function() {
    if (typeof ApexCharts === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
        s.onload = initCharts;
        document.head.appendChild(s);
    } else { initCharts(); }

    function initCharts() {

    // ---- 1. Trend Harian (dual y-axis: bar kunjungan + line pendapatan) ----
    var trendLabels = {$trendLabels};
    var trendKunj   = {$trendKunj};
    var trendPend   = {$trendPend};

    new ApexCharts(document.querySelector('#chart-trend'), {
        series: [
            { name: 'Kunjungan', type: 'bar',  data: trendKunj },
            { name: 'Pendapatan (jt)', type: 'line', data: trendPend }
        ],
        chart: { height: 300, fontFamily: 'Inter,sans-serif', toolbar:{ show:false }, animations:{ speed:400 } },
        colors: ['#3B82F6','#10B981'],
        stroke: { width:[0,2.5], curve:'smooth' },
        fill: { opacity:[0.85,1] },
        dataLabels: { enabled: false },
        xaxis: { categories: trendLabels, labels:{ rotate:-30, style:{ colors:'#94A3B8', fontSize:'11px' } }, axisBorder:{show:false}, axisTicks:{show:false} },
        yaxis: [
            { title:{ text:'Kunjungan', style:{color:'#3B82F6',fontWeight:700} }, labels:{ style:{colors:'#3B82F6'} } },
            { opposite:true, title:{ text:'Pendapatan (jt Rp)', style:{color:'#10B981',fontWeight:700} }, labels:{ style:{colors:'#10B981'}, formatter:function(v){ return v+'jt'; } } }
        ],
        grid: { borderColor:'#F1F5F9', strokeDashArray:4 },
        tooltip: { shared:true, intersect:false },
        legend: { position:'top', horizontalAlign:'left', fontSize:'12px', fontWeight:600 },
        plotOptions: { bar:{ borderRadius:5, columnWidth:'55%' } }
    }).render();

    // ---- 2. Donut Penjamin ----
    var penjLabels = {$penjLabels};
    var penjValues = {$penjValues};
    if (penjLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-penjamin'), {
            series: penjValues,
            labels: penjLabels,
            chart: { type:'donut', height:300, fontFamily:'Inter,sans-serif', animations:{speed:400} },
            colors: ['#1D4ED8','#7C3AED','#F59E0B','#0D9488','#EF4444','#64748B'],
            dataLabels: { enabled:true, formatter:function(val){ return parseFloat(val).toFixed(1)+'%'; } },
            plotOptions: { pie:{ donut:{ size:'65%', labels:{ show:true, total:{ show:true, label:'Total', formatter:function(w){ return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0).toLocaleString('id-ID'); } } } } } },
            legend: { position:'bottom', fontSize:'12px', fontWeight:600 },
            tooltip: { y:{ formatter:function(v){ return v.toLocaleString('id-ID') + ' kunjungan'; } } }
        }).render();
    }

    // ---- 3. Bar Asal Rujukan ----
    var rujLabels = {$rujLabels};
    var rujValues = {$rujValues};
    if (rujLabels.length > 0) {
        new ApexCharts(document.querySelector('#chart-rujukan'), {
            series: [{ name:'Kunjungan', data: rujValues }],
            chart: { type:'bar', height:280, fontFamily:'Inter,sans-serif', toolbar:{show:false}, animations:{speed:400} },
            colors: ['#002D72'],
            plotOptions: { bar:{ horizontal:true, borderRadius:6, dataLabels:{position:'right'} } },
            dataLabels: { enabled:true, offsetX:4, style:{fontSize:'11px',fontWeight:700}, formatter:function(v){ return v.toLocaleString('id-ID'); } },
            xaxis: { categories: rujLabels, labels:{ style:{colors:'#94A3B8',fontSize:'11px'} }, axisBorder:{show:false} },
            yaxis: { labels:{ style:{colors:'#475569',fontSize:'12px',fontWeight:600} } },
            grid: { borderColor:'#F1F5F9', strokeDashArray:4, xaxis:{lines:{show:true}}, yaxis:{lines:{show:false}} },
            tooltip: { y:{ formatter:function(v){ return v.toLocaleString('id-ID') + ' kunjungan'; } } }
        }).render();
    }

    } // end initCharts
})();
");
?>
