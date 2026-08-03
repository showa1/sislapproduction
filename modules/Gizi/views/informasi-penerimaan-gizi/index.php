<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Informasi Penerimaan Gizi';
$this->params['breadcrumbs'][] = ['label' => 'Gizi', 'url' => ['/gizi/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="gizi-informasi-penerimaan">
    <?php Pjax::begin(['id' => 'pjax-grid-penerimaan-gizi', 'timeout' => false]); ?>

    <!-- Filter Toolbar -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body px-4 py-3">
            <?= Html::beginForm(['index'], 'get', ['id' => 'filterForm', 'data-pjax' => '1']) ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Dari Tanggal</label>
                        <input type="date" class="form-control" name="date_from" value="<?= Html::encode($dateFrom) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small fw-bold">Sampai Tanggal</label>
                        <input type="date" class="form-control" name="date_to" value="<?= Html::encode($dateTo) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small fw-bold">Pencarian (Supplier / Bahan / No Penerimaan)</label>
                        <input type="text" class="form-control" name="search" placeholder="Masukkan kata kunci..." value="<?= Html::encode($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 w-100"><i class="bi bi-filter"></i> Terapkan</button>
                    </div>
                </div>
                <div class="row mt-3 align-items-center">
                    <div class="col-6">
                        <a href="<?= Url::to(['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'search' => $search, 'export' => 1]) ?>" class="btn btn-sm btn-success" data-pjax="0" target="_blank">
                            <i class="bi bi-file-earmark-excel"></i> Download Excel
                        </a>
                    </div>
                    <div class="col-6 text-end">
                        <a href="<?= Url::to(['index']) ?>" class="btn btn-sm btn-light border text-muted"><i class="bi bi-arrow-clockwise"></i> Reset Filter</a>
                    </div>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center p-3 border-top'>{summary}\n{pager}</div>",
                    'tableOptions' => ['class' => 'table table-hover table-striped align-middle mb-0'],
                    'headerRowOptions' => ['class' => 'table-light text-muted small text-uppercase'],
                    'pager' => [
                        'class' => \yii\bootstrap5\LinkPager::class,
                        'options' => ['class' => 'pagination mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                        'disabledListItemSubTagOptions' => ['tag' => 'span', 'class' => 'page-link'],
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn', 'header' => 'No'],
                        [
                            'attribute' => 'nopenerimaanbahan',
                            'label' => 'No Penerimaan',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return Html::tag('span', Html::encode($model['nopenerimaanbahan']), ['class' => 'fw-bold text-primary']);
                            }
                        ],
                        [
                            'attribute' => 'tglterimabahan',
                            'label' => 'Tanggal Terima',
                            'format' => ['date', 'php:d M Y H:i']
                        ],
                        [
                            'attribute' => 'supplier_nama',
                            'label' => 'Supplier',
                        ],
                        [
                            'attribute' => 'namabahanmakanan',
                            'label' => 'Nama Bahan Makanan',
                        ],
                        [
                            'attribute' => 'qty_terima',
                            'label' => 'Qty',
                            'contentOptions' => ['class' => 'text-end'],
                            'headerOptions' => ['class' => 'text-end'],
                            'value' => function($model) {
                                return number_format((float)$model['qty_terima'], 2, ',', '.');
                            }
                        ],
                        [
                            'attribute' => 'harganettobhn',
                            'label' => 'Harga Netto',
                            'contentOptions' => ['class' => 'text-end'],
                            'headerOptions' => ['class' => 'text-end'],
                            'value' => function($model) {
                                return 'Rp ' . number_format((float)$model['harganettobhn'], 0, ',', '.');
                            }
                        ],
                        [
                            'attribute' => 'harga_total',
                            'label' => 'Total Harga',
                            'contentOptions' => ['class' => 'text-end fw-bold'],
                            'headerOptions' => ['class' => 'text-end'],
                            'value' => function($model) {
                                return 'Rp ' . number_format((float)$model['harga_total'], 0, ',', '.');
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <?php Pjax::end(); ?>
</div>
