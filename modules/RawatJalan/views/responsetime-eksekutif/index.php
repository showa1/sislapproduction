<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use yii\helpers\Url;
    
    $this->title = 'Laporan Respontime Eksekutif Detail';
    $this->params['breadcrumbs'][] = $this->title;
    
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #002D72 !important;
            color: #fff !important;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
            border: none;
            padding: 15px !important;
            vertical-align: middle !important;
        }
        .custom-gridview tbody td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 15px !important;
        }
    ");

    $resetUrl = \yii\helpers\Url::to(['responsetime-eksekutif/index']);
?>

<!-- Breadcrumbs & Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::home() ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Rawat Jalan</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page">Laporan Respontime Eksekutif Detail</li>
            </ol>
        </nav>
        <h2 style="color: #002D72; font-weight: 800; margin: 0;">Laporan Respontime Eksekutif Detail</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Search Card -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-4">
                <?= Html::beginForm(['/rawatjalan/responsetime-eksekutif/index'], 'get', ['id' => 'search-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;"><i class="bi bi-calendar3 me-2" style="color: #002D72;"></i> Rentang Tanggal Inputan</label>
                        <!-- Flatpickr Date Range Input (Standard HTML) -->
                        <div class="input-group">
                            <span class="input-group-text bg-white" style="border-color: #e2e8f0; border-radius: 10px 0 0 10px;">
                                <i class="bi bi-calendar-event" style="color: #002D72;"></i>
                            </span>
                            <input type="text" name="date_from" id="date_from" class="form-control flatpickr-range" 
                                   value="<?= !empty($dropdownselect['start']) ? date('d-m-Y', strtotime($dropdownselect['start'])) : date('d-m-Y', strtotime('-1 month')) ?>" 
                                   placeholder="Tanggal Awal" 
                                   style="border: 1px solid #e2e8f0; box-shadow: none; background: #fff; cursor: pointer;" autocomplete="off">
                            <span class="input-group-text" style="padding: 10px 15px; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; background: #f8f9fa;">To</span>
                            <input type="text" name="date_to" id="date_to" class="form-control flatpickr-range" 
                                   value="<?= !empty($dropdownselect['to']) ? date('d-m-Y', strtotime($dropdownselect['to'])) : date('d-m-Y') ?>" 
                                   placeholder="Tanggal Akhir" 
                                   style="border: 1px solid #e2e8f0; border-radius: 0 10px 10px 0; box-shadow: none; background: #fff; cursor: pointer;" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6 mb-3 d-flex gap-2">
                        <?= Html::submitButton('<i class="bi bi-search me-2"></i> Cari', ['class' => 'btn px-4', 'style' => 'background: #002D72; color: #fff; border-radius: 8px; font-weight: 600;']) ?>
                        <?= Html::a('<i class="bi bi-arrow-counterclockwise me-2"></i> Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn px-4', 'style' => 'border: 1px solid #002D72; color: #002D72; background: #fff; border-radius: 8px; font-weight: 600;']) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-excel me-2"></i> Export', ['id' => 'export-button', 'class' => 'btn px-4', 'style' => 'background: #6DC536; color: #fff; border-radius: 8px; font-weight: 600; border: none;']) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>
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
                                'contentOptions' => ['style' => 'text-align: center;'],
                            ],
                            [
                                'attribute' => 'tgl_pendaftaran',
                                'label' => 'Tanggal Pendaftaran',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'ruangan_nama',
                                'label' => 'Nama Ruangan',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'no_pendaftaran',
                                'label' => 'No Pendaftaran',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'nama_pasien',
                                'label' => 'Nama Pasien',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'font-weight: 500; color: #002D72;'],
                                'value' => function ($model) {
                                    return $model['namadepan'] . ' ' . $model['nama_pasien'];
                                }
                            ],
                            [
                                'attribute' => 'no_rekam_medik',
                                'label' => 'No Rekam Medik',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'nama_pegawai',
                                'label' => 'Dokter',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'value' => function ($model) {
                                    return $model['gelardepan'] . ' ' . $model['nama_pegawai'];
                                }
                            ],
                            [
                                'attribute' => 'akhir_waktu_layanan_admisi_atau_mulai_waktu_tunggu_poli',
                                'label' => 'Admisi / Tunggu Poli',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'value' => function ($model) {
                                    return isset($model['akhir_waktu_layanan_admisi_atau_mulai_waktu_tunggu_poli']) ? $model['akhir_waktu_layanan_admisi_atau_mulai_waktu_tunggu_poli'] : '';
                                }
                            ],
                            [
                                'attribute' => 'akhir_waktu_tunggu_poli_atau_mulai_waktu_layan_poli',
                                'label' => 'Tunggu Poli / Layan Poli',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'value' => function ($model) {
                                    return isset($model['akhir_waktu_tunggu_poli_atau_mulai_waktu_layan_poli']) ? $model['akhir_waktu_tunggu_poli_atau_mulai_waktu_layan_poli'] : '';
                                }
                            ],
                            [
                                'attribute' => 'akhir_waktu_layanan_poli_atau_mulai_waktu_tunggu_farmasi',
                                'label' => 'Layan Poli / Tunggu Farmasi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'value' => function ($model) {
                                    return isset($model['akhir_waktu_layanan_poli_atau_mulai_waktu_tunggu_farmasi']) ? $model['akhir_waktu_layanan_poli_atau_mulai_waktu_tunggu_farmasi'] : '';
                                }
                            ],
                            [
                                'attribute' => 'waktu_layanan_farmasi_membuat',
                                'label' => 'Tunggu Farmasi / Layanan Obat',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'value' => function ($model) {
                                    return isset($model['waktu_layanan_farmasi_membuat']) ? $model['waktu_layanan_farmasi_membuat'] : '';
                                }
                            ],
                            [
                                'attribute' => 'akhir_waktu_obat_selesai_dibuat',
                                'label' => 'Obat Selesai',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'value' => function ($model) {
                                    return isset($model['akhir_waktu_obat_selesai_dibuat']) ? $model['akhir_waktu_obat_selesai_dibuat'] : '';
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
$urlExport = \yii\helpers\Url::to(['responsetime-eksekutif/export']);

$this->registerCssFile('@web/template/vendors/flatpickr/flatpickr.min.css');
$this->registerJsFile('@web/template/vendors/flatpickr/flatpickr.min.js', ['position' => \yii\web\View::POS_END]);

$js = "
    // Initialize Flatpickr and add icon click functionality
    function initFlatpickr() {
        if (typeof flatpickr !== 'undefined') {
            var fpItems = flatpickr('.flatpickr-range', {
                dateFormat: 'd-m-Y',
                allowInput: true,
            });

            // Make calendar icons trigger the datepicker
            $('#date_from').prev('.input-group-text').css('cursor', 'pointer').on('click', function() {
                var fp = document.querySelector('#date_from')._flatpickr;
                if(fp) fp.open();
            });

            $('#date_to').prev('.input-group-text').css('cursor', 'pointer').on('click', function() {
                var fp = document.querySelector('#date_to')._flatpickr;
                if(fp) fp.open();
            });
        } else {
            setTimeout(initFlatpickr, 100);
        }
    }
    initFlatpickr();

    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName('date_from')[0].value;
        let date_to = document.getElementsByName('date_to')[0].value;
        window.location.href = '$urlExport' + '&date_from=' + date_from + '&date_to=' + date_to +'&cari=aktif';
    });
";
$this->registerJs($js);
?>
