<?php
    use yii\grid\GridView;
    use yii\widgets\ActiveForm;
    use yii\helpers\ArrayHelper;
    use yii\helpers\Html;
    use kartik\date\DatePicker;
    
    $this->title = 'Laporan Minimal Stok';
    $this->params['breadcrumbs'][] = $this->title;
    
    $this->registerCss("
        .custom-gridview thead th {
            background-color: #f5981b;
            font-size: 16px;
            text-align: center;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
            
    ");

    $resetUrl = \yii\helpers\Url::to(['minimal-stok/index']);
?>

<div class="row quick-action-toolbar">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-block d-md-flex">
                <h2 class="mb-0">Laporan Minimal Stok</h2>
            </div>
            <div class="d-md-flex row m-0 quick-action-btns">
                <?= Html::beginForm(['/farmasi/minimal-stok/index'], 'get') ?>
                <?= Html::hiddenInput('cari', 'aktif'); ?>
                <div class="row px-4 pt-3">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Ruangan</label>
                        <?php echo Html::dropDownList('ruangan', $dropdownselect['ruangan'], $listruangan, [
                            'class' => 'form-select',
                            'prompt' => 'Pilih Ruangan',
                            'required' => true,
                        ]); ?>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Kategori Obat Alkes</label>
                        <input type="text" class="form-control" name="kategori" value="<?= Html::encode(Yii::$app->request->get('kategori')) ?>" placeholder="Cari Kategori (mis. Generic)">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Kronis / Non Kronis</label>
                        <?php echo Html::dropDownList('kronis', Yii::$app->request->get('kronis'), [
                            'Kronis' => 'Kronis',
                            'Non Kronis' => 'Non Kronis',
                        ], [
                            'class' => 'form-select',
                            'prompt' => 'Semua',
                        ]); ?>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Kadaluarsa Dari</label>
                        <input type="date" class="form-control" name="date_from" value="<?= Html::encode(Yii::$app->request->get('date_from') ? explode(' ', Yii::$app->request->get('date_from'))[0] : '') ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Kadaluarsa Sampai</label>
                        <input type="date" class="form-control" name="date_to" value="<?= Html::encode(Yii::$app->request->get('date_to') ? explode(' ', Yii::$app->request->get('date_to'))[0] : '') ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-start flex-wrap">
                        <?= Html::submitButton('Cari', ['class' => 'btn btn-dark m-3']) ?>
                        <?= Html::a('Ulang', $resetUrl, ['id' => 'ulang-button', 'class' => 'btn btn-danger m-3']) ?>
                        <?= Html::button('Export', ['id' => 'export-button', 'class' => 'btn btn-primary m-3']) ?>
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
                            'rowOptions' => function ($model) {
                                return $model['minimal'] ? ['class' => 'table-danger'] : [];
                            },
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'ruangan_id',
                                    'label' => 'Ruangan ID'
                                ],
                                [
                                    'attribute' => 'ruangan_nama',
                                    'label' => 'Ruangan'
                                ],
                                [
                                    'attribute' => 'obatalkes_kategori',
                                    'label' => 'Kategori Obat Alkes'
                                ],
                                [
                                    'attribute' => 'obatalkes_kronis',
                                    'label' => 'Kronis/ Non Kronis'
                                ],
                                [
                                    'attribute' => 'obatalkes_kode',
                                    'label' => 'Kode Obat Alkes'
                                ],
                                [
                                    'attribute' => 'obatalkes_nama',
                                    'label' => 'Nama Obat Alkes'
                                ],
                                [
                                    'attribute' => 'tglkadaluarsa',
                                    'label' => 'Tanggal Kadaluarsa'
                                ],
                                [
                                    'attribute' => 'stok',
                                    'label' => 'Stok',
                                    'value' => function ($model) {
                                        return isset($model['stok']) ? number_format($model['stok'], 2, ',', '.') : '';
                                    }
                                ],
                                [
                                    'attribute' => 'jmlminimalstok',
                                    'label' => 'Jumlah Minimal Stok',
                                    'value' => function ($model) {
                                        return isset($model['jmlminimalstok']) ? number_format($model['jmlminimalstok'], 2, ',', '.') : '';
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
$urlExport = \yii\helpers\Url::to(['minimal-stok/export']);

$js = <<<JS

    $('#export-button').on('click', function() {
        let ruangan = document.getElementsByName("ruangan")[0].value;
        let kategori = document.getElementsByName("kategori")[0].value;
        let kronis = document.getElementsByName("kronis")[0].value;
        let date_from = document.getElementsByName("date_from")[0].value;
        let date_to = document.getElementsByName("date_to")[0].value;

        let url = "$urlExport";
        let separator = url.indexOf('?') === -1 ? '?' : '&';
        
        url += separator + "cari=aktif&ruangan=" + encodeURIComponent(ruangan);
        if(kategori) url += "&kategori=" + encodeURIComponent(kategori);
        if(kronis) url += "&kronis=" + encodeURIComponent(kronis);
        if(date_from) url += "&date_from=" + encodeURIComponent(date_from);
        if(date_to) url += "&date_to=" + encodeURIComponent(date_to);

        window.location.href = url;
    });
JS;
$this->registerJs($js);
?>

