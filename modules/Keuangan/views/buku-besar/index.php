<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Buku Besar';
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

    $resetUrl = \yii\helpers\Url::to(['buku-besar/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Buku Besar</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/keuangan/buku-besar/index'], 'get', ['id' => 'buku-besar-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                <div class="row">
                    <div class="col-md-6 col-sm-12 p-3">
                        <label class="form-label mb-2 fs-5">Tanggal</label>
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
                    <div class="col-md-6 col-sm-12 p-3">
                        <label class="form-label mb-2 fs-5">No. Referensi</label>
                        <?= Html::textInput('no_referensi', $dropdownselect['no_referensi'] ?? '', [
                            'class' => 'form-control fs-6',
                            'style' => 'border: 1px solid grey; height: 38px;',
                            'placeholder' => 'No. Referensi',
                            'autocomplete' => 'off',
                        ]) ?>
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

                <div class="row" style="margin-top: 70px; margin-bottom:100px;">
                    <!-- Ini Loop untuk data -->
                    <?php foreach ($data as $key => $row): ?>
                        <div class="table-responsive" style="margin-bottom:70px;">
                            <table class="table">
                                <thead>
                                    <tr style="background-color:#edeff2;">
                                        <th style="background-color:inherit;color:dodgerblue;"><h4><?php echo $key ?></h4></th>
                                        <th style="background-color:inherit;color:dodgerblue;" colspan="6"><h4><?php echo $row['nama']; ?></h4></th>
                                    </tr>
                                    <tr>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>Tanggal</b></th>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>Tp</b></th>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>No. Ref.</b></th>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>Keterangan</b></th>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>Debit</b></th>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>Kredit</b></th>
                                        <th style="border:0;border-bottom:1px solid #000;"><b>Saldo</b></th>
                                    </tr>	
                                </thead>			
                                <tbody>
                                    <?php
                                        $saldodebit = $row['saldo_awal_debit'];
                                        $saldokredit = $row['saldo_awal_kredit'];
                                        $totalsaldo = $saldodebit + $saldokredit;
                                    ?>
                                    <tr style="border:none;">
                                        <td colspan="4" style="border:none;"><b>Saldo Awal :</b></td>
                                        <td style="border:none; text-align:right;"><?php echo (!empty($saldodebit) ? number_format($saldodebit, 2, ',', '.') : ''); ?></td>
                                        <td style="border:none; text-align:right;"><?php echo (!empty($saldokredit) ? number_format($saldokredit, 2, ',', '.') : ''); ?></td>
                                        <td style="border:none;"><?php echo number_format($totalsaldo, 2, ',', '.'); ?></td>
                                    </tr>
                                    <?php 
                                        $subdebit = 0;
                                        $subkredit = 0;
                                        $saldo = 0;
                                        $subsaldo = 0;
                                    ?>
                                    <?php foreach ($row['data'] as $detail): ?>
                                        <?php
                                            $debit  = (float) $detail['saldodebit'];
                                            $kredit = (float) $detail['saldokredit'];

                                            $subdebit  += $debit;
                                            $subkredit += $kredit;


                                            $saldonormal = $detail['saldonormal'] ?? 'D';
                                            if ($saldonormal === 'K') {
                                                $subsaldo += $kredit - $debit;
                                            } else {
                                                $subsaldo += $debit - $kredit;
                                            }

                                            // hitung perubahan saldo berdasarkan saldo normal
                                            if ($detail['saldonormal'] === 'K') {
                                                $saldo += $kredit - $debit;
                                            } else { // default: 'D' atau selain itu
                                                $saldo += $debit - $kredit;
                                            }

                                            // format saldo untuk print
                                            $psaldo = number_format(abs((float) str_replace(',', '', trim($saldo))), 2, ',', '.');
                                            if ($saldo < 0) {
                                                $psaldo = '(' . $psaldo . ')';
                                            }
                                            ?>
                                        <tr>
                                            <td><?php echo date('d-m-Y', strtotime($detail['tglbukubesar'])); ?></td>
                                            <td><?php echo $detail['jeniskode']; ?></td>
                                            <td><?php echo $detail['noreferensi']; ?></td>
                                            <td><?php echo $detail['uraiantransaksi']; ?></td>
                                            <td style="text-align:right;"><?php echo number_format(abs((float) str_replace(',', '', trim($detail['saldodebit']))), 2, ',', '.'); ?></td>
                                            <td style="text-align:right;"><?php echo number_format(abs((float) str_replace(',', '', trim($detail['saldokredit']))), 2, ',', '.'); ?></td>
                                            <td><?php echo $psaldo; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <?php 
                                        $subsal = number_format(abs($subsaldo), 2, ',', '.');
                                        if ($subsaldo < 0) {
                                            $subsal = '(' . $subsal . ')';
                                        }   

                                        $subkre = number_format(abs($subkredit), 2, ',', '.');
                                        if ($subkredit < 0) {
                                            $subkre = '(' . $subkre . ')';
                                        }

                                        $subde = number_format(abs($subdebit), 2, ',', '.');
                                        if ($subdebit < 0) {
                                            $subde = '(' . $subde . ')';
                                        }

                                        $saldoakhir = $totalsaldo + $subsaldo;
                                        $subakhir = number_format(abs($saldoakhir), 2, ',', '.');
                                        if ($saldoakhir < 0) {
                                            $subakhir = '(' . $subakhir . ')';
                                        }   
                                    ?>
                                    <tr style="background-color:#c2c4c6;">
                                        <td style="border:none;background-color:inherit"><b>Saldo Awal : </b></td>
                                        <td style="border:none;background-color:inherit" colspan="2"><?php echo number_format(abs($totalsaldo), 2, ',', '.'); ?></td>
                                        <td style="border:none;background-color:inherit"><b>Total :</b></td>
                                        <td style="border:none;background-color:inherit; text-align:right;"><b><?php echo $subde; ?></b></td>
                                        <td style="border:none;background-color:inherit; text-align:right;"><b><?php echo $subkre; ?></b></td>
                                        <td style="border:none;background-color:inherit; text-align:right;">&nbsp;</td>
                                    </tr>
                                    <tr style="background-color:#c2c4c6">
                                        <td style="border:none;background-color:inherit"><b>Saldo Akhir : </b></td>
                                        <td style="border:none;background-color:inherit" colspan="2"><?php echo $subakhir; ?></td>
                                        <td style="border:none;background-color:inherit"><b>Mutasi : </b></td>
                                        <td style="border:none;background-color:inherit; text-align:right;"><b><?php echo $subsal; ?></b></td>
                                        <td colspan="2" style="border:none;background-color:inherit; text-align:right;">&nbsp;</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$urlExport = \yii\helpers\Url::to(['buku-besar/export']);

$js = <<<JS
    
    document.getElementById('buku-besar-form').addEventListener('submit', function(e) {
        const startDate = document.querySelector('input[name="date_from"]').value;
        const endDate = document.querySelector('input[name="date_to"]').value;

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
        let no_referensi = document.getElementsByName("no_referensi")[0] ? document.getElementsByName("no_referensi")[0].value : '';

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
            data: { date_from: date_from, date_to: date_to, no_referensi: no_referensi, cari: 'aktif' },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data) {
                Swal.close();
                const url = window.URL.createObjectURL(new Blob([data]));
                const a = document.createElement('a');
                a.href = url;
                a.download = 'laporan-buku-besar.xlsx';
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
