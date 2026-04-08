<?php

namespace app\assets;

use yii\web\AssetBundle;

class TemplateAsset extends AssetBundle
{
    public $basePath = '@webroot'; // Path fisik
    public $baseUrl = '@web'; // URL
    public $css = [
        'template/vendors/simple-line-icons/css/simple-line-icons.css',
        'template/vendors/flag-icon-css/css/flag-icons.min.css',
        'template/vendors/css/vendor.bundle.base.css',
        'template/vendors/font-awesome/css/font-awesome.min.css',
        'template/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css',
        'template/vendors/jvectormap/jquery-jvectormap.css',
        'template/vendors/daterangepicker/daterangepicker.css',
        'template/vendors/chartist/chartist.min.css',
        'template/css/vertical-light-layout/style.css',
    ];
    public $js = [
        // 'template/vendors/chart.js/chart.umd.js',
        // 'template/vendors/jvectormap/jquery-jvectormap.min.js',
        // 'template/vendors/jvectormap/jquery-jvectormap-world-mill-en.js',
        // 'template/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js',
        // 'template/vendors/moment/moment.min.js',
        // 'template/vendors/daterangepicker/daterangepicker.js',
        // 'template/vendors/chartist/chartist.min.js',
        // 'template/vendors/progressbar.js/progressbar.min.js',
        'template/js/jquery.cookie.js',
        'template/js/off-canvas.js',
        'template/js/hoverable-collapse.js',
        'template/js/misc.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
