<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\TemplateAsset;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

TemplateAsset::register($this);
$menuItems = \Yii::$app->view->params['menuItems'];

$this->registerCssFile('@web/js/sweetalert2/sweetalert2.min.css');
$this->registerJsFile('@web/js/sweetalert2/sweetalert2.all.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>

    <div class="container-scroller">
        <!-- Navbar -->
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
                <a class="navbar-brand brand-logo" href="<?= Url::to(['/rawatjalan/default/index']) ?>" style="text-decoration: none;">
                    <span
                        style="color: #002D72; font-weight: 800; font-size: 1.1rem; letter-spacing: 0.5px; font-family: 'Inter', sans-serif;">SISLAP
                        <span style="font-weight: 400; opacity: 0.7;">V26.1</span>
                </a>
                <a class="navbar-brand brand-logo-mini" href="<?= Url::to(['/rawatjalan/default/index']) ?>" style="text-decoration: none;">
                    <span
                        style="color: #002D72; font-weight: 800; font-size: 1rem; font-family: 'Inter', sans-serif;">S</span>
                </a>
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="icon-menu"></span>
                </button>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-center">
                <h5 class="mb-0 font-weight-medium d-none d-lg-flex">Welcome</h5>
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item">
                        <a href="/sislap/web/index.php?r=site/logout" class="nav-link">
                            <i class="dropdown-item-icon icon-power text-primary"></i> Sign Out
                        </a>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                    <span class="icon-menu"></span>
                </button>
            </div>
        </nav>

        <!-- Sidebar -->
        <div class="container-fluid page-body-wrapper">
            <?php
            $currentRoute = '/' . Yii::$app->controller->route;

            $this->registerCss("
            /* TOTAL WHITE OVERRIDE - Menghapus semua sisa tema gelap template */
            .navbar.default-layout-navbar,
            .navbar.default-layout-navbar .navbar-brand-wrapper,
            .navbar.default-layout-navbar .navbar-menu-wrapper,
            .sidebar,
            .sidebar-offcanvas,
            #sidebar,
            #sidebar-menu-list,
            .sidebar .nav,
            .sidebar .nav .nav-item,
            .page-body-wrapper,
            .navbar-brand-mini-wrapper,
            .sidebar-header {
                background-color: #ffffff !important;
                background-image: none !important;
            }
            
            /* Bersihkan sisa filter / pseudo-elements dari template */
            #sidebar::before,
            #sidebar::after,
            .sidebar::before,
            .sidebar::after {
                display: none !important;
            }

            .navbar.default-layout-navbar {
                box-shadow: 0 4px 15px rgba(0, 45, 114, 0.05); /* Shadow halus navy */
                z-index: 1050;
            }

            .sidebar-offcanvas {
                z-index: 1040;
            }
            
            /* Warna Ikon Hamburger */
            .navbar-toggler {
                position: relative;
                z-index: 1060 !important;
                display: flex !important;
                align-items: center;
                justify-content: center;
            }
            .navbar-toggler .icon-menu {
                color: #002D72 !important; /* Navy Blue PMC */
            }

            /* Container Sidebar */
            #sidebar {
                background: #ffffff !important;
                border-right: 1px solid rgba(43, 49, 91, 0.05); /* Batas pudar Navy PMC */
                box-shadow: 4px 0 15px rgba(0,0,0,0.03); /* Soft shadow tipis */
                padding-top: 20px !important; /* Jarak agar tidak menempel atas */
            }
            #sidebar .nav-link {
                color: #4a5568 !important; /* Teks Grey-Navy yang lebih lembut */
                font-family: 'Inter', 'Roboto', sans-serif; /* Modern typography */
                font-size: 14px;
                padding: 12px 18px;
                border-radius: 8px;
                margin: 4px 15px;
                transition: all 0.2s ease;
                display: flex;
                align-items: flex-start;
                white-space: normal;
                line-height: 1.4;
                border-left: 4px solid transparent; /* Outline kiri pasif */
            }
            #sidebar .nav-link i {
                font-size: 1.2rem;
                margin-right: 12px;
                color: #4a5568 !important; /* Ikon juga Grey-Navy */
                transition: all 0.2s ease;
                margin-top: -2px;
                opacity: 0.8; /* Sedikit lebih pudar agar tidak mendominasi teks */
            }
            #sidebar .nav-link:hover {
                background: rgba(0, 45, 114, 0.05) !important; /* Hover: Navy sangat lembut */
            }
            #sidebar .nav-link:hover i {
                opacity: 1; /* Ikon jelas saat hover */
            }
            #sidebar .nav.active > .nav-link,
            #sidebar .nav-item.active > .nav-link,
            #sidebar .nav-link.active {
                background: #F0FDF4 !important; /* Active: Latar Hijau Lime Sangat Lembut */
                color: #002D72 !important; /* Text Navy Terasa solid */
                font-weight: 700;
                border-left: 4px solid #6DC536 !important; /* Border Kiri Hijau Terang (Aksen) */
                border-radius: 0 8px 8px 0 !important; /* Kotak nempel ke pinggir layar sebelah kiri */
                margin-left: 0 !important; 
                padding-left: 29px !important; /* Kompensasi padding (18 asli + 15 margin dihilangkan - 4 border) */
            }
            #sidebar .nav-link.active i {
                color: #6DC536 !important; /* Ikon hijau menandakan aktif */
                opacity: 1;
            }
            
            /* Jarak Kolom Pencarian (Search Bar) */
            .sidebar-search {
                padding: 10px 0 25px 0 !important; /* Jarak proporsional */
                margin: 0;
            }
            @media (max-width: 991px) {
                .sidebar-search {
                    margin-top: 15px;
                }
            }
            .sidebar-search input {
                border-radius: 50px;
                border: 1px solid #e2e8f0;
                padding: 10px 16px;
                font-size: 0.85rem;
                background: #f8fafc;
                margin: 0 15px; /* Margin kiri kanan (floating) */
                width: calc(100% - 30px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.04); /* Efek melayang tipis */
                transition: all 0.2s ease;
            }
            .sidebar-search input:focus {
                box-shadow: 0 4px 12px rgba(41, 171, 226, 0.15);
                border-color: #29ABE2;
                background: #fff;
                outline: none;
            }
            
            /* Sembunyikan mini logo di sidebar desktop jika bikin overlap */
            @media (min-width: 992px) {
                #sidebar .navbar-brand-mini-wrapper {
                    display: none !important;
                }
            }
            
            /* Jarak Teks LAPORAN */
            .nav-category .nav-link {
                color: #a0aec0 !important;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-weight: 700;
                padding: 5px 18px 5px 18px !important; /* Jarak kiri (indent) 18px agar sejajar dengan ikon */
                margin-top: 10px !important; /* Margin atas agar tidak terlalu dekat dengan search */
                margin-bottom: 5px !important;
                background: transparent !important;
                pointer-events: none;
            }
        ");
            ?>
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav" id="sidebar-menu-list">
                    <li class="nav-item navbar-brand-mini-wrapper">
                        <a class="nav-link navbar-brand brand-logo-mini" href="<?= Url::to(['/rawatjalan/default/index']) ?>">
                            <img src="<?= Url::to('@web/assets/template/images/logo-mini.svg') ?>" alt="logo">
                        </a>
                    </li>

                    <li class="nav-item sidebar-search">
                        <input type="text" class="form-control" id="menu-search" placeholder="Cari laporan...">
                    </li>

                    <li class="nav-item <?= ($currentRoute == '/site/home') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/site/home') ? 'active' : '' ?>" href="<?= Url::to(['/site/home']) ?>">
                            <i class="bi bi-house-door"></i>
                            <span class="menu-title">Dashboard Utama</span>
                        </a>
                    </li>

                    <?php if (isset(Yii::$app->controller->module) && Yii::$app->controller->module->id === 'rawatjalan'): ?>
                    <li class="nav-item nav-category">
                        <span class="nav-link">Rawat Jalan</span>
                    </li>

                    <li class="nav-item menu-item-searchable <?= ($currentRoute == '/rawatjalan/dashboard/index') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/rawatjalan/dashboard/index') ? 'active' : '' ?>" href="<?= Url::to(['/rawatjalan/dashboard/index']) ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-title">Dashboard Rawat Jalan</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset(Yii::$app->controller->module) && Yii::$app->controller->module->id === 'eksekutif'): ?>
                    <li class="nav-item nav-category">
                        <span class="nav-link">Eksekutif</span>
                    </li>

                    <li class="nav-item menu-item-searchable <?= ($currentRoute == '/eksekutif/dashboard/index') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/eksekutif/dashboard/index') ? 'active' : '' ?>" href="<?= Url::to(['/eksekutif/dashboard/index']) ?>">
                            <i class="bi bi-speedometer"></i>
                            <span class="menu-title">Ringkasan Eksekutif</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset(Yii::$app->controller->module) && Yii::$app->controller->module->id === 'farmasi'): ?>
                    <li class="nav-item nav-category">
                        <span class="nav-link">Farmasi</span>
                    </li>

                    <li class="nav-item menu-item-searchable <?= ($currentRoute == '/farmasi/default/index') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/farmasi/default/index') ? 'active' : '' ?>" href="<?= Url::to(['/farmasi/default/index']) ?>">
                            <i class="bi bi-capsule"></i>
                            <span class="menu-title">Dashboard Farmasi</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset(Yii::$app->controller->module) && Yii::$app->controller->module->id === 'keuangan'): ?>
                    <li class="nav-item nav-category">
                        <span class="nav-link">Keuangan</span>
                    </li>

                    <li class="nav-item menu-item-searchable <?= ($currentRoute == '/keuangan/default/index') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/keuangan/default/index') ? 'active' : '' ?>" href="<?= Url::to(['/keuangan/default/index']) ?>">
                            <i class="bi bi-cash-coin"></i>
                            <span class="menu-title">Dashboard Keuangan</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset(Yii::$app->controller->module) && Yii::$app->controller->module->id === 'laboratorium'): ?>
                    <li class="nav-item nav-category">
                        <span class="nav-link">Laboratorium</span>
                    </li>

                    <li class="nav-item menu-item-searchable <?= ($currentRoute == '/laboratorium/dashboard/index') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/laboratorium/dashboard/index') ? 'active' : '' ?>" href="<?= Url::to(['/laboratorium/dashboard/index']) ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-title">Dashboard Laboratorium</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (isset(Yii::$app->controller->module) && Yii::$app->controller->module->id === 'pendaftaran'): ?>
                    <li class="nav-item nav-category">
                        <span class="nav-link">Pendaftaran & Penjadwalan</span>
                    </li>

                    <li class="nav-item menu-item-searchable <?= ($currentRoute == '/pendaftaran/dashboard/index') ? 'active' : '' ?>">
                        <a class="nav-link <?= ($currentRoute == '/pendaftaran/dashboard/index') ? 'active' : '' ?>" href="<?= Url::to(['/pendaftaran/dashboard/index']) ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span class="menu-title">Dashboard Pendaftaran</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item nav-category">
                        <span class="nav-link">Laporan</span>
                    </li>

                    <?php foreach ($menuItems as $value): ?>
                        <?php
                        $menuUrl = Url::to([$value['url']]);
                        $isActive = ($currentRoute == $value['url']) ? 'active' : '';
                        // Lewati "Dashboard Laboratorium" dari Module->menu agar tidak muncul dobel di sini
                        if ($value['url'] === '/laboratorium/dashboard/index') continue;
                        ?>
                        <li class="nav-item menu-item-searchable <?= $isActive ?>">
                            <a class="nav-link <?= $isActive ?>" href="<?= $menuUrl ?>">
                                <i class="<?= $value['icon'] ?>"></i>
                                <span class="menu-title"><?= $value['label'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <?php
            $this->registerJs("
            $('#menu-search').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#sidebar-menu-list .menu-item-searchable').filter(function() {
                    $(this).toggle($(this).find('.menu-title').text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Global shortcut for search (Ctrl+K or /)
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey && e.key === 'k') || (e.key === '/' && !$(e.target).is('input, textarea'))) {
                    e.preventDefault();
                    $('#menu-search').focus();
                }
            });
        ");
            ?>

            <!-- Main Panel -->
            <div class="main-panel">
                <div class="content-wrapper">
                    <?= $content ?>
                </div>
            </div>
        </div>
    </div>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>