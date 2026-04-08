<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Laporan Rawat Jalan';
$menuItems = \Yii::$app->view->params['menuItems'] ?? [];

$this->registerCss("
    .breadcrumb-custom {
        background: transparent;
        padding: 0;
        margin-bottom: 20px;
        font-size: 0.9rem;
        color: #6c757d;
    }
    .breadcrumb-custom a {
        color: #29ABE2;
        text-decoration: none;
    }
    .breadcrumb-custom a:hover {
        text-decoration: underline;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: '>';
        color: #6c757d;
        padding-right: .5rem;
    }
    
    .report-banner {
        background: linear-gradient(135deg, rgba(41, 171, 226, 0.1), rgba(41, 171, 226, 0.2));
        border-left: 4px solid #29ABE2;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        color: #0c2340;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .report-banner i {
        font-size: 1.5rem;
        color: #29ABE2;
    }
    .report-banner h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .report-card {
        display: block;
        background: #ffffff;
        border-radius: 12px;
        padding: 20px 15px;
        text-align: center;
        text-decoration: none;
        color: #2b3445;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.04);
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .report-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #6DC536; /* Default PMC Green */
        transition: all 0.3s ease;
        transform: scaleX(0);
        transform-origin: left;
    }

    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        color: #1a1a2e;
    }
    
    .report-card:hover::before {
        transform: scaleX(1);
    }

    .report-icon {
        font-size: 2rem;
        margin-bottom: 12px;
        color: #29ABE2; /* PMC Blue */
        display: inline-block;
        transition: transform 0.3s ease;
    }
    
    .report-card:hover .report-icon {
        transform: scale(1.1);
        color: #6DC536;
    }

    .report-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.4;
    }
");
?>

<div class="container-fluid p-0">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb breadcrumb-custom">
        <li class="breadcrumb-item"><a href="<?= Url::to(['/site/home']) ?>"><i class="bi bi-house-door"></i> Home</a></li>
        <li class="breadcrumb-item"><a href="<?= Url::to(['/rawatjalan/default/index']) ?>">Rawat Jalan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Laporan</li>
      </ol>
    </nav>

    <!-- Sub-Header Banner -->
    <div class="report-banner">
        <i class="bi bi-info-circle"></i>
        <div>
            <h5>Silahkan pilih menu untuk membuka laporan</h5>
            <small class="text-muted">Pilih salah satu kartu laporan di bawah atau gunakan menu di sidebar.</small>
        </div>
    </div>

    <!-- Card Grid -->
    <div class="row g-4">
        <?php if(!empty($menuItems)): ?>
            <?php foreach ($menuItems as $value): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <a href="<?= Url::to([$value['url']]) ?>" class="report-card">
                        <i class="<?= $value['icon'] ?> report-icon"></i>
                        <h6 class="report-title"><?= $value['label'] ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Menu laporan tidak tersedia saat ini.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
