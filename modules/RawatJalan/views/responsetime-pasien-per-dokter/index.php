<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use yii\helpers\Url;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Respontime Per Dokter';
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
        /* Flatpickr Custom Styling */
        .flatpickr-input-custom {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 10px 15px !important;
            font-weight: 500;
            color: #4a5568 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
            transition: all 0.2s ease;
        }
        .flatpickr-input-custom:focus {
            border-color: #002D72 !important;
            box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1) !important;
        }
    ");

    // Register Flatpickr from CDN with robust dist path
    $this->registerCssFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    $this->registerJsFile('https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

    $resetUrl = \yii\helpers\Url::to(['responsetime-pasien-per-dokter/index']);
?>

<!-- Breadcrumbs & Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::to(['/rawatjalan/default/index']) ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Rawat Jalan</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page">Laporan Respontime Per Dokter</li>
            </ol>
        </nav>
        <h2 style="color: #002D72; font-weight: 800; margin: 0;">Laporan Respontime Pasien Eksekutif Per Dokter</h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Search Card -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-4">
                <?= Html::beginForm(['/rawatjalan/responsetime-pasien-per-dokter/index'], 'get', ['id' => 'search-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;"><i class="bi bi-calendar3 me-2" style="color: #002D72;"></i> Rentang Tanggal Inputan</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0;">
                                <i class="bi bi-calendar-event" style="color: #002D72;"></i>
                            </span>
                            <?php 
                                $dateRangeValue = (!empty($dropdownselect['start']) && !empty($dropdownselect['to'])) 
                                    ? $dropdownselect['start'] . ' to ' . $dropdownselect['to'] 
                                    : '';
                            ?>
                            <?= Html::textInput('date_range', $dateRangeValue, [
                                'id' => 'date-range-picker',
                                'class' => 'form-control flatpickr-input-custom border-start-0',
                                'placeholder' => 'Pilih Rentang Tanggal...',
                                'readonly' => true,
                                'style' => 'border-radius: 0 10px 10px 0 !important;'
                            ]) ?>
                            <?= Html::hiddenInput('date_from', $dropdownselect['start'], ['id' => 'date-from']) ?>
                            <?= Html::hiddenInput('date_to', $dropdownselect['to'], ['id' => 'date-to']) ?>
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
                                'attribute' => 'nama_pegawai',
                                'label' => 'Dokter',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'font-weight: 500; color: #002D72;'],
                                'value' => function ($model) {
                                    return $model['gelardepan'] . ' ' . $model['nama_pegawai'];
                                }
                            ],
                            [
                                'attribute' => 'avg_3_4',
                                'label' => 'Waktu Tunggu Poli',
                                'encodeLabel' => false,
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;',
                                    'title' => 'Rata-rata waktu tunggu dari pendaftaran ke pemeriksaan dokter (Status 3 → 4)', 'data-bs-toggle' => 'tooltip'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                                'value' => function($model) { return !empty($model['avg_3_4']) ? $model['avg_3_4'] . ' mnt' : '-'; }
                            ],
                            [
                                'attribute' => 'avg_4_5',
                                'label' => 'Waktu Pemeriksaan',
                                'encodeLabel' => false,
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;',
                                    'title' => 'Durasi dokter melakukan pemeriksaan di dalam poli (Status 4 → 5)', 'data-bs-toggle' => 'tooltip'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                                'value' => function($model) { return !empty($model['avg_4_5']) ? $model['avg_4_5'] . ' mnt' : '-'; }
                            ],
                            [
                                'attribute' => 'avg_5_6',
                                'label' => 'Waktu Tunggu Obat',
                                'encodeLabel' => false,
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;',
                                    'title' => 'Durasi dari resep dibuat hingga obat siap diambil (Status 5 → 6)', 'data-bs-toggle' => 'tooltip'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                                'value' => function($model) { return !empty($model['avg_5_6']) ? $model['avg_5_6'] . ' mnt' : '-'; }
                            ],
                            [
                                'attribute' => 'avg_6_7',
                                'label' => 'Waktu Tunggu Layanan Obat',
                                'encodeLabel' => false,
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;',
                                    'title' => 'Waktu tunggu dari selesai pemeriksaan hingga layanan obat selesai (Status 6 → 7)', 'data-bs-toggle' => 'tooltip'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                                'value' => function($model) { return !empty($model['avg_6_7']) ? $model['avg_6_7'] . ' mnt' : '-'; }
                            ],
                            [
                                'attribute' => 'avg_total_3_7',
                                'label' => 'Total Waktu Layanan',
                                'encodeLabel' => false,
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;',
                                    'title' => 'Total durasi pasien dari awal hingga selesai (Status 3 → 7)', 'data-bs-toggle' => 'tooltip'],
                                'contentOptions' => ['style' => 'text-align: center; font-weight: 700; color: #002D72;'],
                                'value' => function($model) { return !empty($model['avg_total_3_7']) ? $model['avg_total_3_7'] . ' mnt' : '-'; }
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
$urlExport = \yii\helpers\Url::to(['responsetime-pasien-per-dokter/export']);
$startDate = $dropdownselect['start'];
$endDate = $dropdownselect['to'];

$js = "
    let fpOptions = {
        mode: 'range',
        dateFormat: 'd-m-Y',
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                let start = instance.formatDate(selectedDates[0], 'd-m-Y');
                let end = instance.formatDate(selectedDates[1], 'd-m-Y');
                $('#date-from').val(start);
                $('#date-to').val(end);
            }
        }
    };
";

if (!empty($startDate) && !empty($endDate)) {
    $js .= "fpOptions.defaultDate = ['$startDate', '$endDate'];";
}

$js .= "
    flatpickr('#date-range-picker', fpOptions);

    $('#export-button').on('click', function() {
        let date_from = $('#date-from').val();
        let date_to = $('#date-to').val();

        window.location.href = '$urlExport' + '&date_from=' + date_from + '&date_to=' + date_to +'&cari=aktif';
    });
";
$this->registerJs($js);
?>
