<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Penjualan';
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
                <h2 class="mb-0">Laporan Obat Per Pasien</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/farmasi/penggunaan-obat-perpasien/index'], 'get') ?>
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
                                    'attribute' => 'tgl_pendaftaran',
                                    'label' => 'Tanggal Pendaftaran'
                                ],
                                [
                                    'attribute' => 'tglpelayanan',
                                    'label' => 'Tanggal Pelayanan'
                                ],
                                [
                                    'attribute' => 'tglinput',
                                    'label' => 'Tanggal Input'
                                ],
                                [
                                    'attribute' => 'asalmasuk',
                                    'label' => 'Asal Masuk'
                                ],
                                [
                                    'attribute' => 'dokter',
                                    'label' => 'Dokter'
                                ],
                                [
                                    'attribute' => 'nopembayaran',
                                    'label' => 'No Pembayaran'
                                ],
                                [
                                    'attribute' => 'tglpembayaran',
                                    'label' => 'Tanggal Pembayaran'
                                ],
                                [
                                    'attribute' => 'obatalkes_kode',
                                    'label' => 'Obat Alkes Kode'
                                ],
                                [
                                    'attribute' => 'obatalkes_nama',
                                    'label' => 'Obat Alkes Nama'
                                ],
                                [
                                    'attribute' => 'qty_oa',
                                    'label' => 'Qty oa'
                                ],
                                [
                                    'attribute' => 'no_rekam_medik',
                                    'label' => 'No Rekam Medik'
                                ],
                                [
                                    'attribute' => 'nama_pasien',
                                    'label' => 'Nama Pasien'
                                ],
                                [
                                    'attribute' => 'obatalkes_kronis',
                                    'label' => 'Kronis/Non Kronis'
                                ],
                                [
                                    'attribute' => 'obatalkes_kategori',
                                    'label' => 'Kategori'
                                ],
                                [
                                    'attribute' => 'jenisobatalkes_nama',
                                    'label' => 'Jenis Obat Alkes'
                                ],                           
                                [
                                    'attribute' => 'bpjskesehatan',
                                    'label' => 'BPJS Kesehatan',
                                    'value' => function ($model) {
                                        return !empty($model['bpjskesehatan']) ? number_format($model['bpjskesehatan'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'umum',
                                    'label' => 'Umum',
                                    'value' => function ($model) {
                                        return !empty($model['umum']) ? number_format($model['umum'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bpjsketenagakerjaan',
                                    'label' => 'BPJS Ketemagakerjaan',
                                    'value' => function ($model) {
                                        return !empty($model['bpjsketenagakerjaan']) ? number_format($model['bpjsketenagakerjaan'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'kemenkesri',
                                    'label' => 'Kemenkes RI',
                                    'value' => function ($model) {
                                        return !empty($model['kemenkesri']) ? number_format($model['kemenkesri'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'asuransi',
                                    'label' => 'Asuransi',
                                    'value' => function ($model) {
                                        return !empty($model['asuransi']) ? number_format($model['asuransi'], 2, ',', '.') : '';
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
$urlExport = \yii\helpers\Url::to(['penggunaan-obat-perpasien/export']);

$js = <<<JS

    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName("date_from")[0].value;
        let date_to = document.getElementsByName("date_to")[0].value;

        window.location.href = "$urlExport" + "&date_from=" + date_from + "&date_to=" + date_to +"&cari=aktif";
    });
JS;
$this->registerJs($js);
?>

