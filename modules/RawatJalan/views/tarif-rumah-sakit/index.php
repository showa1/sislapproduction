<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use yii\helpers\Url;
    
    $this->title = 'Laporan Penjualan';
    $this->params['breadcrumbs'][] = $this->title;
    $optionjenis = ArrayHelper::map($listjenis, 'jenistarif_id', 'jenistarif_nama');
    $optionpelayanan = ArrayHelper::map($listpelayanan, 'kelaspelayanan_id', 'kelaspelayanan_nama');
   
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #f5981b;
            font-size: 16px;
            text-align: center;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
    ");

    $resetUrl = \yii\helpers\Url::to(['tarif-rumah-sakit/index']);
?>

<!-- Breadcrumbs & Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::home() ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Rawat Jalan</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page">Laporan Tarif Rumah Sakit</li>
            </ol>
        </nav>
        <h2 style="color: #002D72; font-weight: 800; margin: 0; display: flex; align-items: center;">
             Laporan Tarif Rumah Sakit
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Search Card -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-4">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                    'id' => 'search-form'
                ]); ?>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;"><i class="bi bi-tag-fill me-2" style="color: #002D72;"></i> Jenis Tarif</label>
                        <?php echo Html::dropDownList('jenistarif', $dropdownselect['jenistarif'], $optionjenis, [
                            'class' => 'form-select select2-basic',
                            'prompt' => '-- Pilih Jenis Tarif --',
                            'style' => 'border-radius: 8px; padding: 10px; border-color: #e2e8f0;'
                        ]); ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;"><i class="bi bi-hospital-fill me-2" style="color: #002D72;"></i> Kelas Pelayanan</label>
                        <?php echo Html::dropDownList('kelaspelayanan', $dropdownselect['kelaspelayanan'], $optionpelayanan, [
                                'class' => 'form-select select2-basic',
                                'prompt' => '-- Pilih Kelas Pelayanan --',
                                'style' => 'border-radius: 8px; padding: 10px; border-color: #e2e8f0;'
                        ]); ?>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;"><i class="bi bi-search me-2" style="color: #002D72;"></i> Nama Tindakan</label>
                        <?= Html::textInput('namatindakan', $dropdownselect['namatindakan'], [
                            'class' => 'form-control', 
                            'placeholder' => 'Ketik nama tindakan...',
                            'style' => 'border-radius: 8px; padding: 10px; border-color: #e2e8f0;'
                        ]) ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-start align-items-center mt-3 gap-2">
                    <?= Html::submitButton('<i class="bi bi-search me-2"></i> Cari', ['class' => 'btn px-4', 'style' => 'background: #002D72; color: #fff; border-radius: 8px; font-weight: 600;']) ?>
                    <?= Html::a('<i class="bi bi-arrow-counterclockwise me-2"></i> Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn px-4', 'style' => 'border: 1px solid #002D72; color: #002D72; background: #fff; border-radius: 8px; font-weight: 600;']) ?>
                    <?= Html::button('<i class="bi bi-file-earmark-excel me-2"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn px-4', 'style' => 'background: #6DC536; color: #fff; border-radius: 8px; font-weight: 600; border: none;']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>

        <!-- Data Card -->
        <div class="card" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-0">
                <div class="table-responsive" style="border-radius: 0 0 12px 12px;">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => [
                            'class' => 'table table-hover mb-0 custom-gridview',
                            'style' => 'border-collapse: separate; border-spacing: 0;'
                        ],
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'headerOptions' => ['style' => 'width: 50px; background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                            ],
                            [
                                'attribute' => 'jenistarif_nama',
                                'label' => 'Jenis Tarif',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                            ],
                            [
                                'attribute' => 'kelompoktindakan_nama',
                                'label' => 'Kelompok Tindakan',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                            ],
                            [
                                'attribute' => 'daftartindakan_kode',
                                'label' => 'Kode Tindakan',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                            ],
                            [
                                'attribute' => 'daftartindakan_nama',
                                'label' => 'Nama Tindakan',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle; border-bottom: 1px solid #f1f5f9; min-width: 200px; font-weight: 500;'],
                            ],
                            [
                                'attribute' => 'kelaspelayanan_nama',
                                'label' => 'Kelas Pelayanan',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: left;'],
                                'contentOptions' => ['style' => 'vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                            ],
                            [
                                'attribute' => 'jasa_asisten_anastesi',
                                'label' => 'Jasa Asisten Anestesi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_asisten_anastesi']) ? number_format($model['jasa_asisten_anastesi'], 0, ',', '.') : "-";
                                }
                            ],
                            [
                                'attribute' => 'jasa_administrasi',
                                'label' => 'Jasa Administrasi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_administrasi']) ? number_format($model['jasa_administrasi'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_instrumen',
                                'label' => 'Jasa Instrumen',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_instrumen']) ? number_format($model['jasa_instrumen'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'billing_rawat_inap',
                                'label' => 'Billing Rawat Inap',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['billing_rawat_inap']) ? number_format($model['billing_rawat_inap'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'sewa_kamar_oprasi',
                                'label' => 'Sewa Kamar Oprasi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['sewa_kamar_oprasi']) ? number_format($model['sewa_kamar_oprasi'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_operator',
                                'label' => 'Jasa Operator',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_operator']) ? number_format($model['jasa_operator'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_asisten_operator',
                                'label' => 'Jasa Asisten Operator',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_asisten_operator']) ? number_format($model['jasa_asisten_operator'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_bidan',
                                'label' => 'Jasa Bidan',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_bidan']) ? number_format($model['jasa_bidan'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_bhp',
                                'label' => 'Jasa BHP',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_bhp']) ? number_format($model['jasa_bhp'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'sewa_alat',
                                'label' => 'Sewa Alat',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['sewa_alat']) ? number_format($model['sewa_alat'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'visite_dokter',
                                'label' => 'Visite Dokter',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['visite_dokter']) ? number_format($model['visite_dokter'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_observasi',
                                'label' => 'Jasa Observasi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_observasi']) ? number_format($model['jasa_observasi'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'visite_spa',
                                'label' => 'Viste Spa',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['visite_spa']) ? number_format($model['visite_spa'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_dokter_anastesi',
                                'label' => 'Jasa Dokter Anastesi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_dokter_anastesi']) ? number_format($model['jasa_dokter_anastesi'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_visite',
                                'label' => 'Jasa Visite',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_visite']) ? number_format($model['jasa_visite'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'visite_spog',
                                'label' => 'Visite Spog',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['visite_spog']) ? number_format($model['visite_spog'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'bmhp',
                                'label' => 'Bmhp',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['bmhp']) ? number_format($model['bmhp'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'jasa_rumah_sakit',
                                'label' => 'Jasa Rumah Sakit',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9;'],
                                'value' => function ($model) {
                                    return !empty($model['jasa_rumah_sakit']) ? number_format($model['jasa_rumah_sakit'], 0, ',', '.') : '-';
                                }
                            ],
                            [
                                'attribute' => 'total',
                                'label' => 'Total',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 700; color: #002D72;'],
                                'value' => function ($model) {
                                    return !empty($model['total']) ? number_format($model['total'], 0, ',', '.') : '-';
                                }
                            ],
                        ],
                        'layout' => "{items}\n<div class='p-4 d-flex justify-content-between align-items-center flex-wrap gap-3'>
                                    <div style='color: #64748b; font-size: 0.9rem;'>{summary}</div>
                                    <div class='custom-pagination'>{pager}</div>
                                </div>",
                        'summary' => 'Menampilkan {begin} - {end} dari {totalCount} data.', 
                        'pager' => [
                            'options' => ['class' => 'pagination pagination-sm m-0'],
                            'linkOptions' => ['class' => 'page-link', 'style' => 'border-color: #e2e8f0; color: #002D72;'],
                            'activePageCssClass' => 'active',
                            'disabledPageCssClass' => 'disabled',
                            'prevPageLabel' => '<i class="bi bi-chevron-left"></i>', 
                            'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$urlExport = \yii\helpers\Url::to(['tarif-rumah-sakit/export']);

$js = <<<JS
    $('#export-button').on('click', function() {
        window.location.href = "$urlExport";
    });
JS;
$this->registerJs($js);
?>

