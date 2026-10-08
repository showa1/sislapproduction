<?php
    use yii\grid\GridView;
    use yii\helpers\Html;
    use yii\helpers\Url;
    use kartik\date\DatePicker;

    $this->title = 'Penggunaan BMHP/Pasien';
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
            vertical-align: top !important;
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 15px !important;
            white-space: pre-line;
        }
        .badge-carabayar {
            background: #e8f4fd;
            color: #1565c0;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
        }
        .text-diagnosa {
            color: #374151;
            font-size: 13px;
            line-height: 1.7;
        }
        .text-obat {
            color: #1e3a5f;
            font-size: 13px;
            line-height: 1.7;
            font-weight: 500;
        }
    ");

    $resetUrl = Url::to(['penggunaan-bmhp-perpasien/index']);
?>

<!-- Breadcrumbs & Header -->
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="background: transparent; padding: 0; margin-bottom: 8px;">
                <li class="breadcrumb-item"><a href="<?= Url::home() ?>" style="color: #6c757d; text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 500;">Farmasi</li>
                <li class="breadcrumb-item active" style="color: #002D72; font-weight: 700;" aria-current="page">Penggunaan BMHP / Pasien</li>
            </ol>
        </nav>
        <h2 style="color: #002D72; font-weight: 800; margin: 0;">
            <i class="bi bi-capsule me-2"></i>Penggunaan BMHP / Pasien
        </h2>
        <p style="color: #64748b; margin-top: 4px; font-size: 0.92rem;">Laporan penggunaan Bahan Medis Habis Pakai (BHP) berdasarkan data pasien dan tanggal pelayanan.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Search Card -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #ffffff;">
            <div class="card-body p-4">
                <?= Html::beginForm(['/farmasi/penggunaan-bmhp-perpasien/index'], 'get', ['id' => 'search-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>

                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: 600; color: #4a5568;">
                            <i class="bi bi-calendar3 me-2" style="color: #002D72;"></i>Tanggal Pelayanan
                        </label>
                        <?= DatePicker::widget([
                            'type'    => DatePicker::TYPE_RANGE,
                            'name'    => 'date_from',
                            'value'   => $dropdownselect['start'],
                            'name2'   => 'date_to',
                            'value2'  => $dropdownselect['to'],
                            'separator' => '<span style="font-size:15px; padding: 0 8px;">To</span>',
                            'layout'  => '{input1}{separator}{input2}',
                            'options' => [
                                'placeholder'  => 'Tanggal Awal',
                                'class'        => 'form-control fs-6',
                                'style'        => 'border: 1px solid grey;',
                                'autocomplete' => 'off',
                            ],
                            'options2' => [
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
                                'headerOptions'  => ['style' => 'width: 50px; background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important;'],
                            ],
                            [
                                'attribute'      => 'tgl_pendaftaran',
                                'label'          => 'Tgl Pendaftaran',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align:center;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important; font-weight: 500; color: #374151;'],
                                'value'          => function ($model) {
                                    return !empty($model['tgl_pendaftaran'])
                                        ? date('d-m-Y', strtotime($model['tgl_pendaftaran']))
                                        : '-';
                                },
                            ],
                            [
                                'attribute'      => 'no_pendaftaran',
                                'label'          => 'No Pendaftaran',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'font-weight: 600; color: #002D72; vertical-align: middle !important;'],
                            ],
                            [
                                'attribute'      => 'no_rekam_medik',
                                'label'          => 'No Rekam Medik',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: middle !important; color: #374151;'],
                            ],
                            [
                                'attribute'      => 'nama_pasien',
                                'label'          => 'Nama Pasien',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'font-weight: 600; vertical-align: middle !important; color: #1e293b;'],
                            ],
                            [
                                'attribute'      => 'carabayar_nama',
                                'label'          => 'Cara Bayar',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: center;'],
                                'contentOptions' => ['style' => 'text-align: center; vertical-align: middle !important;'],
                                'value'          => function ($model) {
                                    return !empty($model['carabayar_nama']) ? $model['carabayar_nama'] : '-';
                                },
                                'format'         => 'raw',
                                'value'          => function ($model) {
                                    return !empty($model['carabayar_nama'])
                                        ? '<span class="badge-carabayar">' . Html::encode($model['carabayar_nama']) . '</span>'
                                        : '-';
                                },
                            ],
                            [
                                'attribute'      => 'diagnosa',
                                'label'          => 'Diagnosa',
                                'format'         => 'raw',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: top;'],
                                'value'          => function ($model) {
                                    if (empty($model['diagnosa'])) return '<span style="color:#94a3b8;">-</span>';
                                    $lines = explode("\n", $model['diagnosa']);
                                    $html  = '';
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if ($line !== '') {
                                            $html .= '<div class="text-diagnosa"><i class="bi bi-circle-fill me-1" style="color:#002D72; font-size:7px; vertical-align:middle;"></i>' . Html::encode($line) . '</div>';
                                        }
                                    }
                                    return $html;
                                },
                            ],
                            [
                                'attribute'      => 'obat',
                                'label'          => 'BMHP yang Digunakan',
                                'format'         => 'raw',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px;'],
                                'contentOptions' => ['style' => 'vertical-align: top;'],
                                'value'          => function ($model) {
                                    if (empty($model['obat'])) return '<span style="color:#94a3b8;">-</span>';
                                    $lines = explode("\n", $model['obat']);
                                    $html  = '';
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if ($line !== '') {
                                            $html .= '<div class="text-obat"><i class="bi bi-capsule me-1" style="color:#6DC536;"></i>' . Html::encode($line) . '</div>';
                                        }
                                    }
                                    return $html;
                                },
                            ],
                            [
                                'attribute'      => 'total_harga_netto',
                                'label'          => 'Total Harga Netto',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: top !important; font-weight: 600; color: #374151; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return isset($model['total_harga_netto']) && $model['total_harga_netto'] !== null
                                        ? number_format((float)$model['total_harga_netto'], 2, ',', '.')
                                        : '0,00';
                                },
                            ],
                            [
                                'attribute'      => 'total_harga_jual',
                                'label'          => 'Total Harga Jual',
                                'headerOptions'  => ['style' => 'background: #002D72; color: #fff; border: none; padding: 15px; text-align: right; white-space: nowrap;'],
                                'contentOptions' => ['style' => 'text-align: right; vertical-align: top !important; font-weight: 600; color: #002D72; white-space: nowrap;'],
                                'value'          => function ($model) {
                                    return isset($model['total_harga_jual']) && $model['total_harga_jual'] !== null
                                        ? number_format((float)$model['total_harga_jual'], 2, ',', '.')
                                        : '0,00';
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
$urlExport = \yii\helpers\Url::to(['penggunaan-bmhp-perpasien/export']);

$js = "
    $('#export-button').on('click', function() {
        let date_from = document.getElementsByName('date_from')[0].value;
        let date_to   = document.getElementsByName('date_to')[0].value;
        window.location.href = '$urlExport' + '&date_from=' + date_from + '&date_to=' + date_to + '&cari=aktif';
    });
";
$this->registerJs($js);
?>
