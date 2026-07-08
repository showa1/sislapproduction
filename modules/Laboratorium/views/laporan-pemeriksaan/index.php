<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;
use yii\helpers\Url;

$this->title = 'Laporan Jumlah Pemeriksaan Laboratorium';
$this->params['breadcrumbs'][] = ['label' => 'Laboratorium', 'url' => ['/laboratorium']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .custom-gridview thead th {
        background-color: #002D72;
        color: white;
        font-size: 16px;
        text-align: center;
        font-weight: bold;
        border-bottom: 2px solid #dee2e6;
        vertical-align: middle;
    }
");

$resetUrl = Url::to(['laporan-pemeriksaan/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">Laporan Jumlah Pemeriksaan Laboratorium</h2>
        <div class="card border-0 shadow-sm p-3">
            <div class="d-md-flex row m-0 quick-action-btns">
                
                <?= Html::beginForm(['/laboratorium/laporan-pemeriksaan/index'], 'get') ?>
                <div class="row">
                    <div class="col-sm-6 p-4">
                        <label class="form-label mb-3 fs-5">Periode Pemeriksaan</label>
                        <?= DatePicker::widget([
                            'type' => DatePicker::TYPE_RANGE,
                            'name' => 'date_from',
                            'value' => $dateFrom,
                            'name2' => 'date_to',
                            'value2' => $dateTo, 
                            'separator' => '<span style="font-size:15px;">To</span>',
                            'layout' => '{input1}{separator}{input2}',
                            'options' => [
                                'placeholder' => 'Tanggal Awal',
                                'class' => 'form-control fs-6',
                                'style' => 'border: 1px solid grey;',
                                'autocomplete' => 'off',
                                'required' => true,
                            ],
                            'options2' => [
                                'placeholder' => 'Tanggal Akhir',
                                'class' => 'form-control fs-6',
                                'style' => 'border: 1px solid grey;',
                                'autocomplete' => 'off',
                                'required' => true,
                            ],
                            'pluginOptions' => [
                                'format' => 'dd-mm-yyyy',
                                'autoclose' => true,
                                'todayHighlight' => true,
                                'orientation' => 'bottom auto',
                            ],
                        ]); ?>  
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12 d-flex justify-content-start flex-wrap mt-2">
                        <?= Html::submitButton('<i class="bi bi-search"></i> Tampilkan', ['class' => 'btn btn-primary m-2', 'style' => 'background-color: #002D72; border-color: #002D72;']) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn btn-outline-secondary m-2']) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-spreadsheet"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn btn-success m-2', 'style' => 'background-color: #6DC536; border-color: #6DC536;']) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>

                <div class="row mt-4">
                    <div class="table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'tableOptions' => [
                                'class' => 'table table-striped table-bordered custom-gridview',
                            ],
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                
                                [
                                    'attribute' => 'daftartindakan_kode',
                                    'label' => 'Kode Tindakan',
                                ],
                                [
                                    'attribute' => 'daftartindakan_nama',
                                    'label' => 'Nama Tindakan',
                                ],
                                [
                                    'attribute' => 'pribadi',
                                    'label' => 'Pribadi',
                                    'format' => 'integer',
                                    'contentOptions' => ['class' => 'text-end'],
                                ],
                                [
                                    'attribute' => 'bpjs',
                                    'label' => 'BPJS Kes',
                                    'format' => 'integer',
                                    'contentOptions' => ['class' => 'text-end'],
                                ],
                                [
                                    'attribute' => 'asuransi',
                                    'label' => 'Asuransi',
                                    'format' => 'integer',
                                    'contentOptions' => ['class' => 'text-end'],
                                ],
                                [
                                    'attribute' => 'bpjs_ketenagakerjaan',
                                    'label' => 'BPJS Ketenagakerjaan',
                                    'format' => 'integer',
                                    'contentOptions' => ['class' => 'text-end'],
                                ],
                                [
                                    'attribute' => 'total',
                                    'label' => 'Total',
                                    'format' => 'integer',
                                    'contentOptions' => ['class' => 'text-end fw-bold', 'style' => 'background-color:#f8f9fa;'],
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

<?php
$urlExport = Url::to(['laporan-pemeriksaan/export']);

$js = <<<JS
    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName("date_from")[0].value;
        let date_to = document.getElementsByName("date_to")[0].value;
        window.location.href = "$urlExport" + "&date_from=" + date_from + "&date_to=" + date_to;
    });
JS;
$this->registerJs($js);
?>
