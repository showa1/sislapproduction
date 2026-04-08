<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Login';

$this->registerCss("
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background: #eef2f7;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow: hidden;
    }

    .login-wrapper {
        display: flex;
        width: 900px;
        min-height: 520px;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    }

    /* ===== LEFT PANEL ===== */
    .login-left {
        flex: 1;
        background: #ffffff;
        padding: 48px 44px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-logo {
        width: 72px;
        height: 72px;
        object-fit: contain;
        display: block;
        margin: 0 auto 16px;
    }

    .login-title {
        text-align: center;
        font-size: 18px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 4px;
    }

    .login-subtitle {
        text-align: center;
        font-size: 13px;
        color: #7a8799;
        margin-bottom: 28px;
    }

    .field-group {
        position: relative;
        margin-bottom: 16px;
    }

    .field-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #a0aab4;
        font-size: 16px;
        pointer-events: none;
        z-index: 2;
    }

    .field-divider {
        position: absolute;
        left: 40px;
        top: 50%;
        transform: translateY(-50%);
        width: 1px;
        height: 22px;
        background: #d8dde6;
        z-index: 2;
    }

    .login-input {
        width: 100% !important;
        padding: 12px 16px 12px 56px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 10px !important;
        font-size: 14px !important;
        color: #1a1a2e !important;
        background: #f8fafc !important;
        outline: none !important;
        box-shadow: none !important;
        transition: border-color 0.25s, box-shadow 0.25s !important;
    }

    .login-input:focus {
        border-color: #1e4ed8 !important;
        box-shadow: 0 0 0 3px rgba(30,78,216,0.1) !important;
        background: #fff !important;
    }

    .login-input::placeholder {
        color: #b0bac6;
    }

    .login-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 8px 0 20px;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #5a6475;
        cursor: pointer;
    }

    .remember-me input[type=checkbox] {
        width: 16px;
        height: 16px;
        accent-color: #1e4ed8;
        cursor: pointer;
    }

    .forgot-link {
        font-size: 13px;
        color: #1e4ed8;
        text-decoration: none;
        font-weight: 500;
    }

    .forgot-link:hover { text-decoration: underline; }

    .btn-login {
        width: 100%;
        padding: 13px;
        background: #1e4ed8;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: background 0.25s, transform 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-login:hover {
        background: #1639b0;
        transform: translateY(-1px);
    }

    .alert-danger {
        background: #fff0f0;
        border: 1px solid #ffcdd2;
        color: #c0392b;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13px;
        margin-bottom: 16px;
    }

    /* ===== RIGHT PANEL ===== */
    .login-right {
        flex: 1;
        position: relative;
        background: linear-gradient(135deg, #1e4ed8 0%, #0ea5e9 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 40px;
        overflow: hidden;
    }

    .right-bg-photo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.25;
        z-index: 0;
    }

    .right-blob {
        position: absolute;
        bottom: -60px;
        right: -60px;
        width: 260px;
        height: 260px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
        z-index: 0;
    }

    .right-blob-2 {
        position: absolute;
        top: -40px;
        left: -40px;
        width: 180px;
        height: 180px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
        z-index: 0;
    }

    .right-content {
        position: relative;
        z-index: 1;
        text-align: center;
        color: #fff;
    }

    .right-content h1 {
        font-size: 28px;
        font-weight: 900;
        letter-spacing: 1px;
        margin-bottom: 10px;
        text-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    .right-content h2 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .right-content p {
        font-size: 13px;
        opacity: 0.85;
        margin-bottom: 28px;
        line-height: 1.6;
    }

    .right-photo-card {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 62%;
        height: 55%;
        object-fit: cover;
        border-radius: 60px 0 0 0;
        z-index: 0;
        opacity: 0.85;
    }

    .btn-signup {
        display: inline-block;
        padding: 12px 36px;
        background: #fff;
        color: #1e4ed8;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 1px;
        text-decoration: none;
        cursor: pointer;
        text-transform: uppercase;
        transition: background 0.2s, transform 0.15s;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }

    .btn-signup:hover {
        background: #e8eeff;
        transform: translateY(-1px);
        color: #1639b0;
    }

    /* Error fields */
    .help-block { font-size: 12px; color: #e74c3c; margin-top: 4px; }
    .has-error .login-input { border-color: #e74c3c !important; }
");
?>

<div class="login-wrapper">

    <!-- ===== LEFT: Form Panel ===== -->
    <div class="login-left">

        <!-- Logo -->
        <img src="<?= Yii::getAlias('@web') ?>/template/images/pmc.png" alt="PMC Logo" class="login-logo">

        <div class="login-title">Priscilla Medical Center - SISLAP V26.1</div>
        <div class="login-subtitle">Sistem Iformasi Eksekutif</div>

        <?php if ($model->hasErrors()): ?>
            <div class="alert-danger">
                <?= implode('<br>', $model->getFirstErrors()) ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['autocomplete' => 'off'],
        ]); ?>

        <!-- Username Field -->
        <div class="field-group">
            <span class="field-icon">&#x1F4CB;</span>
            <span class="field-divider"></span>
            <?= $form->field($model, 'nama_pemakai', [
                'template' => '{input}{error}',
                'options' => ['class' => ''],
                'inputOptions' => [
                    'class' => 'login-input',
                    'placeholder' => 'Input your user ID or Email',
                    'autocomplete' => 'username',
                ],
            ])->label(false) ?>
        </div>

        <!-- Password Field -->
        <div class="field-group">
            <span class="field-icon">&#x1F511;</span>
            <span class="field-divider"></span>
            <?= $form->field($model, 'katakunci_pemakai', [
                'template' => '{input}{error}',
                'options' => ['class' => ''],
                'inputOptions' => [
                    'class' => 'login-input',
                    'placeholder' => 'Input your password',
                    'autocomplete' => 'current-password',
                ],
            ])->passwordInput()->label(false) ?>
        </div>

        <!-- Remember Me + Forgot Password -->
        <div class="login-options">
            <label class="remember-me">
                <input type="checkbox" name="remember_me" id="remember_me">
                Remember me
            </label>
            <a href="#" class="forgot-link">Forgot Password?</a>
        </div>

        <!-- Login Button -->
        <?= Html::submitButton(
            '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right:6px"><path d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/><path d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg> LOG IN',
            ['class' => 'btn-login', 'name' => 'login-button']
        ) ?>

        <?php ActiveForm::end(); ?>
    </div>

    <!-- ===== RIGHT: Welcome Panel ===== -->
    <div class="login-right">
        <img src="<?= Yii::getAlias('@web') ?>/template/images/login/RSPMC.png" alt="Hospital" class="right-bg-photo">
        <div class="right-blob"></div>
        <div class="right-blob-2"></div>

        <!-- Bottom-right photo card -->
        <img src="<?= Yii::getAlias('@web') ?>/template/images/login/admisi.png" alt="Staff" class="right-photo-card">

        <div class="right-content">
            <h1>SELAMAT DATANG DI PMC!</h1>
            <h2>Akses Layanan Kesehatan Anda</h2>
            <p>Gabung dan Mulai Journey Kesehatan Bersama Kami</p>
            <a href="#" class="btn-signup">SIGNUP</a>
        </div>
    </div>

</div>