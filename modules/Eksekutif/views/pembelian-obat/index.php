<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Pembelian Obat Langsung';
    $this->params['breadcrumbs'][] = $this->title;
    
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #002D72;
            color: white;
            font-size: 16px;
            text-align: center;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
            
    ");

    $resetUrl = \yii\helpers\Url::to(['pembelian-obat/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">Laporan Pembelian Obat Langsung</h2>
        <div class="card border-0 shadow-sm p-3">
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/eksekutif/pembelian-obat/index'], 'get') ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                <div class="row">
                    <div class="col-sm-6 p-5">
                        <label class="form-label mb-4 fs-5">Tanggal</label>
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
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-start flex-wrap">
                        <?= Html::submitButton('<i class="bi bi-search"></i> Cari', ['class' => 'btn btn-primary m-3', 'style' => 'background-color: #002D72; border-color: #002D72;']) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn btn-outline-secondary m-3']) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-spreadsheet"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn btn-success m-3', 'style' => 'background-color: #6DC536; border-color: #6DC536;']) ?>
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
                                    'attribute' => 'no_pendaftaran',
                                    'label' => 'No Pendaftaran'
                                ],
                                [
                                    'attribute' => 'tglpembayaran',
                                    'label' => 'Tanggal Pembayaran'
                                ],
                                [
                                    'attribute' => 'nopembayaran',
                                    'label' => 'No Pembayaran'
                                ],
                                [
                                    'attribute' => 'tglclosingkasir',
                                    'label' => 'Tanggal Closing Kasir'
                                ],
                                [
                                    'attribute' => 'closingkasir_no',
                                    'label' => 'No Closing Kasir'
                                ],
                                [
                                    'attribute' => 'intalasi_asal',
                                    'label' => 'Instalasi Asal'
                                ],
                                [
                                    'attribute' => 'nama_pegawai',
                                    'label' => 'Nama Pegawai',
                                    'value' => function ($model) {
                                        return isset($model['nama_pegawai']) ? $model['nama_pegawai'] : '';
                                    }
                                ],
                                [
                                    'attribute' => 'ruangan_asal',
                                    'label' => 'Ruangan Asal'
                                ],
                                [
                                    'attribute' => 'instalasi_bayar',
                                    'label' => 'Instalasi Akhir',
                                    'value' => function ($model) {
                                        return isset($model['instalasi_bayar']) ? $model['instalasi_bayar'] : $model['intalasi_asal'];
                                    }
                                ],
                                [
                                    'attribute' => 'ruangan_bayar',
                                    'label' => 'Ruangan Akhir',
                                    'value' => function ($model) {
                                        return isset($model['ruangan_bayar']) ? $model['ruangan_bayar'] : $model['ruangan_asal'];
                                    }
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
                                    'attribute' => 'carabayar_nama',
                                    'label' => 'Cara Bayar'
                                ],
                                [
                                    'attribute' => 'totalbiayatindakan',
                                    'label' => 'Total Biaya Tindakan',
                                    'value' => function ($model) {
                                        return isset($model['totalbiayatindakan']) ? number_format($model['totalbiayatindakan'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'totalbiayaoa',
                                    'label' => 'Total Biaya OA',
                                    'value' => function ($model) {
                                        return isset($model['totalbiayaoa']) ? number_format($model['totalbiayaoa'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'totalbiaya',
                                    'label' => 'Total Biaya',
                                    'value' => function ($model) {
                                        return isset($model['totalbiaya']) ? number_format($model['totalbiaya'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'totalppnfarmasi',
                                    'label' => 'Total PPN Farmasi',
                                    'value' => function ($model) {
                                        return isset($model['totalppnfarmasi']) ? number_format($model['totalppnfarmasi'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'totalpelayanan',
                                    'label' => 'Total Pelayanan',
                                    'value' => function ($model) {
                                        return isset($model['totalpelayanan']) ? number_format($model['totalpelayanan'], 2, ',', '.') : '';
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
$urlExport = \yii\helpers\Url::to(['pembelian-obat/export']);

$js = <<<JS

    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName("date_from")[0].value;
        let date_to = document.getElementsByName("date_to")[0].value;
        window.location.href = "$urlExport" + "&date_from=" + date_from + "&date_to=" + date_to +"&cari=aktif";
    });
JS;
$this->registerJs($js);
?>

