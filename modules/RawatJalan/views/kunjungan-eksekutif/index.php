<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use yii\helpers\Url;
    
    $this->title = 'Data Kunjungan Pasien Eksekutif';
    $this->params['breadcrumbs'][] = ['label' => 'Rawat Jalan', 'url' => ['/rawatjalan/default/index']];
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

    $resetUrl = \yii\helpers\Url::to(['kunjungan-eksekutif/index']);
?>

<!-- Breadcrumbs & Header Section -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::home() ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Rawat Jalan</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page"><?= $this->title ?></li>
            </ol>
        </nav>
        <h2 style="color: #002D72; font-weight: 800; margin: 0;"><?= $this->title ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Search Card -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-4">
                <?= Html::beginForm(['/rawatjalan/kunjungan-eksekutif/index'], 'get', ['id' => 'search-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;"><i class="bi bi-calendar3 me-2" style="color: #002D72;"></i> Rentang Tanggal Pendaftaran</label>
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
                        <?= Html::button('<i class="bi bi-file-earmark-excel me-2"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn px-4', 'style' => 'background: #6DC536; color: #fff; border-radius: 8px; font-weight: 600; border: none;']) ?>
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
                        'rowOptions' => function ($model, $key, $index, $grid) {
                            if ($model['total_visit'] > 1) {
                                return ['style' => 'background-color: rgba(99, 102, 241, 0.05);'];
                            }
                        },
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
                                'attribute' => 'no_pendaftaran',
                                'label' => 'No Pendaftaran',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'no_rekam_medik',
                                'label' => 'No Rekam Medik',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'nama_pasien',
                                'label' => 'Nama Pasien',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'font-weight: 500; color: #002D72;'],
                                'format' => 'raw',
                                'value' => function ($model) {
                                    $name = $model['namadepan'] . ' ' . $model['nama_pasien'];
                                    $badge = '';
                                    if ($model['total_visit'] > 1) {
                                        $badge = ' <span class="badge" style="background:#6366f1; font-size: 0.65rem; padding: 3px 8px; vertical-align: middle;">Repeat (' . $model['total_visit'] . 'x)</span>';
                                    }
                                    return $name . $badge;
                                }
                            ],
                            [
                                'attribute' => 'jeniskelamin',
                                'label' => 'L/P',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'ruangan_nama',
                                'label' => 'Poliklinik',
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
                                'attribute' => 'carabayar_nama',
                                'label' => 'Cara Bayar',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'attribute' => 'penjaminpasien_nama',
                                'label' => 'Penjamin',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Aksi',
                                'headerOptions' => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center;'],
                                'template' => '{history}',
                                'buttons' => [
                                    'history' => function ($url, $model) {
                                        return Html::a('<i class="bi bi-clock-history"></i>', '#', [
                                            'class' => 'btn btn-sm btn-outline-primary show-history',
                                            'title' => 'Riwayat Kunjungan Eksekutif',
                                            'data-pid' => $model['pasien_id'],
                                            'data-name' => $model['namadepan'] . ' ' . $model['nama_pasien'],
                                            'style' => 'border-radius: 6px;'
                                        ]);
                                    },
                                ],
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

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      <div class="modal-header" style="background: #002D72; color: #fff; border-radius: 14px 14px 0 0; border: none;">
        <h5 class="modal-title" id="historyModalLabel"><i class="bi bi-clock-history me-2"></i> Riwayat Kunjungan Eksekutif</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
            <label class="text-muted small text-uppercase fw-bold">Nama Pasien</label>
            <div id="modalPatientName" class="fs-5 fw-bold" style="color: #002D72;"></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover" id="historyTable">
                <thead class="table-light">
                    <tr>
                        <th>Tgl Pendaftaran</th>
                        <th>No Pendaftaran</th>
                        <th>Poliklinik</th>
                        <th>Dokter</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- JS Injected -->
                </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer" style="border: none;">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?php
$urlExport = \yii\helpers\Url::to(['kunjungan-eksekutif/export']);
$urlHistory = \yii\helpers\Url::to(['kunjungan-eksekutif/history']);

$this->registerCssFile('@web/template/vendors/flatpickr/flatpickr.min.css');
$this->registerJsFile('@web/template/vendors/flatpickr/flatpickr.min.js', ['position' => \yii\web\View::POS_END]);

$js = "
    function initFlatpickr() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.flatpickr-range', {
                dateFormat: 'd-m-Y',
                allowInput: true,
            });
        } else {
            setTimeout(initFlatpickr, 100);
        }
    }
    initFlatpickr();

    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName('date_from')[0].value;
        let date_to = document.getElementsByName('date_to')[0].value;
        window.location.href = '$urlExport' + (window.location.search.includes('?') ? '&' : '?') + 'date_from=' + date_from + '&date_to=' + date_to +'&cari=aktif';
    });

    $('.show-history').on('click', function(e) {
        e.preventDefault();
        let pid = $(this).data('pid');
        let name = $(this).data('name');
        
        $('#modalPatientName').text(name);
        $('#historyTable tbody').html('<tr><td colspan=\"4\" class=\"text-center\"><div class=\"spinner-border spinner-border-sm text-primary\"></div> Memuat data...</td></tr>');
        
        let myModal = new bootstrap.Modal(document.getElementById('historyModal'));
        myModal.show();

        $.getJSON('$urlHistory', {pasien_id: pid}, function(res) {
            if(res.success) {
                let html = '';
                if(res.data.length > 0) {
                    res.data.forEach(item => {
                        html += `<tr>
                            <td>\${item.tgl_pendaftaran}</td>
                            <td>\${item.no_pendaftaran}</td>
                            <td>\${item.ruangan_nama}</td>
                            <td>\${item.nama_pegawai}</td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan=\"4\" class=\"text-center\">Tidak ada riwayat kunjungan eksekutif.</td></tr>';
                }
                $('#historyTable tbody').html(html);
            }
        });
    });
";
$this->registerJs($js);
?>
