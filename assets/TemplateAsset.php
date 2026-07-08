<?php

namespace app\assets;

use yii\web\AssetBundle;

class TemplateAsset extends AssetBundle
{
    public $sourcePath = '@webroot/template';
    public $css = [
        'vendors/simple-line-icons/css/simple-line-icons.css',
        'vendors/flag-icon-css/css/flag-icons.min.css',
        'vendors/css/vendor.bundle.base.css',
        'vendors/font-awesome/css/font-awesome.min.css',
        'vendors/jvectormap/jquery-jvectormap.css',
        'vendors/daterangepicker/daterangepicker.css',
        'vendors/chartist/chartist.min.css',
        'css/vertical-light-layout/style.css',
    ];
    public $js = [
        'vendors/chart.js/chart.umd.js',
        'js/jquery.cookie.js',
        'js/off-canvas.js',
        'js/hoverable-collapse.js',
        'js/misc.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
