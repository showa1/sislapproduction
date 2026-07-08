<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\eksekutif\models\DashboardSearch */
/* @var $activeTab string */
/* @var $rj_curr int */
/* @var $rj_growth float */
/* @var $rj_baru_pct float */
/* @var $igd_curr int */
/* @var $igd_growth float */
/* @var $ranap_curr int */
/* @var $ranap_growth float */
/* @var $bor_pct float */
/* @var $labrad_curr int */
/* @var $labrad_growth float */
/* @var $rjPenjaminData array */
/* @var $rjPenjaminLabels array */
/* @var $rjPenjaminValues array */
/* @var $rjTotalPenjamin int */
/* @var $trendRJ array */
/* @var $trendIGD array */
/* @var $trendRI array */
/* @var $trendPenunjang array */
/* @var $heatmapSeries array */
/* @var $donutLabels array */
/* @var $donutValues array */
/* @var $dataProviderPenunjang yii\data\ArrayDataProvider */
/* @var $year string */
/* @var $kpiPenjamin array */
/* @var $kpiClosing array */
/* @var $pendapatanData array */

$this->title = 'Ringkasan Eksekutif';

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

$this->registerCss("
    body { font-family: 'Inter', sans-serif; background-color: #F8F9FC; }
    .content-wrapper { background-color: #F8F9FC !important; padding: 30px !important; }
    .page-title { color: #0F172A; font-weight: 800; font-size: 1.6rem; letter-spacing: -0.5px; margin-bottom: 2px; }
    .page-subtitle { color: #64748B; font-size: 0.9rem; }

    /* ---- Tab Toggle ---- */
    .btn-toggle-grp { background: #F1F5F9; padding: 4px; border-radius: 10px; display: inline-flex; }
    .btn-toggle {
        border: none; background: transparent; padding: 7px 20px; border-radius: 7px;
        font-size: 0.88rem; font-weight: 600; color: #64748B; transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-toggle.active { background: #fff; color: #0F172A; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }

    /* ---- Filter Card ---- */
    .filter-card { background:#fff; border-radius:14px; border:1px solid #F1F5F9; box-shadow:0 4px 20px rgba(0,0,0,0.02); margin-bottom:24px; }

    /* ---- Generic KPI Card ---- */
    .kpi-card {
        border:none; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,0.04);
        background:#fff; padding:22px 24px; transition:all .3s ease;
        border:1px solid #F1F5F9; height:100%; position:relative; overflow:hidden;
    }
    .kpi-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px rgba(15,23,42,0.07); }
    .kpi-label { font-size:.72rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:1px; }
    .kpi-icon-wrapper { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
    .kpi-value { font-size:2rem; font-weight:800; color:#0F172A; line-height:1.15; margin:8px 0 6px; }
    .trend-indicator { font-size:.82rem; font-weight:600; display:inline-flex; align-items:center; gap:3px; }
    .trend-indicator.up { color:#10B981; } .trend-indicator.down { color:#EF4444; } .trend-indicator.neutral { color:#64748B; }
    .sub-info { font-size:.78rem; color:#94A3B8; margin-top:3px; }

    .kpi-rj .kpi-icon-wrapper { background:rgba(59,130,246,.1); color:#3B82F6; }
    .kpi-igd .kpi-icon-wrapper { background:rgba(239,68,68,.1); color:#EF4444; }
    .kpi-igd .kpi-value { color:#EF4444; }
    .kpi-ranap .kpi-icon-wrapper { background:rgba(16,185,129,.1); color:#10B981; }
    .kpi-labrad .kpi-icon-wrapper { background:rgba(139,92,246,.1); color:#8B5CF6; }
    .kpi-penjamin .kpi-icon-wrapper { background:rgba(14,165,233,.1); color:#0EA5E9; }
    .kpi-penjamin-card {
        border:none; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,0.04);
        background:#fff; padding:18px 20px 14px; transition:all .3s ease;
        border:1px solid #F1F5F9; height:100%; position:relative; overflow:hidden;
    }
    .kpi-penjamin-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px rgba(15,23,42,0.07); }
    .penjamin-legend { display:flex; flex-direction:column; gap:3px; margin-top:6px; }
    .penjamin-legend-item { display:flex; align-items:center; gap:5px; font-size:.71rem; font-weight:600; color:#475569; }
    .penjamin-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .penjamin-pct { margin-left:auto; font-weight:700; color:#0F172A; font-size:.72rem; }

    /* ---- Pendapatan KPI Cards ---- */
    .kpi-pend-bpjs  { background: linear-gradient(135deg,#EFF6FF 0%,#DBEAFE 100%); border-color:#BFDBFE; }
    .kpi-pend-jr    { background: linear-gradient(135deg,#F0FDF4 0%,#DCFCE7 100%); border-color:#BBF7D0; }
    .kpi-pend-asur  { background: linear-gradient(135deg,#FFF7ED 0%,#FED7AA 100%); border-color:#FDBA74; }
    .kpi-pend-umum  { background: linear-gradient(135deg,#FAF5FF 0%,#E9D5FF 100%); border-color:#D8B4FE; }
    .pend-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; margin-bottom:4px; }
    .pend-amount { font-size:1.9rem; font-weight:800; line-height:1.2; }
    .pend-sub { font-size:.8rem; margin-top:4px; }
    .pend-bpjs-color  { color:#1D4ED8; } .pend-jr-color  { color:#15803D; }
    .pend-asur-color  { color:#C2410C; } .pend-umum-color { color:#7C3AED; }

    /* ---- Total Revenue Banner ---- */
    .revenue-banner {
        background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #0F172A 100%);
        border-radius: 16px; padding: 28px 32px; margin-bottom: 24px;
        display: flex; justify-content: space-between; align-items: center;
        color: #fff; position: relative; overflow: hidden;
    }
    .revenue-banner::before {
        content: ''; position:absolute; right:-60px; top:-60px;
        width:220px; height:220px; border-radius:50%;
        background: rgba(255,255,255,0.03);
    }
    .rev-banner-label { font-size:.7rem; font-weight:700; letter-spacing:1.5px; color:#94A3B8; text-transform:uppercase; margin-bottom:8px; }
    .rev-banner-amount { font-size:2.4rem; font-weight:900; color:#fff; letter-spacing:-0.5px; }
    .rev-banner-period { font-size:.9rem; color:#94A3B8; font-weight:500; }

    /* ---- Charts ---- */
    .card-chart {
        border:none; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,0.04);
        background:#fff; padding:24px; margin-bottom:24px; border:1px solid #F1F5F9; height:100%;
    }
    .chart-title { font-size:1.05rem; font-weight:700; color:#0F172A; }
    .chart-subtitle { font-size:.78rem; color:#94A3B8; margin-top:2px; }

    /* ---- Rincian Table ---- */
    .rincian-table { width:100%; border-collapse:collapse; font-size:.9rem; }
    .rincian-table thead th {
        padding:10px 14px; border-bottom:2px solid #E2E8F0;
        font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px;
        color:#94A3B8; white-space:nowrap;
    }
    .rincian-table tbody td { padding:13px 14px; border-bottom:1px solid #F1F5F9; color:#475569; vertical-align:middle; }
    .rincian-table tbody tr:last-child td { border-bottom:none; }
    .rincian-table tbody tr:hover td { background:#F8FAFF; }
    .unit-name { font-weight:700; color:#0F172A; }
    .cb-bpjs  { color:#1D4ED8; font-weight:600; }
    .cb-jr    { color:#15803D; font-weight:600; }
    .cb-asur  { color:#C2410C; font-weight:600; }
    .cb-umum  { color:#7C3AED; font-weight:600; }
    .cb-total { font-weight:800; color:#0F172A; }
    .pct-bar-wrap { display:flex; align-items:center; gap:10px; }
    .pct-bar-track { flex:1; height:6px; background:#E2E8F0; border-radius:9px; }
    .pct-bar-fill  { height:100%; background:#3B82F6; border-radius:9px; }

    /* ---- Mini Progress Bars (penjamin per card) ---- */
    .mini-penjamin-wrap { margin-top:10px; padding-top:10px; border-top:1px solid #F1F5F9; }
    .mini-pb-row { display:flex; align-items:center; gap:6px; margin-bottom:5px; }
    .mini-pb-row:last-child { margin-bottom:0; }
    .mini-pb-label { font-size:.68rem; font-weight:700; color:#64748B; min-width:52px; white-space:nowrap; }
    .mini-pb-track { flex:1; height:5px; background:#EFF3F8; border-radius:99px; overflow:hidden; }
    .mini-pb-fill  { height:100%; border-radius:99px; transition:width .6s cubic-bezier(.4,0,.2,1); }
    .mini-pb-val   { font-size:.68rem; font-weight:700; color:#94A3B8; min-width:34px; text-align:right; white-space:nowrap; }

    /* ---- Closing Status Row ---- */
    .closing-status-row { display:flex; align-items:center; justify-content:space-between; margin-top:12px; padding-top:10px; border-top:1px dashed #E2E8F0; font-size:.72rem; font-weight:700; }
    .closing-item { display:flex; align-items:center; gap:5px; }
    .closing-done { color:#10B981; }
    .closing-pending { color:#F59E0B; }
    .closing-icon { font-size:.85rem; }

    /* ---- 5-column grid ---- */
    @media (min-width:1200px) { .col-custom-5 { width:20%; flex:0 0 20%; max-width:20%; } }

    /* ---- Trend Monitoring (TradingView Dark Theme) ---- */
    .trend-card {
        background:#131722; border-radius:16px; border:1px solid #2a2e39;
        box-shadow:0 12px 32px rgba(0,0,0,0.25); padding:24px; margin-bottom:24px;
        color: #d1d4dc;
    }
    .trend-card .chart-title { color: #ffffff !important; }
    .trend-card .chart-subtitle { color: #868996 !important; }
    
    .scope-btn-grp { background:#1c2030; padding:4px; border-radius:10px; display:inline-flex; border:1px solid #2a2e39; }
    .scope-btn {
        border:none; background:transparent; padding:6px 18px; border-radius:7px;
        font-size:.82rem; font-weight:700; color:#868996; cursor:pointer;
        transition:all .2s ease; letter-spacing:.3px;
    }
    .scope-btn.active { background:#2962ff; color:#ffffff; box-shadow:0 2px 10px rgba(41,98,255,0.4); }
    .scope-btn:hover:not(.active) { background:#2a2e39; color:#ffffff; }

    .trend-kpi-row { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px; }
    .trend-kpi-chip {
        display:inline-flex; align-items:center; gap:8px; padding:6px 14px;
        border-radius:20px; font-size:.82rem; font-weight:600;
        background:#1c2030 !important; border:1px solid #2a2e39;
    }
    .trend-kpi-chip .chip-dot { width:8px; height:8px; border-radius:50%; }
    .trend-kpi-chip .chip-label { color:#868996; }
    .trend-kpi-chip .chip-val   { color:#ffffff; font-weight:700; }

    #trend-loading {
        position:absolute; inset:0; display:none; background:rgba(19,23,34,0.7);
        border-radius:12px; z-index:10; align-items:center; justify-content:center;
    }
    #trend-loading.show { display:flex; }
    .spinner-ring {
        width:36px; height:36px; border:3px solid #2a2e39;
        border-top-color:#2962ff; border-radius:50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform:rotate(360deg); } }
    .brush-label { font-size:.7rem; color:#868996; font-weight:600; text-transform:uppercase;
        letter-spacing:.8px; margin-bottom:6px; }
");

// Mini progress bar helper — renders penjamin rows inside a KPI card
function renderPenjaminBars(array $rows): string {
    if (empty($rows)) return '';
    $html = '<div class="mini-penjamin-wrap">';
    foreach ($rows as $r) {
        $pct   = $r['pct'];
        $color = $r['color'];
        $label = $r['label'];
        $val   = number_format($r['val'], 0, ',', '.');
        $html .= "<div class='mini-pb-row'>";
        $html .= "<span class='mini-pb-label'>{$label}</span>";
        $html .= "<div class='mini-pb-track'><div class='mini-pb-fill' style='width:{$pct}%;background:{$color};'></div></div>";
        $html .= "<span class='mini-pb-val'>{$val}</span>";
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

// Closing status helper — renders the closing/pending counts inside a KPI card
function renderClosingStatus(array $data): string {
    if (empty($data)) return '';
    $closing = number_format($data['closing'] ?? 0, 0, ',', '.');
    $pending = number_format($data['pending'] ?? 0, 0, ',', '.');
    return "
        <div class='closing-status-row'>
            <div class='closing-item closing-done'>
                <i class='bi bi-check-circle-fill closing-icon'></i>
                <span>Sudah Bayar: {$closing}</span>
            </div>
            <div class='closing-item closing-pending'>
                <i class='bi bi-clock-fill closing-icon'></i>
                <span>Belum Bayar: {$pending}</span>
            </div>
        </div>
    ";
}

// Growth badge helper
function renderGrowthBadge($val) {
    if ($val > 0) return "<span class='trend-indicator up'><i class='bi bi-arrow-up'></i> +{$val}%</span>";
    if ($val < 0) return "<span class='trend-indicator down'><i class='bi bi-arrow-down'></i> {$val}%</span>";
    return "<span class='trend-indicator neutral'><i class='bi bi-dash'></i> 0%</span>";
}

// Currency formatter: "Rp 3.83 M" / "Rp 267 jt" / "Rp 312 jt"
function formatCurrency($amount) {
    if ($amount >= 1_000_000_000) {
        return 'Rp ' . number_format($amount / 1_000_000_000, 2, '.', ',') . ' M';
    }
    return 'Rp ' . number_format($amount / 1_000_000, 0, ',', '.') . ' jt';
}

// Build tab URLs
$tabBaseParams = ['DashboardSearch[date_from]' => $searchModel->date_from, 'DashboardSearch[date_to]' => $searchModel->date_to];
$urlRingkasan = \yii\helpers\Url::to(array_merge(['/eksekutif/dashboard/index', 'tab' => 'ringkasan'], $tabBaseParams));
$urlPendapatan = \yii\helpers\Url::to(array_merge(['/eksekutif/dashboard/index', 'tab' => 'pendapatan'], $tabBaseParams));
?>
<!-- ApexCharts: loaded inline to guarantee it runs before our chart JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid p-0">

    <!-- === HEADER === -->
    <div class="row mb-3 align-items-center">
        <div class="col-sm-8">
            <h1 class="page-title">Ringkasan Eksekutif</h1>
            <p class="page-subtitle">Periode: Bulan <?= date('F Y', strtotime($searchModel->date_from)) ?></p>
        </div>
        <div class="col-sm-4 text-right">
            <div class="btn-toggle-grp">
                <a href="<?= $urlRingkasan ?>" class="btn-toggle <?= $activeTab === 'ringkasan' ? 'active' : '' ?>">Ringkasan</a>
                <a href="<?= $urlPendapatan ?>" class="btn-toggle <?= $activeTab === 'pendapatan' ? 'active' : '' ?>">Pendapatan</a>
            </div>
        </div>
    </div>

    <!-- === DATE FILTER === -->
    <div class="filter-card card mb-4">
        <div class="card-body p-3">
            <?php $form = ActiveForm::begin(['action' => ['/eksekutif/dashboard/index', 'tab' => $activeTab], 'method' => 'get', 'options' => ['class' => 'row align-items-end']]); ?>
            <div class="col-md-4">
                <?= $form->field($searchModel, 'date_from')->textInput(['type'=>'date','class'=>'form-control','style'=>'border-radius:8px;'])->label('Tanggal Mulai', ['class'=>'small font-weight-bold text-muted']) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($searchModel, 'date_to')->textInput(['type'=>'date','class'=>'form-control','style'=>'border-radius:8px;'])->label('Tanggal Akhir', ['class'=>'small font-weight-bold text-muted']) ?>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <?= Html::submitButton('<i class="bi bi-filter"></i> Terapkan Filter', ['class'=>'btn btn-primary btn-block font-weight-bold','style'=>'background:#002D72;border:none;border-radius:8px;padding:10px;']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if ($activeTab === 'pendapatan' && !empty($pendapatanData)): ?>
    <!-- ============================================== -->
    <!-- PENDAPATAN TAB                                 -->
    <!-- ============================================== -->

        <!-- 4 KPI Cards by Cara Bayar -->
        <div class="row mb-3">
            <?php
            $cbConfig = [
                'BPJS Kesehatan'     => ['class'=>'kpi-pend-bpjs','label-class'=>'pend-bpjs-color',  'amount-class'=>'pend-bpjs-color',  'label'=>'BPJS Kesehatan'],
                'Jasa Raharja'       => ['class'=>'kpi-pend-jr',   'label-class'=>'pend-jr-color',    'amount-class'=>'pend-jr-color',    'label'=>'Jasa Raharja'],
                'Asuransi Komersial' => ['class'=>'kpi-pend-asur', 'label-class'=>'pend-asur-color',  'amount-class'=>'pend-asur-color',  'label'=>'Asuransi Komersial'],
                'Umum'               => ['class'=>'kpi-pend-umum', 'label-class'=>'pend-umum-color',  'amount-class'=>'pend-umum-color',  'label'=>'Umum / Pribadi'],
            ];
            foreach ($cbConfig as $cbKey => $cfg):
                $cbData = $pendapatanData['kpiCB'][$cbKey] ?? ['amount'=>0,'pct'=>0];
            ?>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="kpi-card <?= $cfg['class'] ?>" style="border-width:1px;">
                    <div class="pend-label <?= $cfg['label-class'] ?>"><?= $cfg['label'] ?></div>
                    <div class="pend-amount <?= $cfg['amount-class'] ?>"><?= formatCurrency($cbData['amount']) ?></div>
                    <div class="pend-sub" style="color:#64748B;"><?= $cbData['pct'] ?>% total pendapatan</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Total Revenue Dark Banner -->
        <div class="revenue-banner mb-4">
            <div>
                <div class="rev-banner-label">Total Pendapatan Rumah Sakit</div>
                <div class="rev-banner-amount"><?= formatCurrency($pendapatanData['totalRev']) ?></div>
            </div>
            <div class="text-right">
                <div class="rev-banner-period">Periode: <?= date('F Y', strtotime($searchModel->date_from)) ?></div>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- TREND MONITORING PENDAPATAN CARD                        -->
        <!-- ====================================================== -->
        <div class="trend-card">

            <!-- Header Row -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <div class="chart-title">Trend Monitoring Pendapatan
                        <span style="font-size:.8rem;font-weight:400;color:#94A3B8;">(Juta Rp)</span>
                    </div>
                    <div class="chart-subtitle">Garis pergerakan kontribusi penjamin pembayaran per unit terpilih</div>
                </div>
                <!-- Controls: Unit Selector + Scope Buttons -->
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <select id="dropdown-unit" class="form-control d-inline-block w-auto font-weight-bold" style="background:#1c2030; color:#ffffff; border:1px solid #2a2e39; border-radius:8px; height:38px; font-size:.85rem; padding: 0 10px; cursor:pointer;">
                        <option value="Ranap" selected>Rawat Inap (Ranap)</option>
                        <option value="Rajal">Rawat Jalan (Rajal)</option>
                        <option value="IGD">IGD</option>
                        <option value="IBS">IBS</option>
                        <option value="ICU/PICU">ICU/PICU</option>
                        <option value="Hemodialisa">Hemodialisa</option>
                        <option value="Radiologi">Radiologi</option>
                        <option value="Laboratorium">Laboratorium</option>
                        <option value="Farmasi">Farmasi</option>
                        <option value="MCU">MCU</option>
                        <option value="VK">VK</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    
                    <div class="scope-btn-grp" id="scopeBtnGrp">
                        <button class="scope-btn btn-scale" data-scale="Harian"  id="scopeDay">Harian</button>
                        <button class="scope-btn btn-scale active" data-scale="Bulanan" id="scopeMonth">Bulanan</button>
                        <button class="scope-btn btn-scale" data-scale="Tahunan" id="scopeYear">Tahunan</button>
                    </div>
                </div>
            </div>

            <!-- Mini KPI chips (updated via JS, showing payment sources for selected unit) -->
            <div class="trend-kpi-row" id="trendKpiRow">
                <div class="trend-kpi-chip">
                    <div class="chip-dot" style="background:#2962ff;"></div>
                    <span class="chip-label">BPJS</span>
                    <span class="chip-val" id="chipBpjs">—</span>
                </div>
                <div class="trend-kpi-chip">
                    <div class="chip-dot" style="background:#00e676;"></div>
                    <span class="chip-label">Jasa Raharja</span>
                    <span class="chip-val" id="chipJr">—</span>
                </div>
                <div class="trend-kpi-chip">
                    <div class="chip-dot" style="background:#ff9800;"></div>
                    <span class="chip-label">Asuransi</span>
                    <span class="chip-val" id="chipAsuransi">—</span>
                </div>
                <div class="trend-kpi-chip">
                    <div class="chip-dot" style="background:#f50057;"></div>
                    <span class="chip-label">Umum</span>
                    <span class="chip-val" id="chipUmum">—</span>
                </div>
            </div>

            <!-- Main Area Chart (with zoom) -->
            <div style="position:relative;">
                <div id="trend-loading"><div class="spinner-ring"></div></div>
                <div id="chart-trend-main" style="min-height:320px;"></div>
            </div>

            <!-- Brush Navigator -->
            <div class="brush-label mt-3">Navigator — seret untuk memperbesar rentang</div>
            <div id="chart-trend-brush" style="min-height:100px;"></div>

        </div>

        <!-- Rincian Pendapatan Per Unit Table -->
        <div class="card-chart">
            <div class="chart-title mb-1">Rincian Pendapatan Per Unit</div>
            <div class="chart-subtitle mb-3">Detail per unit layanan dan penjamin pembayaran</div>
            <div class="table-responsive">
                <table class="rincian-table">
                    <thead>
                        <tr>
                            <th style="min-width:120px;">Unit</th>
                            <th class="cb-bpjs text-right">BPJS</th>
                            <th class="cb-jr text-right">Jasa Raharja</th>
                            <th class="cb-asur text-right">Asuransi</th>
                            <th class="cb-umum text-right">Umum</th>
                            <th class="text-right" style="min-width:90px;">Total</th>
                            <th style="min-width:130px;">% Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendapatanData['tableRows'] as $row): ?>
                        <tr>
                            <td class="unit-name"><?= Html::encode($row['unit']) ?></td>
                            <td class="cb-bpjs text-right"><?= $row['bpjs']>0 ? formatCurrency($row['bpjs']) : '<span style="color:#CBD5E1;">—</span>' ?></td>
                            <td class="cb-jr text-right"><?= $row['jr']>0 ? formatCurrency($row['jr']) : '<span style="color:#CBD5E1;">—</span>' ?></td>
                            <td class="cb-asur text-right"><?= $row['asuransi']>0 ? formatCurrency($row['asuransi']) : '<span style="color:#CBD5E1;">—</span>' ?></td>
                            <td class="cb-umum text-right"><?= $row['umum']>0 ? formatCurrency($row['umum']) : '<span style="color:#CBD5E1;">—</span>' ?></td>
                            <td class="cb-total text-right"><?= formatCurrency($row['total']) ?></td>
                            <td>
                                <div class="pct-bar-wrap">
                                    <div class="pct-bar-track">
                                        <div class="pct-bar-fill" style="width:<?= $row['pct_total'] ?>%;background:#3B82F6;"></div>
                                    </div>
                                    <span style="font-size:.8rem;font-weight:700;color:#475569;white-space:nowrap;"><?= $row['pct_total'] ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
    <!-- ============================================== -->
    <!-- RINGKASAN TAB (default)                        -->
    <!-- ============================================== -->

        <!-- 5 KPI Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4 col-custom-5">
                <div class="kpi-card kpi-rj">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="kpi-label">Rawat Jalan</span>
                        <div class="kpi-icon-wrapper"><i class="bi bi-person-lines-fill"></i></div>
                    </div>
                    <div class="kpi-value"><?= number_format($rj_curr,0,',','.') ?></div>
                    <div><?= renderGrowthBadge($rj_growth) ?> <span class="sub-info">vs bln lalu</span></div>
                    <div class="sub-info mt-1"><strong><?= $rj_baru_pct ?>%</strong> Pasien Baru</div>
                    <?= renderClosingStatus($kpiClosing['rj'] ?? []) ?>
                    <?= renderPenjaminBars($kpiPenjamin['rj'] ?? []) ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 col-custom-5">
                <div class="kpi-card kpi-igd">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="kpi-label">IGD</span>
                        <div class="kpi-icon-wrapper"><i class="bi bi-heart-pulse"></i></div>
                    </div>
                    <div class="kpi-value"><?= number_format($igd_curr,0,',','.') ?></div>
                    <div><?= renderGrowthBadge($igd_growth) ?> <span class="sub-info">vs bln lalu</span></div>
                    <div class="sub-info mt-1">Kunjungan Darurat</div>
                    <?= renderClosingStatus($kpiClosing['igd'] ?? []) ?>
                    <?= renderPenjaminBars($kpiPenjamin['igd'] ?? []) ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 col-custom-5">
                <div class="kpi-card kpi-ranap">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="kpi-label">Rawat Inap</span>
                        <div class="kpi-icon-wrapper"><i class="bi bi-hospital"></i></div>
                    </div>
                    <div class="kpi-value"><?= number_format($ranap_curr,0,',','.') ?></div>
                    <div><?= renderGrowthBadge($ranap_growth) ?> <span class="sub-info">vs bln lalu</span></div>
                    <div class="sub-info mt-1"><strong><?= $bor_pct ?>%</strong> Bed Occupancy Rate</div>
                    <?= renderClosingStatus($kpiClosing['ranap'] ?? []) ?>
                    <?= renderPenjaminBars($kpiPenjamin['ranap'] ?? []) ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 col-custom-5">
                <div class="kpi-card kpi-labrad">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="kpi-label">Lab &amp; Rad</span>
                        <div class="kpi-icon-wrapper"><i class="bi bi-droplet-half"></i></div>
                    </div>
                    <div class="kpi-value"><?= number_format($labrad_curr,0,',','.') ?></div>
                    <div><?= renderGrowthBadge($labrad_growth) ?> <span class="sub-info">vs bln lalu</span></div>
                    <div class="sub-info mt-1">Total Pemeriksaan Penunjang</div>
                    <?= renderClosingStatus($kpiClosing['labrad'] ?? []) ?>
                    <?= renderPenjaminBars($kpiPenjamin['labrad'] ?? []) ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 col-custom-5">
                <div class="kpi-penjamin-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="kpi-label">Proporsi Penjamin RJ</span>
                        <div class="kpi-icon-wrapper" style="background:rgba(14,165,233,.1);color:#0EA5E9;width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;">
                            <i class="bi bi-pie-chart-fill"></i>
                        </div>
                    </div>
                    <div style="font-size:.75rem;color:#94A3B8;margin-bottom:4px;">Rawat Jalan &bull; Periode Terpilih</div>
                    <div id="chart-rj-penjamin" style="min-height:120px;margin:0 -8px;"></div>
                    <div class="penjamin-legend" id="rj-penjamin-legend">
                        <?php
                        $pjColors = ['BPJS Kesehatan'=>'#007BFF','Jasa Raharja'=>'#28A745','Asuransi Komersial'=>'#FFA500','Umum'=>'#6F42C1'];
                        $pjShort  = ['BPJS Kesehatan'=>'BPJS','Jasa Raharja'=>'JR','Asuransi Komersial'=>'Asuransi','Umum'=>'Umum'];
                        foreach ($rjPenjaminData as $cb => $val):
                            if ($val <= 0) continue;
                            $pct = $rjTotalPenjamin > 0 ? round($val / $rjTotalPenjamin * 100, 1) : 0;
                            $color = $pjColors[$cb] ?? '#94A3B8';
                            $label = $pjShort[$cb] ?? $cb;
                        ?>
                        <div class="penjamin-legend-item">
                            <div class="penjamin-dot" style="background:<?= $color ?>;"></div>
                            <span><?= $label ?></span>
                            <span class="penjamin-pct"><?= $pct ?>%</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card-chart">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="chart-title">Engine Utama Kunjungan (Trend 12 Bulan)</div>
                            <div class="chart-subtitle">Bandingkan tren kunjungan bulanan layanan utama</div>
                        </div>
                        <span class="badge badge-light px-3 py-2 text-muted" style="border:1px solid #E2E8F0;border-radius:20px;font-weight:600;font-size:.78rem;">Health Service Funnel</span>
                    </div>
                    <div id="chart-kunjungan" style="min-height:350px;"></div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card-chart">
                    <div class="mb-3">
                        <div class="chart-title">Heatmap Kepadatan Pasien <span style="font-size:.8rem;font-weight:400;color:#94A3B8;">(Pagi &amp; IGD)</span></div>
                        <div class="chart-subtitle">Visualisasi kepadatan pasien berdasarkan Hari &amp; Jam</div>
                    </div>
                    <div id="chart-heatmap" style="min-height:350px;"></div>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card-chart">
                    <div class="mb-3">
                        <div class="chart-title">Matriks Asal Pasien &amp; Cara Bayar</div>
                        <div class="chart-subtitle">Distribusi penjamin pembayaran pasien periode terpilih</div>
                    </div>
                    <div id="chart-donut" style="min-height:300px;"></div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card-chart">
                    <div class="mb-3">
                        <div class="chart-title">Efisiensi Operasional (Top 5 Penunjang)</div>
                        <div class="chart-subtitle">Daftar unit penunjang berdasarkan volume</div>
                    </div>
                    <div class="table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProviderPenunjang,
                            'summary' => false,
                            'tableOptions' => ['class' => 'table table-hover border-0 align-middle'],
                            'columns' => [
                                [
                                    'attribute' => 'unit_bisnis',
                                    'header' => 'Jenis Pemeriksaan / Unit',
                                    'headerOptions' => ['class'=>'text-muted font-weight-bold border-0','style'=>'font-size:.82rem;text-transform:uppercase;'],
                                    'contentOptions' => ['class'=>'border-0 py-3 font-weight-bold','style'=>'color:#334155;font-size:.95rem;'],
                                    'value' => function($m){ return $m['unit_bisnis']; }
                                ],
                                [
                                    'attribute' => 'total',
                                    'header' => 'Total Permintaan',
                                    'headerOptions' => ['class'=>'text-right text-muted font-weight-bold border-0','style'=>'font-size:.82rem;text-transform:uppercase;'],
                                    'contentOptions' => ['class'=>'text-right border-0 py-3 font-weight-bold','style'=>'color:#0F172A;font-size:1.05rem;'],
                                    'value' => function($m){ return number_format($m['total'],0,',','.'); }
                                ],
                                [
                                    'header' => 'Status',
                                    'headerOptions' => ['class'=>'text-center text-muted font-weight-bold border-0','style'=>'font-size:.82rem;text-transform:uppercase;width:110px;'],
                                    'contentOptions' => ['class'=>'text-center border-0 py-3'],
                                    'format' => 'raw',
                                    'value' => function($m){ return '<span class="badge" style="background:#DCFCE7;color:#15803D;font-weight:700;padding:5px 12px;border-radius:6px;font-size:.75rem;">Selesai</span>'; }
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php
// ---------- JS: prepare data ----------
$trendRJ_json       = json_encode($trendRJ);
$trendIGD_json      = json_encode($trendIGD);
$trendRI_json       = json_encode($trendRI);
$trendPenunjang_json= json_encode($trendPenunjang);
$heatmap_json       = json_encode($heatmapSeries);
$donut_labels_json  = json_encode($donutLabels);
$donut_values_json  = json_encode($donutValues);
$rjPenjamin_labels_json = json_encode($rjPenjaminLabels);
$rjPenjamin_values_json = json_encode($rjPenjaminValues);

// Pendapatan stacked chart — kept for backward compat (not rendered in trend mode)
$pend_series_json   = isset($pendapatanData['stackedSeries']) ? json_encode($pendapatanData['stackedSeries']) : '[]';
$pend_units_json    = isset($pendapatanData['unitOrder']) ? json_encode(array_values($pendapatanData['unitOrder'])) : '[]';

// AJAX base URL for trend endpoint
$trendAjaxUrl = \yii\helpers\Url::to(['/eksekutif/dashboard/get-trend-data']);
$trendAjaxUrlJs = json_encode($trendAjaxUrl);

$this->registerJs("
    function initAllCharts() {
        if (typeof ApexCharts === 'undefined') {
            setTimeout(initAllCharts, 50);
            return;
        }

        var chartLine       = null;
        var chartQuarter    = null;
        var trendAjaxUrl    = {$trendAjaxUrlJs};

    // ---- Formatter helpers ----
    function fmtJuta(v) {
        if (v >= 1000) return 'Rp ' + (v/1000).toFixed(2) + ' M';
        return 'Rp ' + parseFloat(v).toFixed(0) + ' jt';
    }
    function fmtAxisJuta(v) {
        if (v >= 1000) return (v/1000).toFixed(1) + 'M';
        return v + 'jt';
    }

    // ---- Build ApexCharts options ----
    function buildMainOptions(labels, series) {
        return {
            series: series,
            chart: {
                id: 'chartLine',
                type: 'area',
                height: 320,
                background: '#131722',
                fontFamily: 'Inter, sans-serif',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                zoom: { enabled: true, type: 'x', autoScaleYaxis: true },
                toolbar: {
                    show: true,
                    tools: { download: false, selection: false, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true },
                    autoSelected: 'zoom'
                }
            },
            theme: { mode: 'dark' },
            colors: ['#2962ff', '#00e676', '#ff5252', '#ffd600'],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.02,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels,
                tickAmount: Math.min(labels.length, 12),
                labels: {
                    rotate: -30,
                    style: { colors: '#868996', fontWeight: 500, fontSize: '11px' }
                },
                axisBorder: { color: '#2a2e39' },
                axisTicks: { color: '#2a2e39' },
                crosshairs: {
                    show: true,
                    position: 'back',
                    stroke: { color: '#5d606b', width: 1, dashArray: 3 }
                },
                tooltip: { enabled: true }
            },
            yaxis: {
                labels: {
                    style: { colors: '#868996', fontWeight: 500 },
                    formatter: function(v) { return fmtAxisJuta(v); }
                },
                axisBorder: { color: '#2a2e39', show: true },
                axisTicks: { color: '#2a2e39', show: true },
                crosshairs: {
                    show: true,
                    position: 'back',
                    stroke: { color: '#5d606b', width: 1, dashArray: 3 }
                },
                tooltip: { enabled: true }
            },
            tooltip: {
                theme: 'dark',
                shared: true,
                intersect: false,
                y: { formatter: function(val) { return fmtJuta(val); } }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                fontSize: '13px',
                fontWeight: 600,
                labels: { colors: '#d1d4dc' },
                markers: { radius: 10, width: 10, height: 10 },
                itemMargin: { horizontal: 12 }
            },
            grid: {
                borderColor: '#2a2e39',
                strokeDashArray: 4,
                padding: { left: 0, right: 0 }
            },
            markers: { size: 0, hover: { size: 5 } }
        };
    }

    function buildBrushOptions(labels, series) {
        var brushSeries = [{
            name: 'Total',
            data: labels.map(function(_, i) {
                return series.reduce(function(acc, s) { return acc + (s.data[i] || 0); }, 0);
            })
        }];
        return {
            series: brushSeries,
            chart: {
                id: 'chartQuarter',
                type: 'area',
                height: 100,
                background: '#131722',
                fontFamily: 'Inter, sans-serif',
                brush: { target: 'chartLine', enabled: true },
                selection: {
                    enabled: true,
                    xaxis: {
                        min: labels.length > 6 ? labels.length - 5 : 1,
                        max: labels.length
                    }
                },
                animations: { enabled: false },
                toolbar: { show: false },
                sparkline: { enabled: false }
            },
            theme: { mode: 'dark' },
            colors: ['#3f4350'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.01 } },
            stroke: { curve: 'smooth', width: 1.5 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels,
                tickAmount: Math.min(labels.length, 8),
                labels: { style: { colors: '#868996', fontSize: '10px' } },
                axisBorder: { color: '#2a2e39' },
                axisTicks: { color: '#2a2e39' }
            },
            yaxis: { show: false },
            grid: { borderColor: '#2a2e39', strokeDashArray: 4 },
            markers: { size: 0 }
        };
    }

    // ---- Update KPI chips ----
    function updateChips(series) {
        var names = {
            'BPJS Kesehatan': 'chipBpjs',
            'Jasa Raharja': 'chipJr',
            'Asuransi Komersial': 'chipAsuransi',
            'Umum': 'chipUmum'
        };
        // Reset values first
        for (var k in names) {
            var el = document.getElementById(names[k]);
            if (el) el.textContent = 'Rp 0 jt';
        }
        series.forEach(function(s) {
            var elId = names[s.name];
            if (!elId) return;
            var total = s.data.reduce(function(a,b){ return a + (b||0); }, 0);
            var el = document.getElementById(elId);
            if (el) el.textContent = fmtJuta(total);
        });
    }

    // ---- Main AJAX loader ----
    function updateDashboardTrend() {
        var selectedUnit = $('#dropdown-unit').val() || 'Ranap';
        var selectedScale = $('.btn-scale.active').data('scale') || 'Bulanan';
        var loading = document.getElementById('trend-loading');
        if (loading) loading.classList.add('show');

        // Disable controls during load
        $('.btn-scale').prop('disabled', true);
        $('#dropdown-unit').prop('disabled', true);

        $.ajax({
            url: trendAjaxUrl,
            type: 'GET',
            data: { unit: selectedUnit, scale: selectedScale },
            xhrFields: {
                withCredentials: true
            },
            success: function(response) {
                var categories = response.categories;
                var series = response.series;

                if (!chartLine) {
                    // First render
                    chartLine = new ApexCharts(
                        document.querySelector('#chart-trend-main'),
                        buildMainOptions(categories, series)
                    );
                    chartLine.render();

                    chartQuarter = new ApexCharts(
                        document.querySelector('#chart-trend-brush'),
                        buildBrushOptions(categories, series)
                    );
                    chartQuarter.render();
                } else {
                    // Update main chart data
                    chartLine.updateSeries(series);
                    chartLine.updateOptions({
                        xaxis: { categories: categories }
                    });

                    // Update navigator brush chart to match
                    var brushData = categories.map(function(_, i) {
                        return series.reduce(function(acc, s) { return acc + (s.data[i] || 0); }, 0);
                    });
                    chartQuarter.updateSeries([{
                        name: 'Total',
                        data: brushData
                    }]);
                    chartQuarter.updateOptions({
                        xaxis: {
                            categories: categories,
                            selection: {
                                xaxis: {
                                    min: categories.length > 6 ? categories.length - 5 : 1,
                                    max: categories.length
                                }
                            }
                        }
                    });
                }

                updateChips(series);
            },
            error: function(xhr, status, error) {
                console.error('Trend data load failed:', error);
            },
            complete: function() {
                if (loading) loading.classList.remove('show');
                $('.btn-scale').prop('disabled', false);
                $('#dropdown-unit').prop('disabled', false);
            }
        });
    }

    // ---- Event Listeners ----
    $('#dropdown-unit').on('change', updateDashboardTrend);
    $('.btn-scale').on('click', function() {
        $('.btn-scale').removeClass('active');
        $(this).addClass('active');
        updateDashboardTrend();
    });

    // ---- Auto-init on Pendapatan tab ----
    if (document.getElementById('chart-trend-main')) {
        setTimeout(function() { updateDashboardTrend(); }, 100);
    }

    // =============================================
    // RINGKASAN TAB charts
    // =============================================
    if (document.getElementById('chart-kunjungan')) {
        new ApexCharts(document.querySelector('#chart-kunjungan'), {
            series: [
                { name:'Rawat Jalan', data:{$trendRJ_json} },
                { name:'IGD', data:{$trendIGD_json} },
                { name:'Rawat Inap (Admisi)', data:{$trendRI_json} },
                { name:'Penunjang', data:{$trendPenunjang_json} }
            ],
            chart: { type:'bar', height:350, fontFamily:'Inter,sans-serif', toolbar:{show:false} },
            colors: ['#007BFF','#DC3545','#28A745','#FFC107'],
            plotOptions: { bar: { horizontal:false, columnWidth:'55%', borderRadius:4 } },
            dataLabels: { enabled:false },
            stroke: { show:true, width:2, colors:['transparent'] },
            xaxis: {
                categories: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'],
                labels: { style:{colors:'#64748B',fontWeight:500} }
            },
            yaxis: { labels: { style:{colors:'#64748B',fontWeight:500}, formatter:function(v){ return v.toLocaleString('id-ID'); } } },
            tooltip: { y: { formatter:function(v){ return v.toLocaleString('id-ID')+' Pasien'; } } },
            legend: { position:'bottom', fontSize:'13px', fontWeight:500, labels:{colors:'#475569'}, markers:{radius:12} },
            grid: { borderColor:'#F1F5F9', strokeDashArray:4 },
            fill: { opacity:1 }
        }).render();
    }

    if (document.getElementById('chart-heatmap')) {
        new ApexCharts(document.querySelector('#chart-heatmap'), {
            series: {$heatmap_json},
            chart: { height:350, type:'heatmap', fontFamily:'Inter,sans-serif', toolbar:{show:false} },
            plotOptions: {
                heatmap: {
                    shadeIntensity:0.5, radius:4, useDirectColors:false,
                    colorScale: { ranges:[
                        {from:0,to:9,name:'Senggang',color:'#DBEAFE'},
                        {from:10,to:19,name:'Normal',color:'#93C5FD'},
                        {from:20,to:29,name:'Ramai',color:'#FCA5A5'},
                        {from:30,to:100,name:'Padat',color:'#EF4444'}
                    ]}
                }
            },
            dataLabels: { enabled:false },
            xaxis: { labels:{style:{colors:'#64748B',fontWeight:600}} },
            yaxis: { labels:{style:{colors:'#64748B',fontWeight:500}} },
            legend: { position:'bottom', fontSize:'12px', fontWeight:500, labels:{colors:'#475569'} }
        }).render();
    }

        if (document.getElementById('chart-donut')) {
        new ApexCharts(document.querySelector('#chart-donut'), {
            series: {$donut_values_json},
            labels: {$donut_labels_json},
            chart: { type:'donut', height:320, fontFamily:'Inter,sans-serif' },
            colors: ['#007BFF','#28A745','#FFA500','#6F42C1'],
            legend: { position:'right', fontSize:'13px', fontWeight:500, labels:{colors:'#475569'} },
            dataLabels: {
                enabled:true,
                formatter:function(val,opts){
                    return opts.w.config.series[opts.seriesIndex].toLocaleString('id-ID') + ' (' + val.toFixed(1) + '%)';
                }
            },
            plotOptions: {
                pie: { donut: { size:'65%', labels: { show:true, total: {
                    show:true, label:'Total Pasien', fontSize:'14px', fontWeight:600, color:'#64748B',
                    formatter:function(w){ return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0).toLocaleString('id-ID'); }
                }}}}
            },
            responsive:[{breakpoint:480,options:{legend:{position:'bottom'}}}]
        }).render();
    }

    // ---- Mini Donut: Proporsi Penjamin Rawat Jalan (KPI Card) ----
    if (document.getElementById('chart-rj-penjamin')) {
        var rjPenjaminLabels = {$rjPenjamin_labels_json};
        var rjPenjaminValues = {$rjPenjamin_values_json};
        var hasData = rjPenjaminValues.some(function(v){ return v > 0; });
        if (hasData) {
            new ApexCharts(document.querySelector('#chart-rj-penjamin'), {
                series: rjPenjaminValues,
                labels: rjPenjaminLabels,
                chart: {
                    type: 'donut',
                    height: 120,
                    fontFamily: 'Inter,sans-serif',
                    sparkline: { enabled: false },
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 600 }
                },
                colors: ['#007BFF','#28A745','#FFA500','#6F42C1'],
                legend: { show: false },
                dataLabels: { enabled: false },
                stroke: { width: 2 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '9px',
                                    fontWeight: 700,
                                    color: '#64748B',
                                    formatter: function(w) {
                                        var t = w.globals.seriesTotals.reduce(function(a,b){return a+b;},0);
                                        return t > 1000 ? (t/1000).toFixed(1)+'rb' : t.toLocaleString('id-ID');
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '11px',
                                    fontWeight: 800,
                                    color: '#0F172A',
                                    offsetY: 2
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    y: { formatter: function(val, opts) {
                        var total = opts.w.globals.seriesTotals.reduce(function(a,b){return a+b;},0);
                        var pct = total > 0 ? (val/total*100).toFixed(1) : 0;
                        return val.toLocaleString('id-ID') + ' pasien (' + pct + '%)';
                    }}
                },
                responsive:[{breakpoint:480,options:{chart:{height:100}}}]
            }).render();
        } else {
            var _el = document.getElementById('chart-rj-penjamin');
            var _d = document.createElement('div');
            _d.textContent = 'Tidak ada data';
            _d.style.cssText = 'text-align:center;padding:20px 0;color:#94A3B8;font-size:.8rem;';
            _el.appendChild(_d);
        }
    }
}
initAllCharts();
");
?>
