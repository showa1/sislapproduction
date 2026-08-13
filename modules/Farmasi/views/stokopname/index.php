<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Stok Opname';
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

    $resetUrl = \yii\helpers\Url::to(['stokopname/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Stok Opname</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/farmasi/stokopname/index'], 'get') ?>
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
                                    'attribute' => 'ruangan_nama',
                                    'label' => 'Ruangan'
                                ],
                                [
                                    'attribute' => 'nostokopname',
                                    'label' => 'No Stok Opname'
                                ],
                                [
                                    'attribute' => 'tglstokopname',
                                    'label' => 'Tanggal Stok Opname'
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
                                    'attribute' => 'obatalkes_nama',
                                    'label' => 'Nama Obat Alkes'
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
                                    'attribute' => 'volume_sistem',
                                    'label' => 'Volume Sistem',
                                    'value' => function ($model) {
                                        return isset($model['volume_sistem']) ? number_format($model['volume_sistem'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'volume_fisik',
                                    'label' => 'Volume Fisik',
                                    'value' => function ($model) {
                                        return isset($model['volume_fisik']) ? number_format($model['volume_fisik'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jmlselisihstok',
                                    'label' => 'Jml Selisih Stok',
                                    'value' => function ($model) {
                                        return isset($model['jmlselisihstok']) ? number_format($model['jmlselisihstok'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'petugas_1',
                                    'label' => 'Petugas 1'
                                ],
                                [
                                    'attribute' => 'petugas_2',
                                    'label' => 'Petugas 2'
                                ],
                                [
                                    'attribute' => 'pegawai_mengetahui',
                                    'label' => 'Pegawai Mengetahui'
                                ],
                                [
                                    'attribute' => 'keterangan_opname',
                                    'label' => 'Keterangan Opname'
                                ],
                                
                            ],
                            'layout' => "{items}\n<div class='row'>
                                        <div class='col-md-6'>{pager}</div>
                                        <div class='col-md-6 text-end' style='margin-top: 20px'>{summary}</div>
                                    </div>",
                            'summary' => 'Menampilkan {begin} - {end} dari {totalCount} item.',
                            'pager' => [
                                'options' => ['class' => 'pagination', 'style' => 'margin-top: 20px;'],
                                'linkOptions' => ['class' => 'page-link'],
                                'prevPageLabel' => '&laquo;', 
                                'nextPageLabel' => '&raquo;',
                            ],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$urlExport = \yii\helpers\Url::to(['stokopname/export']);

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
