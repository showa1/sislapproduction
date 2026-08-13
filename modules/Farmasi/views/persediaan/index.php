<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Persediaan';
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

    $resetUrl = \yii\helpers\Url::to(['persediaan/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Persediaan</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/farmasi/persediaan/index'], 'get') ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 p-3">
                        <label class="form-label mb-2 fs-5">Tanggal</label>
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
                    <div class="col-md-4 col-sm-6 p-3">
                        <label class="form-label mb-2 fs-5">Ruangan</label>
                        <div>
                            <?php echo Html::dropDownList('ruangan', $dropdownselect['ruangan'], $listruangan, [
                                'class' => 'form-select fs-6',
                                'style' => 'border: 1px solid grey;',
                                'prompt' => 'Pilih Ruangan',
                                'required' => true,
                            ]); ?>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 p-3">
                        <label class="form-label mb-2 fs-5">Nama Obat / BMHP</label>
                        <?= Html::textInput('nama_obatalkes', $dropdownselect['nama_obatalkes'] ?? '', [
                            'class' => 'form-control fs-6',
                            'placeholder' => 'Cari nama / kode obat...',
                            'style' => 'border: 1px solid grey;',
                            'autocomplete' => 'off',
                        ]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-start flex-wrap">
                        <?= Html::submitButton('Cari', ['class' => 'btn btn-dark m-3']) ?>
                        <?= Html::a('Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn btn-danger m-3']) ?>
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
                                    'attribute' => 'obatalkes_aktif',
                                    'label' => 'Obat Alkes Aktif'
                                ],
                                [
                                    'attribute' => 'obatalkes_kode',
                                    'label' => 'Kode Obat Alkes'
                                ],
                                [
                                    'attribute' => 'jenisobatalkes_nama',
                                    'label' => 'Jenis'
                                ],
                                [
                                    'attribute' => 'obatalkes_kategori',
                                    'label' => 'Kategori'
                                ],
                                [
                                    'attribute' => 'obatalkes_kronis',
                                    'label' => 'Kronis/ Non Kronis'
                                ],
                                [
                                    'attribute' => 'obatalkes_nama',
                                    'label' => 'Nama Obat Alkes'
                                ],
                                [
                                    'attribute' => 'nobatch',
                                    'label' => 'No Batch'
                                ],
                                [
                                    'attribute' => 'satuankecil_nama',
                                    'label' => 'Satuan Kecil',
                                    'value' => function ($model) {
                                        return !empty($model['satuankecil_nama']) ? $model['satuankecil_nama'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'satuanterkecilnama',
                                    'label' => 'Satuan Terkecil',
                                    'value' => function ($model) {
                                        return !empty($model['satuanterkecilnama']) ? $model['satuanterkecilnama'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'kekuatan',
                                    'label' => 'Kekuatan'
                                ],
                                [
                                    'attribute' => 'obatalkeszataktif_nama',
                                    'label' => 'Zat Aktif',
                                    'value' => function ($model) {
                                        return isset($model['obatalkeszataktif_nama']) ? $model['obatalkeszataktif_nama'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tglkadaluarsa',
                                    'label' => 'Tanggal Kadaluarsa'
                                ],
                                [
                                    'attribute' => 'harganetto',
                                    'label' => 'Harga Netto',
                                    'value' => function ($model) {
                                        return isset($model['harganetto']) ? number_format($model['harganetto'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'discount',
                                    'label' => 'Discount',
                                    'value' => function ($model) {
                                        return isset($model['discount']) ? number_format($model['discount'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'ppn_persen',
                                    'label' => 'PPN persen',
                                    'value' => function ($model) {
                                        return isset($model['ppn_persen']) ? number_format($model['ppn_persen'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'hargajual',
                                    'label' => 'Harga Jual',
                                    'value' => function ($model) {
                                        return isset($model['hargajual']) ? number_format($model['hargajual'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'stok_bulan_lalu',
                                    'label' => 'Stok Bulan Lalu',
                                    'value' => function ($model) {
                                        return isset($model['stok_bulan_lalu']) ? number_format($model['stok_bulan_lalu'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'masuk_so',
                                    'label' => 'Masuk SO',
                                    'value' => function ($model) {
                                        return isset($model['masuk_so']) ? number_format($model['masuk_so'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'masuk_unit',
                                    'label' => 'Masuk Unit',
                                    'value' => function ($model) {
                                        return isset($model['masuk_unit']) ? number_format($model['masuk_unit'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'masuk_po',
                                    'label' => 'Masuk PO',
                                    'value' => function ($model) {
                                        return isset($model['masuk_po']) ? number_format($model['masuk_po'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'masuk',
                                    'label' => 'Masuk',
                                    'value' => function ($model) {
                                        return isset($model['masuk']) ? number_format($model['masuk'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'keluar_so',
                                    'label' => 'Keluar SO',
                                    'value' => function ($model) {
                                        return isset($model['keluar_so']) ? number_format($model['keluar_so'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'keluar_pasien',
                                    'label' => 'Keluar Pasien',
                                    'value' => function ($model) {
                                        return isset($model['keluar_pasien']) ? number_format($model['keluar_pasien'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'keluar_unit',
                                    'label' => 'Keluar Unit',
                                    'value' => function ($model) {
                                        return isset($model['keluar_unit']) ? number_format($model['keluar_unit'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'keluar',
                                    'label' => 'Keluar',
                                    'value' => function ($model) {
                                        return isset($model['keluar']) ?number_format($model['keluar'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'stok_sekarang',
                                    'label' => 'Stok Sekarang',
                                    'value' => function ($model) {
                                        return isset($model['stok_sekarang']) ? number_format($model['stok_sekarang'], 2, ',', '.') : '';
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
$urlExport = \yii\helpers\Url::to(['persediaan/export']);

$js = <<<JS

    $('#export-button').on('click', function(e) {
        e.preventDefault();
        let date_from = document.getElementsByName("date_from")[0] ? document.getElementsByName("date_from")[0].value : '';
        let date_to = document.getElementsByName("date_to")[0] ? document.getElementsByName("date_to")[0].value : '';
        let ruangan = document.getElementsByName("ruangan")[0] ? document.getElementsByName("ruangan")[0].value : '';
        let nama_obatalkes = document.getElementsByName("nama_obatalkes")[0] ? document.getElementsByName("nama_obatalkes")[0].value : '';

        let separator = "$urlExport".indexOf('?') !== -1 ? "&" : "?";
        let url = "$urlExport" + separator + 
                  "date_from=" + encodeURIComponent(date_from) + 
                  "&date_to=" + encodeURIComponent(date_to) + 
                  "&cari=aktif" + 
                  "&ruangan=" + encodeURIComponent(ruangan) + 
                  "&nama_obatalkes=" + encodeURIComponent(nama_obatalkes);
        window.location.href = url;
    });
JS;
$this->registerJs($js);
?>


