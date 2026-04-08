<?php
    use yii\grid\GridView;
    use yii\helpers\Html;
    use yii\helpers\Url;

    $this->title = 'Dashboard Pendapatan Rawat Inap';
    $this->params['breadcrumbs'][] = ['label' => 'Keuangan', 'url' => ['/keuangan/default/index']];
    $this->params['breadcrumbs'][] = $this->title;

    $resetUrl  = Url::to(['pendapatan-rawat-inap/index']);
    $exportUrl = Url::to(['pendapatan-rawat-inap/export']);

    $fmtRp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');

    // Chart data untuk JS
    $jChartRevLabels  = json_encode(array_column($chartRevenue, 'label'));
    $jChartRevData    = json_encode(array_map('floatval', array_column($chartRevenue, 'value')));
    $jChartCbLabels   = json_encode(array_column($chartCaraBayar, 'label'));
    $jChartCbData     = json_encode(array_map('intval', array_column($chartCaraBayar, 'jumlah')));
    $jChartTrendLabels= json_encode(array_column($chartTrend, 'label'));
    $jChartTrendData  = json_encode(array_map('floatval', array_column($chartTrend, 'value')));

    // Grand Total summary table
    $gtPasien    = array_sum(array_column($summaryTable, 'jumlah_pasien'));
    $gtTindakan  = array_sum(array_column($summaryTable, 'total_tindakan'));
    $gtFarmasi   = array_sum(array_column($summaryTable, 'total_farmasi'));
    $gtPendapatan= array_sum(array_column($summaryTable, 'total_pendapatan'));

    $this->registerCssFile('@web/template/vendors/flatpickr/flatpickr.min.css');
    $this->registerJsFile('@web/template/vendors/flatpickr/flatpickr.min.js', ['position' => \yii\web\View::POS_END]);
    $this->registerCss("
        body { font-family: 'Inter', sans-serif; }
        .ri-page-header { background: linear-gradient(135deg, #002D72 0%, #1565C0 100%); color:#fff; border-radius:16px; padding:28px 32px; margin-bottom:24px; }
        .ri-page-header h2 { font-size:1.7rem; font-weight:800; margin:0; }
        .ri-page-header p { opacity:.8; margin:4px 0 0; font-size:.9rem; }
        .ri-card { background:#fff; border-radius:14px; border:none; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:24px; margin-bottom:20px; }
        .ri-metric-card { border-radius:14px; border:none; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:20px 24px; color:#fff; position:relative; overflow:hidden; }
        .ri-metric-card .mc-icon { font-size:2.2rem; opacity:.25; position:absolute; right:20px; top:50%; transform:translateY(-50%); }
        .ri-metric-card .mc-label { font-size:.78rem; text-transform:uppercase; letter-spacing:.06em; opacity:.85; font-weight:600; }
        .ri-metric-card .mc-value { font-size:1.45rem; font-weight:800; margin:4px 0 0; line-height:1.2; }
        .ri-metric-card .mc-sub { font-size:.78rem; opacity:.8; margin-top:4px; }
        .mc-blue   { background:linear-gradient(135deg, #002D72, #1565C0); }
        .mc-green  { background:linear-gradient(135deg, #218838, #6DC536); }
        .mc-orange { background:linear-gradient(135deg, #e65c00, #f9a825); }
        .mc-purple { background:linear-gradient(135deg, #6f42c1, #a855f7); }
        .ri-section-title { font-size:1rem; font-weight:700; color:#002D72; margin-bottom:16px; border-left:4px solid #002D72; padding-left:12px; }
        .ri-filter-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); padding:20px 24px; margin-bottom:24px; }
        .ri-filter-card .form-control, .ri-filter-card .form-select { border-radius:8px; border:1px solid #e2e8f0; font-size:.9rem; padding:8px 12px; }
        .ri-filter-card .form-label { font-weight:600; font-size:.82rem; color:#4a5568; text-transform:uppercase; letter-spacing:.04em; margin-bottom:5px; }
        .btn-ri-primary { background:#002D72; color:#fff; border:none; border-radius:8px; font-weight:600; padding:9px 20px; }
        .btn-ri-primary:hover { background:#1565C0; color:#fff; }
        .btn-ri-reset { background:#fff; color:#002D72; border:1.5px solid #002D72; border-radius:8px; font-weight:600; padding:9px 20px; }
        .btn-ri-export { background:#6DC536; color:#fff; border:none; border-radius:8px; font-weight:600; padding:9px 20px; }
        .btn-ri-export:hover { background:#5aad2d; color:#fff; }
        .chart-container { position:relative; height:260px; }
        .ri-summary-table { width:100%; border-collapse:collapse; font-size:.88rem; }
        .ri-summary-table thead th { background:#002D72; color:#fff; padding:12px 16px; text-align:left; font-weight:600; font-size:.8rem; text-transform:uppercase; letter-spacing:.04em; }
        .ri-summary-table tbody td { padding:11px 16px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .ri-summary-table tbody tr:hover { background:#f8faff; }
        .ri-summary-table tfoot td { background:#f1f5f9; font-weight:700; color:#002D72; padding:12px 16px; border-top:2px solid #002D72; }
        .ri-rank-badge { background:#e8f0fe; color:#002D72; border-radius:20px; padding:2px 10px; font-size:.75rem; font-weight:700; display:inline-block; margin-right:6px; }
        .payor-bpjs    { background:#e3f2fd; color:#0d47a1; border-radius:6px; padding:3px 10px; font-size:.78rem; font-weight:600; }
        .payor-pribadi { background:#e8f5e9; color:#1b5e20; border-radius:6px; padding:3px 10px; font-size:.78rem; font-weight:600; }
        .payor-asuransi{ background:#fff3e0; color:#bf360c; border-radius:6px; padding:3px 10px; font-size:.78rem; font-weight:600; }
        .payor-other   { background:#f3f4f6; color:#374151; border-radius:6px; padding:3px 10px; font-size:.78rem; font-weight:600; }
        .ri-nav-tabs .nav-link { color:#64748b; font-weight:600; border:none; border-bottom:3px solid transparent; padding:10px 20px; background:transparent; }
        .ri-nav-tabs .nav-link.active { color:#002D72; border-bottom-color:#002D72; background:transparent; }
        .detail-gridview thead th { background:#002D72 !important; color:#fff !important; font-size:.8rem; text-align:center; font-weight:600; border:none; padding:12px 10px !important; vertical-align:middle !important; white-space:nowrap; }
        .detail-gridview tbody td { vertical-align:middle !important; border-bottom:1px solid #f1f5f9; padding:10px !important; font-size:.85rem; }
        .detail-gridview tfoot td { background:#f1f5f9; font-weight:700; border-top:2px solid #002D72; padding:10px !important; font-size:.85rem; }
        .pagination .page-link { color:#002D72; border-color:#e2e8f0; }
        .pagination .page-item.active .page-link { background:#002D72; border-color:#002D72; }
        .ri-multiselect { border-radius:8px; border:1px solid #e2e8f0; font-size:.9rem; padding:4px 8px; min-height:44px; max-height:130px; overflow-y:auto; width:100%; cursor:pointer; }
        .ri-multiselect option { padding:5px 8px; border-radius:4px; cursor:pointer; }
        .ri-multiselect option:checked { background:#002D72; color:#fff; }
        .ri-select-hint { font-size:.72rem; color:#94a3b8; margin-top:3px; }
    ");
?>

<!-- Page Header -->
<div class="ri-page-header mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <p class="mb-1"><i class="bi bi-house me-1"></i> Keuangan</p>
            <h2><i class="bi bi-hospital me-2"></i><?= $this->title ?></h2>
            <p>Analisis strategis pendapatan unit Rawat Inap untuk Direksi</p>
        </div>
        <div class="text-end d-none d-md-block">
            <div style="opacity:.7; font-size:.85rem;">Rawat Inap</div>
            <div style="font-size:2rem;"><i class="bi bi-building-fill-check"></i></div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="ri-filter-card">
    <?= Html::beginForm(['/keuangan/pendapatan-rawat-inap/index'], 'get', ['id' => 'filter-form']) ?>
    <?= Html::hiddenInput('cari', 'aktif') ?>
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-calendar3 me-1"></i> Tanggal Awal</label>
            <input type="text" name="date_from" id="date_from" class="form-control flatpickr-ri"
                   value="<?= Html::encode($dropdownselect['start']) ?>" placeholder="01-01-2026" autocomplete="off">
        </div>
        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-calendar3-range me-1"></i> Tanggal Akhir</label>
            <input type="text" name="date_to" id="date_to" class="form-control flatpickr-ri"
                   value="<?= Html::encode($dropdownselect['to']) ?>" placeholder="31-01-2026" autocomplete="off">
        </div>
        <div class="col-md-2">
            <label class="form-label"><i class="bi bi-door-open me-1"></i> Ruangan</label>
            <select name="ruangan[]" id="sel-ruangan" class="ri-multiselect" multiple>
                <?php foreach ($ruanganList as $r): ?>
                    <option value="<?= Html::encode($r) ?>" <?= in_array($r, (array)$filterRuangan) ? 'selected' : '' ?>>
                        <?= Html::encode($r) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="ri-select-hint"><i class="bi bi-info-circle me-1"></i>Ctrl+Klik untuk pilih lebih dari satu</div>
        </div>
        <div class="col-md-2">
            <label class="form-label"><i class="bi bi-credit-card me-1"></i> Cara Bayar</label>
            <select name="cara_bayar" id="sel-carabayar" class="form-select">
                <option value="">-- Semua --</option>
                <?php foreach ($carabayarList as $c): ?>
                    <option value="<?= Html::encode($c) ?>" <?= ($filterCaraBayar == $c) ? 'selected' : '' ?>>
                        <?= Html::encode($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <?= Html::submitButton('<i class="bi bi-search me-1"></i> Cari', ['class' => 'btn btn-ri-primary flex-fill']) ?>
            <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i>', $resetUrl, ['class' => 'btn btn-ri-reset']) ?>
        </div>
    </div>
    <?= Html::endForm() ?>
</div>

<?php if ($statuscari): ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="ri-metric-card mc-blue">
            <i class="bi bi-currency-dollar mc-icon"></i>
            <div class="mc-label"><i class="bi bi-bar-chart-fill me-1"></i> Total Pendapatan</div>
            <div class="mc-value"><?= $fmtRp($summaryCards['total_pendapatan']) ?></div>
            <div class="mc-sub"><?= $summaryCards['total_pasien'] ?> pasien di periode ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="ri-metric-card mc-green">
            <i class="bi bi-trophy-fill mc-icon"></i>
            <div class="mc-label"><i class="bi bi-award me-1"></i> Ruangan Terlaris</div>
            <div class="mc-value" style="font-size:1.1rem;"><?= Html::encode($summaryCards['ruangan_terlaris']) ?></div>
            <div class="mc-sub"><?= $fmtRp($summaryCards['ruangan_top_revenue']) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="ri-metric-card mc-orange">
            <i class="bi bi-person-fill mc-icon"></i>
            <div class="mc-label"><i class="bi bi-graph-up me-1"></i> Avg Revenue/Pasien</div>
            <div class="mc-value"><?= $fmtRp($summaryCards['arpp']) ?></div>
            <div class="mc-sub">Average Revenue Per Patient</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="ri-metric-card mc-purple">
            <i class="bi bi-pie-chart-fill mc-icon"></i>
            <div class="mc-label"><i class="bi bi-shield-check me-1"></i> Rasio BPJS / Non-BPJS</div>
            <div class="mc-value"><?= $summaryCards['pct_bpjs'] ?>% / <?= $summaryCards['pct_non_bpjs'] ?>%</div>
            <div class="mc-sub">Berdasarkan jumlah pasien unik</div>
        </div>
    </div>
</div>

<!-- Charts Row 1: Bar + Pie -->
<div class="row g-3 mb-3">
    <div class="col-md-7">
        <div class="ri-card">
            <div class="ri-section-title"><i class="bi bi-bar-chart me-2"></i>Revenue per Ruangan</div>
            <div class="chart-container"><canvas id="chartRevenue"></canvas></div>
            <div class="text-center mt-2" style="font-size:.75rem; color:#94a3b8;">Klik bar untuk filter tabel detail</div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="ri-card">
            <div class="ri-section-title"><i class="bi bi-pie-chart me-2"></i>Kontribusi Cara Bayar</div>
            <div class="chart-container"><canvas id="chartCaraBayar"></canvas></div>
        </div>
    </div>
</div>

<!-- Chart Row 2: Trend Line -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="ri-card">
            <div class="ri-section-title"><i class="bi bi-graph-up-arrow me-2"></i>Tren Pendapatan Bulanan</div>
            <div class="chart-container" style="height:200px;"><canvas id="chartTrend"></canvas></div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="ri-card p-0">
    <div class="border-bottom px-4 pt-3 d-flex align-items-center justify-content-between">
        <ul class="nav ri-nav-tabs" id="riTabs">
            <li class="nav-item">
                <button class="nav-link active" data-tab="ringkasan"><i class="bi bi-table me-1"></i> Ringkasan per Ruangan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-tab="detail"><i class="bi bi-list-ul me-1"></i> Detail Data</button>
            </li>
        </ul>
        <button class="btn btn-ri-export btn-sm mb-2" id="export-btn">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </button>
    </div>

    <!-- Tab Ringkasan -->
    <div id="tab-ringkasan" class="p-4">
        <table class="ri-summary-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ruangan Akhir</th>
                    <th class="text-end">Jml Pasien</th>
                    <th class="text-end">Total Tindakan</th>
                    <th class="text-end">Total Farmasi (OA)</th>
                    <th class="text-end">Total Pendapatan</th>
                    <th class="text-center">% Kontribusi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summaryTable as $i => $row): ?>
                <tr>
                    <td><span class="ri-rank-badge"><?= $i + 1 ?></span></td>
                    <td><strong><?= Html::encode($row['ruangan_akhir']) ?></strong></td>
                    <td class="text-end"><?= number_format((int)$row['jumlah_pasien']) ?></td>
                    <td class="text-end"><?= $fmtRp($row['total_tindakan']) ?></td>
                    <td class="text-end"><?= $fmtRp($row['total_farmasi']) ?></td>
                    <td class="text-end"><strong><?= $fmtRp($row['total_pendapatan']) ?></strong></td>
                    <td class="text-center">
                        <?php $pct = $gtPendapatan > 0 ? round(($row['total_pendapatan'] / $gtPendapatan) * 100, 1) : 0; ?>
                        <div class="d-flex align-items-center gap-2">
                            <div style="flex:1; background:#e2e8f0; border-radius:4px; height:8px; overflow:hidden;">
                                <div style="width:<?= $pct ?>%; background:#002D72; height:8px; border-radius:4px;"></div>
                            </div>
                            <span style="font-size:.8rem; font-weight:600; color:#002D72; min-width:36px;"><?= $pct ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"><i class="bi bi-calculator me-1"></i> GRAND TOTAL</td>
                    <td class="text-end"><?= number_format((int)$gtPasien) ?></td>
                    <td class="text-end"><?= $fmtRp($gtTindakan) ?></td>
                    <td class="text-end"><?= $fmtRp($gtFarmasi) ?></td>
                    <td class="text-end"><?= $fmtRp($gtPendapatan) ?></td>
                    <td class="text-center">100%</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Tab Detail -->
    <div id="tab-detail" class="p-4" style="display:none;">
        <div class="table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover mb-0 detail-gridview'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width:45px;'],
                        'contentOptions' => ['style' => 'text-align:center;']],
                    ['attribute' => 'tgl_pendaftaran', 'label' => 'Tgl Daftar',
                        'headerOptions' => ['style' => 'white-space:nowrap;']],
                    ['attribute' => 'no_pendaftaran', 'label' => 'No Daftar',
                        'headerOptions' => ['style' => 'white-space:nowrap;']],
                    ['attribute' => 'ruangan_akhir', 'label' => 'Ruangan Akhir'],
                    ['attribute' => 'no_rekam_medik', 'label' => 'No RM'],
                    ['attribute' => 'nama_pasien', 'label' => 'Nama Pasien',
                        'contentOptions' => ['style' => 'font-weight:500; color:#002D72;']],
                    ['attribute' => 'carabayar_nama', 'label' => 'Cara Bayar',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $name  = $model['carabayar_nama'] ?? '';
                            $lower = strtolower($name);
                            if (str_contains($lower, 'bpjs') || str_contains($lower, 'jkn')) {
                                $cls = 'payor-bpjs';
                            } elseif (str_contains($lower, 'pribadi') || str_contains($lower, 'umum')) {
                                $cls = 'payor-pribadi';
                            } elseif (str_contains($lower, 'asuransi')) {
                                $cls = 'payor-asuransi';
                            } else {
                                $cls = 'payor-other';
                            }
                            return '<span class="' . $cls . '">' . Html::encode($name) . '</span>';
                        }],
                    ['attribute' => 'totalbiayatindakan', 'label' => 'Tindakan',
                        'contentOptions' => ['style' => 'text-align:right; white-space:nowrap;'],
                        'value' => fn($m) => isset($m['totalbiayatindakan']) ? 'Rp ' . number_format((float)$m['totalbiayatindakan'], 0, ',', '.') : '-'],
                    ['attribute' => 'totalbiayaoa', 'label' => 'Farmasi (OA)',
                        'contentOptions' => ['style' => 'text-align:right; white-space:nowrap;'],
                        'value' => fn($m) => isset($m['totalbiayaoa']) ? 'Rp ' . number_format((float)$m['totalbiayaoa'], 0, ',', '.') : '-'],
                    ['attribute' => 'totalpelayanan', 'label' => 'Total Pelayanan',
                        'contentOptions' => ['style' => 'text-align:right; white-space:nowrap; font-weight:700; color:#002D72;'],
                        'value' => fn($m) => isset($m['totalpelayanan']) ? 'Rp ' . number_format((float)$m['totalpelayanan'], 0, ',', '.') : '-'],
                ],
                'layout' => "{items}\n<div class='p-3 d-flex justify-content-between align-items-center flex-wrap gap-2'>
                    <div style='color:#64748b; font-size:.85rem;'>{summary}</div>
                    <div>{pager}</div></div>",
                'summary' => 'Menampilkan {begin}–{end} dari {totalCount} data.',
                'pager' => [
                    'options'         => ['class' => 'pagination pagination-sm m-0'],
                    'linkOptions'     => ['class' => 'page-link'],
                    'prevPageLabel'   => '<i class="bi bi-chevron-left"></i>',
                    'nextPageLabel'   => '<i class="bi bi-chevron-right"></i>',
                ],
            ]); ?>
        </div>
    </div>
</div>

<?php else: ?>
<div class="ri-card text-center py-5">
    <i class="bi bi-search" style="font-size:3rem; color:#cbd5e1;"></i>
    <h5 class="mt-3" style="color:#94a3b8;">Pilih rentang tanggal dan klik <strong>Cari</strong> untuk memuat data</h5>
    <p style="color:#cbd5e1; font-size:.9rem;">Dashboard akan menampilkan summary, grafik, dan tabel pendapatan rawat inap.</p>
</div>
<?php endif; ?>

<?php
$js = <<<JS
// Flatpickr
flatpickr('.flatpickr-ri', { dateFormat: 'd-m-Y', allowInput: true });

// Tab toggle
document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const tab = this.dataset.tab;
        document.getElementById('tab-ringkasan').style.display = tab === 'ringkasan' ? '' : 'none';
        document.getElementById('tab-detail').style.display    = tab === 'detail'    ? '' : 'none';
    });
});

// Export
document.getElementById('export-btn') && document.getElementById('export-btn').addEventListener('click', function() {
    const df = document.getElementById('date_from').value;
    const dt = document.getElementById('date_to').value;
    const cb = document.getElementById('sel-carabayar').value;
    const ruanganEl = document.getElementById('sel-ruangan');
    const ruanganParams = Array.from(ruanganEl.selectedOptions)
        .map(o => 'ruangan%5B%5D=' + encodeURIComponent(o.value)).join('&');
    const cbParam = cb ? '&cara_bayar=' + encodeURIComponent(cb) : '';
    window.location.href = '$exportUrl&cari=aktif&date_from=' + df + '&date_to=' + dt + '&' + ruanganParams + cbParam;
});
JS;

$this->registerJs($js);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    ['position' => \yii\web\View::POS_END, 'defer' => true]);

if ($statuscari && !empty($chartRevenue)):
$chartJs = <<<JS
(function() {
    const palette = ['#002D72','#1565C0','#1976D2','#1E88E5','#2196F3','#42A5F5','#64B5F6','#90CAF9','#BBDEFB','#E3F2FD'];

    // Bar Chart - Revenue per Ruangan
    new Chart(document.getElementById('chartRevenue'), {
        type: 'bar',
        data: {
            labels: $jChartRevLabels,
            datasets: [{
                label: 'Total Pendapatan (Rp)',
                data: $jChartRevData,
                backgroundColor: $jChartRevLabels.map((_, i) => palette[i % palette.length]),
                borderRadius: 6, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID') } } },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(0) + 'jt' }, grid: { color: '#f1f5f9' } },
                x: { ticks: { maxRotation: 30 }, grid: { display: false } }
            },
            onClick: (e, el) => {
                if (el.length > 0) {
                    const label = e.chart.data.labels[el[0].index];
                    const sel = document.getElementById('sel-ruangan');
                    Array.from(sel.options).forEach(opt => {
                        if (opt.value === label) opt.selected = !opt.selected;
                    });
                    document.getElementById('filter-form').submit();
                }
            }
        }
    });

    // Pie Chart - Cara Bayar
    const pieColors = ['#002D72','#1565C0','#6DC536','#f9a825','#a855f7','#e53935','#00bcd4'];
    new Chart(document.getElementById('chartCaraBayar'), {
        type: 'doughnut',
        data: {
            labels: $jChartCbLabels,
            datasets: [{ data: $jChartCbData, backgroundColor: pieColors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' pasien' } }
            },
            cutout: '62%'
        }
    });

    // Line Chart - Trend
    new Chart(document.getElementById('chartTrend'), {
        type: 'line',
        data: {
            labels: $jChartTrendLabels,
            datasets: [{
                label: 'Pendapatan',
                data: $jChartTrendData,
                borderColor: '#002D72', backgroundColor: 'rgba(0,45,114,.08)',
                borderWidth: 2.5, tension: 0.4, fill: true,
                pointBackgroundColor: '#002D72', pointRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { callbacks: { label: ctx => 'Rp ' + ctx.raw.toLocaleString('id-ID') } } },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(0) + 'jt' }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
})();
JS;
$this->registerJs($chartJs, \yii\web\View::POS_READY);
endif; ?>
