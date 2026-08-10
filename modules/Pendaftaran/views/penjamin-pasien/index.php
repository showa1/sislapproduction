<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */

$this->title = 'Laporan Penjamin Pasien';
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
        white-space: nowrap;
    }
    .custom-gridview tbody td {
        font-size: 14px;
        vertical-align: middle;
    }
");

$resetUrl = Url::to(['penjamin-pasien/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;"><?= Html::encode($this->title) ?></h2>
        <div class="card border-0 shadow-sm p-3">
            <div class="d-md-flex row m-0 quick-action-btns">
                
                <?= Html::beginForm(['/pendaftaran/penjamin-pasien/index'], 'get', ['id' => 'filter-form']) ?>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Periode Pendaftaran</label>
                        <?= DatePicker::widget([
                            'type' => DatePicker::TYPE_RANGE,
                            'name' => 'date_from',
                            'value' => $dateFrom,
                            'name2' => 'date_to',
                            'value2' => $dateTo, 
                            'separator' => 's/d',
                            'layout' => '{input1}<span class="input-group-text">{separator}</span>{input2}',
                            'options' => [
                                'placeholder' => 'Tanggal Awal',
                                'class' => 'form-control',
                                'autocomplete' => 'off',
                                'required' => true,
                            ],
                            'options2' => [
                                'placeholder' => 'Tanggal Akhir',
                                'class' => 'form-control',
                                'autocomplete' => 'off',
                                'required' => true,
                            ],
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy',
                                'autoclose' => true,
                                'todayHighlight' => true,
                            ],
                        ]); ?>  
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cara Bayar</label>
                        <?= Html::dropDownList('carabayar_id', $carabayarId, $carabayarList, [
                            'prompt' => '-- Pilih Cara Bayar --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Penjamin</label>
                        <?= Html::dropDownList('penjamin_id', $penjaminId, $penjaminList, [
                            'prompt' => '-- Pilih Penjamin --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Instalasi</label>
                        <?= Html::dropDownList('instalasi_id', $instalasiId, $instalasiList, [
                            'prompt' => '-- Pilih Instalasi --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Ruangan</label>
                        <?= Html::dropDownList('ruangan_id', $ruanganId, $ruanganList, [
                            'prompt' => '-- Pilih Ruangan --',
                            'class' => 'form-select',
                        ]) ?>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">No. Rekam Medik</label>
                        <?= Html::textInput('no_rekam_medik', $noRekamMedik, ['class' => 'form-control', 'placeholder' => 'Cari No. RM...']) ?>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nama Pasien</label>
                        <?= Html::textInput('nama_pasien', $namaPasien, ['class' => 'form-control', 'placeholder' => 'Cari Nama Pasien...']) ?>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-12 d-flex justify-content-start flex-wrap">
                        <?= Html::submitButton('<i class="bi bi-search"></i> Tampilkan', ['class' => 'btn btn-primary me-2', 'style' => 'background-color: #002D72; border-color: #002D72;']) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Reset', $resetUrl, ['class' => 'btn btn-outline-secondary me-2']) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-spreadsheet"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn btn-success', 'style' => 'background-color: #6DC536; border-color: #6DC536;']) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="table-responsive">
                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'tableOptions' => [
                                    'class' => 'table table-striped table-bordered custom-gridview',
                                ],
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    'Tanggal Pendaftaran',
                                    'No Pendaftaran',
                                    'No Rekam Medik',
                                    'Nama Pasien',
                                    'Cara Bayar',
                                    'Penjamin',
                                    'Instalasi',
                                    'Ruangan',
                                    'Dokter DPJP',
                                    [
                                        'attribute' => 'Total Visit',
                                        'contentOptions' => ['class' => 'text-center'],
                                    ],
                                ],
                                'layout' => "{items}\n<div class='row'>
                                            <div class='col-md-6'>{pager}</div>
                                            <div class='col-md-6 text-end' style='margin-top: 20px'>{summary}</div>
                                        </div>",
                                'pager' => [
                                    'options' => ['class' => 'pagination', 'style' => 'margin-top: 20px;'],
                                    'linkOptions' => ['class' => 'page-link'],
                                    'prevPageLabel' => '&laquo;', 
                                    'nextPageLabel' => '&raquo;',
                                ],
                                'summary' => 'Menampilkan {begin} - {end} dari {totalCount} item.',
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$urlExport = Url::to(['penjamin-pasien/export']);

$js = <<<JS
    $('#export-button').on('click', function() {
        var formData = $('#filter-form').serialize();
        window.location.href = "$urlExport" + "?" + formData;
    });
JS;
$this->registerJs($js);
?>
