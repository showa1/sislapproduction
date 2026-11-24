<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $dropdownselect array */
/* @var $statuscari bool */

$this->title = 'Laporan Neraca Saldo';
$this->params['breadcrumbs'][] = ['label' => 'Keuangan', 'url' => ['/keuangan/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$resetUrl  = Url::to(['/keuangan/neraca-saldo/index']);
$urlExport = Url::to(['/keuangan/neraca-saldo/export']);

$this->registerCss("
    /* Card Dasar & Header Gradien */
    .bb-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0, 45, 114, 0.06);
        background: #ffffff;
        overflow: hidden;
        margin-bottom: 25px;
    }
    .bb-card-header {
        background: linear-gradient(135deg, #002D72 0%, #0047AB 100%);
        color: #ffffff;
        padding: 16px 22px;
        border-bottom: none;
    }
    .bb-card-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: #ffffff;
        letter-spacing: 0.3px;
    }

    /* Form Filter Inputs */
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

    /* Tombol Aksi */
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

    /* Tabel Neraca Saldo */
    .tbl-neraca {
        margin-bottom: 0;
    }
    .tbl-neraca thead th {
        background-color: #002D72 !important;
        color: #ffffff !important;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
        vertical-align: middle;
        padding: 12px 14px;
        border: 1px solid #0045a5;
        white-space: nowrap;
    }
    .tbl-neraca tbody td {
        vertical-align: middle;
        font-size: 0.875rem;
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        color: #334155;
    }
    .tbl-neraca tbody tr:hover td {
        background-color: #f8fafc;
    }
    .tbl-neraca tr.total-row td {
        background-color: #e8f4fd !important;
        font-weight: 700 !important;
        color: #002D72 !important;
        border-top: 2px solid #002D72 !important;
        border-bottom: 2px solid #002D72 !important;
        font-size: 0.925rem;
    }
    .coa-badge {
        background-color: #f1f5f9;
        color: #002D72;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-family: 'JetBrains Mono', 'Roboto Mono', consolas, monospace;
        display: inline-block;
        border: 1px solid #cbd5e1;
    }
    .num-cell {
        font-family: 'JetBrains Mono', 'Roboto Mono', consolas, monospace;
        text-align: right;
        color: #1e293b;
    }

    /* Empty State */
    .empty-state {
        padding: 55px 20px;
        text-align: center;
        background: #ffffff;
    }
    .empty-state i {
        font-size: 3.5rem;
        color: #cbd5e1;
        margin-bottom: 15px;
        display: inline-block;
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
");
?>

<div class="row neraca-saldo-page">
    <div class="col-12">

        <!-- Title -->
        <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">
            <i class="bi bi-journal-bookmark-fill me-2"></i>Laporan Neraca Saldo
        </h2>

        <!-- ===== Filter Card ===== -->
        <div class="bb-card">
            <div class="bb-card-header d-flex align-items-center justify-content-between">
                <h2 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filter Laporan</h2>
                <span class="text-white-50 small"><i class="bi bi-info-circle me-1"></i>Pilih rentang tanggal pembukuan</span>
            </div>
            <div class="p-4">
                <?= Html::beginForm(['/keuangan/neraca-saldo/index'], 'get', ['id' => 'neraca-saldo-form']) ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                <div class="row g-3 mb-3">
                    <div class="col-md-6 col-lg-5">
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
                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?= Html::submitButton('<i class="bi bi-search"></i> Tampilkan Data', ['class' => 'btn-bb-primary']) ?>
                        <?= Html::a('<i class="bi bi-arrow-clockwise"></i> Reset Filter', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn-bb-reset']) ?>
                    </div>
                    <div>
                        <?= Html::button('<i class="bi bi-file-earmark-excel"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn-bb-excel']) ?>
                    </div>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <!-- ===== Data Table Card ===== -->
        <div class="bb-card">
            <div class="bb-card-header d-flex align-items-center justify-content-between">
                <h2 class="mb-0"><i class="bi bi-table me-2"></i>Rincian Neraca Saldo</h2>
                <?php if ($statuscari): ?>
                    <span class="badge bg-white text-dark rounded-pill px-3 py-2 fw-bold fs-6">
                        <i class="bi bi-check2-circle text-success me-1"></i> Data Terfilter
                    </span>
                <?php endif; ?>
            </div>
            <div class="p-0">
                <?php if (!$statuscari): ?>
                    <div class="empty-state">
                        <i class="bi bi-funnel"></i>
                        <h4>Silakan Terapkan Filter Tanggal</h4>
                        <p>Pilih rentang tanggal pada form di atas, lalu klik <strong>Tampilkan Data</strong> untuk memuat data Neraca Saldo.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'tableOptions' => [
                                'class' => 'table table-hover tbl-neraca',
                            ],
                            'rowOptions' => function ($model) {
                                if (($model['urutan'] ?? 1) == 2 || ($model['nama_akun'] ?? '') === 'TOTAL SALDO') {
                                    return ['class' => 'total-row'];
                                }
                                return [];
                            },
                            'columns' => [
                                [
                                    'header' => '#',
                                    'headerOptions' => ['style' => 'width: 50px; text-align: center;'],
                                    'contentOptions' => ['style' => 'text-align: center; font-weight: 500;'],
                                    'value' => function ($model, $key, $index) {
                                        if (($model['urutan'] ?? 1) == 2 || ($model['nama_akun'] ?? '') === 'TOTAL SALDO') {
                                            return '';
                                        }
                                        return $index + 1;
                                    }
                                ],
                                [
                                    'attribute' => 'no_akun',
                                    'label' => 'No. Akun',
                                    'format' => 'raw',
                                    'headerOptions' => ['style' => 'text-align: center; width: 140px;'],
                                    'contentOptions' => ['style' => 'text-align: center;'],
                                    'value' => function ($model) {
                                        if (($model['urutan'] ?? 1) == 2 || ($model['nama_akun'] ?? '') === 'TOTAL SALDO') {
                                            return '';
                                        }
                                        return !empty($model['no_akun']) ? '<span class="coa-badge">' . Html::encode($model['no_akun']) . '</span>' : '-';
                                    }
                                ],
                                [
                                    'attribute' => 'nama_akun',
                                    'label' => 'Nama Akun',
                                    'format' => 'raw',
                                    'headerOptions' => ['style' => 'text-align: left;'],
                                    'contentOptions' => function ($model) {
                                        if (($model['urutan'] ?? 1) == 2 || ($model['nama_akun'] ?? '') === 'TOTAL SALDO') {
                                            return ['style' => 'font-weight: 700; color: #002D72; font-size: 0.95rem;'];
                                        }
                                        return ['style' => 'font-weight: 600; color: #1e293b;'];
                                    },
                                    'value' => function ($model) {
                                        if (($model['urutan'] ?? 1) == 2 || ($model['nama_akun'] ?? '') === 'TOTAL SALDO') {
                                            return '<i class="bi bi-calculator-fill me-2"></i>' . Html::encode($model['nama_akun']);
                                        }
                                        return Html::encode($model['nama_akun'] ?? '');
                                    }
                                ],
                                [
                                    'attribute' => 'saldo_awal',
                                    'label' => 'Saldo Awal',
                                    'headerOptions' => ['style' => 'text-align: right; width: 160px;'],
                                    'contentOptions' => ['class' => 'num-cell'],
                                    'value' => function ($model) {
                                        return isset($model['saldo_awal']) ? number_format((float)$model['saldo_awal'], 2, ',', '.') : '0,00';
                                    }
                                ],
                                [
                                    'attribute' => 'debit',
                                    'label' => 'Debit',
                                    'headerOptions' => ['style' => 'text-align: right; width: 160px;'],
                                    'contentOptions' => ['class' => 'num-cell'],
                                    'value' => function ($model) {
                                        return isset($model['debit']) ? number_format((float)$model['debit'], 2, ',', '.') : '0,00';
                                    }
                                ],
                                [
                                    'attribute' => 'kredit',
                                    'label' => 'Kredit',
                                    'headerOptions' => ['style' => 'text-align: right; width: 160px;'],
                                    'contentOptions' => ['class' => 'num-cell'],
                                    'value' => function ($model) {
                                        return isset($model['kredit']) ? number_format((float)$model['kredit'], 2, ',', '.') : '0,00';
                                    }
                                ],
                                [
                                    'attribute' => 'saldo_akhir',
                                    'label' => 'Saldo Akhir',
                                    'headerOptions' => ['style' => 'text-align: right; width: 160px;'],
                                    'contentOptions' => function ($model) {
                                        $val = (float)($model['saldo_akhir'] ?? 0);
                                        $cls = 'num-cell fw-bold';
                                        if ($val < 0) {
                                            $cls .= ' text-danger';
                                        }
                                        return ['class' => $cls];
                                    },
                                    'value' => function ($model) {
                                        return isset($model['saldo_akhir']) ? number_format((float)$model['saldo_akhir'], 2, ',', '.') : '0,00';
                                    }
                                ],
                            ],
                            'layout' => "{items}\n<div class='row p-3'>
                                        <div class='col-md-6'>{pager}</div>
                                    </div>",
                        ]); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
$js = <<<JS
    document.getElementById('neraca-saldo-form').addEventListener('submit', function(e) {
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
                a.download = 'laporan-neraca-saldo.xlsx';
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
