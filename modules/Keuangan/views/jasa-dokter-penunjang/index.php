<?php
    use yii\grid\GridView;
    use yii\helpers\Html;
    use yii\helpers\Url;
    use kartik\date\DatePicker;
    
    $this->title = 'Jasa Dokter (Penunjang)';
    $this->params['breadcrumbs'][] = $this->title;
    
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #002D72; /* PMC Navy Blue */
            color: #ffffff;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
        }
        .custom-gridview tfoot td {
            background-color: #f8f9fa;
            font-weight: 700;
            color: #002D72;
            border-top: 2px solid #dee2e6;
        }
        .summary-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background: #ffffff;
            border-left: 5px solid #002D72;
        }
        .summary-card:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(0,0,0,0.1); }
        .summary-icon { font-size: 2.2rem; opacity: 0.15; position: absolute; right: 20px; top: 15px; color: #002D72; }
        .summary-value { font-size: 1.5rem; font-weight: 700; color: #002D72; line-height: 1; margin-top: 5px; }
        .summary-label { color: #6c757d; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px; }
        .filter-section { background-color: #f8f9fa; border-radius: 12px; padding: 25px; margin-bottom: 25px; border: 1px solid #e2e8f0; }
        .btn-pmc { background-color: #002D72; border-color: #002D72; color: white; transition: all 0.2s; }
        .btn-pmc:hover { background-color: #001f4d; border-color: #001f4d; color: white; transform: translateY(-1px); }
        .card-round { border-radius: 15px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .list-unstyled li { padding-bottom: 4px; border-bottom: 1px dashed #eee; margin-bottom: 4px; }
        .list-unstyled li:last-child { border-bottom: none; }
    ");
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-round">
            <div class="card-header d-flex justify-content-between align-items-center py-4 bg-white border-bottom">
                <h3 class="fw-bold mb-0" style="color: #002D72 !important; font-family: 'Inter', sans-serif;">
                    <i class="bi bi-clipboard2-pulse me-2"></i> Laporan Jasa Dokter (Penunjang)
                </h3>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-pmc btn-sm fw-bold px-4 rounded-pill" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="bi bi-funnel"></i> Opsi Pencarian
                    </button>
                    <span class="text-muted small fw-bold d-none d-md-block ms-2 border-start ps-3"><?= date('l, d F Y') ?></span>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Filters -->
                <div class="collapse <?= !$statuscari ? 'show' : '' ?>" id="filterCollapse">
                    <div class="filter-section shadow-sm">
                        <?= Html::beginForm(['index'], 'get', ['id' => 'filter-form']) ?>
                        <?= Html::hiddenInput('cari', '1'); ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase" style="color: #4a5568;">Rentang Tanggal (Closing)</label>
                                <?= DatePicker::widget([
                                    'type' => DatePicker::TYPE_RANGE,
                                    'name' => 'date_from',
                                    'value' => $dropdownselect['start'],
                                    'name2' => 'date_to',
                                    'value2' => $dropdownselect['to'], 
                                    'separator' => '<span class="input-group-text bg-light border-0">s/d</span>',
                                    'layout' => '<div class="input-group shadow-sm rounded-pill overflow-hidden">{input1}{separator}{input2}<span class="input-group-text bg-white border-0"><i class="bi bi-calendar3 text-primary"></i></span></div>',
                                    'options' => ['placeholder' => 'Mulai', 'class' => 'form-control border-0 bg-white', 'autocomplete' => 'off'],
                                    'options2' => ['placeholder' => 'Selesai', 'class' => 'form-control border-0 bg-white', 'autocomplete' => 'off'],
                                    'pluginOptions' => [
                                        'format' => 'dd-mm-yyyy',
                                        'autoclose' => true,
                                        'todayHighlight' => true,
                                        'todayBtn' => 'linked',
                                        'clearBtn' => true,
                                    ],
                                ]); ?>  
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase" style="color: #4a5568;">Pilih Dokter</label>
                                <?= Html::dropDownList('pegawai_id', $dropdownselect['pegawai_id'], $dataPegawai, ['prompt' => '-- Semua Dokter --', 'class' => 'form-select border-0 bg-white shadow-sm']) ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase" style="color: #4a5568;">Unit Penunjang</label>
                                <?= Html::dropDownList('ruangan_id', $dropdownselect['ruangan_id'], $dataRuangan, ['prompt' => '-- Semua Ruangan --', 'class' => 'form-select border-0 bg-white shadow-sm']) ?>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase" style="color: #4a5568;">Cara Bayar</label>
                                <?= Html::dropDownList('carabayar_id', $dropdownselect['carabayar_id'], $dataCaraBayar, ['prompt' => '-- Semua --', 'class' => 'form-select border-0 bg-white shadow-sm']) ?>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <?= Html::submitButton('<i class="bi bi-search me-1"></i> Terapkan Filter', ['class' => 'btn btn-pmc px-4 rounded-pill']) ?>
                                <?= Html::button('<i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel', ['class' => 'btn btn-success px-4 rounded-pill', 'id' => 'btn-export']) ?>
                                <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> Reset', ['index'], ['class' => 'btn btn-outline-secondary px-4 rounded-pill']) ?>
                            </div>
                        </div>
                        <?= Html::endForm() ?>
                    </div>
                </div>

                <?php if ($statuscari): ?>
                <!-- Summary Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card summary-card" style="border-left-color: #002D72;">
                            <div class="card-body">
                                <i class="bi bi-person-heart summary-icon"></i>
                                <div class="summary-label">Total Penunjang</div>
                                <div class="summary-value">Rp <?= number_format($stats['total_penunjang'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card" style="border-left-color: #28a745;">
                            <div class="card-body">
                                <i class="bi bi-capsule summary-icon" style="color: #28a745;"></i>
                                <div class="summary-label">Total Resep</div>
                                <div class="summary-value text-success">Rp <?= number_format($stats['total_resep'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card summary-card" style="border-left-color: #fd7e14;">
                            <div class="card-body">
                                <i class="bi bi-people summary-icon" style="color: #fd7e14;"></i>
                                <div class="summary-label">Total Volume Pasien</div>
                                <div class="summary-value text-warning"><?= number_format($stats['total_pasien'], 0, ',', '.') ?> <small style="font-size: 0.9rem; font-weight: normal; color: #6c757d;">Kunjungan</small></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid -->
                <div class="table-responsive">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'showFooter' => true,
                        'tableOptions' => ['class' => 'table table-hover table-bordered custom-gridview align-middle'],
                        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'>{summary}{pager}</div>",
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'label' => 'No. Pendaftaran',
                                'attribute' => 'no_pendaftaran',
                                'value' => function($data) {
                                    return Html::tag('span', $data['no_pendaftaran'], ['class' => 'fw-bold text-primary']);
                                },
                                'format' => 'html',
                                'footer' => '<strong class="text-dark">GRAND TOTAL</strong>'
                            ],
                            [
                                'label' => 'Tgl Pendaftaran',
                                'attribute' => 'tgl_pendaftaran',
                                'format' => ['date', 'php:d/m/Y H:i']
                            ],
                            [
                                'label' => 'Ruangan',
                                'attribute' => 'ruangan_pendaftaran',
                                'contentOptions' => ['class' => 'small fw-bold text-muted']
                            ],
                            [
                                'label' => 'Pasien',
                                'value' => function($data) {
                                    return '<strong>'.$data['nama_pasien'].'</strong><br><small class="text-muted">'.$data['no_rekam_medik'].'</small>';
                                },
                                'format' => 'html'
                            ],
                            [
                                'label' => 'Dokter',
                                'attribute' => 'nama_pegawai',
                            ],
                            [
                                'label' => 'Total Penunjang',
                                'attribute' => 'total_penunjang',
                                'value' => function($data) {
                                    return 'Rp ' . number_format($data['total_penunjang'], 0, ',', '.');
                                },
                                'contentOptions' => ['class' => 'text-end fw-bold text-primary'],
                                'footer' => '<div class="text-end fw-bold text-primary">Rp ' . number_format($stats['total_penunjang'], 0, ',', '.') . '</div>'
                            ],
                            [
                                'label' => 'Total Resep',
                                'attribute' => 'total_resep',
                                'value' => function($data) {
                                    return 'Rp ' . number_format($data['total_resep'], 0, ',', '.');
                                },
                                'contentOptions' => ['class' => 'text-end text-success'],
                                'footer' => '<div class="text-end fw-bold text-success">Rp ' . number_format($stats['total_resep'], 0, ',', '.') . '</div>'
                            ],
                            [
                                'label' => 'Detail Penunjang',
                                'value' => function($data) {
                                    $details = !empty($data['detail_penunjang']) ? json_decode($data['detail_penunjang'], true) : [];
                                    if(empty($details)) return '<span class="text-muted">-</span>';
                                    $html = '<ul class="list-unstyled mb-0 small">';
                                    foreach($details as $ruangan => $tarif) {
                                        $html .= "<li>$ruangan: <strong class='float-end text-dark'>".number_format($tarif, 0, ',', '.')."</strong></li>";
                                    }
                                    $html .= '</ul>';
                                    return $html;
                                },
                                'format' => 'html',
                                'contentOptions' => ['style' => 'min-width: 200px;']
                            ],
                            [
                                'label' => 'Cara Bayar',
                                'attribute' => 'carabayar',
                                'value' => function($data) {
                                    $class = $data['carabayar'] == 'PRIBADI' ? 'bg-primary' : 'bg-info text-dark';
                                    return '<span class="badge '.$class.'">'.$data['carabayar'].'</span>';
                                },
                                'format' => 'html',
                                'contentOptions' => ['class' => 'text-center']
                            ],
                            [
                                'label' => 'Info Closing',
                                'attribute' => 'closingkasir_no',
                                'value' => function($data) {
                                    if(!$data['closingkasir_no']) return '<span class="badge bg-warning text-dark">Pending</span>';
                                    return '<div class="fw-bold">'.$data['closingkasir_no'].'</div><small class="text-muted">' . date('d/m/Y H:i', strtotime($data['tglclosingkasir'])) . '</small>';
                                },
                                'format' => 'html'
                            ],
                        ],
                        'pager' => [
                            'class' => \yii\bootstrap5\LinkPager::class,
                            'options' => ['class' => 'pagination mb-0'],
                            'maxButtonCount' => 5,
                        ],
                    ]); ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5 border rounded-3 bg-light opacity-75">
                    <i class="bi bi-funnel" style="font-size: 3rem; color: #002D72; opacity: 0.3;"></i>
                    <h5 class="mt-3 fw-bold text-muted">Silakan tentukan filter dan klik Terapkan Filter</h5>
                    <p class="text-muted small">Data laporan akan ditampilkan setelah Anda menentukan kriteria pencarian.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$exportUrl = Url::to(['export']);
$this->registerJs("
    $('#btn-export').on('click', function() {
        var params = $('#filter-form').serialize();
        // Bersihkan parameter 'r' supaya tidak double di URL
        params = params.replace(/r=[^&]*&?/, '');
        window.location.href = '{$exportUrl}&' + params;
    });
");
?>
