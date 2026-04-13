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
        .summary-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background: #ffffff;
            border-left: 5px solid #002D72;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .summary-icon {
            font-size: 2rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #002D72;
        }
        .summary-label {
            color: #6c757d;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
        }
    ");

    $resetUrl = \yii\helpers\Url::to(['jasa-dokter/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h2 class="mb-0">Laporan Jasa Dokter</h2>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-primary btn-sm fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="<?= $statuscari ? 'true' : 'false' ?>" aria-controls="filterCollapse" style="background-color: #002D72; border-color: #002D72;">
                        <i class="bi bi-funnel"></i> Opsi Pencarian
                    </button>
                    <div class="text-muted fw-bold d-none d-md-block ms-2"><?= date('d M Y') ?></div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card summary-card" style="border-left-color: #6DC536;">
                            <div class="card-body">
                                <i class="bi bi-wallet2 summary-icon text-success"></i>
                                <div class="summary-label">Total Jasa Medis</div>
                                <div class="summary-value text-success">Rp <?= number_format($stats['total_jasa'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card" style="border-left-color: #002D72;">
                            <div class="card-body">
                                <i class="bi bi-person-check summary-icon text-primary"></i>
                                <div class="summary-label">Total Dokter Aktif</div>
                                <div class="summary-value"><?= $stats['total_dokter'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <i class="bi bi-graph-up-arrow summary-icon text-warning"></i>
                                <div class="summary-label">Tindakan Terbanyak</div>
                                <div class="summary-value" style="font-size: 1.1rem;"><?= Html::encode($stats['tindakan_terbanyak']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="collapse <?= $statuscari ? 'show' : '' ?>" id="filterCollapse">
                    <div class="filter-section">
                        <?= Html::beginForm(['/keuangan/jasa-dokter/index'], 'get', ['id' => 'jasa-dokter-form']) ?>
                        <?= Html::hiddenInput('cari', 'aktif'); ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Rentang Tanggal</label>
                                <?= DatePicker::widget([
                                    'type' => DatePicker::TYPE_RANGE,
                                    'name' => 'date_from',
                                    'value' => $dropdownselect['start'],
                                    'name2' => 'date_to',
                                    'value2' => $dropdownselect['to'], 
                                    'separator' => '<span class="px-2">s/d</span>',
                                    'layout' => '{input1}{separator}{input2}',
                                    'options' => [
                                        'placeholder' => 'Mulai',
                                        'class' => 'form-control',
                                        'autocomplete' => 'off',
                                        'required' => true,
                                    ],
                                    'options2' => [
                                        'placeholder' => 'Selesai',
                                        'class' => 'form-control',
                                        'autocomplete' => 'off',
                                        'required' => true,
                                    ],
                                    'pluginOptions' => [
                                        'format' => 'dd-mm-yyyy',
                                        'autoclose' => true,
                                        'todayHighlight' => true,
                                    ],
                                ]); ?>  
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Pilih Dokter</label>
                                <?= Html::dropDownList('dokter_id', $dropdownselect['dokter_id'], $dataDokter, ['prompt' => '-- Semua Dokter --', 'class' => 'form-select']) ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Unit/Ruangan</label>
                                <?= Html::dropDownList('ruangan_id', $dropdownselect['ruangan_id'], $dataRuangan, ['prompt' => '-- Semua Ruangan --', 'class' => 'form-select']) ?>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Nama Penjamin</label>
                                <?= Html::dropDownList('penjamin_id', $dropdownselect['penjamin_id'], $dataPenjamin, ['prompt' => '-- Semua Penjamin --', 'class' => 'form-select']) ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Jenis Tindakan</label>
                                <?= Html::dropDownList('kategoritindakan_id', $dropdownselect['kategoritindakan_id'], $dataKategori, ['prompt' => '-- Semua Jenis --', 'class' => 'form-select']) ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Status Verifikasi</label>
                                <?= Html::dropDownList('status_verif', $dropdownselect['status_verif'], ['1' => 'Sudah Diverifikasi', '0' => 'Belum Diverifikasi'], ['prompt' => '-- Semua Status --', 'class' => 'form-select']) ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Quick Search (Nama/RM)</label>
                                <?= Html::textInput('keyword', $dropdownselect['keyword'], ['class' => 'form-control', 'placeholder' => 'Cari pasien...']) ?>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <?= Html::submitButton('<i class="bi bi-search"></i> Cari', ['class' => 'btn btn-primary px-4', 'style' => 'background-color: #002D72; border-color: #002D72;']) ?>
                                <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn btn-outline-secondary px-4']) ?>
                                <?= Html::button('<i class="bi bi-file-earmark-excel"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn btn-success px-4', 'style' => 'background-color: #6DC536; border-color: #6DC536;']) ?>
                            </div>
                        </div>
                        <?= Html::endForm() ?>
                    </div>
                </div>
    
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
                                    'value' => function($data) {
                                        return number_format($data['total_uang_tindakan'], 0, ',', '.');
                                    }
                                ]
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
        let dokter_id = document.getElementsByName("dokter_id")[0].value;
        let ruangan_id = document.getElementsByName("ruangan_id")[0].value;
        let penjamin_id = document.getElementsByName("penjamin_id")[0].value;
        let kategoritindakan_id = document.getElementsByName("kategoritindakan_id")[0].value;
        let status_verif = document.getElementsByName("status_verif")[0].value;
        let keyword = document.getElementsByName("keyword")[0].value;

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
            data: { 
                date_from: date_from, 
                date_to: date_to, 
                dokter_id: dokter_id,
                ruangan_id: ruangan_id,
                penjamin_id: penjamin_id,
                kategoritindakan_id: kategoritindakan_id,
                status_verif: status_verif,
                keyword: keyword,
                cari: 'aktif' 
            },
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
