<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Jatuh Tempo';
    $this->params['breadcrumbs'][] = $this->title;
    
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #f5981b;
            font-size: 16px;
            text-align: center;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
            
    ");
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Jatuh Tempo Pasien</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/farmasi/jatuh-tempo/index'], 'get') ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                <div class="row col-sm-6 p-5">
                    <label class="form-label mb-4 fs-5">Tanggal Inputan</label>
                    <?= DatePicker::widget([
                        'type' => DatePicker::TYPE_RANGE,
                        'name' => 'date_from',
                        'value' => $dropdownselect['start'],
                        'name2' => 'date_to',
                        'value2' => $dropdownselect['to'], 
                        'separator' => '<span style="font-size:15px;">To</span>',
                        'layout' => '{input1}{separator}{input2}',
                        'options' => [
                            'placeholder' => 'Tanggal Awal',
                            'class' => 'form-control fs-6',
                            'style' => 'border: 1px solid grey;',
                            'autocomplete' => 'off',
                        ],
                        'options2' => [
                            'placeholder' => 'Tanggal Akhir',
                            'class' => 'form-control fs-6',
                            'style' => 'border: 1px solid grey;',
                            'autocomplete' => 'off'
                        ],
                        'pluginOptions' => [
                            'format' => 'dd-mm-yyyy',
                            'autoclose' => true,
                            'todayHighlight' => true,
                            'orientation' => 'bottom auto',
                        ],
                    ]); ?>  
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-start flex-wrap">
                        <?= Html::submitButton('Cari', ['class' => 'btn btn-dark m-3']) ?>
                        <button type="button" class="btn btn-danger m-3">Ulang</button>
                        <?= Html::button('Export', ['id' => 'export-button', 'class' => 'btn btn-primary m-3']) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>

                <div class="row">
                    <!-- GridView -->
                    <div class="table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'tableOptions' => [
                                'class' => 'table table-striped table-bordered custom-gridview',
                            ],
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'tglterima',
                                    'label' => 'Tanggal Terima',
                                    'value' => function ($model) {
                                        return isset($model['tglterima']) ? $model['tglterima'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'noterima',
                                    'label' => 'Nomor Terima',
                                    'value' => function ($model) {
                                        return isset($model['noterima']) ? $model['noterima'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tglfaktur',
                                    'label' => 'Tanggal Faktur',
                                    'value' => function ($model) {
                                        return isset($model['tglfaktur']) ? $model['tglfaktur'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'nofaktur',
                                    'label' => 'Nomor Faktur',
                                    'value' => function ($model) {
                                        return isset($model['nofaktur']) ? $model['nofaktur'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tglbayarkesupplier',
                                    'label' => 'Tgl Bayar ke Supplier',
                                    'value' => function ($model) {
                                        return isset($model['tglbayarkesupplier']) ? $model['tglbayarkesupplier'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tglpermintaanpembelian',
                                    'label' => 'Tgl Permintaan Pembelian',
                                    'value' => function ($model) {
                                        return isset($model['tglpermintaanpembelian']) ? $model['tglpermintaanpembelian'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jatuh_tempo',
                                    'label' => 'Jatuh Tempo',
                                    'value' => function ($model) {
                                        return isset($model['jatuh_tempo']) ? $model['jatuh_tempo'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'noperencnaan',
                                    'label' => 'Nomor Perencanaan',
                                    'value' => function ($model) {
                                        return isset($model['noperencnaan']) ? $model['noperencnaan'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'sumberdana_nama',
                                    'label' => 'Sumber Dana',
                                    'value' => function ($model) {
                                        return isset($model['sumberdana_nama']) ? $model['sumberdana_nama'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'supplier_nama',
                                    'label' => 'Nama Supplier',
                                    'value' => function ($model) {
                                        return isset($model['supplier_nama']) ? $model['supplier_nama'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'nopermintaan',
                                    'label' => 'Nomor Permintaan',
                                    'value' => function ($model) {
                                        return isset($model['nopermintaan']) ? $model['nopermintaan'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'noreferensi',
                                    'label' => 'Nomor Referensi',
                                    'value' => function ($model) {
                                        return isset($model['noreferensi']) ? $model['noreferensi'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'keteranganpermintaan',
                                    'label' => 'Keterangan Permintaan',
                                    'value' => function ($model) {
                                        return isset($model['keteranganpermintaan']) ? $model['keteranganpermintaan'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'total',
                                    'label' => 'Total',
                                    'value' => function ($model) {
                                        return isset($model['total']) ? number_format($model['total'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'terminpembayaran',
                                    'label' => 'Termin Pembayaran',
                                    'value' => function ($model) {
                                        return isset($model['terminpembayaran']) ? $model['terminpembayaran'] : '';
                                    }
                                ],
                            ],
                            'layout' => "{items}\n<div class='row'>
                                        <div class='col-md-6'>{pager}</div>
                                        <div class='col-md-6 text-end' style='margin-top: 20px'>{summary}</div>
                                    </div>",
                            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} data.', 
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
$urlExport = \yii\helpers\Url::to(['jatuh-tempo/export']);

$js = <<<JS

    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName("date_from")[0].value;
        let date_to = document.getElementsByName("date_to")[0].value;

        window.location.href = "$urlExport" + "&date_from=" + date_from + "&date_to=" + date_to +"&cari=aktif";
    });
JS;
$this->registerJs($js);
?>

