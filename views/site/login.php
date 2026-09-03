<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Login - SISLAP V26.1';

$this->registerCss("
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        background-color: #ffffff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: #1c1e21;
        overflow-x: hidden;
    }

    .fb-page-wrapper {
        display: flex;
        width: 100%;
        max-width: 1180px;
        min-height: 640px;
        padding: 40px 60px;
        align-items: center;
        justify-content: space-between;
        gap: 60px;
    }

    /* ================= LEFT SECTION ================= */
    .fb-left-section {
        flex: 1.1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 520px;
        position: relative;
    }

    /* Brand Logo Header */
    .fb-brand-header {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
    }

    .fb-header-logo-img {
        height: 52px;
        max-width: 340px;
        object-fit: contain;
    }

    /* Hero Composition Area */
    .fb-hero-composition {
        position: relative;
        width: 100%;
        height: 260px;
        margin-bottom: 24px;
    }

    /* Main Featured Card 1 */
    .fb-card-main {
        position: absolute;
        top: 10px;
        left: 20px;
        width: 200px;
        height: 210px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        overflow: hidden;
        border: 1px solid #e4e6eb;
        transform: rotate(-4deg);
        transition: transform 0.3s ease;
    }

    .fb-card-main:hover {
        transform: rotate(-2deg) scale(1.02);
    }

    .fb-card-main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Main Featured Card 2 (Top Stack) */
    .fb-card-top {
        position: absolute;
        top: -15px;
        left: 170px;
        width: 190px;
        height: 220px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 16px 36px rgba(0,0,0,0.15);
        overflow: hidden;
        border: 1px solid #e4e6eb;
        transform: rotate(5deg);
        z-index: 2;
        transition: transform 0.3s ease;
    }

    .fb-card-top:hover {
        transform: rotate(3deg) scale(1.02);
    }

    .fb-card-top img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Time pill badge */
    .fb-pill-time {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(0, 100, 224, 0.85);
        backdrop-filter: blur(8px);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 12px;
        z-index: 3;
    }

    /* Floating Badges */
    .fb-emoji-badge {
        position: absolute;
        top: 0px;
        left: 0px;
        width: 44px;
        height: 44px;
        background: #ffb703;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        box-shadow: 0 8px 20px rgba(255, 183, 3, 0.4);
        z-index: 4;
        animation: floatAnim 4s ease-in-out infinite;
    }

    .fb-heart-badge {
        position: absolute;
        bottom: 30px;
        right: 80px;
        width: 42px;
        height: 42px;
        background: #ff3366;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 20px;
        box-shadow: 0 8px 20px rgba(255, 51, 102, 0.4);
        z-index: 4;
        animation: floatAnim 4s ease-in-out infinite 2s;
    }

    .fb-avatar-badge {
        position: absolute;
        bottom: 0px;
        left: 140px;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        border: 4px solid #ffffff;
        box-shadow: 0 10px 24px rgba(0,0,0,0.18);
        overflow: hidden;
        z-index: 5;
    }

    .fb-avatar-badge img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @keyframes floatAnim {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }

    /* Headline Typography */
    .fb-hero-title {
        font-size: 46px;
        font-weight: 800;
        line-height: 1.12;
        color: #1c1e21;
        letter-spacing: -1.2px;
    }

    .fb-hero-title .highlight {
        color: #0064e0;
    }

    /* ================= RIGHT SECTION ================= */
    .fb-right-section {
        flex: 0.9;
        display: flex;
        justify-content: center;
        width: 100%;
        max-width: 440px;
    }

    .fb-login-card {
        background: #ffffff;
        width: 100%;
        padding: 32px 28px;
        border-radius: 20px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.09), 0 2px 8px rgba(0,0,0,0.04);
        border: 1px solid #e4e6eb;
    }

    .fb-form-title {
        font-size: 22px;
        font-weight: 700;
        color: #1c1e21;
        margin-bottom: 24px;
        letter-spacing: -0.3px;
    }

    /* Input Field Formatting */
    .fb-input-wrapper {
        margin-bottom: 16px;
    }

    .fb-form-control {
        width: 100% !important;
        height: 54px !important;
        padding: 14px 18px !important;
        border: 1px solid #ccd0d5 !important;
        border-radius: 12px !important;
        font-size: 16px !important;
        color: #1c1e21 !important;
        background: #ffffff !important;
        outline: none !important;
        box-shadow: none !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    .fb-form-control:focus {
        border-color: #0064e0 !important;
        box-shadow: 0 0 0 3px rgba(0, 100, 224, 0.15) !important;
    }

    .fb-form-control::placeholder {
        color: #8a8d91;
    }

    /* Log in Button */
    .fb-btn-login {
        width: 100%;
        height: 50px;
        background-color: #0064e0;
        color: #ffffff;
        border: none;
        border-radius: 25px;
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.1s ease;
        margin-top: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fb-btn-login:hover {
        background-color: #0052b8;
    }

    .fb-btn-login:active {
        transform: scale(0.99);
    }

    /* Forgotten password link */
    .fb-forgot-container {
        text-align: center;
        margin-top: 16px;
        margin-bottom: 20px;
    }

    .fb-forgot-link {
        color: #0064e0;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
    }

    .fb-forgot-link:hover {
        text-decoration: underline;
    }

    /* Divider */
    .fb-divider {
        height: 1px;
        background: #dadde1;
        margin: 20px 0 24px;
    }

    /* Create Account / Secondary Button */
    .fb-create-container {
        text-align: center;
        margin-bottom: 24px;
    }

    .fb-btn-secondary {
        display: inline-block;
        height: 48px;
        line-height: 46px;
        padding: 0 24px;
        border: 1.5px solid #0064e0;
        color: #0064e0;
        border-radius: 24px;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .fb-btn-secondary:hover {
        background-color: #f0f7ff;
        color: #0052b8;
    }

    /* Bottom Branding Footer */
    .fb-footer-branding {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #65676b;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
    }

    .fb-footer-logo {
        height: 20px;
        object-fit: contain;
    }

    /* Alert formatting */
    .alert-danger {
        background-color: #ffebe9;
        border: 1px solid #ffc0c0;
        color: #ce0000;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14px;
        margin-bottom: 16px;
    }

    /* Error fields */
    .help-block { font-size: 12px; color: #ce0000; margin-top: 4px; padding-left: 4px; }
    .has-error .fb-form-control { border-color: #ce0000 !important; }

    /* Responsive scaling */
    @media (max-width: 960px) {
        .fb-page-wrapper {
            flex-direction: column;
            padding: 24px 20px;
            gap: 32px;
        }
        .fb-left-section {
            min-height: auto;
            align-items: center;
            text-align: center;
        }
        .fb-hero-title {
            font-size: 34px;
        }
        .fb-hero-composition {
            display: none;
        }
    }
");
?>

<div class="fb-page-wrapper">

    <!-- ================= LEFT SECTION: BRANDING & HERO GRAPHICS ================= -->
    <div class="fb-left-section">
        
        <!-- Brand Header Logo -->
        <div class="fb-brand-header">
            <img src="<?= Yii::getAlias('@web') ?>/template/images/logo-pmc-header.png" alt="Priscilla Medical Center Logo" class="fb-header-logo-img">
        </div>

        <!-- Overlapping Card Graphics Composition (Replicating FB Login Illustration) -->
        <div class="fb-hero-composition">
            
            <!-- Floating Emoji -->
            <div class="fb-emoji-badge">😆</div>

            <!-- Card Back -->
            <div class="fb-card-main">
                <img src="<?= Yii::getAlias('@web') ?>/template/images/login/RSPMC.png" alt="PMC Hospital">
            </div>

            <!-- Card Front (Top Stack) -->
            <div class="fb-card-top">
                <span class="fb-pill-time">16:45</span>
                <img src="<?= Yii::getAlias('@web') ?>/template/images/login/admisi.png" alt="Medical Staff">
            </div>

            <!-- Floating Heart Badge -->
            <div class="fb-heart-badge">❤️</div>

            <!-- Floating Avatar Badge -->
            <div class="fb-avatar-badge">
                <img src="<?= Yii::getAlias('@web') ?>/template/images/pmc.png" alt="PMC Logo">
            </div>

        </div>

        <!-- Hero Headline Slogan -->
        <h1 class="fb-hero-title">
            Explore<br>
            the metrics<br>
            <span class="highlight">you love.</span>
        </h1>

    </div>

    <!-- ================= RIGHT SECTION: FACEBOOK-STYLE LOGIN FORM ================= -->
    <div class="fb-right-section">
        
        <div class="fb-login-card">
            
            <h2 class="fb-form-title">Log in to SISLAP</h2>

            <?php if ($model->hasErrors()): ?>
                <div class="alert-danger">
                    <?= implode('<br>', $model->getFirstErrors()) ?>
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['autocomplete' => 'off'],
            ]); ?>

            <!-- Username or Email Input -->
            <div class="fb-input-wrapper">
                <?= $form->field($model, 'nama_pemakai', [
                    'template' => '{input}{error}',
                    'inputOptions' => [
                        'class' => 'fb-form-control',
                        'placeholder' => 'Email address or user ID',
                        'autocomplete' => 'username',
                    ],
                ])->label(false) ?>
            </div>

            <!-- Password Input -->
            <div class="fb-input-wrapper">
                <?= $form->field($model, 'katakunci_pemakai', [
                    'template' => '{input}{error}',
                    'inputOptions' => [
                        'class' => 'fb-form-control',
                        'placeholder' => 'Password',
                        'autocomplete' => 'current-password',
                    ],
                ])->passwordInput()->label(false) ?>
            </div>

            <!-- Log in Button -->
            <?= Html::submitButton('Log in', ['class' => 'fb-btn-login', 'name' => 'login-button']) ?>

            <!-- Forgotten password link -->
            <div class="fb-forgot-container">
                <a href="#" class="fb-forgot-link">Forgotten password?</a>
            </div>

            <!-- Horizontal Divider -->
            <div class="fb-divider"></div>

            <!-- Secondary Button -->
            <div class="fb-create-container">
                <a href="#" class="fb-btn-secondary">Hubungi Administrator</a>
            </div>

            <!-- Bottom Branding Footer -->
            <div class="fb-footer-branding">
                <img src="<?= Yii::getAlias('@web') ?>/template/images/pmc.png" alt="PMC Logo" class="fb-footer-logo">
                <span>Priscilla Medical Center</span>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>

</div>