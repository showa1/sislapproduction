<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

// Register DataTables assets
$this->registerCssFile('https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css', ['depends' => [\yii\bootstrap5\BootstrapAsset::class]]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'Informasi Hari Rawat';
$this->params['breadcrumbs'][] = ['label' => 'Rawat Inap', 'url' => ['/rawatinap/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="informasi-hari-rawat-index">
    
    <?php Pjax::begin(['id' => 'pjax-grid-hari-rawat', 'timeout' => false]); ?>
    
    <!-- Filter Toolbar -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body px-4 py-3">
            <form id="filterForm" method="GET" action="<?= Url::to(['index']) ?>" data-pjax="1">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Cara Bayar</label>
                        <select class="form-select" name="cara_bayar">
                            <option value="">-- Semua Cara Bayar --</option>
                            <?php foreach ($optCaraBayar as $opt): ?>
                                <option value="<?= Html::encode($opt) ?>" <?= ($caraBayar === $opt) ? 'selected' : '' ?>>
                                    <?= Html::encode($opt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Cari Diagnosa (ICD-10)</label>
                        <input type="text" class="form-control" name="diagnosa" placeholder="Contoh: G81 atau Hemiplegia" value="<?= Html::encode($diagnosaFilter ?? '') ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 w-100"><i class="bi bi-filter"></i> Terapkan</button>
                    </div>
                </div>
                <div class="row mt-3 align-items-center">
                    <div class="col-6">
                        <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'cara_bayar' => $caraBayar, 'diagnosa' => $diagnosaFilter ?? '', 'export' => 1]) ?>" class="btn btn-sm btn-success" data-pjax="0" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> Download Excel
                        </a>
                    </div>
                    <div class="col-6 text-end">
                        <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-light border text-muted"><i class="bi bi-arrow-clockwise"></i> Reset Filter</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
            <div>
                <h4 class="mb-0 fw-bold text-primary" style="color: #0dcaf0 !important;"><i class="bi bi-info-square me-2"></i> <?= Html::encode($this->title) ?></h4>
                <p class="text-muted small mt-1 mb-0">Daftar pasien rawat inap yang belum pulang beserta lama hari rawat.</p>
            </div>
        </div>
        
        <div class="card-body px-4 py-4">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle" id="tableInformasiHariRawat">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>No Rekam Medik</th>
                            <th>Nama Pasien</th>
                            <th>Cara Bayar</th>
                            <th>Diagnosa</th>
                            <th>Riwayat Kamar</th>
                            <th>Tgl Menginap</th>
                            <th>Lama Dirawat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-folder2-open fs-2 d-block mb-2 text-black-50"></i>
                                    Tidak ada data pasien rawat inap.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data as $index => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= Html::encode($row['no_rekam_medik']) ?></div>
                                        <div class="small text-muted">Reg: <?= Html::encode($row['no_pendaftaran']) ?></div>
                                    </td>
                                    <td class="fw-medium text-dark"><?= Html::encode($row['nama_pasien']) ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $cbText = $row['carabayar_nama'];
                                            $cbClass = stripos($cbText, 'bpjs') !== false ? 'bg-success bg-opacity-10 text-success border-success' : 'bg-primary bg-opacity-10 text-primary border-primary';
                                        ?>
                                        <span class="badge rounded-pill border <?= $cbClass ?> px-3 py-2 fw-medium">
                                            <?= Html::encode($cbText) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $diagnosaList = array_filter(explode("\n", $row['diagnosa'] ?? ''));
                                            if (empty($diagnosaList)) {
                                                echo '-';
                                            } else {
                                                $visibleCount = min(2, count($diagnosaList));
                                                echo "<ul class='list-unstyled mb-0' style='font-size: 0.85rem;'>";
                                                for ($i = 0; $i < $visibleCount; $i++) {
                                                    $parts = explode(' - ', $diagnosaList[$i], 2);
                                                    if (count($parts) == 2) {
                                                        echo "<li><strong class='text-dark'>{$parts[0]}</strong> - {$parts[1]}</li>";
                                                    } else {
                                                        echo "<li>{$diagnosaList[$i]}</li>";
                                                    }
                                                }
                                                echo "</ul>";
                                                if (count($diagnosaList) > 2) {
                                                    $more = count($diagnosaList) - 2;
                                                    $fullText = implode("<br>", $diagnosaList);
                                                    echo "<span class='badge bg-light text-secondary mt-1' style='cursor:pointer;' data-bs-toggle='tooltip' data-bs-html='true' title='{$fullText}'>+{$more} lainnya</span>";
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="small text-muted text-wrap" style="max-width: 200px;">
                                            <?= Html::encode($row['riwayat_kamar'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div><?= date('d M Y', strtotime($row['tgl_nginap'])) ?></div>
                                        <div class="small text-muted"><?= date('H:i', strtotime($row['tgl_nginap'])) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $lama = $row['lama_dirawat'];
                                            if (preg_match('/(\d+)\s+days?/', $lama, $matches)) {
                                                $hari = (int)$matches[1];
                                                if ($hari > 7) {
                                                    echo "<span class='badge bg-danger px-3 py-2 fs-6 shadow-sm border border-danger'>{$hari} Hari</span>";
                                                } else if ($hari > 3) {
                                                    echo "<span class='badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm border border-warning'>{$hari} Hari</span>";
                                                } else {
                                                    echo "<span class='badge bg-info text-dark px-3 py-2 fs-6 border border-info'>{$hari} Hari</span>";
                                                }
                                            } else {
                                                if (strpos($lama, ':') !== false) {
                                                    echo "<span class='badge bg-light text-secondary px-3 py-2 fs-6 border'>Hari ini</span>";
                                                } else {
                                                    echo Html::encode($lama);
                                                }
                                            }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-outline-primary" title="Detail Pasien" data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" title="Cetak Ringkasan" data-bs-toggle="tooltip">
                                                <i class="bi bi-printer"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php Pjax::end(); ?>
</div>

<?php
$this->registerJs("
    function initDataTable() {
        if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#tableInformasiHariRawat')) {
            $('#tableInformasiHariRawat').DataTable({
                'language': {
                    'url': '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                    'search': 'Pencarian Cepat:'
                },
                'pageLength': 5,
                'drawCallback': function() {
                    $('[data-bs-toggle=\"tooltip\"]').tooltip();
                }
            });
        }
    }
    
    $(document).ready(function() {
        initDataTable();
        $('[data-bs-toggle=\"tooltip\"]').tooltip();
    });

    $(document).on('pjax:success', function() {
        initDataTable();
        $('[data-bs-toggle=\"tooltip\"]').tooltip();
    });
");
?>
