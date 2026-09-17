<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $search string */
/* @var $dateFrom string */
/* @var $dateTo string */
/* @var $tipepaketId string */
/* @var $penjaminId string */
/* @var $sortOrder string */
/* @var $paketList array */
/* @var $penjaminList array */
/* @var $totalPasien int */
/* @var $totalPaketTerpakai int */

$this->title = 'Informasi Paket / Pasien MCU';
$this->params['breadcrumbs'][] = ['label' => 'Pendaftaran', 'url' => ['/pendaftaran']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .custom-gridview thead th {
        background-color: #002D72;
        color: white;
        font-size: 14px;
        text-align: center;
        font-weight: bold;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
    .custom-gridview tbody td {
        vertical-align: middle;
        font-size: 14px;
    }
    .summary-card {
        border-radius: 10px;
        transition: transform 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
    }
");

$resetUrl = Url::to(['informasi-paket-pasien/index']);
$urlExport = Url::to(['informasi-paket-pasien/export']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
            <i class="bi bi-person-vcard me-2"></i>Informasi Paket / Pasien MCU
        </h2>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 summary-card bg-primary text-white" style="background: linear-gradient(135deg, #002D72 0%, #0056b3 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Total Pasien MCU (Periode Ini)</div>
                            <div class="fs-3 fw-bold"><?= number_format($totalPasien) ?> <span class="fs-6 fw-normal">Pasien</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 summary-card text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <div class="d-flex align-items-center">
                        <div class="fs-1 me-3"><i class="bi bi-box-seam"></i></div>
                        <div>
                            <div class="fs-6 opacity-75">Jenis Paket Terpakai</div>
                            <div class="fs-3 fw-bold"><?= number_format($totalPaketTerpakai) ?> <span class="fs-6 fw-normal">Paket Active</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <div class="d-md-flex row m-0 quick-action-btns">
                
                <?= Html::beginForm(['/pendaftaran/informasi-paket-pasien/index'], 'get', ['id' => 'filter-form']) ?>
                
                <!-- Filter Row 1 -->
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Pilih Paket MCU</label>
                        <?= Html::dropDownList('tipepaket_id', $tipepaketId, $paketList, [
                            'prompt' => '-- Semua Paket MCU --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Pilih Penjamin</label>
                        <?= Html::dropDownList('penjamin_id', $penjaminId, $penjaminList, [
                            'prompt' => '-- Semua Penjamin --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                </div>

                <!-- Filter Row 2 -->
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Cari Pasien (Nama / No RM / No Pendaftaran)</label>
                        <?= Html::textInput('search', $search, [
                            'class' => 'form-control',
                            'placeholder' => 'Ketik Nama Pasien, No RM, atau No Pendaftaran...',
                        ]) ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Urutkan Data</label>
                        <?= Html::dropDownList('sort_order', $sortOrder, [
                            'tgl_desc'   => 'Tanggal Tindakan: Terbaru → Terlama',
                            'tgl_asc'    => 'Tanggal Tindakan: Terlama → Terbaru',
                            'nama_asc'   => 'Nama Pasien (A - Z)',
                            'nama_desc'  => 'Nama Pasien (Z - A)',
                            'rm_asc'     => 'No Rekam Medik',
                            'paket_asc'  => 'Nama Paket MCU',
                        ], [
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                </div>

                <!-- Filter Action Buttons -->
                <div class="row mt-3">
                    <div class="col-12 d-flex justify-content-start flex-wrap">
                        <?= Html::submitButton('<i class="bi bi-filter me-1"></i> Tampilkan', [
                            'class' => 'btn btn-primary me-2 mb-2',
                            'style' => 'background-color: #002D72; border-color: #002D72;'
                        ]) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> Reset Filter', $resetUrl, [
                            'class' => 'btn btn-outline-secondary me-2 mb-2'
                        ]) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel', [
                            'id' => 'export-button',
                            'class' => 'btn btn-success me-2 mb-2',
                            'style' => 'background-color: #28a745; border-color: #28a745;'
                        ]) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>

                <!-- Table Content -->
                <div class="row mt-4">
                    <div class="table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'tableOptions' => [
                                'class' => 'table table-striped table-bordered custom-gridview align-middle',
                            ],
                            'columns' => [
                                [
                                    'class' => 'yii\grid\SerialColumn',
                                    'header' => 'No',
                                    'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                                    'contentOptions' => ['style' => 'text-align: center;'],
                                ],
                                [
                                    'attribute' => 'tgl_tindakan',
                                    'label' => 'Tgl Tindakan',
                                    'headerOptions' => ['style' => 'width: 120px; text-align: center;'],
                                    'contentOptions' => ['style' => 'text-align: center;'],
                                    'value' => function($model) {
                                        return !empty($model['tgl_tindakan']) ? date('d/m/Y', strtotime($model['tgl_tindakan'])) : '-';
                                    }
                                ],
                                [
                                    'attribute' => 'no_pendaftaran',
                                    'label' => 'No Pendaftaran',
                                    'headerOptions' => ['style' => 'width: 140px; text-align: center;'],
                                    'contentOptions' => ['style' => 'text-align: center; font-weight: 600; text-transform: uppercase;'],
                                ],
                                [
                                    'attribute' => 'no_rm',
                                    'label' => 'No RM',
                                    'headerOptions' => ['style' => 'width: 100px; text-align: center;'],
                                    'contentOptions' => ['style' => 'text-align: center; font-weight: 700; color: #002D72;'],
                                ],
                                [
                                    'attribute' => 'nama_pasien',
                                    'label' => 'Nama Pasien',
                                    'format' => 'raw',
                                    'value' => function($model) {
                                        return Html::tag('strong', Html::encode($model['nama_pasien']));
                                    }
                                ],
                                [
                                    'attribute' => 'penjamin',
                                    'label' => 'Penjamin',
                                    'headerOptions' => ['style' => 'width: 150px; text-align: center;'],
                                    'contentOptions' => ['style' => 'text-align: center;'],
                                    'format' => 'raw',
                                    'value' => function($model) {
                                        $pj = Html::encode($model['penjamin']);
                                        $isUmum = strtolower($pj) === 'umum';
                                        $badgeClass = $isUmum ? 'bg-info text-dark' : 'bg-primary';
                                        return Html::tag('span', $pj, ['class' => "badge {$badgeClass} px-2 py-1"]);
                                    }
                                ],
                                [
                                    'attribute' => 'nama_paket_mcu',
                                    'label' => 'Nama Paket MCU',
                                    'format' => 'raw',
                                    'value' => function($model) {
                                        return Html::tag('span', Html::encode($model['nama_paket_mcu']), [
                                            'class' => 'text-dark fw-semibold'
                                        ]);
                                    }
                                ],
                                [
                                    'attribute' => 'tarif_per_paket',
                                    'label' => 'Tarif Paket',
                                    'headerOptions' => ['style' => 'text-align: right; width: 140px;'],
                                    'contentOptions' => ['style' => 'text-align: right; font-weight: 500;'],
                                    'value' => function($model) {
                                        return 'Rp ' . number_format($model['tarif_per_paket'], 0, ',', '.');
                                    }
                                ],
                            ],
                            'layout' => "{items}\n<div class='row mt-3 align-items-center'>
                                        <div class='col-md-6'>{pager}</div>
                                        <div class='col-md-6 text-end'>{summary}</div>
                                    </div>",
                            'pager' => [
                                'options' => ['class' => 'pagination mb-0'],
                                'linkOptions' => ['class' => 'page-link'],
                                'prevPageLabel' => '&laquo;', 
                                'nextPageLabel' => '&raquo;',
                            ],
                            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} data pasien MCU.',
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<JS
    $('#export-button').on('click', function(e) {
        e.preventDefault();
        var formParams = $('#filter-form').serializeArray().filter(function(item) {
            return item.name !== 'r';
        });
        var formData = $.param(formParams);
        var separator = "$urlExport".indexOf('?') !== -1 ? "&" : "?";
        window.location.href = "$urlExport" + separator + formData;
    });
JS;
$this->registerJs($js);
?>
