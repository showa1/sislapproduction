<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $totalCount int */
/* @var $pageSize int */
/* @var $keyword string */
/* @var $dateFrom string */
/* @var $dateTo string */

$this->title = 'Informasi Pasien Clinical Pathway';

$models = $dataProvider->getModels();
$pagination = $dataProvider->pagination;
$page = $pagination->getPage() + 1;
$startItem = $totalCount > 0 ? ($page - 1) * $pageSize + 1 : 0;
$endItem = min($page * $pageSize, $totalCount);

$this->registerCss("
    .cp-header-bar {
        background: #e2e8f0;
        padding: 10px 16px;
        font-weight: 600;
        color: #334155;
        font-size: 15px;
        border-bottom: 1px solid #cbd5e1;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
    }
    .cp-section-bar {
        background: #a3e635;
        background: linear-gradient(180deg, #b7f071 0%, #9be24a 100%);
        padding: 10px 16px;
        font-weight: 700;
        color: #1e3a8a;
        font-size: 14px;
        border: 1px solid #86efac;
    }
    .cp-table-container {
        overflow-x: auto;
        border: 1px solid #cbd5e1;
        background: #ffffff;
    }
    .cp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        color: #1e293b;
    }
    .cp-table th {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 8px 6px;
        font-weight: 700;
        text-align: left;
        vertical-align: middle;
        white-space: nowrap;
    }
    .cp-table td {
        border: 1px solid #cbd5e1;
        padding: 6px;
        vertical-align: top;
        font-size: 11px;
        line-height: 1.3;
    }
    .cp-table tr:hover {
        background-color: #f1f5f9;
    }
    .bg-red-alert {
        background-color: #ff0000 !important;
        color: #ffffff !important;
        font-weight: bold;
    }
    .text-edit-pencil {
        color: #475569;
        margin-right: 4px;
    }
    .filter-box {
        background: #ffffff;
        padding: 12px;
        border: 1px solid #cbd5e1;
        margin-bottom: 12px;
        border-radius: 4px;
    }
");
?>

<div class="pasien-clinical-pathway-index">

    <!-- Header Title Bar -->
    <div class="cp-header-bar mb-3">
        Informasi Pasien <span class="fw-bold">Clinical Pathway</span>
    </div>

    <!-- Filter Form -->
    <div class="filter-box">
        <form method="get" action="<?= Url::to(['/keuangan/pasien-clinical-pathway/index']) ?>" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Cari Pasien / RM / No. Reg</label>
                <input type="text" name="q" value="<?= Html::encode($keyword) ?>" class="form-control form-control-sm" placeholder="No. RM / Nama / No. Pendaftaran...">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Tgl. Pendaftaran Dari</label>
                <input type="date" name="date_from" value="<?= Html::encode($dateFrom) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Tgl. Pendaftaran Sampai</label>
                <input type="date" name="date_to" value="<?= Html::encode($dateTo) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Tampilkan</label>
                <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="10" <?= $pageSize == 10 ? 'selected' : '' ?>>10 baris</option>
                    <option value="25" <?= $pageSize == 25 ? 'selected' : '' ?>>25 baris</option>
                    <option value="50" <?= $pageSize == 50 ? 'selected' : '' ?>>50 baris</option>
                    <option value="100" <?= $pageSize == 100 ? 'selected' : '' ?>>100 baris</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i> Cari Data
                </button>
                <a href="<?= Url::to(['/keuangan/pasien-clinical-pathway/index']) ?>" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sub Header Bar -->
    <div class="cp-section-bar">
        Tabel Pasien <span class="fw-bold">Clinical Pathway</span>
    </div>

    <!-- Table Container -->
    <div class="cp-table-container">
        
        <!-- Controls Info & Pagination top -->
        <div class="d-flex justify-content-between align-items-center p-2 bg-light border-bottom">
            <div class="small text-muted">
                Menampilkan <strong><?= $startItem ?>-<?= $endItem ?></strong> dari <strong><?= $totalCount ?></strong> hasil.
                <select class="form-select form-select-sm d-inline-block mx-1" style="width: 80px;" onchange="location = this.value;">
                    <option value="<?= Url::current(['limit' => 10]) ?>" <?= $pageSize == 10 ? 'selected' : '' ?>>10</option>
                    <option value="<?= Url::current(['limit' => 25]) ?>" <?= $pageSize == 25 ? 'selected' : '' ?>>25</option>
                    <option value="<?= Url::current(['limit' => 50]) ?>" <?= $pageSize == 50 ? 'selected' : '' ?>>50</option>
                    <option value="<?= Url::current(['limit' => 100]) ?>" <?= $pageSize == 100 ? 'selected' : '' ?>>100</option>
                </select> baris per halaman.
            </div>
        </div>

        <table class="cp-table">
            <thead>
                <tr>
                    <th style="width: 140px;">Tanggal Admisi / Masuk Kamar</th>
                    <th style="width: 90px;">Cara Masuk</th>
                    <th style="width: 130px;">Tgl. Pendaftaran/ No. Pendaftaran</th>
                    <th style="width: 90px;">No. Rekam Medik</th>
                    <th style="width: 130px;">Nama Pasien</th>
                    <th style="width: 90px;">Tanggal Lahir</th>
                    <th style="width: 130px;">Cara Bayar / Penjamin</th>
                    <th style="width: 120px;">Dokter Penerima</th>
                    <th style="width: 130px;">DPJP</th>
                    <th style="width: 90px;">Kelas Pelayanan</th>
                    <th style="width: 90px;">Kasus Penyakit</th>
                    <th style="width: 100px;">No.Kamar No.Bed</th>
                    <th style="width: 100px;" class="text-end">Total Tagihan</th>
                    <th style="width: 100px;" class="text-end">Total Klaim</th>
                    <th style="width: 60px;" class="text-center">Persentase</th>
                    <th style="width: 100px;" class="text-end">Total Pagu</th>
                    <th style="width: 100px;" class="text-end">Selisih</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="17" class="text-center text-muted py-4">
                            <em>Tidak ada data pasien clinical pathway yang ditemukan.</em>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($models as $row): 
                        $tglAdmisi = !empty($row['tgladmisi']) ? date('d M Y H:i:s', strtotime($row['tgladmisi'])) : '-';
                        $tglKamar = !empty($row['tglmasukkamar']) ? date('d M Y H:i:s', strtotime($row['tglmasukkamar'])) : $tglAdmisi;
                        
                        $tglReg = !empty($row['tgl_pendaftaran']) ? date('d M Y H:i:s', strtotime($row['tgl_pendaftaran'])) : '-';
                        $tglLahir = !empty($row['tanggal_lahir']) ? date('d M Y', strtotime($row['tanggal_lahir'])) : '-';

                        $namaPasien = trim(($row['namadepan'] ?? '') . ' ' . ($row['nama_pasien'] ?? ''));
                        
                        $caraBayar = $row['carabayar_nama'] ?? '-';
                        $penjamin = $row['penjamin_nama'] ?? '-';

                        $totalTagihan = (float)($row['total_tagihan'] ?? 0);
                        $totalKlaim = (float)($row['total_klaim'] ?? 0);
                        $persentase = (float)($row['persentase'] ?? 80);
                        $totalPagu = (float)($row['total_pagu'] ?? 0);
                        $selisih = (float)($row['selisih'] ?? 0);

                        $isDefisit = $selisih < 0 || ($totalTagihan > $totalPagu && $totalPagu > 0);
                    ?>
                        <tr>
                            <td><?= $tglAdmisi ?><br><?= $tglKamar ?></td>
                            <td><?= Html::encode($row['cara_masuk'] ?? 'Melalui Rawat Darurat') ?></td>
                            <td><?= $tglReg ?><br><strong><?= Html::encode($row['no_pendaftaran'] ?? '-') ?></strong></td>
                            <td><strong><?= Html::encode($row['no_rekam_medik'] ?? '-') ?></strong></td>
                            <td><strong><?= Html::encode($namaPasien) ?></strong></td>
                            <td><?= $tglLahir ?></td>
                            <td><?= Html::encode($caraBayar) ?> /<br><?= Html::encode($penjamin) ?></td>
                            <td><?= Html::encode($row['dokter_penerima'] ?? '-') ?></td>
                            <td>
                                <i class="bi bi-pencil-fill text-edit-pencil"></i>
                                DPJP 1 : <?= Html::encode($row['dpjp_nama'] ?? '-') ?>
                            </td>
                            <td><?= Html::encode($row['kelaspelayanan_nama'] ?? 'Kelas III') ?></td>
                            <td>
                                <i class="bi bi-pencil-fill text-edit-pencil"></i>
                                <?= Html::encode($row['jeniskasuspenyakit_nama'] ?? 'Umum') ?>
                            </td>
                            <td>
                                Kmr : <?= Html::encode($row['kamarruangan_nokamar'] ?? '-') ?><br>
                                Bed : <?= Html::encode($row['kamarruangan_nobed'] ?? '-') ?>
                                <i class="bi bi-pencil-fill text-edit-pencil ms-1"></i>
                            </td>
                            
                            <!-- Total Tagihan -->
                            <td class="text-end <?= $isDefisit ? 'bg-red-alert' : '' ?>">
                                <?= number_format($totalTagihan, 2, ',', '.') ?>
                            </td>
                            
                            <!-- Total Klaim -->
                            <td class="text-end">
                                <?= number_format($totalKlaim, 2, ',', '.') ?>
                            </td>
                            
                            <!-- Persentase -->
                            <td class="text-center">
                                <?= number_format($persentase, 0, ',', '.') ?>
                            </td>
                            
                            <!-- Total Pagu -->
                            <td class="text-end">
                                <?= number_format($totalPagu, 2, ',', '.') ?>
                            </td>
                            
                            <!-- Selisih -->
                            <td class="text-end <?= $isDefisit ? 'bg-red-alert' : '' ?>">
                                <?= number_format($selisih, 2, ',', '.') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- LinkPager Pagination -->
        <div class="p-2 d-flex justify-content-between align-items-center bg-light border-top">
            <div>
                <small class="text-muted">Halaman <?= $page ?> dari <?= ceil($totalCount / max($pageSize, 1)) ?></small>
            </div>
            <div>
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination pagination-sm m-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ]) ?>
            </div>
        </div>

    </div>

</div>
