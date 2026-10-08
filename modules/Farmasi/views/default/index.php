<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Dashboard Farmasi';
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

    .modal-xl { max-width:900px; }
    .progress-mini { height:6px; border-radius:3px; background:#e2e8f0; margin-top:5px; }
    .progress-mini .fill { height:100%; border-radius:3px; }
    .row-label { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#a0aec0; margin-bottom:10px; padding-left:4px; }
    .border-top-section { border-top:2px solid #f0f4f8; margin-top:8px; padding-top:18px; }

    /* Skeleton loader */
    .skeleton { background: linear-gradient(90deg, #f0f4f8 25%, #e2e8f0 50%, #f0f4f8 75%);
        background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 8px; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    .skeleton-text { height: 28px; margin: 6px 0; }
    .skeleton-sub  { height: 14px; width: 60%; margin-top: 8px; }
    .skeleton-chart { height: 280px; }
    .skeleton-row  { height: 40px; margin-bottom: 6px; }
");

function growthBadge($v) {
    if ($v > 0) return "<span class='badge-growth up'><i class='bi bi-arrow-up-right-circle-fill'></i> +{$v}%</span>";
    if ($v < 0) return "<span class='badge-growth down'><i class='bi bi-arrow-down-right-circle-fill'></i> {$v}%</span>";
    return "<span class='badge-growth neutral'><i class='bi bi-dash-circle-fill'></i> 0%</span>";
}
function rp($n) { return 'Rp '.number_format($n, 0, ',', '.'); }
function num($n) { return number_format((float)$n, 0, ',', '.'); }
?>

<div class="row mb-3 align-items-center">
    <div class="col">
        <h3 style="color:#002D72;font-weight:800;font-family:'Inter',sans-serif;margin-bottom:3px;">Dashboard Instalasi Farmasi</h3>
        <p class="text-muted mb-0" style="font-size:0.88rem;">Data Real-time · Periode: Bulan <?= date('F Y', mktime(0,0,0,$bulanIni,10,$tahunIni)) ?></p>
    </div>
</div>

<!-- ====== ROW 1: KPI Utama (Server-side, cepat) ====== -->
<div class="row-label">Performa Layanan Resep</div>
<div class="row mb-3">
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Pasien Dilayani <span class="badge bg-success" style="font-size:0.6rem;vertical-align:text-top;margin-left:4px;">Real</span></div>
                <div class="kpi-icon-box"><i class="bi bi-person-lines-fill"></i></div>
            </div>
            <div class="kpi-value text-primary"><?= num($resep_curr) ?></div>
            <div class="mt-2"><?= growthBadge($resep_growth) ?> <span class="kpi-sub">vs bulan lalu</span></div>
            <div class="kpi-sub mt-1">Penerimaan Resep</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-green">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Estimasi Omset Obat <span class="badge bg-success" style="font-size:0.6rem;vertical-align:text-top;margin-left:4px;">Real</span></div>
                <div class="kpi-icon-box"><i class="bi bi-cash-stack"></i></div>
            </div>
            <div class="kpi-value text-success kpi-value sm"><?= rp($pendapatan_curr) ?></div>
            <div class="mt-2"><?= growthBadge($pendapatan_growth) ?> <span class="kpi-sub">vs bulan lalu</span></div>
            <div class="kpi-sub mt-1">Bruto Pemakaian Obat/Alkes</div>
        </div>
    </div>
    <!-- Nilai Aset & TOR: skeleton dulu, isi via AJAX -->
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-cyan" id="card-nilai-aset">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Nilai Aset Gudang <span class="badge bg-warning text-dark" style="font-size:0.6rem;">Loading…</span></div>
                <div class="kpi-icon-box"><i class="bi bi-bank"></i></div>
            </div>
            <div class="skeleton skeleton-text" style="width:80%;"></div>
            <div class="kpi-sub mt-2">TOR (Turnover Ratio)</div>
            <div class="skeleton skeleton-sub"></div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card-kpi card-purple" data-bs-toggle="modal" data-bs-target="#modalDeadStock" id="card-deadstock">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Dead Stock <span class="badge bg-success" style="font-size:0.6rem;vertical-align:text-top;margin-left:4px;">Real</span> <small style="font-size:0.65rem;color:#6610f2;">▶ Lihat Detail</small></div>
                <div class="kpi-icon-box"><i class="bi bi-archive"></i></div>
            </div>
            <div class="kpi-value" style="color:#6610f2;"><?= num($deadstock) ?> <span style="font-size:1rem;color:#718096;font-weight:500;">Item</span></div>
            <div class="mt-2"><span class="badge-growth down"><i class='bi bi-hourglass-split'></i> Perhatian</span></div>
            <div class="kpi-sub mt-1">Tidak Bergerak ≥ 6 Bulan</div>
        </div>
    </div>
</div>

<!-- ====== ROW 2: Alert (Server-side via cache) ====== -->
<div class="row-label border-top-section">Peringatan Kritis</div>
<div class="row mb-3">
    <div class="col-md-4 mb-3">
        <div class="card-kpi card-yellow" data-bs-toggle="modal" data-bs-target="#modalStokMinimal">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Stok Menipis <span class="badge bg-success" style="font-size:0.6rem;vertical-align:text-top;margin-left:4px;">Real</span> <small style="font-size:0.65rem;color:#d97706;">▶ Lihat Detail</small></div>
                <div class="kpi-icon-box" style="background:rgba(255,193,7,.15);color:#d97706;"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
            <div class="kpi-value text-warning"><?= num($stok_min_alert) ?> <span style="font-size:1rem;color:#718096;">Item</span></div>
            <div class="kpi-sub mt-2">Item ≤ Batas Minimal Stok</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card-kpi card-red" data-bs-toggle="modal" data-bs-target="#modalExpired">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Mendekati Expired <span class="badge bg-success" style="font-size:0.6rem;vertical-align:text-top;margin-left:4px;">Real</span> <small style="font-size:0.65rem;color:#dc3545;">▶ Lihat Detail</small></div>
                <div class="kpi-icon-box"><i class="bi bi-shield-x"></i></div>
            </div>
            <div class="kpi-value text-danger"><?= num($expired_alert) ?> <span style="font-size:1rem;color:#718096;">Item</span></div>
            <div class="kpi-sub mt-2">Expired Date &lt; 90 Hari</div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card-kpi card-red" data-bs-toggle="modal" data-bs-target="#modalStockOut">
            <div class="d-flex justify-content-between align-items-start">
                <div class="kpi-title">Stock Out <span class="badge bg-success" style="font-size:0.6rem;vertical-align:text-top;margin-left:4px;">Real</span> <small style="font-size:0.65rem;color:#dc3545;">▶ Lihat Detail</small></div>
                <div class="kpi-icon-box"><i class="bi bi-x-octagon-fill"></i></div>
            </div>
            <div class="kpi-value text-danger"><?= num($stockout_alert) ?> <span style="font-size:1rem;color:#718096;">Item</span></div>
            <div class="kpi-sub mt-2">Item dengan Stok = 0 hari ini</div>
        </div>
    </div>
</div>

<!-- ====== ROW 3: Chart (skeleton, isi via AJAX) ====== -->
<div class="row-label border-top-section">Analisis &amp; Tren</div>
<div class="row">
    <div class="col-md-7 mb-4">
        <div class="section-card">
            <div class="section-title">Tren Pelayanan Resep Harian <span class="section-subtitle float-end">Bulan Ini</span></div>
            <div style="height:300px;position:relative;">
                <div id="skeleton-trend" class="skeleton skeleton-chart"></div>
                <canvas id="trendChart" style="display:none;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5 mb-4">
        <div class="section-card">
            <div class="section-title">Distribusi Stok per Kategori <span class="section-subtitle float-end">Aktif</span></div>
            <div style="height:280px;display:flex;align-items:center;justify-content:center;position:relative;">
                <div id="skeleton-donut" class="skeleton skeleton-chart" style="width:100%;"></div>
                <canvas id="donutChart" style="display:none;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ====== ROW 4: Top 10 + Activity Log (skeleton, isi via AJAX) ====== -->
<div class="row">
    <div class="col-md-7 mb-4">
        <div class="section-card" style="overflow-y:auto;max-height:420px;">
            <div class="section-title">Top 10 Fast Moving (Obat Terlaris) <span class="section-subtitle float-end">Bulan Ini</span></div>
            <div id="top-obat-container">
                <?php for($i=0;$i<6;$i++): ?>
                <div class="skeleton skeleton-row"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    <div class="col-md-5 mb-4">
        <div class="section-card">
            <div class="section-title">Log Aktivitas Terbaru <span class="section-subtitle float-end">Real-time</span></div>
            <div id="activity-log-container">
                <?php for($i=0;$i<6;$i++): ?>
                <div class="skeleton skeleton-row"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- ====== ROW 5: Pengadaan (skeleton, isi via AJAX) ====== -->
<div class="row-label border-top-section">Monitoring Pengadaan</div>
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="section-card" id="card-pengadaan">
            <div class="section-title">Status Pengadaan</div>
            <div class="skeleton skeleton-text" style="width:60%;"></div>
            <div class="skeleton skeleton-sub"></div>
        </div>
    </div>
    <div class="col-md-8 mb-4">
        <div class="section-card">
            <div class="section-title">Ringkasan PO Terbaru</div>
            <div id="recent-po-container">
                <?php for($i=0;$i<4;$i++): ?>
                <div class="skeleton skeleton-row"></div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<!-- ========================= MODALS (konten dimuat saat dibuka) ========================= -->

<!-- Modal: Stok Menipis -->
<div class="modal fade" id="modalStokMinimal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#fffbeb;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Detail Stok Menipis (Top 20 Prioritas)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modal-body-stok-menipis">
                <div class="p-4 text-center text-muted">Memuat data…</div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<!-- Modal: Expired -->
<div class="modal fade" id="modalExpired" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#fef2f2;border-radius:16px 16px 0 0;">
                <h5 class="modal-title"><i class="bi bi-shield-x text-danger me-2"></i>Detail Obat Mendekati Expired (Top 20)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modal-body-expired">
                <div class="p-4 text-center text-muted">Memuat data…</div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<!-- Modal: Dead Stock -->
<div class="modal fade" id="modalDeadStock" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#f3e8ff;border-radius:16px 16px 0 0;">
                <h5 class="modal-title" style="color:#6610f2;"><i class="bi bi-archive-fill me-2"></i>Detail Dead Stock (Tidak Bergerak ≥ 6 Bulan)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modal-body-deadstock">
                <div class="p-4 text-center text-muted">Memuat data…</div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<!-- Modal: Stock Out -->
<div class="modal fade" id="modalStockOut" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;border:none;">
            <div class="modal-header" style="background:#fef2f2;border-radius:16px 16px 0 0;">
                <h5 class="modal-title" style="color:#dc3545;"><i class="bi bi-x-octagon-fill text-danger me-2"></i>Detail Item Stock Out (Stok = 0)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modal-body-stockout">
                <div class="p-4 text-center text-muted">Memuat data…</div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<?php
$urlStats    = Url::to(['/farmasi/default/ajax-stats']);
$urlCharts   = Url::to(['/farmasi/default/ajax-charts']);
$urlActivity = Url::to(['/farmasi/default/ajax-activity']);
$urlModal    = Url::to(['/farmasi/default/ajax-modal']);

$this->registerJs("
Chart.defaults.font.family = 'Inter, sans-serif';
Chart.defaults.color = '#718096';

let trendChartInst = null, donutChartInst = null;
const donutColors = ['#007bff','#28a745','#ffc107','#dc3545','#6610f2','#17a2b8','#fd7e14','#20c997','#6c757d'];

// ---- 1. Load chart data (deferred after DOM ready) ----
setTimeout(function() {
    $.getJSON('$urlCharts', function(d) {
        // Trend
        document.getElementById('skeleton-trend').style.display = 'none';
        const cvT = document.getElementById('trendChart');
        cvT.style.display = 'block';
        const ctxT = cvT.getContext('2d');
        let grad = ctxT.createLinearGradient(0, 0, 0, 300);
        grad.addColorStop(0, 'rgba(0,123,255,0.35)');
        grad.addColorStop(1, 'rgba(0,123,255,0)');
        trendChartInst = new Chart(ctxT, {
            type: 'line',
            data: {
                labels: d.trendDates,
                datasets: [{ label: 'Pasien/Resep', data: d.trendData,
                    borderColor: '#007bff', backgroundColor: grad,
                    tension: 0.4, fill: true, borderWidth: 2.5,
                    pointRadius: 3, pointBackgroundColor: '#fff', pointBorderWidth: 2, pointHoverRadius: 5 }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font:{weight:'600'} } }, tooltip: { mode: 'index', intersect: false } },
                scales: { y: { beginAtZero: true, grid: { borderDash:[4,4], color:'#e2e8f0', drawBorder:false } }, x: { grid: { display: false } } }
            }
        });

        // Top obat
        let html = '<table class=\"tbl\"><thead><tr><th>Nama Obat / Alkes</th><th style=\"text-align:right;width:100px;\">Total Qty</th></tr></thead><tbody>';
        const maxVal = d.topObat.length > 0 ? parseInt(d.topObat[0].total) : 1;
        d.topObat.forEach(function(o, idx) {
            const pct = Math.round((parseInt(o.total) / maxVal) * 100);
            const color = idx < 3 ? '#007bff' : '#94a3b8';
            html += '<tr><td><strong>' + o.nama + '</strong><div class=\"progress-mini\"><div class=\"fill\" style=\"width:' + pct + '%;background:' + color + ';\"></div></div></td><td style=\"text-align:right;font-weight:700;color:#2d3748;\">' + parseInt(o.total).toLocaleString() + '</td></tr>';
        });
        html += '</tbody></table>';
        document.getElementById('top-obat-container').innerHTML = html;
    });
}, 100);

// ---- 2. Load stats berat (nilai aset, TOR, PO) ----
setTimeout(function() {
    $.getJSON('$urlStats', function(d) {
        // Nilai Aset card
        document.getElementById('card-nilai-aset').innerHTML =
            '<div class=\"d-flex justify-content-between align-items-start\">' +
            '<div class=\"kpi-title\">Nilai Aset Gudang <span class=\"badge bg-success\" style=\"font-size:0.6rem;\">Real</span></div>' +
            '<div class=\"kpi-icon-box\"><i class=\"bi bi-bank\"></i></div></div>' +
            '<div class=\"kpi-value\" style=\"font-size:1.3rem;color:#17a2b8;\">Rp ' + Math.round(d.nilaiAset).toLocaleString('id-ID') + '</div>' +
            '<div class=\"kpi-sub mt-2\">TOR (Turnover Ratio)</div>' +
            '<div style=\"font-size:1.2rem;font-weight:800;color:#17a2b8;\">' + d.tor + 'x / tahun</div>';

        // Donut chart
        document.getElementById('skeleton-donut').style.display = 'none';
        const cvD = document.getElementById('donutChart');
        cvD.style.display = 'block';
        const labels = d.distribusi.map(x => x.kategori);
        const vals   = d.distribusi.map(x => parseInt(x.jumlah));
        donutChartInst = new Chart(cvD.getContext('2d'), {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: vals, backgroundColor: donutColors, borderWidth: 2, borderColor: '#ffffff', hoverOffset: 6 }] },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 9, font:{size:11} } },
                    tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString() + ' item' } } },
                cutout: '62%' }
        });

        // Pengadaan
        document.getElementById('card-pengadaan').innerHTML =
            '<div class=\"section-title\">Status Pengadaan</div>' +
            '<div class=\"d-flex gap-4\"><div class=\"text-center flex-fill\">' +
            '<div style=\"font-size:2.4rem;font-weight:800;color:#f59e0b;\">' + d.pendingPO + '</div>' +
            '<div style=\"font-size:0.8rem;color:#718096;\">PO Pending<br><small>Belum Diterima</small></div></div>' +
            '<div class=\"text-center flex-fill\">' +
            '<div style=\"font-size:2.4rem;font-weight:800;color:#6610f2;\">' + (d.leadTime || '—') + '</div>' +
            '<div style=\"font-size:0.8rem;color:#718096;\">Hari Lead Time<br><small>Rata-rata Pengiriman</small></div></div></div>' +
            '<div class=\"alert-banner warn mt-3\" style=\"padding:10px 14px;\"><i class=\"bi bi-info-circle-fill text-warning\"></i>' +
            '<span style=\"font-size:0.8rem;\">PO pending = pesanan ke vendor yang belum tiba.</span></div>';
    });
}, 200);

// ---- 3. Load activity log ----
setTimeout(function() {
    $.getJSON('$urlActivity', function(logs) {
        let html = '<table class=\"tbl\"><thead><tr><th>Waktu</th><th>Obat / Alkes</th><th style=\"text-align:center;\">Tipe</th><th style=\"text-align:right;\">Qty</th></tr></thead><tbody>';
        logs.forEach(function(log) {
            const isIn = parseInt(log.qtystok_in) > 0;
            const tipe = isIn ? '<span class=\"activity-badge badge-in\"><i class=\"bi bi-box-arrow-in-down\"></i> Masuk</span>'
                              : '<span class=\"activity-badge badge-out\"><i class=\"bi bi-box-arrow-up-right\"></i> Keluar</span>';
            const qty  = isIn ? log.qtystok_in : log.qtystok_out;
            const dt   = new Date(log.create_time);
            const waktu = (dt.getDate()).toString().padStart(2,'0') + '/' + (dt.getMonth()+1).toString().padStart(2,'0') + ' ' + dt.getHours().toString().padStart(2,'0') + ':' + dt.getMinutes().toString().padStart(2,'0');
            html += '<tr><td style=\"font-size:0.78rem;color:#94a3b8;white-space:nowrap;\">' + waktu + '</td>' +
                    '<td style=\"max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;\">' + log.obatalkes_nama + '</td>' +
                    '<td style=\"text-align:center;\">' + tipe + '</td>' +
                    '<td style=\"text-align:right;font-weight:700;\">' + parseInt(qty).toLocaleString() + '</td></tr>';
        });
        html += '</tbody></table>';
        document.getElementById('activity-log-container').innerHTML = html;
    });
}, 300);

// ---- 4. Modal lazy-load: hanya query saat modal dibuka ----
const modalLoaded = {};
function loadModal(modalId, bodyId, type) {
    const el = document.getElementById(modalId);
    if (!el) return;
    el.addEventListener('show.bs.modal', function() {
        if (modalLoaded[type]) return;
        modalLoaded[type] = true;
        $.getJSON('$urlModal', { type: type }, function(rows) {
            let html = '';
            if (type === 'stok_menipis') {
                html = '<table class=\"tbl\"><thead><tr><th>#</th><th>Nama Obat</th><th>Kategori</th><th style=\"text-align:right;\">Stok</th><th style=\"text-align:right;\">Minimal</th><th style=\"text-align:center;\">Status</th></tr></thead><tbody>';
                rows.forEach(function(r, i) {
                    const neg = parseInt(r.stok) < 0;
                    const lbl = neg ? '<span class=\"activity-badge badge-out\">Stock Out</span>' : '<span class=\"activity-badge\" style=\"background:#fef3c7;color:#d97706;\">Menipis</span>';
                    html += '<tr' + (neg ? ' style=\"background:#fef2f2;\"' : '') + '><td style=\"color:#a0aec0;\">' + (i+1) + '</td><td><strong>' + r.obatalkes_nama + '</strong></td><td>' + (r.obatalkes_kategori||'-') + '</td><td style=\"text-align:right;font-weight:700;color:' + (neg?'#dc3545':'#d97706') + ';\">' + parseInt(r.stok).toLocaleString() + '</td><td style=\"text-align:right;color:#718096;\">' + parseInt(r.jmlminimalstok).toLocaleString() + '</td><td style=\"text-align:center;\">' + lbl + '</td></tr>';
                });
                html += '</tbody></table>';
            } else if (type === 'expired') {
                html = '<table class=\"tbl\"><thead><tr><th>#</th><th>Nama Obat</th><th>Kategori</th><th style=\"text-align:center;\">Tgl Expired</th><th style=\"text-align:center;\">Sisa Hari</th><th style=\"text-align:center;\">Urgensi</th></tr></thead><tbody>';
                rows.forEach(function(r, i) {
                    const sisa = parseInt(r.sisa_hari);
                    const exp  = sisa <= 0;
                    const lbl  = exp ? '<span class=\"activity-badge badge-out\">Expired!</span>' : (sisa <= 30 ? '<span class=\"activity-badge badge-out\">Kritis</span>' : '<span class=\"activity-badge\" style=\"background:#fef3c7;color:#d97706;\">Perhatian</span>');
                    html += '<tr' + (exp ? ' style=\"background:#fef2f2;\"' : '') + '><td style=\"color:#a0aec0;\">' + (i+1) + '</td><td><strong>' + r.obatalkes_nama + '</strong></td><td>' + (r.obatalkes_kategori||'-') + '</td><td style=\"text-align:center;\">' + r.tglkadaluarsa + '</td><td style=\"text-align:center;font-weight:700;color:' + (sisa<=0?'#dc3545':(sisa<=30?'#d97706':'#4a5568')) + ';\">' + (sisa<=0?'EXPIRED':sisa) + '</td><td style=\"text-align:center;\">' + lbl + '</td></tr>';
                });
                html += '</tbody></table>';
            } else if (type === 'deadstock') {
                html = '<table class=\"tbl\"><thead><tr><th>#</th><th>Nama Obat</th><th>Kategori</th><th style=\"text-align:right;\">Stok</th><th style=\"text-align:center;\">Trx Terakhir</th><th style=\"text-align:center;\">Durasi Pasif</th></tr></thead><tbody>';
                rows.forEach(function(r, i) {
                    html += '<tr><td style=\"color:#a0aec0;\">' + (i+1) + '</td><td><strong>' + r.obatalkes_nama + '</strong></td><td>' + (r.obatalkes_kategori||'-') + '</td><td style=\"text-align:right;font-weight:700;color:#6610f2;\">' + parseInt(r.stok).toLocaleString() + '</td><td style=\"text-align:center;\">' + (r.last_trx||'—') + '</td><td style=\"text-align:center;font-weight:700;color:#dc3545;\">' + parseInt(r.hari_tidak_bergerak) + ' Hari</td></tr>';
                });
                html += '</tbody></table>';
            } else if (type === 'stockout') {
                html = '<table class=\"tbl\"><thead><tr><th>#</th><th>Nama Obat</th><th>Kategori</th><th style=\"text-align:right;\">Stok</th><th style=\"text-align:center;\">Trx Terakhir</th></tr></thead><tbody>';
                rows.forEach(function(r, i) {
                    html += '<tr style=\"background:#fef2f2;\"><td style=\"color:#a0aec0;\">' + (i+1) + '</td><td><strong>' + r.obatalkes_nama + '</strong></td><td>' + (r.obatalkes_kategori||'-') + '</td><td style=\"text-align:right;font-weight:700;color:#dc3545;\">' + parseInt(r.stok).toLocaleString() + '</td><td style=\"text-align:center;\">' + (r.last_trx||'—') + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            document.getElementById(bodyId).innerHTML = html || '<div class=\"p-4 text-center text-muted\">Tidak ada data.</div>';
        });
    });
}

loadModal('modalStokMinimal', 'modal-body-stok-menipis', 'stok_menipis');
loadModal('modalExpired',     'modal-body-expired',      'expired');
loadModal('modalDeadStock',   'modal-body-deadstock',    'deadstock');
loadModal('modalStockOut',    'modal-body-stockout',     'stockout');
");
?>