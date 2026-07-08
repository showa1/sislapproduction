<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $dateFrom string */   /* @var $dateTo string */
/* @var $caraBayar string */  /* @var $asalRujukan string */
/* @var $jenisPemeriksaan string */
/* @var $rows array */
/* @var $totalKunjungan int */ /* @var $totalTarif float */
/* @var $optCaraBayar array */ /* @var $optAsalRujukan array */
/* @var $optJenisPemeriksaan array */
/* @var $errorMsg string|null */

$this->title = 'Laporan Laboratorium – Pasien, Pemeriksaan & Penjamin';

$this->registerCssFile('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

$this->registerCss("
body, .content-wrapper { font-family: 'Inter', sans-serif; background: #F4F7FE !important; }
.page-title { font-weight:800; font-size:1.5rem; color:#0F172A; margin:0; }
.page-sub   { font-size:.87rem; color:#64748B; margin-top:2px; }

.filter-card { background:#fff; border-radius:14px; border:1px solid #E8EDF5; box-shadow:0 2px 12px rgba(0,0,0,0.03); padding:18px 22px; margin-bottom:20px; }
.filter-card label { font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#64748B; display:block; margin-bottom:4px; }
.filter-card .form-control, .filter-card select { border-radius:8px; border:1px solid #E2E8F0; font-size:.87rem; height:38px; }
.btn-apply  { background:#002D72; color:#fff; border:none; border-radius:8px; padding:8px 20px; font-weight:700; font-size:.87rem; }
.btn-apply:hover { background:#1D4ED8; color:#fff; }
.btn-back   { background:#fff; color:#475569; border:1px solid #E2E8F0; border-radius:8px; padding:7px 18px; font-weight:600; font-size:.87rem; text-decoration:none; }
.btn-back:hover { background:#F8FAFF; color:#0F172A; }
.btn-export { background:#10B981; color:#fff; border:none; border-radius:8px; padding:7px 18px; font-weight:700; font-size:.87rem; }
.btn-export:hover { background:#059669; }

/* Summary strip */
.summary-strip { background:#0F172A; border-radius:12px; padding:16px 22px; margin-bottom:20px; display:flex; gap:32px; flex-wrap:wrap; align-items:center; }
.ss-item .ss-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#64748B; }
.ss-item .ss-val   { font-size:1.4rem; font-weight:800; color:#fff; line-height:1.2; }
.ss-item .ss-sub   { font-size:.75rem; color:#64748B; }

/* Table */
.report-wrap { background:#fff; border-radius:14px; border:1px solid #F1F5F9; box-shadow:0 4px 20px rgba(15,23,42,0.04); overflow:hidden; }
.report-table { width:100%; border-collapse:collapse; font-size:.84rem; }
.report-table thead th {
    background:#F8FAFF; padding:11px 14px; border-bottom:2px solid #E2E8F0;
    font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px;
    color:#64748B; white-space:nowrap; position:sticky; top:0; z-index:1;
}
.report-table tbody td { padding:11px 14px; border-bottom:1px solid #F8FAFF; color:#475569; vertical-align:middle; }
.report-table tbody tr:last-child td { border-bottom:none; }
.report-table tbody tr:hover td { background:#F8FAFF; }

.badge-rujukan { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:20px; font-size:.72rem; font-weight:700; }
.br-pkm   { background:#DBEAFE; color:#1D4ED8; }
.br-rs    { background:#D1FAE5; color:#059669; }
.br-klinik { background:#FEF3C7; color:#D97706; }
.br-instansi { background:#EDE9FE; color:#6D28D9; }
.br-sendiri  { background:#F1F5F9; color:#64748B; }

.badge-cb { padding:3px 9px; border-radius:20px; font-size:.72rem; font-weight:700; }
.cb-bpjs  { background:#DBEAFE; color:#1D4ED8; }
.cb-pribadi { background:#EDE9FE; color:#6D28D9; }
.cb-asuransi { background:#FEF3C7; color:#D97706; }
.cb-bpjstk { background:#D1FAE5; color:#059669; }

.status-chip { padding:3px 9px; border-radius:20px; font-size:.72rem; font-weight:700; }
.st-antri  { background:#FEF3C7; color:#D97706; }
.st-periksa { background:#DBEAFE; color:#1D4ED8; }
.st-selesai { background:#D1FAE5; color:#059669; }

/* Pagination */
.pagination { justify-content:center; margin-top:16px; gap:4px; }
.page-item .page-link { border-radius:8px; border:1px solid #E2E8F0; color:#475569; font-size:.85rem; font-weight:600; padding:7px 13px; }
.page-item.active .page-link { background:#002D72; border-color:#002D72; color:#fff; }
");

function rujukanBadge($asal) {
    $map = ['PKM / Puskesmas'=>'br-pkm','Rumah Sakit'=>'br-rs','Klinik'=>'br-klinik','Instansi Lain'=>'br-instansi','Tanpa Rujukan'=>'br-sendiri'];
    $cls = $map[$asal] ?? 'br-sendiri';
    return "<span class='badge-rujukan {$cls}'>" . Html::encode($asal) . "</span>";
}
function cbBadge($cb) {
    $map = ['BPJS Kesehatan'=>'cb-bpjs','PRIBADI'=>'cb-pribadi','ASURANSI'=>'cb-asuransi','BPJS Ketenagakerjaan'=>'cb-bpjstk'];
    $cls = $map[$cb] ?? 'cb-pribadi';
    return "<span class='badge-cb {$cls}'>" . Html::encode($cb) . "</span>";
}
function statusBadge($s) {
    if (stripos($s, 'antri') !== false) return "<span class='status-chip st-antri'>Antri</span>";
    if (stripos($s, 'periksa') !== false) return "<span class='status-chip st-periksa'>Periksa</span>";
    return "<span class='status-chip st-selesai'>Selesai</span>";
}
function fmtRp2($n) {
    return 'Rp '.number_format((float)$n,0,',','.');
}
function fmtNum2($n) { return number_format((int)$n,0,',','.'); }
?>

<div class="container-fluid p-0">

    <!-- ======== HEADER ======== -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-7">
            <h1 class="page-title"><i class="bi bi-table text-primary" style="font-size:1.3rem;"></i> Laporan Pemeriksaan Laboratorium</h1>
            <p class="page-sub">Data pasien, pemeriksaan, penjamin &amp; asal rujukan &middot; <?= Html::encode($dateFrom) ?> s/d <?= Html::encode($dateTo) ?></p>
        </div>
        <div class="col-md-5 text-md-right">
            <a href="<?= Url::to(['/laboratorium/dashboard/index', 'date_from'=>$dateFrom, 'date_to'=>$dateTo]) ?>" class="btn-back mr-2">
                <i class="bi bi-arrow-left"></i> Dashboard
            </a>
            <button onclick="window.print()" class="btn-export" style="background:#0F172A; margin-right:4px;">
                <i class="bi bi-printer-fill"></i> Cetak
            </button>
            <button id="btn-export-excel" class="btn-export" style="background:#10B981;">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- ======== ERROR ALERT (jika ada exception dari query) ======== -->
    <?php if (!empty($errorMsg)): ?>
    <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;gap:14px;align-items:flex-start;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#EF4444;font-size:1.3rem;margin-top:2px;"></i>
        <div>
            <div style="font-weight:700;color:#DC2626;margin-bottom:4px;">Terjadi kesalahan saat mengambil data</div>
            <div style="font-size:.85rem;color:#7F1D1D;font-family:monospace;"><?= Html::encode($errorMsg) ?></div>
            <div style="font-size:.8rem;color:#B91C1C;margin-top:6px;">Silakan coba rentang tanggal yang berbeda atau hubungi administrator.</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ======== FILTER ======== -->
    <div class="filter-card">
        <form method="get" action="<?= Url::to(['/laboratorium/dashboard/laporan']) ?>">
            <input type="hidden" name="r" value="laboratorium/dashboard/laporan">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label>Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="<?= Html::encode($dateFrom) ?>">
                </div>
                <div class="col-md-2">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="<?= Html::encode($dateTo) ?>">
                </div>
                <div class="col-md-3">
                    <label>Cara Bayar / Penjamin</label>
                    <select name="cara_bayar" class="form-control">
                        <option value="">-- Semua --</option>
                        <?php foreach ($optCaraBayar as $opt): ?>
                        <option value="<?= Html::encode($opt) ?>" <?= $caraBayar === $opt ? 'selected' : '' ?>><?= Html::encode($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Asal Rujukan</label>
                    <select name="asal_rujukan" class="form-control">
                        <option value="">-- Semua --</option>
                        <?php foreach ($optAsalRujukan as $opt): ?>
                        <option value="<?= Html::encode($opt) ?>" <?= $asalRujukan === $opt ? 'selected' : '' ?>><?= Html::encode($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Jenis Pemeriksaan</label>
                    <select name="jenis_pemeriksaan" class="form-control">
                        <option value="">-- Semua --</option>
                        <?php foreach ($optJenisPemeriksaan as $opt): ?>
                        <option value="<?= Html::encode($opt) ?>" <?= $jenisPemeriksaan === $opt ? 'selected' : '' ?>><?= Html::encode($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn-apply w-100"><i class="bi bi-funnel-fill"></i></button>
                </div>
            </div>
        </form>
    </div>

    <!-- ======== SUMMARY STRIP ======== -->
    <div class="summary-strip">
        <div class="ss-item">
            <div class="ss-label">Total Kunjungan</div>
            <div class="ss-val"><?= fmtNum2($totalKunjungan) ?></div>
            <div class="ss-sub">kunjungan lab</div>
        </div>
        <div class="ss-item">
            <div class="ss-label">Total Tarif</div>
            <div class="ss-val"><?= fmtRp2($totalTarif) ?></div>
            <div class="ss-sub">akumulasi periode</div>
        </div>
        <div class="ss-item">
            <div class="ss-label">Rata-rata/Kunjungan</div>
            <div class="ss-val"><?= $totalKunjungan > 0 ? fmtRp2($totalTarif/$totalKunjungan) : 'Rp 0' ?></div>
            <div class="ss-sub">tarif rata-rata</div>
        </div>
        <div class="ss-item" style="margin-left:auto;">
            <div class="ss-label" style="color:#94A3B8;">Filter Aktif</div>
            <div class="ss-val" style="font-size:1rem;">
                <?= $caraBayar ? "<span style='color:#60A5FA;font-size:.85rem;'>".Html::encode($caraBayar)."</span> " : '' ?>
                <?= $asalRujukan ? "<span style='color:#34D399;font-size:.85rem;'>".Html::encode($asalRujukan)."</span> " : '' ?>
                <?= $jenisPemeriksaan ? "<span style='color:#FBBF24;font-size:.85rem;'>".Html::encode($jenisPemeriksaan)."</span>" : '' ?>
                <?= (!$caraBayar && !$asalRujukan && !$jenisPemeriksaan) ? "<span style='color:#64748B;font-size:.85rem;'>Semua data</span>" : '' ?>
            </div>
        </div>
    </div>

    <!-- ======== TABLE ======== -->
    <div class="report-wrap">
        <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
            <table class="report-table" id="laporan-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>L/P</th>
                        <th>Umur</th>
                        <th>No. Order Lab</th>
                        <th>Tgl Masuk Lab</th>
                        <th>No. Pendaftaran</th>
                        <th>Kunjungan</th>
                        <th>Status</th>
                        <th>Cara Bayar</th>
                        <th>Penjamin</th>
                        <th>Asal Rujukan</th>
                        <th>No. Rujukan</th>
                        <th>Perujuk</th>
                        <th>Dokter Periksa</th>
                        <th class="text-right">Jml Item</th>
                        <th>Daftar Pemeriksaan</th>
                        <th class="text-right">Total Tarif</th>
                        <th class="text-right">Iur Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $page    = (int)Yii::$app->request->get('page', 1);
                    $perPage = 10;
                    $offset  = ($page - 1) * $perPage;
                    $pageRows = array_slice($rows, $offset, $perPage);
                    foreach ($pageRows as $i => $r):
                        $rowNo = $offset + $i + 1;
                    ?>
                    <tr>
                        <td style="color:#94A3B8;font-size:.8rem;"><?= $rowNo ?></td>
                        <td style="font-weight:700;color:#0F172A;white-space:nowrap;"><?= Html::encode($r['no_rekam_medis'] ?? '-') ?></td>
                        <td style="font-weight:600;color:#0F172A;min-width:150px;"><?= Html::encode($r['nama_pasien'] ?? '-') ?></td>
                        <td style="text-align:center;">
                            <span style="font-size:.8rem;font-weight:700;color:<?= ($r['jenis_kelamin']??'') === 'PEREMPUAN' ? '#EC4899' : '#3B82F6' ?>;">
                                <?= ($r['jenis_kelamin']??'') === 'PEREMPUAN' ? 'P' : 'L' ?>
                            </span>
                        </td>
                        <td style="font-size:.82rem;color:#64748B;white-space:nowrap;"><?= Html::encode($r['umur'] ?? '-') ?></td>
                        <td style="font-family:monospace;font-size:.83rem;font-weight:600;color:#1D4ED8;"><?= Html::encode($r['no_order_lab'] ?? '-') ?></td>
                        <td style="white-space:nowrap;font-size:.83rem;"><?= $r['tgl_masuk_lab'] ? date('d/m/Y H:i', strtotime($r['tgl_masuk_lab'])) : '-' ?></td>
                        <td style="font-size:.82rem;color:#64748B;"><?= Html::encode($r['no_pendaftaran'] ?? '-') ?></td>
                        <td style="font-size:.8rem;">
                            <span class="stat-chip" style="font-size:.7rem;padding:2px 7px;">
                                <?= stripos($r['kunjungan'] ?? '', 'baru') !== false ? '🔵 Baru' : '🔄 Lama' ?>
                            </span>
                        </td>
                        <td><?= statusBadge($r['status_periksa'] ?? '') ?></td>
                        <td><?= cbBadge($r['cara_bayar'] ?? '-') ?></td>
                        <td style="font-size:.82rem;color:#475569;"><?= Html::encode($r['penjamin_nama'] ?? '-') ?></td>
                        <td><?= rujukanBadge($r['asal_rujukan'] ?? 'Tanpa Rujukan') ?></td>
                        <td style="font-size:.8rem;font-family:monospace;color:#64748B;"><?= Html::encode($r['no_surat_rujukan'] ?? '-') ?></td>
                        <td style="font-size:.82rem;color:#475569;"><?= Html::encode($r['nama_perujuk'] ?? '-') ?></td>
                        <td style="font-size:.82rem;color:#475569;white-space:nowrap;"><?= Html::encode($r['dokter_pemeriksa'] ?? '-') ?></td>
                        <td class="text-right" style="font-weight:700;color:#0F172A;"><?= fmtNum2($r['jumlah_item'] ?? 0) ?></td>
                        <td style="font-size:.8rem;color:#475569;max-width:200px;"><?= Html::encode($r['daftar_pemeriksaan'] ?? '-') ?></td>
                        <td class="text-right" style="font-weight:700;color:#10B981;white-space:nowrap;"><?= fmtRp2($r['total_tarif'] ?? 0) ?></td>
                        <td class="text-right" style="font-size:.82rem;color:#475569;"><?= fmtRp2($r['total_iur_biaya'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="20" class="text-center" style="padding:40px;color:#94A3B8;">
                            <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                            Tidak ada data untuk filter yang dipilih
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalKunjungan > $perPage): ?>
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span style="font-size:.83rem;color:#64748B;">
                Menampilkan <?= $offset+1 ?>–<?= min($offset+$perPage, $totalKunjungan) ?> dari <?= fmtNum2($totalKunjungan) ?> kunjungan
            </span>
            <nav>
                <ul class="pagination pagination-sm">
                    <?php
                    $totalPages = ceil($totalKunjungan / $perPage);
                    $baseUrl = Url::current(['page' => null]);
                    for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++):
                    ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= Url::current(['page'=>$p]) ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

</div>

<style>
@media print {
    .filter-card, .btn-back, .btn-export, nav { display:none !important; }
    .summary-strip { background:#0F172A !important; -webkit-print-color-adjust:exact; }
    .report-wrap { box-shadow:none !important; border:1px solid #ccc; }
}
/* Tambah stat-chip yang dipakai di laporan tapi di-define di dashboard */
.stat-chip { display:inline-flex; align-items:center; gap:6px; padding:3px 8px; border-radius:20px; font-size:.75rem; font-weight:600; background:#F8FAFF; border:1px solid #E2E8F0; color:#475569; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var btn = document.getElementById('btn-export-excel');
    if (btn) {
        btn.addEventListener('click', function() {
            let url = new URL(window.location.href);
            url.searchParams.set('export', '1');
            window.location.href = url.href;
        });
    }
});
</script>
