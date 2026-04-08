<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Jasa Dokter';
    $this->params['breadcrumbs'][] = $this->title;
    
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #002D72; /* PMC Navy Blue */
            color: #ffffff;
            font-size: 15px;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
        }
        .card-header {
            background-color: transparent !important;
            border-bottom: 2px solid #002D72 !important;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .card-header h2 {
            color: #002D72;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #002D72;
            border-color: #002D72;
        }
            
    ");

    $resetUrl = \yii\helpers\Url::to(['jasa-dokter/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Jasa Dokter</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/keuangan/jasa-dokter/index'], 'get', ['id' => 'jasa-dokter-form']) ?>
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
                        <?= Html::submitButton('<i class="bi bi-search"></i> Cari', ['class' => 'btn btn-outline-primary m-3', 'style' => 'border-color: #002D72; color: #002D72;']) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn btn-outline-danger m-3']) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-excel"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn btn-success m-3', 'style' => 'background-color: #6DC536; border-color: #6DC536;']) ?>
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
                                    'attribute' => 'no_rekam_medik',
                                    'label' => 'No Rekam Medik',
                                ],
                                [
                                    'attribute' => 'nama_pasien',
                                    'label' => 'Nama Pasien',
                                ],
                                [
                                    'attribute' => 'ruangan_nama',
                                    'label' => 'Ruangan',
                                ],
                                [
                                    'attribute' => 'nopembayaran',
                                    'label' => 'No Pembayaran',
                                ],
                                [
                                    'attribute' => 'tglpembayaran',
                                    'label' => 'Tanggal Pembayaran',
                                ],
                                [
                                    'attribute' => 'nama_pegawai',
                                    'label' => 'Dokter',
                                ],
                                [
                                    'attribute' => 'daftartindakan_kode',
                                    'label' => 'Kode Tindakan',
                                ],
                                [
                                    'attribute' => 'daftartindakan_nama',
                                    'label' => 'Nama Tindakan',
                                ],
                                [
                                    'attribute' => 'penjamin_nama',
                                    'label' => 'Nama Penjamin',
                                ],
                                [
                                    'attribute' => 'jumlah_komponen',
                                    'label' => 'Jumlah Komponen',
                                ],
                                [
                                    'attribute' => 'list_komponentarif',
                                    'label' => 'List Komponen Tarif',
                                ],
                                [
                                    'attribute' => 'uang_per_komponen',
                                    'label' => 'Uang Per Komponen',
                                  
                                ],
                                [
                                    'attribute' => 'total_uang_tindakan',
                                    'label' => 'Total Uang Tindakan',
                                   
                                ]
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
$urlExport = \yii\helpers\Url::to(['jasa-dokter/export']);

$js = <<<JS
    
    document.getElementById('jasa-dokter-form').addEventListener('submit', function(e) {
        const startDate = document.querySelector('input[name=\"date_from\"]').value;
        const endDate = document.querySelector('input[name=\"date_to\"]').value;

        if (startDate && endDate) {
            const start = new Date(startDate.split('-').reverse().join('-'));
            const end = new Date(endDate.split('-').reverse().join('-'));
            const diffTime = end - start;
            const diffDays = diffTime / (1000 * 3600 * 24);

            if (diffDays < 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Tanggal Tidak Valid',
                    text: 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.'
                });
                return;
            } else if (diffDays > 31) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Rentang Tanggal Terlalu Panjang',
                    text: 'Rentang tanggal tidak boleh lebih dari 1 bulan.'
                });
                return;
            }
        }

        Swal.fire({
            title: 'Mohon Tunggu',
            text: 'Proses sedang berlangsung...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
    
    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName("date_from")[0].value;
        let date_to = document.getElementsByName("date_to")[0].value;

        Swal.fire({
            title: 'Mohon Tunggu',
            text: 'Proses export sedang berlangsung...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "$urlExport",
            type: "GET",
            data: { date_from: date_from, date_to: date_to, cari: 'aktif' },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data) {
                Swal.close();
                const url = window.URL.createObjectURL(new Blob([data]));
                const a = document.createElement('a');
                a.href = url;
                a.download = 'laporan-jasa-dokter.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat export.'
                });
            }
        });
    });
JS;
$this->registerJs($js);
?>

