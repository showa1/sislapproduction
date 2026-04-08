<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Home - Priscilla Medical Center';

// Using PMC brand colors
$this->registerCss("
    body {
        background-color: #f4f6f9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Header styling with Glassmorphism */
    .dashboard-header {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        padding: 12px 0;
        margin-bottom: 40px;
        position: sticky;
        top: 0;
        z-index: 1000;
        border-bottom: 1px solid rgba(255, 255, 255, 0.4);
    }

    .pmc-logo-header {
        height: 50px;
        object-fit: contain;
    }

    .brand-name {
        color: #0c2340; /* Navy Blue */
        font-weight: 800;
        font-size: 1.3rem;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    /* Welcome Section */
    .welcome-section {
        margin-bottom: 48px;
    }
    .welcome-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #1a1a2e;
        margin-bottom: 8px;
    }
    .welcome-subtitle {
        color: #6c757d;
        font-size: 1.1rem;
    }

    /* Module Card Styling */
    .module-card {
        display: block;
        background: #ffffff;
        border-radius: 16px;
        padding: 35px 20px;
        text-align: center;
        text-decoration: none;
        color: #2b3445;
        box-shadow: 0 8px 25px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.03);
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .module-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transition: all 0.3s ease;
    }

    .module-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        color: #1a1a2e;
    }

    .module-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: inline-block;
        transition: transform 0.3s ease;
    }
    
    .module-card:hover .module-icon {
        transform: scale(1.1);
    }

    .module-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.4;
    }

    /* PMC Color Variants */
    .pmc-green .module-icon { color: #6DC536; }
    .pmc-green.module-card::before { background-color: #6DC536; }
    
    .pmc-blue .module-icon { color: #29ABE2; }
    .pmc-blue.module-card::before { background-color: #29ABE2; }

    /* Sign out button */
    .btn-signout {
        color: #0c2340;
        background: transparent;
        border: 1px solid rgba(12, 35, 64, 0.2);
        font-weight: 600;
        text-decoration: none;
        padding: 6px 16px;
        border-radius: 50rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }
    .btn-signout:hover {
        background: #0c2340;
        color: #ffffff;
        border-color: #0c2340;
    }
    .btn-signout i {
        font-size: 1.1rem;
    }
    
    /* User Profile */
    .user-avatar {
        width: 42px;
        height: 42px;
        background-color: #e3f2fd;
        color: #29ABE2;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .session-badge {
        background-color: rgba(109, 197, 54, 0.15); /* Light Lime */
        color: #55a626; /* Darker Lime / Green */
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .header-divider {
        width: 1px;
        height: 30px;
        background-color: rgba(0,0,0,0.1);
        margin: 0 8px;
    }
");

// Dapatkan nama pengguna (jika tidak ada fallback ke 'User')
$username = !Yii::$app->user->isGuest ? Yii::$app->user->identity->nama_pemakai : 'User';
?>

<!-- Header -->
<header class="dashboard-header">
    <div class="container d-flex justify-content-between align-items-center">
        <!-- Logo -->
        <!-- Logo & Branding -->
        <a href="<?= Url::to(['site/home']) ?>" class="d-flex align-items-center text-decoration-none gap-3">
            <img src="<?= Yii::getAlias('@web') ?>/template/images/pmc.png" alt="PMC Logo" class="pmc-logo-header">
            <div class="d-none d-sm-block">
                <div class="brand-name">Priscilla Medical Center</div>
            </div>
        </a>

        <!-- User Profile & Sign Out -->
        <div class="d-flex align-items-center gap-3">
            
            <!-- User Info & Avatar -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold" style="color: #1a1a2e; font-size: 0.95rem; line-height: 1.2;"><?= Html::encode($username) ?></div>
                    <div class="badge rounded-pill session-badge mt-1">
                        Active Session <i class="bi bi-circle-fill ms-1" style="font-size: 0.45rem; vertical-align: middle;"></i>
                    </div>
                </div>
                <!-- Avatar Initial -->
                <div class="rounded-circle user-avatar fw-bold shadow-sm">
                    <?= strtoupper(substr($username, 0, 1)) ?>
                </div>
            </div>

            <div class="header-divider d-none d-md-block"></div>

            <!-- Sign Out Button -->
            <?= Html::beginForm(['site/logout'], 'post', ['class' => 'm-0']) ?>
            <button type="submit" class="btn btn-link btn-signout">
                <span class="d-none d-md-inline">Sign Out</span>
                <i class="bi bi-box-arrow-right"></i>
            </button>
            <?= Html::endForm() ?>
        </div>
    </div>
</header>

<!-- Main Content -->
<div class="container pb-5">

    <!-- Welcome Section -->
    <div class="welcome-section text-center">
        <h1 class="welcome-title">Selamat Datang, <?= Html::encode($username) ?>!</h1>
        <p class="welcome-subtitle">Silahkan pilih modul layanan yang ingin Anda akses hari ini.</p>
    </div>

    <!-- Modular Grid -->
    <div class="row g-4 justify-content-center">
        <?php foreach ($modul as $key => $value): ?>
            <?php
            $icon = isset($value['icon']) ? $value['icon'] : 'bi bi-grid';
            $colorClass = isset($value['colorClass']) ? $value['colorClass'] : 'pmc-blue';
            ?>
            <div class="col-sm-6 col-md-4 col-lg-4">
                <a href="<?= Url::to([$value['url']]) ?>" class="module-card <?= $colorClass ?>">
                    <i class="<?= $icon ?> module-icon"></i>
                    <h5 class="module-title"><?= $value['label'] ?></h5>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

</div>