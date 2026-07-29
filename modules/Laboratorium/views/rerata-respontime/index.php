<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $date_from string */
/* @var $date_to string */
/* @var $averageMinutes float|null */

$this->title = 'Rerata Respontime Pelayanan Laboratorium';
$this->params['breadcrumbs'][] = ['label' => 'Laboratorium', 'url' => ['/laboratorium/dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

// Format average to a readable string (e.g., "15m 30s")
$averageString = '-';
if ($averageMinutes !== null && $averageMinutes !== false) {
    $totalSeconds = round($averageMinutes * 60);
    $hours = floor($totalSeconds / 3600);
    $minutes = floor(($totalSeconds % 3600) / 60);
    $seconds = $totalSeconds % 60;
    
    $parts = [];
    if ($hours > 0) $parts[] = "{$hours} jam";
    if ($minutes > 0) $parts[] = "{$minutes} mnt";
    if ($seconds > 0 || empty($parts)) $parts[] = "{$seconds} dtk";
    
    $averageString = implode(' ', $parts);
}

$this->registerCss("
    .kpi-title { font-size: 1rem; color: #64748B; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
    .kpi-value { font-size: 2.5rem; color: #0F172A; font-weight: 800; margin-top: 10px; line-height: 1.2; }
    .kpi-icon { color: #3B82F6; font-size: 2rem; margin-bottom: 10px; }
");

?>
<div class="container-fluid p-0">
    <div class="row mb-3 align-items-center">
        <div class="col-md-8">
            <h1 class="page-title" style="font-weight: 800; color: #0F172A; font-size: 1.6rem; letter-spacing: -0.5px;">
                <i class="bi bi-clock-history text-primary"></i> <?= Html::encode($this->title) ?>
            </h1>
            <p class="text-muted" style="font-size: 0.9rem;">Analisis waktu respon pelayanan laboratorium berdasarkan tanggal hasil pemeriksaan.</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
        <div class="card-body">
            <?= Html::beginForm(['index'], 'get', ['class' => 'row align-items-end']) ?>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label font-weight-bold text-muted small">Tanggal Mulai (Hasil Lab)</label>
                    <?= Html::input('date', 'date_from', $date_from, ['class' => 'form-control', 'style' => 'border-radius: 8px;']) ?>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label class="form-label font-weight-bold text-muted small">Tanggal Akhir (Hasil Lab)</label>
                    <?= Html::input('date', 'date_to', $date_to, ['class' => 'form-control', 'style' => 'border-radius: 8px;']) ?>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                        <span class="text-muted small font-weight-bold" style="text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;"><i class="bi bi-stopwatch"></i> Rata-rata Waktu</span>
                        <span class="font-weight-bold text-info" style="font-size: 0.9rem;"><?= Html::encode($averageString) ?></span>
                    </div>
                    <?= Html::submitButton('<i class="bi bi-search"></i> Tampilkan Data', ['class' => 'btn btn-info w-100 text-white', 'style' => 'border-radius: 8px; font-weight: 600;']) ?>
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
                        'attribute' => 'no_rekam_medik',
                        'label' => 'No RM',
                        'format' => 'raw',
                        'value' => function($data) {
                            return "<span style='font-weight: 600; color: #002D72;'>" . Html::encode($data['no_rekam_medik']) . "</span>";
                        }
                    ],
                    [
                        'attribute' => 'nama_pasien',
                        'label' => 'Nama Pasien',
                        'value' => function($data) { return $data['nama_pasien']; }
                    ],
                    [
                        'attribute' => 'no_pendaftaran',
                        'label' => 'No Pendaftaran',
                    ],
                    [
                        'attribute' => 'no_masukpenunjang',
                        'label' => 'No Penunjang',
                    ],
                    [
                        'attribute' => 'tanggal_order',
                        'label' => 'Waktu Order (Kirim)',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'tgl_verif',
                        'label' => 'Waktu Verif (Terima)',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'response_time_minutes',
                        'label' => 'Response Time',
                        'format' => 'raw',
                        'value' => function($data) {
                            if ($data['response_time_minutes'] === null) return '-';
                            $mins = round($data['response_time_minutes']);
                            
                            $color = '#10B981'; // Green
                            if ($mins > 60) $color = '#F59E0B'; // Orange
                            if ($mins > 120) $color = '#EF4444'; // Red
                            
                            $totalSeconds = round($data['response_time_minutes'] * 60);
                            $hours = floor($totalSeconds / 3600);
                            $minutes = floor(($totalSeconds % 3600) / 60);
                            $seconds = $totalSeconds % 60;
                            $str = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                            
                            return "<span class='badge' style='background-color: {$color}; color: #fff; padding: 5px 10px; font-weight: 600; border-radius: 6px; letter-spacing: 0.5px;'>" . $str . "</span>";
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
