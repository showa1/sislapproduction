<?php
    use yii\grid\GridView;
    use yii\helpers\Html;
    use yii\helpers\Url;
    use kartik\date\DatePicker;

    $this->title = 'Durante Operasi';
    $this->params['breadcrumbs'][] = $this->title;

    $this->registerCss("
        .custom-gridview thead th {
            background-color: #002D72 !important;
            color: #fff !important;
            font-size: 13px;
            text-align: center;
            font-weight: 600;
            border: none;
            padding: 12px 10px !important;
            vertical-align: middle !important;
            white-space: nowrap;
        }
        .custom-gridview tbody td {
            vertical-align: middle !important;
            border-bottom: 1px solid #f1f5f9;
            padding: 11px 12px !important;
            font-size: 13px;
        }
        .badge-carabayar {
            background: #e8f4fd;
            color: #1565c0;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-durasi {
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
            border-radius: 6px;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 700;
            font-family: 'Consolas', monospace;
            display: inline-block;
        }
        .tindakan-kode {
            font-size: 11px;
            font-weight: 700;
            color: #002D72;
            background: #f0f4f8;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 3px;
        }
        .tindakan-nama {
            color: #1e293b;
            font-weight: 500;
            line-height: 1.4;
        }
    ");

    $resetUrl = Url::to(['durante-operasi/index']);
?>

<!-- Breadcrumbs & Header -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::home() ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Rawat Inap</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page">Durante Operasi</li>
            </ol>
        </nav>
        <h2 style="color: #002D72; font-weight: 800; margin: 0;">
            <i class="bi bi-scissors me-2"></i>Durante Operasi
        </h2>
        <p style="color: #64748b; margin-top: 4px; font-size: 0.92rem;">Laporan tindakan dan durasi durante operasi berdasarkan data rencana operasi dan tanggal tindakan pelayanan.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Search Card -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-4">
                <?= Html::beginForm(['/rawatinap/durante-operasi/index'], 'get', ['id' => 'search-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>

                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;">
                            <i class="bi bi-calendar3 me-2" style="color: #002D72;"></i>Tanggal Tindakan
                        </label>
                        <?= DatePicker::widget([
                            'type'      => DatePicker::TYPE_RANGE,
                            'name'      => 'date_from',
                            'value'     => $dropdownselect['start'],
                            'name2'     => 'date_to',
                            'value2'    => $dropdownselect['to'],
                            'separator' => '<span style="font-size:15px; padding: 0 8px;">To</span>',
                            'layout'    => '{input1}{separator}{input2}',
                            'options'   => [
                                'placeholder'  => 'Tanggal Awal',
                                'class'        => 'form-control fs-6',
                                'style'        => 'border: 1px solid grey;',
                                'autocomplete' => 'off',
                            ],
                            'options2'  => [
                                'placeholder'  => 'Tanggal Akhir',
                                'class'        => 'form-control fs-6',
                                'style'        => 'border: 1px solid grey;',
                                'autocomplete' => 'off',
                            ],
                            'pluginOptions' => [
                                'format'         => 'dd-mm-yyyy',
                                'autoclose'      => true,
                                'todayHighlight' => true,
                                'orientation'    => 'bottom auto',
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-6 mb-3 d-flex gap-2 align-items-end">
                        <?= Html::submitButton('<i class="bi bi-search me-2"></i> Cari', [
                            'class' => 'btn px-4',
                            'style' => 'background: #002D72; color: #fff; border-radius: 8px; font-weight: 600;'
                        ]) ?>
                        <?= Html::a('<i class="bi bi-arrow-counterclockwise me-2"></i> Ulang', $resetUrl, [
                            'class' => 'btn px-4',
                            'style' => 'border: 1px solid #002D72; color: #002D72; background: #fff; border-radius: 8px; font-weight: 600;'
                        ]) ?>
                        <?= Html::button('<i class="bi bi-file-earmark-excel me-2"></i> Export', [
                            'id'    => 'export-button',
                            'class' => 'btn px-4',
                            'style' => 'background: #6DC536; color: #fff; border-radius: 8px; font-weight: 600; border: none;'
                        ]) ?>
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
                            'style' => 'border-collapse: separate; border-spacing: 0;',
                        ],
                        'columns' => [
                            [
                                'class'          => 'yii\grid\SerialColumn',
                                'headerOptions'  => ['style' => 'width: 50px; background: #002D72; color: #fff; border: none; padding: 12px;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-weight: 600;'],
                            ],
                            [
                                'attribute'      => 'no_rekam_medik',
                                'label'          => 'No. RM',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-weight: 600; color: #002D72;'],
                            ],
                            [
                                'attribute'      => 'nama_pasien',
                                'label'          => 'Nama Pasien',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle !important; font-weight: 600; color: #1e293b;'],
                            ],
                            [
                                'attribute'      => 'no_pendaftaran',
                                'label'          => 'No. Pendaftaran',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-weight: 500; color: #475569;'],
                            ],
                            [
                                'attribute'      => 'tgl_pendaftaran',
                                'label'          => 'Tgl Pendaftaran',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-size: 12px; color: #475569; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return !empty($model['tgl_pendaftaran'])
                                        ? date('d-m-Y H:i', strtotime($model['tgl_pendaftaran']))
                                        : '-';
                                },
                            ],
                            [
                                'attribute'      => 'carabayar_nama',
                                'label'          => 'Cara Bayar',
                                'format'         => 'raw',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important;'],
                                'value'          => function ($model) {
                                    return !empty($model['carabayar_nama'])
                                        ? '<span class="badge-carabayar">' . Html::encode($model['carabayar_nama']) . '</span>'
                                        : '-';
                                },
                            ],
                            [
                                'label'          => 'Tindakan Operasi',
                                'format'         => 'raw',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; min-width: 200px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle !important;'],
                                'value'          => function ($model) {
                                    $kode = !empty($model['daftartindakan_kode']) ? '<span class="tindakan-kode">' . Html::encode($model['daftartindakan_kode']) . '</span><br>' : '';
                                    $nama = !empty($model['daftartindakan_nama']) ? Html::encode($model['daftartindakan_nama']) : '-';
                                    return $kode . '<div class="tindakan-nama">' . $nama . '</div>';
                                },
                            ],
                            [
                                'attribute'      => 'tgl_tindakan',
                                'label'          => 'Tgl Tindakan',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-size: 12px; font-weight: 500; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return !empty($model['tgl_tindakan'])
                                        ? date('d-m-Y H:i', strtotime($model['tgl_tindakan']))
                                        : '-';
                                },
                            ],
                            [
                                'attribute'      => 'qty_tindakan',
                                'label'          => 'Qty',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-weight: 600;'],
                                'value'          => function ($model) {
                                    return isset($model['qty_tindakan']) ? (int)$model['qty_tindakan'] : 0;
                                },
                            ],
                            [
                                'attribute'      => 'tarif_satuan',
                                'label'          => 'Tarif Satuan',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: right; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle !important; white-space: nowrap; color: #475569;'],
                                'value'          => function ($model) {
                                    return isset($model['tarif_satuan']) && $model['tarif_satuan'] !== null
                                        ? number_format((float)$model['tarif_satuan'], 2, ',', '.')
                                        : '0,00';
                                },
                            ],
                            [
                                'attribute'      => 'tarif_tindakan',
                                'label'          => 'Tarif Tindakan',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: right; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: middle !important; font-weight: 600; color: #002D72; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return isset($model['tarif_tindakan']) && $model['tarif_tindakan'] !== null
                                        ? number_format((float)$model['tarif_tindakan'], 2, ',', '.')
                                        : '0,00';
                                },
                            ],
                            [
                                'attribute'      => 'mulaioperasi',
                                'label'          => 'Mulai Operasi',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-size: 12px; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return !empty($model['mulaioperasi'])
                                        ? date('d-m-Y H:i', strtotime($model['mulaioperasi']))
                                        : '-';
                                },
                            ],
                            [
                                'attribute'      => 'selesaioperasi',
                                'label'          => 'Selesai Operasi',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-size: 12px; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return !empty($model['selesaioperasi'])
                                        ? date('d-m-Y H:i', strtotime($model['selesaioperasi']))
                                        : '-';
                                },
                            ],
                            [
                                'attribute'      => 'durasi_operasi',
                                'label'          => 'Durasi',
                                'format'         => 'raw',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 12px; text-align: center; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return !empty($model['durasi_operasi'])
                                        ? '<span class="badge-durasi"><i class="bi bi-clock me-1"></i>' . Html::encode($model['durasi_operasi']) . '</span>'
                                        : '-';
                                },
                            ],
                        ],
                        'layout'  => "{items}\n<div class='p-4 d-flex justify-content-between align-items-center flex-wrap gap-3'>
                                    <div style='color: #64748b; font-size: 0.9rem;'>{summary}</div>
                                    <div class='custom-pagination'>{pager}</div>
                                </div>",
                        'summary' => 'Menampilkan {begin} - {end} dari {totalCount} data.',
                        'pager'   => [
                            'options'              => ['class' => 'pagination pagination-sm m-0'],
                            'linkOptions'          => ['class' => 'page-link', 'style' => 'border-color: #e2e8f0; color: #002D72;'],
                            'activePageCssClass'   => 'active',
                            'disabledPageCssClass' => 'disabled',
                            'prevPageLabel'        => '<i class="bi bi-chevron-left"></i>',
                            'nextPageLabel'        => '<i class="bi bi-chevron-right"></i>',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$urlExport = Url::to(['durante-operasi/export']);

$js = "
    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName('date_from')[0].value;
        let date_to   = document.getElementsByName('date_to')[0].value;
        window.location.href = '$urlExport' + '&date_from=' + date_from + '&date_to=' + date_to + '&cari=aktif';
    });
";
$this->registerJs($js);
?>
