<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Buku Besar';
    $this->params['breadcrumbs'][] = $this->title;

    $this->registerCssFile('@web/template/vendors/select2/select2.min.css');
    $this->registerCssFile('@web/template/vendors/select2-bootstrap-theme/select2-bootstrap.min.css');
    $this->registerJsFile('@web/template/vendors/select2/select2.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
    
    $this->registerCss("
        .bb-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 45, 114, 0.06);
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .bb-card-header {
            background: linear-gradient(135deg, #002D72 0%, #0047AB 100%);
            color: #ffffff;
            padding: 18px 24px;
            border-bottom: none;
        }
        .bb-card-header h2 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        .bb-filter-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
        }
        .bb-filter-label i {
            color: #002D72;
            margin-right: 6px;
            font-size: 1rem;
        }
        
        /* Custom Select2 Styling */
        .select2-container--bootstrap .select2-selection--single {
            height: 42px !important;
            padding: 6px 12px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 0.9rem !important;
            background-color: #ffffff !important;
        }
        .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            color: #1e293b !important;
        }
        .select2-container--bootstrap .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 12px !important;
        }
        .select2-container--bootstrap.select2-container--focus .select2-selection,
        .select2-container--bootstrap.select2-container--open .select2-selection {
            border-color: #002D72 !important;
            box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.12) !important;
        }
        .select2-dropdown {
            border-radius: 8px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            border: 1px solid #cbd5e1 !important;
            z-index: 1060 !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: #002D72 !important;
        }

        /* Inputs & Buttons */
        .bb-input {
            height: 42px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 0.9rem !important;
            padding: 6px 12px !important;
            transition: all 0.2s ease;
        }
        .bb-input:focus {
            border-color: #002D72 !important;
            box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.12) !important;
        }
        
        .btn-bb-primary {
            background-color: #002D72 !important;
            border-color: #002D72 !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 9px 22px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-bb-primary:hover {
            background-color: #001f52 !important;
            box-shadow: 0 4px 14px rgba(0, 45, 114, 0.25);
            color: #ffffff !important;
        }
        .btn-bb-reset {
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 8px;
            background-color: #f8fafc !important;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-bb-reset:hover {
            background-color: #fee2e2 !important;
            color: #ef4444 !important;
            border-color: #fca5a5 !important;
        }
        .btn-bb-excel {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 9px 22px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-bb-excel:hover {
            background-color: #059669 !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25);
            color: #ffffff !important;
        }

        /* Account Cards Aesthetics */
        .account-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            margin-bottom: 30px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .account-card:hover {
            box-shadow: 0 6px 20px rgba(0, 45, 114, 0.07);
        }
        .account-card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .coa-badge {
            background-color: #002D72;
            color: #ffffff;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 6px;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
        }
        .account-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-left: 8px;
        }
        .account-table {
            margin-bottom: 0;
            width: 100%;
        }
        .account-table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 0.825rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            border-bottom: 2px solid #cbd5e1;
            white-space: nowrap;
        }
        .account-table tbody td {
            padding: 11px 16px;
            font-size: 0.875rem;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .account-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .row-saldo-awal {
            background-color: #eff6ff !important;
            font-weight: 600;
        }
        .row-saldo-awal td {
            color: #1e40af !important;
            border-bottom: 1px solid #dbeafe !important;
        }
        .account-table tfoot tr {
            background: #f8fafc;
            font-weight: 700;
        }
        .account-table tfoot td {
            padding: 12px 16px;
            border-top: 2px solid #cbd5e1;
            color: #0f172a;
            font-size: 0.875rem;
        }
        .num-col {
            text-align: right;
            font-family: 'JetBrains Mono', 'Roboto Mono', consolas, monospace;
        }
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            background: #ffffff;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }
        .empty-state i {
            font-size: 3.5rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }
        .empty-state h4 {
            color: #475569;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .empty-state p {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        .summary-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 50px;
            background: #e0f2fe;
            color: #0369a1;
        }
    ");

    $resetUrl = \yii\helpers\Url::to(['buku-besar/index']);
?>

<div class="row">
    <div class="col-12">
        <!-- Main Form Filter Card -->
        <div class="bb-card">
            <div class="bb-card-header d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-0"><i class="bi bi-book-half me-2"></i>Laporan Buku Besar</h2>
                </div>
                <?php if (!empty($data)): ?>
                    <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold fs-6">
                        <i class="bi bi-layers me-1 text-primary"></i> <?= count($data) ?> COA / Rekening
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="p-4">
                <?= Html::beginForm(['/keuangan/buku-besar/index'], 'get', ['id' => 'buku-besar-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                
                <div class="row g-3 mb-4">
                    <!-- Tanggal Filter -->
                    <div class="col-lg-4 col-md-12">
                        <label class="bb-filter-label"><i class="bi bi-calendar3"></i> Periode Tanggal</label>
                        <?= DatePicker::widget([
                            'type' => DatePicker::TYPE_RANGE,
                            'name' => 'date_from',
                            'value' => $dropdownselect['start'],
                            'name2' => 'date_to',
                            'value2' => $dropdownselect['to'], 
                            'separator' => '<span class="px-2 text-muted fw-bold">s/d</span>',
                            'layout' => '{input1}{separator}{input2}',
                            'options' => [
                                'placeholder' => 'Tanggal Awal',
                                'class' => 'form-control bb-input',
                                'autocomplete' => 'off',
                                'required' => true,
                            ],
                            'options2' => [
                                'placeholder' => 'Tanggal Akhir',
                                'class' => 'form-control bb-input',
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

                    <!-- Filter COA (kdrekening5) -->
                    <div class="col-lg-5 col-md-7">
                        <label class="bb-filter-label"><i class="bi bi-journal-bookmark"></i> COA / Kode Rekening (kdrekening5)</label>
                        <?= Html::dropDownList('kdrekening5', $dropdownselect['kdrekening5'] ?? '', $listRekening ?? [], [
                            'prompt' => '-- Semua COA / Rekening --',
                            'class' => 'form-select select2-coa',
                            'id' => 'filter-kdrekening5',
                        ]) ?>
                    </div>

                    <!-- Filter No. Referensi -->
                    <div class="col-lg-3 col-md-5">
                        <label class="bb-filter-label"><i class="bi bi-hash"></i> No. Referensi</label>
                        <?= Html::textInput('no_referensi', $dropdownselect['no_referensi'] ?? '', [
                            'class' => 'form-control bb-input',
                            'placeholder' => 'Cari No. Referensi...',
                            'autocomplete' => 'off',
                        ]) ?>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?= Html::submitButton('<i class="bi bi-search"></i> Cari Data', ['class' => 'btn-bb-primary']) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Reset Filter', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn-bb-reset']) ?>
                    </div>
                    <div>
                        <?= Html::button('<i class="bi bi-file-earmark-excel"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn-bb-excel']) ?>
                    </div>
                </div>

                <?= Html::endForm() ?>
            </div>
        </div>

        <!-- Data Results Area -->
        <?php if (!empty($data)): ?>
            <div class="mb-4">
                <?php foreach ($data as $key => $row): ?>
                    <div class="account-card">
                        <!-- Account Header -->
                        <div class="account-card-header">
                            <div class="d-flex align-items-center">
                                <span class="coa-badge"><?= Html::encode($key) ?></span>
                                <span class="account-name"><?= Html::encode($row['nama']) ?></span>
                            </div>
                            <div>
                                <span class="summary-badge">
                                    <i class="bi bi-info-circle me-1"></i> Saldo Normal: <?= Html::encode($row['nb'] ?? 'D') ?>
                                </span>
                            </div>
                        </div>

                        <!-- Account Table -->
                        <div class="table-responsive">
                            <table class="table account-table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 110px;">Tanggal</th>
                                        <th style="width: 75px; text-align: center;">Tp</th>
                                        <th style="width: 140px;">No. Ref.</th>
                                        <th>Keterangan</th>
                                        <th class="num-col" style="width: 140px;">Debit</th>
                                        <th class="num-col" style="width: 140px;">Kredit</th>
                                        <th class="num-col" style="width: 150px;">Saldo</th>
                                    </tr>	
                                </thead>			
                                <tbody>
                                    <?php
                                        $saldodebit = (float)($row['saldo_awal_debit'] ?? 0);
                                        $saldokredit = (float)($row['saldo_awal_kredit'] ?? 0);
                                        $totalsaldo = $saldodebit + $saldokredit;
                                    ?>
                                    <tr class="row-saldo-awal">
                                        <td colspan="4">
                                            <i class="bi bi-wallet2 me-1"></i> <b>Saldo Awal</b>
                                        </td>
                                        <td class="num-col"><?= (!empty($saldodebit) ? number_format($saldodebit, 2, ',', '.') : '-') ?></td>
                                        <td class="num-col"><?= (!empty($saldokredit) ? number_format($saldokredit, 2, ',', '.') : '-') ?></td>
                                        <td class="num-col"><b><?= number_format($totalsaldo, 2, ',', '.') ?></b></td>
                                    </tr>

                                    <?php 
                                        $subdebit = 0;
                                        $subkredit = 0;
                                        $saldo = 0;
                                        $subsaldo = 0;
                                    ?>

                                    <?php if (!empty($row['data']) && is_array($row['data'])): ?>
                                        <?php foreach ($row['data'] as $detail): ?>
                                            <?php
                                                $debit  = (float) ($detail['saldodebit'] ?? 0);
                                                $kredit = (float) ($detail['saldokredit'] ?? 0);

                                                $subdebit  += $debit;
                                                $subkredit += $kredit;

                                                $saldonormal = $detail['saldonormal'] ?? 'D';
                                                if ($saldonormal === 'K') {
                                                    $subsaldo += $kredit - $debit;
                                                    $saldo += $kredit - $debit;
                                                } else {
                                                    $subsaldo += $debit - $kredit;
                                                    $saldo += $debit - $kredit;
                                                }

                                                $psaldo = number_format(abs((float) str_replace(',', '', trim($saldo))), 2, ',', '.');
                                                if ($saldo < 0) {
                                                    $psaldo = '(' . $psaldo . ')';
                                                }
                                            ?>
                                            <tr>
                                                <td><?= date('d-m-Y', strtotime($detail['tglbukubesar'])) ?></td>
                                                <td class="text-center"><span class="badge bg-light text-dark border"><?= Html::encode($detail['jeniskode']) ?></span></td>
                                                <td><code class="text-dark"><?= Html::encode($detail['noreferensi']) ?></code></td>
                                                <td><?= Html::encode($detail['uraiantransaksi']) ?></td>
                                                <td class="num-col"><?= ($debit > 0 ? number_format($debit, 2, ',', '.') : '-') ?></td>
                                                <td class="num-col"><?= ($kredit > 0 ? number_format($kredit, 2, ',', '.') : '-') ?></td>
                                                <td class="num-col fw-semibold"><?= $psaldo ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">
                                                <small><i>Tidak ada transaksi mutasi untuk periode ini.</i></small>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
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
                                    <tr>
                                        <td><b>Saldo Awal:</b></td>
                                        <td colspan="2"><?= number_format(abs($totalsaldo), 2, ',', '.') ?></td>
                                        <td><b class="text-primary">Total Mutasi:</b></td>
                                        <td class="num-col"><b><?= $subde ?></b></td>
                                        <td class="num-col"><b><?= $subkre ?></b></td>
                                        <td class="num-col"><span class="badge bg-primary px-2 py-1"><?= $subsal ?></span></td>
                                    </tr>
                                    <tr style="background-color: #f1f5f9;">
                                        <td><b>Saldo Akhir:</b></td>
                                        <td colspan="2"><span class="fw-bold text-dark fs-6"><?= $subakhir ?></span></td>
                                        <td colspan="4" class="text-end text-muted small align-middle">
                                            <i>Detail pergerakan saldo rekening <b><?= Html::encode($key) ?></b></i>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($dropdownselect['start'])): ?>
            <!-- Active Search but No Data Found -->
            <div class="empty-state">
                <i class="bi bi-journal-x"></i>
                <h4>Data Buku Besar Tidak Ditemukan</h4>
                <p>Tidak ada transaksi atau saldo pada periode/filter yang dipilih. Silakan sesuaikan kata kunci atau rentang tanggal.</p>
            </div>
        <?php else: ?>
            <!-- Initial Empty State -->
            <div class="empty-state">
                <i class="bi bi-funnel"></i>
                <h4>Silakan Pilih Filter Laporan</h4>
                <p>Tentukan Periode Tanggal, Kode Rekening (COA), atau No. Referensi di atas lalu klik tombol <b>Cari Data</b>.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$urlExport = \yii\helpers\Url::to(['buku-besar/export']);

$js = <<<JS
    // Initialize Select2 for COA dropdown
    if ($.fn.select2) {
        $('.select2-coa').select2({
            placeholder: '-- Semua COA / Rekening --',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap'
        });
    }

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
        let date_from = document.getElementsByName("date_from")[0] ? document.getElementsByName("date_from")[0].value : '';
        let date_to = document.getElementsByName("date_to")[0] ? document.getElementsByName("date_to")[0].value : '';
        let no_referensi = document.getElementsByName("no_referensi")[0] ? document.getElementsByName("no_referensi")[0].value : '';
        let kdrekening5 = document.getElementsByName("kdrekening5")[0] ? document.getElementsByName("kdrekening5")[0].value : '';

        if (!date_from || !date_to) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan isi tanggal awal dan tanggal akhir terlebih dahulu.'
            });
            return;
        }

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
            data: { date_from: date_from, date_to: date_to, no_referensi: no_referensi, kdrekening5: kdrekening5, cari: 'aktif' },
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
