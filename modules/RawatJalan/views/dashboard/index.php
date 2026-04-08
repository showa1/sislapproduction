<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Dashboard Rawat Jalan';

// Helper: format menit
function fmtMenit($val) {
    if ($val === null || $val === false) return '-';
    $v = (float)$val;
    $m = (int)$v;
    $s = (int)(($v - $m) * 60);
    return $s > 0 ? "{$m} mnt {$s} dtk" : "{$m} mnt";
}

$this->registerCss("
    .stat-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        padding: 24px 20px;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,0.09); }
    .stat-card .stat-icon {
        font-size: 2.4rem;
        opacity: 0.15;
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
    }
    .stat-card .stat-label { font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.7px; opacity: 0.7; margin-bottom: 6px; }
    .stat-card .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; }
    .stat-card .stat-sub   { font-size: 0.8rem; margin-top: 6px; opacity: 0.65; }
    .chart-card { border-radius: 14px; border: none; box-shadow: 0 4px 16px rgba(0,0,0,0.05); }
    .rt-table thead th { background: #002D72 !important; color: #fff !important; font-size: 13px; padding: 12px 14px !important; border: none; }
    .rt-table tbody td { vertical-align: middle; padding: 10px 14px !important; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
    .badge-rank { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.75rem; }
    .section-title { font-size: 1rem; font-weight: 700; color: #002D72; border-left: 4px solid #6DC536; padding-left: 10px; margin-bottom: 16px; }
");
?>

<!-- Breadcrumb -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::to(['/rawatjalan/default/index']) ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Rawat Jalan</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page">Dashboard</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h2 style="color: #002D72; font-weight: 800; margin: 0;">Dashboard Rawat Jalan</h2>
            <!-- Filter Rentang Tanggal -->
            <form method="get" action="<?= Url::to(['/rawatjalan/dashboard/index']) ?>" class="d-flex align-items-center gap-2">
                <label class="mb-0 fw-600" style="font-size:.9rem; color:#4a5568;">Periode:</label>
                <div class="input-group input-group-sm" style="width: auto;">
                    <input type="text" name="date_from" value="<?= date('d-m-Y', strtotime($dateFrom)) ?>" 
                           class="form-control flatpickr-single" style="width:110px; border-radius:8px 0 0 8px; border-color:#e2e8f0;">
                    <span class="input-group-text" style="background: #f8f9fa; border-color:#e2e8f0; font-size: 0.75rem;">s/d</span>
                    <input type="text" name="date_to" value="<?= date('d-m-Y', strtotime($dateTo)) ?>" 
                           class="form-control flatpickr-single" style="width:110px; border-radius:0 8px 8px 0; border-color:#e2e8f0;">
                </div>
                <button type="submit" class="btn btn-sm px-3" style="background:#002D72; color:#fff; border-radius:8px; font-weight:600;">Tampilkan</button>
            </form>
        </div>
        <div style="font-size:.85rem; color:#6c757d; margin-top:4px;">
            Periode: <strong><?= date('d M Y', strtotime($dateFrom)) ?></strong> s/d <strong><?= date('d M Y', strtotime($dateTo)) ?></strong>
        </div>
    </div>
</div>

<!-- ── STAT CARDS ── -->
<div class="row g-3 mb-4">
    <!-- Total Pasien -->
    <div class="col-sm-6 col-lg">
        <div class="stat-card" style="background: linear-gradient(135deg,#002D72,#1a4fa0); color:#fff;">
            <div class="stat-label">Total Pasien</div>
            <div class="stat-value"><?= number_format($stats['total_pasien'] ?? 0) ?></div>
            <div class="stat-sub">Kunjungan bulan ini</div>
            <i class="bi bi-people-fill stat-icon"></i>
        </div>
    </div>
    <!-- Avg Responstime -->
    <div class="col-sm-6 col-lg">
        <div class="stat-card" style="background: linear-gradient(135deg,#29ABE2,#1a8cbf); color:#fff;">
            <div class="stat-label">Rata-rata Responstime</div>
            <div class="stat-value" style="font-size:1.5rem;"><?= fmtMenit($stats['avg_responstime_menit'] ?? null) ?></div>
            <div class="stat-sub">Total waktu layanan (Status 3→7)</div>
            <i class="bi bi-stopwatch-fill stat-icon"></i>
        </div>
    </div>
    <!-- Poli Teraktif -->
    <div class="col-sm-6 col-lg">
        <div class="stat-card" style="background: linear-gradient(135deg,#6DC536,#55a626); color:#fff;">
            <div class="stat-label">Poli Teraktif</div>
            <div class="stat-value" style="font-size:1.1rem; line-height:1.3;"><?= Html::encode($politeraktif['ruangan_nama'] ?? '-') ?></div>
            <div class="stat-sub"><?= number_format($politeraktif['jml'] ?? 0) ?> kunjungan</div>
            <i class="bi bi-hospital-fill stat-icon"></i>
        </div>
    </div>
    <!-- Pasien Loyal (Repeat) -->
    <div class="col-sm-6 col-lg">
        <div class="stat-card" style="background: linear-gradient(135deg,#6366f1,#4338ca); color:#fff;">
            <div class="stat-label">Pasien Loyal (Repeat)</div>
            <div class="stat-value"><?= number_format($repeatStats['count_loyal'] ?? 0) ?></div>
            <div class="stat-sub">
                <?php 
                    $totalUnik = $repeatStats['total_pasien_unik'] ?? 0;
                    $loyal = $repeatStats['count_loyal'] ?? 0;
                    $pct = $totalUnik > 0 ? round(($loyal / $totalUnik) * 100, 1) : 0;
                ?>
                <span class="badge bg-white text-indigo-700" style="font-weight:700; color:#4338ca;"><?= $pct ?>%</span> dari total pasien
            </div>
            <i class="bi bi-person-check-fill stat-icon"></i>
        </div>
    </div>
    <!-- Periode -->
    <div class="col-sm-6 col-lg">
        <div class="stat-card" style="background: linear-gradient(135deg,#f8f9fa,#e9ecef); color:#002D72;">
            <div class="stat-label" style="color:#6c757d;">Periode Aktif</div>
            <div class="stat-value" style="font-size:1.4rem;"><?= date('M Y', strtotime($dateFrom)) ?></div>
            <div class="stat-sub" style="color:#6c757d;"><?= date('d M', strtotime($dateFrom)) ?> – <?= date('d M Y', strtotime($dateTo)) ?></div>
            <i class="bi bi-calendar3 stat-icon" style="color:#002D72;"></i>
        </div>
    </div>
</div>

<!-- ── CHARTS ── -->
<div class="row g-3 mb-4">
    <!-- Line Chart: Tren Kunjungan -->
    <div class="col-lg-7">
        <div class="card chart-card p-4">
            <div class="section-title">Tren Kunjungan Harian</div>
            <canvas id="lineChart" height="120"></canvas>
        </div>
    </div>
    <!-- Bar Chart: Avg Waktu Pelayanan per Poli -->
    <div class="col-lg-5">
        <div class="card chart-card p-4">
            <div class="section-title">Avg Waktu Pemeriksaan per Poli (mnt)</div>
            <canvas id="barChart" height="180"></canvas>
        </div>
    </div>
</div>

<!-- ── RESPONSTIME TABLE + TOP 5 ── -->
<div class="row g-3">
    <!-- Tabel Responstime -->
    <div class="col-lg-7">
        <div class="card chart-card p-4">
            <div class="section-title">Ringkasan Responstime Bulan Ini</div>
            <div class="table-responsive">
                <table class="table rt-table mb-0">
                    <thead>
                        <tr>
                            <th>Tahapan</th>
                            <th class="text-center">Rata-rata</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge" style="background:#e3f2fd; color:#29ABE2;">Status 3→4</span> Waktu Tunggu Poli</td>
                            <td class="text-center fw-bold"><?= fmtMenit($rtSummary['avg_3_4'] ?? null) ?></td>
                            <td style="font-size:0.8rem; color:#6c757d;">Pendaftaran → Mulai Anamnesa</td>
                        </tr>
                        <tr>
                            <td><span class="badge" style="background:#e8f5e9; color:#55a626;">Status 4→5</span> Waktu Pemeriksaan</td>
                            <td class="text-center fw-bold"><?= fmtMenit($rtSummary['avg_4_5'] ?? null) ?></td>
                            <td style="font-size:0.8rem; color:#6c757d;">Mulai Anamnesa → Selesai Poli</td>
                        </tr>
                        <tr>
                            <td><span class="badge" style="background:#fff8e1; color:#f59e0b;">Status 5→6</span> Waktu Tunggu Obat</td>
                            <td class="text-center fw-bold"><?= fmtMenit($rtSummary['avg_5_6'] ?? null) ?></td>
                            <td style="font-size:0.8rem; color:#6c757d;">Resep dibuat → Obat siap</td>
                        </tr>
                        <tr>
                            <td><span class="badge" style="background:#fce4ec; color:#e53935;">Status 6→7</span> Waktu Tunggu Layanan Obat</td>
                            <td class="text-center fw-bold"><?= fmtMenit($rtSummary['avg_6_7'] ?? null) ?></td>
                            <td style="font-size:0.8rem; color:#6c757d;">Obat siap → Selesai</td>
                        </tr>
                        <tr style="background:#f0fdf4;">
                            <td><span class="badge" style="background:#002D72; color:#fff;">Total</span> <strong>Total Waktu Layanan</strong></td>
                            <td class="text-center fw-bold" style="color:#002D72; font-size:1.1rem;"><?= fmtMenit($rtSummary['avg_total'] ?? null) ?></td>
                            <td style="font-size:0.8rem; color:#6c757d;">Status 3 → 7</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top 5 Dokter -->
    <div class="col-lg-5">
        <div class="card chart-card p-4">
            <div class="section-title">🏆 Top 5 Dokter Responstime Terbaik</div>
            <?php if (empty($top5)): ?>
                <p class="text-muted text-center py-4">Belum ada data untuk periode ini.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table rt-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:40px;">#</th>
                            <th>Dokter</th>
                            <th class="text-center">Avg Total</th>
                            <th class="text-center">Pasien</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rankColors = ['#FFD700','#C0C0C0','#CD7F32','#29ABE2','#6DC536'];
                        foreach ($top5 as $i => $row): ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge-rank" style="background:<?= $rankColors[$i] ?? '#e2e8f0' ?>; color:<?= $i < 3 ? '#fff' : '#002D72' ?>;">
                                    <?= $i + 1 ?>
                                </span>
                            </td>
                            <td style="font-weight:600; font-size:0.88rem;"><?= Html::encode(trim($row['nama_dokter'])) ?></td>
                            <td class="text-center"><span class="badge" style="background:#e3f2fd; color:#002D72; font-size:.85rem;"><?= fmtMenit($row['avg_total_menit']) ?></span></td>
                            <td class="text-center text-muted" style="font-size:.85rem;"><?= $row['jml_pasien'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── TOP 10 HIGH-FREQUENCY PATIENTS ── -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card chart-card p-4">
            <div class="section-title">Top 10 High-Frequency Patients (Loyalty List)</div>
            <?php if (empty($topRepeatPatients)): ?>
                <p class="text-muted text-center py-4">Belum ada data pasien repeat order untuk periode ini.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table rt-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:50px;">Rank</th>
                            <th>No Rekam Medik</th>
                            <th>Nama Pasien</th>
                            <th class="text-center">Total Kunjungan</th>
                            <th>Poliklinik Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topRepeatPatients as $i => $rp): ?>
                        <tr>
                            <td class="text-center">
                                <span class="badge" style="background:#f1f5f9; color:#002D72; font-weight:700;"><?= $i + 1 ?></span>
                            </td>
                            <td class="fw-bold" style="color: #002D72;"><?= Html::encode($rp['no_rekam_medik']) ?></td>
                            <td style="font-weight:600;"><?= Html::encode($rp['nama_pasien']) ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-primary px-3"><?= $rp['total_kunjungan'] ?> Kali</span>
                            </td>
                            <td><i class="bi bi-geo-alt-fill me-2 text-muted"></i><?= Html::encode($rp['poliklinik_terakhir']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// ─── JavaScript: Chart.js ────────────────────────────────────────────────
$trendLabels = json_encode(array_column($trendData, 'tgl_label'));
$trendValues = json_encode(array_column($trendData, 'jml_pasien'));
$barLabels   = json_encode(array_column($barData,   'ruangan_nama'));
$barValues   = json_encode(array_column($barData,   'avg_menit'));

$this->registerJsFile(Yii::$app->request->baseUrl . '/template/vendors/chart.js/chart.umd.js',
    ['depends' => [\yii\web\JqueryAsset::class]]);

$this->registerJs("
    // Line Chart
    new Chart(document.getElementById('lineChart'), {
        type: 'line',
        data: {
            labels: $trendLabels,
            datasets: [{
                label: 'Jumlah Pasien',
                data: $trendValues,
                borderColor: '#002D72',
                backgroundColor: 'rgba(0,45,114,0.08)',
                borderWidth: 2.5,
                pointBackgroundColor: '#002D72',
                pointRadius: 4,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Bar Chart
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: $barLabels,
            datasets: [{
                label: 'Avg Waktu (mnt)',
                data: $barValues,
                backgroundColor: [
                    '#002D72','#29ABE2','#6DC536','#f59e0b','#e53935',
                    '#8b5cf6','#14b8a6','#f97316','#64748b','#ec4899'
                ],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
");

$this->registerCssFile('@web/template/vendors/flatpickr/flatpickr.min.css');
$this->registerJsFile('@web/template/vendors/flatpickr/flatpickr.min.js', ['position' => \yii\web\View::POS_END]);

$this->registerJs("
    function initFlatpickrDash() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.flatpickr-single', {
                dateFormat: 'd-m-Y',
                allowInput: true,
            });
        } else {
            setTimeout(initFlatpickrDash, 100);
        }
    }
    initFlatpickrDash();
");
?>
