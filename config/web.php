<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$cookieValidationKey = bin2hex(random_bytes(32));

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $cookieValidationKey,
            'enableCsrfValidation' => false,
            'csrfParam' => '_csrf',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => ['site/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => '@webroot/template/vendors/js',
                    'js' => [
                        'vendor.bundle.base.js',
                    ],
                ],
            ],
        ],
        'db' => $db,
        'db_dashboard' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:@app/runtime/dashboard.db',
            'charset' => 'utf8',
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
    'modules' => [
        'rawatjalan' => [
            'class' => 'app\modules\rawatjalan\Module',
        ],
        'farmasi' => [
            'class' => 'app\modules\farmasi\Module',
        ],
        'keuangan' => [
            'class' => 'app\modules\keuangan\Module',
        ],
        'eksekutif' => [
            'class' => 'app\modules\eksekutif\Module',
        ],
        'laboratorium' => [
            'class' => 'app\modules\laboratorium\Module',
        ],
        'pendaftaran' => [
            'class' => 'app\modules\Pendaftaran\Module',
        ],
        'rawatinap' => [
            'class' => 'app\modules\RawatInap\Module',
        ],
    ],
    'as access' => [
    'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'allow' => true,
                'actions' => ['login', 'error'], // Halaman yang boleh diakses tanpa login
            ],
            [
                'allow' => true,
                'roles' => ['@'], // Hanya pengguna yang sudah login
            ],
            [
                'allow' => false, // Blokir semua lainnya
            ],
        ],
    ],
];


return $config;
