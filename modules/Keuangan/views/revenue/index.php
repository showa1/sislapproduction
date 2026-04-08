<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Revenue';
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

    $resetUrl = \yii\helpers\Url::to(['revenue/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Revenue</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/keuangan/revenue/index'], 'get', ['id' => 'revenue-form']) ?>
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
                                    'attribute' => 'tanggalbayar',
                                    'label' => 'Tanggal Bayar'
                                ],
                                [
                                    'attribute' => 'nopembayaran',
                                    'label' => 'No Pembayaran'
                                ],
                                [
                                    'attribute' => 'administrasirj',
                                    'label' => 'Administrasi RJ',
                                    'value' => function ($model) {
                                        return isset($model['administrasirj']) ? number_format($model['administrasirj'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'dokterspesialis',
                                    'label' => 'Dokter Spesialis',
                                    'value' => function ($model) {
                                        return isset($model['dokterspesialis']) ? number_format($model['dokterspesialis'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhprj',
                                    'label' => 'BHP RJ',
                                    'value' => function ($model) {
                                        return isset($model['bhprj']) ? number_format($model['bhprj'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'pendapatanparamedisdannon',
                                    'label' => 'Pendapatan Para Medis Dannon',
                                    'value' => function ($model) {
                                        return isset($model['pendapatanparamedisdannon']) ? number_format($model['pendapatanparamedisdannon'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanrawatjalan',
                                    'label' => 'Tindakan Rawat Jalan',
                                    'value' => function ($model) {
                                        return isset($model['tindakanrawatjalan']) ? number_format($model['tindakanrawatjalan'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'administrasirawatinap',
                                    'label' => 'Administrasi Rawat Inap',
                                    'value' => function ($model) {
                                        return isset($model['administrasirawatinap']) ? number_format($model['administrasirawatinap'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'akomodasirawatinap',
                                    'label' => 'Akomodasi Rawat Inap',
                                    'value' => function ($model) {
                                        return isset($model['akomodasirawatinap']) ? number_format($model['akomodasirawatinap'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jasadokterrawatinap',
                                    'label' => 'Jasa Dokter Rawat Inap',
                                    'value' => function ($model) {
                                        return isset($model['jasadokterrawatinap']) ? number_format($model['jasadokterrawatinap'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanrawatinap',
                                    'label' => 'Tindakan Rawat Inap',
                                    'value' => function ($model) {
                                        return isset($model['tindakanrawatinap']) ? number_format($model['tindakanrawatinap'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpri',
                                    'label' => 'BHP RI',
                                    'value' => function ($model) {
                                        return isset($model['bhpri']) ? number_format($model['bhpri'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'administrasiigd',
                                    'label' => 'Administrasi IGD',
                                    'value' => function ($model) {
                                        return isset($model['administrasiigd']) ? number_format($model['administrasiigd'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jasadokterigd',
                                    'label' => 'Jasa Dokter IGD',
                                    'value' => function ($model) {
                                        return isset($model['jasadokterigd']) ? number_format($model['jasadokterigd'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'pendapatanparamedisdannonigd',
                                    'label' => 'Pendapatan paramedis dannon IGD',
                                    'value' => function ($model) {
                                        return isset($model['pendapatanparamedisdannonigd']) ? number_format($model['pendapatanparamedisdannonigd'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanigd',
                                    'label' => 'Tindakan IGD',
                                    'value' => function ($model) {
                                        return isset($model['tindakanigd']) ? number_format($model['tindakanigd'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhprd',
                                    'label' => 'BHP RD',
                                    'value' => function ($model) {
                                        return isset($model['bhprd']) ? number_format($model['bhprd'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jasadoktervk',
                                    'label' => 'Jasa Dokter VK',
                                    'value' => function ($model) {
                                        return isset($model['jasadoktervk']) ? number_format($model['jasadoktervk'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanvk',
                                    'label' => 'Tindakan VK',
                                    'value' => function ($model) {
                                        return isset($model['tindakanvk']) ? number_format($model['tindakanvk'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpvk',
                                    'label' => 'BHP VK',
                                    'value' => function ($model) {
                                        return isset($model['bhpvk']) ? number_format($model['bhpvk'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'akomodasiperawatanintensif',
                                    'label' => 'Akomodasi Perawatan Intensif',
                                    'value' => function ($model) {
                                        return isset($model['akomodasiperawatanintensif']) ? number_format($model['akomodasiperawatanintensif'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jasadokterperawatanintesif',
                                    'label' => 'Jasa Dokter Perawatan Intensif',
                                    'value' => function ($model) {
                                        return isset($model['jasadokterperawatanintesif']) ? number_format($model['jasadokterperawatanintesif'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanperawatanintesif',
                                    'label' => 'Tindakan Perawatan Intensif',
                                    'value' => function ($model) {
                                        return isset($model['tindakanperawatanintesif']) ? number_format($model['tindakanperawatanintesif'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpperawatanintensif',
                                    'label' => 'BHP Perawatan Intensif',
                                    'value' => function ($model) {
                                        return isset($model['bhpperawatanintensif']) ? number_format($model['bhpperawatanintensif'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanfisiotherapy',
                                    'label' => 'Tindakan Fisiotherapy',
                                    'value' => function ($model) {
                                        return isset($model['tindakanfisiotherapy']) ? number_format($model['tindakanfisiotherapy'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpfisiotherapy',
                                    'label' => 'BHP Fisiotherapy',
                                    'value' => function ($model) {
                                        return isset($model['bhpfisiotherapy']) ? number_format($model['bhpfisiotherapy'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanmcu',
                                    'label' => 'Tindakan MCU',
                                    'value' => function ($model) {
                                        return isset($model['tindakanmcu']) ? number_format($model['tindakanmcu'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jasadoktermcu',
                                    'label' => 'Jasa Dokter MCU',
                                    'value' => function ($model) {
                                        return isset($model['jasadoktermcu']) ? number_format($model['jasadoktermcu'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpmcu',
                                    'label' => 'BHP MCU',
                                    'value' => function ($model) {
                                        return isset($model['bhpmcu']) ? number_format($model['bhpmcu'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanlab',
                                    'label' => 'Tindakan Lab',
                                    'value' => function ($model) {
                                        return isset($model['tindakanlab']) ? number_format($model['tindakanlab'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhplab',
                                    'label' => 'BHP Lab',
                                    'value' => function ($model) {
                                        return isset($model['bhplab']) ? number_format($model['bhplab'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanrad',
                                    'label' => 'Tindakan Rad',
                                    'value' => function ($model) {
                                        return isset($model['tindakanrad']) ? number_format($model['tindakanrad'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhprad',
                                    'label' => 'BHP Rad',
                                    'value' => function ($model) {
                                        return isset($model['bhprad']) ? number_format($model['bhprad'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanibs',
                                    'label' => 'Tindakan Ibs',
                                    'value' => function ($model) {
                                        return isset($model['tindakanibs']) ? number_format($model['tindakanibs'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpibs',
                                    'label' => 'BHP Ibs',
                                    'value' => function ($model) {
                                        return isset($model['bhpibs']) ? number_format($model['bhpibs'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanjenazah',
                                    'label' => 'Tindakan Jenazah',
                                    'value' => function ($model) {
                                        return isset($model['tindakanjenazah']) ? number_format($model['tindakanjenazah'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpjenah',
                                    'label' => 'BHP Jenazah',
                                    'value' => function ($model) {
                                        return isset($model['bhpjenah']) ? number_format($model['bhpjenah'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanbankdarah',
                                    'label' => 'Tindakan Bank Darah',
                                    'value' => function ($model) {
                                        return isset($model['tindakanbankdarah']) ? number_format($model['tindakanbankdarah'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpbankdarah',
                                    'label' => 'BHP Bank Darah',
                                    'value' => function ($model) {
                                        return isset($model['bhpbankdarah']) ? number_format($model['bhpbankdarah'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'tindakanambulance',
                                    'label' => 'Tindakan Ambulance',
                                    'value' => function ($model) {
                                        return isset($model['tindakanambulance']) ? number_format($model['tindakanambulance'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'bhpambulance',
                                    'label' => 'BHP Ambulance',
                                    'value' => function ($model) {
                                        return isset($model['bhpambulance']) ? number_format($model['bhpambulance'], 2, ',', '.') : '';
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
$urlExport = \yii\helpers\Url::to(['revenue/export']);

$js = <<<JS
    
    document.getElementById('revenue-form').addEventListener('submit', function(e) {
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
                a.download = 'laporan-revenue.xlsx';
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

