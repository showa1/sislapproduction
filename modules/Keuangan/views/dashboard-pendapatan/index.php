<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\eksekutif\models\DashboardSearch */
/* @var $pendapatanData array */

$this->title = 'Dashboard Pendapatan';

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

$this->registerCss("
    body { font-family: 'Inter', sans-serif; background-color: #F8F9FC; }
    .content-wrapper { background-color: #F8F9FC !important; padding: 30px !important; }
    .page-title { color: #0F172A; font-weight: 800; font-size: 1.6rem; letter-spacing: -0.5px; margin-bottom: 2px; }
    .page-subtitle { color: #64748B; font-size: 0.9rem; }

    /* ---- Filter Card ---- */
    .filter-card { background:#fff; border-radius:14px; border:1px solid #F1F5F9; box-shadow:0 4px 20px rgba(0,0,0,0.02); margin-bottom:24px; }

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
    .kpi-card {
        border:none; border-radius:16px; box-shadow:0 8px 24px rgba(15,23,42,0.04);
        background:#fff; padding:22px 24px; transition:all .3s ease;
        border:1px solid #F1F5F9; height:100%; position:relative; overflow:hidden;
    }
    .kpi-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px rgba(15,23,42,0.07); }

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

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        if ($amount >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, 2, '.', ',') . ' M';
        }
        return 'Rp ' . number_format($amount / 1_000_000, 0, ',', '.') . ' jt';
    }
}

?>
<!-- ApexCharts: loaded inline to guarantee it runs before our chart JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="container-fluid p-0">

    <!-- === HEADER === -->
    <div class="row mb-3 align-items-center">
        <div class="col-sm-8">
            <h1 class="page-title">Dashboard Pendapatan</h1>
            <p class="page-subtitle">Periode: Bulan <?= date('F Y', strtotime($searchModel->date_from)) ?></p>
        </div>
    </div>

    <!-- === DATE FILTER === -->
    <div class="filter-card card mb-4">
        <div class="card-body p-3">
            <?php $form = ActiveForm::begin(['action' => ['/keuangan/dashboard-pendapatan/index'], 'method' => 'get', 'options' => ['class' => 'row align-items-end']]); ?>
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

    <?php if (!empty($pendapatanData)): ?>
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

    <?php endif; ?>
</div>

<?php
$trendAjaxUrl = \yii\helpers\Url::to(['/keuangan/dashboard-pendapatan/get-trend-data']);
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
        series.forEach(function(s) {
            var elId = names[s.name];
            if (!elId) return;
            var total = s.data.reduce(function(a,b){ return a+b; }, 0);
            document.getElementById(elId).innerText = fmtJuta(total);
        });
    }

    // ---- Load Data via AJAX ----
    function loadTrendData(unit, scale) {
        $('#trend-loading').addClass('show');
        $.ajax({
            url: trendAjaxUrl,
            data: { unit: unit, scale: scale },
            dataType: 'json',
            success: function(res) {
                $('#trend-loading').removeClass('show');
                if (!res || !res.categories) return;

                updateChips(res.series);

                if (chartLine) {
                    chartLine.destroy();
                    chartQuarter.destroy();
                }

                var optionsMain = buildMainOptions(res.categories, res.series);
                chartLine = new ApexCharts(document.querySelector('#chart-trend-main'), optionsMain);
                chartLine.render();

                var optionsBrush = buildBrushOptions(res.categories, res.series);
                chartQuarter = new ApexCharts(document.querySelector('#chart-trend-brush'), optionsBrush);
                chartQuarter.render();
            },
            error: function() {
                $('#trend-loading').removeClass('show');
                alert('Gagal memuat data trend.');
            }
        });
    }

    // ---- Event Listeners ----
    $('#dropdown-unit').on('change', function() {
        var scale = $('.scope-btn.active').data('scale');
        loadTrendData($(this).val(), scale);
    });

    $('.scope-btn').on('click', function() {
        $('.scope-btn').removeClass('active');
        $(this).addClass('active');
        loadTrendData($('#dropdown-unit').val(), $(this).data('scale'));
    });

    // Initial load
    var initScale = $('.scope-btn.active').data('scale') || 'Bulanan';
    loadTrendData($('#dropdown-unit').val(), initScale);
    }
    
    // Call init on load
    $(function(){ initAllCharts(); });
");
?>
