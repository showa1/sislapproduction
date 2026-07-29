<?php
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $date_from string */
/* @var $date_to string */

$this->title = 'Rerata Respontime Tindakan';
$this->params['breadcrumbs'][] = ['label' => 'Laboratorium', 'url' => ['/laboratorium/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container-fluid p-0">
    <div class="row mb-3 align-items-center">
        <div class="col-md-8">
            <h1 class="page-title" style="font-weight: 800; color: #0F172A; font-size: 1.6rem; letter-spacing: -0.5px;">
                <i class="bi bi-stopwatch text-primary"></i> <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted" style="font-size: 0.9rem;">Analisis rata-rata waktu respon pelayanan berdasarkan jenis tindakan.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row align-items-end m-0']) ?>
                <div class="col-md-4 mb-3 mb-md-0 px-1">
                    <label class="form-label font-weight-bold text-muted small">Tanggal Mulai (Hasil Lab)</label>
                    <?= Html::input('date', 'date_from', $date_from, ['class' => 'form-control', 'style' => 'border-radius: 8px;']) ?>
                </div>
                <div class="col-md-4 mb-3 mb-md-0 px-1">
                    <label class="form-label font-weight-bold text-muted small">Tanggal Akhir (Hasil Lab)</label>
                    <?= Html::input('date', 'date_to', $date_to, ['class' => 'form-control', 'style' => 'border-radius: 8px;']) ?>
                </div>
                <div class="col-md-4 px-1">
                    <?= Html::submitButton('<i class="bi bi-search"></i> Tampilkan Data', ['class' => 'btn btn-primary w-100', 'style' => 'border-radius: 8px; font-weight: 600;']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden;">
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n<div class='card-footer d-flex justify-content-between align-items-center bg-white border-top p-3'>{summary}\n{pager}</div>",
                'pager' => [
                    'class' => \yii\widgets\LinkPager::class,
                    'options' => ['class' => 'pagination pagination-sm m-0'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link text-muted'],
                ],
                'tableOptions' => ['class' => 'table table-hover mb-0', 'style' => 'font-size: 0.9rem;'],
                'headerRowOptions' => ['style' => 'background-color: #F8FAFC;'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
                    [
                        'attribute' => 'daftartindakan_kode',
                        'label' => 'Kode Tindakan',
                        'format' => 'raw',
                        'value' => function($data) {
                            return "<span style='font-weight: 600; color: #002D72;'>" . Html::encode($data['daftartindakan_kode']) . "</span>";
                        }
                    ],
                    [
                        'attribute' => 'daftartindakan_nama',
                        'label' => 'Nama Tindakan',
                    ],
                    [
                        'attribute' => 'jumlah_pemeriksaan',
                        'label' => 'Jumlah Pemeriksaan',
                        'format' => 'integer',
                        'contentOptions' => ['class' => 'text-right font-weight-bold'],
                        'headerOptions' => ['class' => 'text-right'],
                    ],
                    [
                        'attribute' => 'rata_response_time',
                        'label' => 'Rata-rata Response Time',
                        'format' => 'raw',
                        'contentOptions' => ['class' => 'text-center'],
                        'headerOptions' => ['class' => 'text-center'],
                        'value' => function($data) {
                            $timeStr = Html::encode($data['rata_response_time']);
                            if (empty($timeStr)) return '-';
                            
                            // Determine color based on hours/minutes. This is a simple logic.
                            $color = '#10B981'; // Green by default
                            $parts = explode(':', $timeStr);
                            if (count($parts) >= 3) {
                                $hours = (int)$parts[0];
                                $minutes = (int)$parts[1];
                                if ($hours >= 1) $color = '#EF4444'; // Red if >= 1 hour
                                elseif ($minutes > 30) $color = '#F59E0B'; // Orange if > 30 mins
                            }
                            
                            return "<span class='badge' style='background-color: {$color}; color: #fff; padding: 5px 10px; font-weight: 600; border-radius: 6px; letter-spacing: 0.5px;'>" . $timeStr . "</span>";
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
