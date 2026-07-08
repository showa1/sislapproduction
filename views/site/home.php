<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Home - Priscilla Medical Center';

$this->registerCss("
    body {
        background-color: #f4f6f9;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
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
    .pmc-logo-header { height: 50px; object-fit: contain; }
    .brand-name {
        color: #0c2340;
        font-weight: 800;
        font-size: 1.3rem;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }
    .welcome-section { margin-bottom: 48px; }
    .welcome-title { font-size: 2.2rem; font-weight: 800; color: #1a1a2e; margin-bottom: 8px; }
    .welcome-subtitle { color: #6c757d; font-size: 1.1rem; }

    /* Module Cards */
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
        top: 0; left: 0; right: 0;
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
    .module-card:hover .module-icon { transform: scale(1.1); }
    .module-title { font-size: 1.15rem; font-weight: 700; margin: 0; line-height: 1.4; }

    .pmc-green .module-icon { color: #6DC536; }
    .pmc-green.module-card::before { background-color: #6DC536; }
    .pmc-blue .module-icon { color: #29ABE2; }
    .pmc-blue.module-card::before { background-color: #29ABE2; }

    /* Sign out */
    .btn-signout {
        color: #0c2340;
        background: transparent;
        border: 1px solid rgba(12, 35, 64, 0.2);
        font-weight: 600;
        text-decoration: none;
        padding: 6px 16px;
        border-radius: 50rem;
        transition: all 0.2s ease;
        display: flex; align-items: center; gap: 6px;
        font-size: 0.9rem;
    }
    .btn-signout:hover { background: #0c2340; color: #ffffff; border-color: #0c2340; }
    .btn-signout i { font-size: 1.1rem; }

    /* User Profile */
    .user-avatar {
        width: 42px; height: 42px;
        background-color: #e3f2fd;
        color: #29ABE2;
        font-size: 1.2rem;
        display: flex; align-items: center; justify-content: center;
    }
    .session-badge {
        background-color: rgba(109, 197, 54, 0.15);
        color: #55a626;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .header-divider {
        width: 1px; height: 30px;
        background-color: rgba(0,0,0,0.1);
        margin: 0 8px;
    }

    /* ============ KEUANGAN LOCK BADGE ============ */
    .module-card-keuangan { cursor: pointer; }
    .lock-badge {
        position: absolute;
        top: 12px; right: 12px;
        width: 24px; height: 24px;
        background: rgba(0,45,114,0.08);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem;
        color: #002D72;
        transition: all 0.3s ease;
    }
    .module-card-keuangan:hover .lock-badge { background: #002D72; color: #fff; }

    /* ============ PIN MODAL ============ */
    #pinModal .modal-content {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    }
    #pinModal .modal-header-custom {
        background: linear-gradient(135deg, #002D72 0%, #0a4b9e 100%);
        padding: 28px 30px 22px;
        text-align: center;
    }
    #pinModal .modal-icon-wrap {
        width: 64px; height: 64px;
        background: rgba(255,255,255,0.15);
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.8rem; color: #fff;
        margin-bottom: 14px;
        backdrop-filter: blur(8px);
    }
    #pinModal .modal-title-custom {
        color: #fff; font-size: 1.25rem; font-weight: 700; margin: 0;
    }
    #pinModal .modal-subtitle {
        color: rgba(255,255,255,0.75); font-size: 0.85rem; margin: 6px 0 0;
    }
    #pinModal .modal-body-custom {
        padding: 28px 30px 24px; background: #fff;
    }
    #pinModal .input-label {
        font-size: 0.8rem; font-weight: 700; color: #475569;
        text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;
    }
    #pinModal .pin-input-wrap { position: relative; }
    #pinModal .pin-input-wrap i.input-icon {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%);
        color: #94a3b8; font-size: 1.1rem; pointer-events: none;
    }
    #pinModal #pinPasswordInput {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 44px 12px 42px;
        font-size: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        width: 100%;
    }
    #pinModal #pinPasswordInput:focus {
        border-color: #002D72;
        box-shadow: 0 0 0 3px rgba(0,45,114,0.1);
        outline: none;
    }
    #pinModal #pinPasswordInput.is-invalid {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
    }
    #pinModal .toggle-pw {
        position: absolute; right: 14px; top: 50%;
        transform: translateY(-50%);
        background: none; border: none; color: #94a3b8;
        cursor: pointer; padding: 0; font-size: 1.1rem; transition: color 0.2s;
    }
    #pinModal .toggle-pw:hover { color: #002D72; }
    #pinModal #pinErrorMsg {
        font-size: 0.82rem; color: #ef4444;
        min-height: 20px; margin-top: 8px;
        display: flex; align-items: center; gap: 5px;
    }
    #pinModal .attempts-indicator {
        display: flex; gap: 6px; justify-content: center; margin: 14px 0 0;
    }
    #pinModal .attempt-dot {
        width: 10px; height: 10px; border-radius: 50%;
        background: #e2e8f0; transition: background 0.3s ease;
    }
    #pinModal .attempt-dot.used { background: #ef4444; }
    #pinModal .btn-verify {
        background: linear-gradient(135deg, #002D72, #0a4b9e);
        border: none; border-radius: 12px; color: #fff;
        font-weight: 700; padding: 13px; width: 100%;
        font-size: 1rem; margin-top: 18px;
        transition: all 0.3s ease;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    #pinModal .btn-verify:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,45,114,0.3);
    }
    #pinModal .btn-verify:disabled { opacity: 0.7; cursor: not-allowed; }
    #pinModal .modal-footer-note {
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 14px 30px;
        font-size: 0.78rem; color: #94a3b8;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%       { transform: translateX(-8px); }
        40%       { transform: translateX(8px); }
        60%       { transform: translateX(-6px); }
        80%       { transform: translateX(6px); }
    }
    .shake { animation: shake 0.4s ease; }

    /* ============ COMING SOON BADGE ============ */
    .coming-soon-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #fff;
        color: #94a3b8;
        font-size: 0.65rem;
        font-weight: 800;
        padding: 3px 8px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        z-index: 2;
    }
    .module-card.disabled {
        opacity: 0.75;
        cursor: not-allowed !important;
        filter: grayscale(0.4);
    }
    .module-card.disabled:hover {
        transform: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.04);
    }
");

$username   = !Yii::$app->user->isGuest ? Yii::$app->user->identity->nama_pemakai : 'User';
$verifyUrl  = Url::to(['site/verify-keuangan']);
$keuanganUrl = Url::to(['keuangan/default/index']);
?>

<!-- Header -->
<header class="dashboard-header">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="<?= Url::to(['site/home']) ?>" class="d-flex align-items-center text-decoration-none gap-3">
            <img src="<?= Yii::getAlias('@web') ?>/template/images/pmc.png" alt="PMC Logo" class="pmc-logo-header">
            <div class="d-none d-sm-block">
                <div class="brand-name">Priscilla Medical Center</div>
            </div>
        </a>

        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold" style="color: #1a1a2e; font-size: 0.95rem; line-height: 1.2;"><?= Html::encode($username) ?></div>
                    <div class="badge rounded-pill session-badge mt-1">
                        Active Session <i class="bi bi-circle-fill ms-1" style="font-size: 0.45rem; vertical-align: middle;"></i>
                    </div>
                </div>
                <div class="rounded-circle user-avatar fw-bold shadow-sm">
                    <?= strtoupper(substr($username, 0, 1)) ?>
                </div>
            </div>
            <div class="header-divider d-none d-md-block"></div>
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
    <div class="welcome-section text-center">
        <h1 class="welcome-title">Selamat Datang, <?= Html::encode($username) ?>!</h1>
        <p class="welcome-subtitle">Silahkan pilih modul layanan yang ingin Anda akses hari ini.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <?php foreach ($modul as $key => $value): ?>
            <?php
            $icon       = isset($value['icon'])       ? $value['icon']       : 'bi bi-grid';
            $colorClass = isset($value['colorClass']) ? $value['colorClass'] : 'pmc-blue';
            $isKeuangan = ($key === 'Keuangan');
            $isComingSoon = isset($value['comingSoon']) && $value['comingSoon'];
            ?>
            <div class="col-sm-6 col-md-4 col-lg-4">
                <?php if ($isComingSoon): ?>
                    <div class="module-card <?= $colorClass ?> disabled" title="Modul dalam pengembangan">
                        <div class="coming-soon-badge">
                            Coming Soon
                        </div>
                        <i class="<?= $icon ?> module-icon"></i>
                        <h5 class="module-title"><?= $value['label'] ?></h5>
                    </div>
                <?php elseif ($isKeuangan): ?>
                    <div class="module-card <?= $colorClass ?> module-card-keuangan"
                         id="keuanganModuleCard"
                         onclick="openKeuanganModal()"
                         role="button"
                         tabindex="0"
                         onkeypress="if(event.key==='Enter') openKeuanganModal()">
                        <div class="lock-badge" title="Akses Terlindungi">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <i class="<?= $icon ?> module-icon"></i>
                        <h5 class="module-title"><?= $value['label'] ?></h5>
                    </div>
                <?php else: ?>
                    <a href="<?= Url::to([$value['url']]) ?>" class="module-card <?= $colorClass ?>">
                        <i class="<?= $icon ?> module-icon"></i>
                        <h5 class="module-title"><?= $value['label'] ?></h5>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===== MODAL VERIFIKASI KEUANGAN ===== -->
<div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header-custom">
                <div>
                    <div class="modal-icon-wrap">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                <h5 class="modal-title-custom" id="pinModalLabel">Verifikasi Akses Keuangan</h5>
                <p class="modal-subtitle">Masukkan password akun Anda untuk melanjutkan</p>
            </div>

            <!-- Modal Body -->
            <div class="modal-body-custom">
                <div class="input-label">Password Akun</div>
                <div class="pin-input-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password"
                           id="pinPasswordInput"
                           class="form-control"
                           placeholder="Masukkan password Anda..."
                           autocomplete="current-password"
                           maxlength="100">
                    <button type="button" class="toggle-pw" id="togglePwBtn" title="Tampilkan/sembunyikan">
                        <i class="bi bi-eye" id="togglePwIcon"></i>
                    </button>
                </div>

                <div id="pinErrorMsg"></div>

                <div class="attempts-indicator">
                    <div class="attempt-dot" id="dot1"></div>
                    <div class="attempt-dot" id="dot2"></div>
                    <div class="attempt-dot" id="dot3"></div>
                    <div class="attempt-dot" id="dot4"></div>
                    <div class="attempt-dot" id="dot5"></div>
                </div>

                <button class="btn-verify" id="btnVerify" onclick="submitVerification()">
                    <i class="bi bi-unlock" id="verifyBtnIcon"></i>
                    <span id="verifyBtnText">Verifikasi &amp; Masuk</span>
                </button>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer-note">
                <i class="bi bi-info-circle"></i>
                Akses ini dicatat untuk keamanan sistem
                <button type="button"
                        class="btn btn-link btn-sm text-secondary ms-auto p-0"
                        onclick="closeKeuanganModal()"
                        style="font-size: 0.78rem; text-decoration: none;">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$csrfToken = Yii::$app->request->enableCsrfValidation ? Yii::$app->request->csrfToken : '';
$this->registerJs("
(function() {
    var verifyUrl    = " . json_encode($verifyUrl) . ";
    var keuanganUrl  = " . json_encode($keuanganUrl) . ";
    var csrfToken    = " . json_encode($csrfToken) . ";
    var failedAttempts = 0;
    var pinModalObj;

    function getModal() {
        if (!pinModalObj) {
            pinModalObj = new bootstrap.Modal(document.getElementById('pinModal'));
        }
        return pinModalObj;
    }

    window.openKeuanganModal = function() {
        getModal().show();
    };

    window.closeKeuanganModal = function() {
        getModal().hide();
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Enter key support
        document.getElementById('pinPasswordInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') submitVerification();
        });

        // Toggle password visibility
        document.getElementById('togglePwBtn').addEventListener('click', function() {
            var inp  = document.getElementById('pinPasswordInput');
            var icon = document.getElementById('togglePwIcon');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                inp.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });

        // Reset modal state on open
        document.getElementById('pinModal').addEventListener('show.bs.modal', function() {
            document.getElementById('pinPasswordInput').value = '';
            document.getElementById('pinErrorMsg').innerHTML  = '';
            document.getElementById('pinPasswordInput').classList.remove('is-invalid');
            document.getElementById('pinPasswordInput').type  = 'password';
            document.getElementById('togglePwIcon').className = 'bi bi-eye';
            setBtnState('idle');
            setTimeout(function() {
                document.getElementById('pinPasswordInput').focus();
            }, 400);
        });
    });

    function setBtnState(state) {
        var btn  = document.getElementById('btnVerify');
        var icon = document.getElementById('verifyBtnIcon');
        var text = document.getElementById('verifyBtnText');
        if (state === 'loading') {
            btn.disabled = true;
            icon.className = 'bi bi-hourglass-split';
            text.textContent = 'Memverifikasi...';
            btn.style.background = '';
        } else if (state === 'success') {
            btn.disabled = true;
            icon.className = 'bi bi-check-circle-fill';
            text.textContent = 'Berhasil! Mengalihkan...';
            btn.style.background = 'linear-gradient(135deg, #16a34a, #15803d)';
        } else if (state === 'locked') {
            btn.disabled = true;
            icon.className = 'bi bi-lock-fill';
            text.textContent = 'Akses Dikunci';
            btn.style.background = 'linear-gradient(135deg, #dc2626, #b91c1c)';
        } else {
            btn.disabled = false;
            icon.className = 'bi bi-unlock';
            text.textContent = 'Verifikasi & Masuk';
            btn.style.background = '';
        }
    }

    function updateDots() {
        for (var i = 1; i <= 5; i++) {
            var dot = document.getElementById('dot' + i);
            if (i <= failedAttempts) dot.classList.add('used');
            else dot.classList.remove('used');
        }
    }

    function showError(msg) {
        var errEl = document.getElementById('pinErrorMsg');
        var inp   = document.getElementById('pinPasswordInput');
        errEl.innerHTML = '<i class=\"bi bi-exclamation-circle\"></i> ' + msg;
        inp.classList.add('is-invalid');
        inp.classList.add('shake');
        setTimeout(function() { inp.classList.remove('shake'); }, 500);
        failedAttempts++;
        updateDots();
        if (failedAttempts >= 5) {
            setBtnState('locked');
        }
    }

    window.submitVerification = function() {
        var password = document.getElementById('pinPasswordInput').value.trim();
        if (!password) { showError('Password tidak boleh kosong.'); return; }

        setBtnState('loading');
        document.getElementById('pinErrorMsg').innerHTML = '';
        document.getElementById('pinPasswordInput').classList.remove('is-invalid');

        var formData = new FormData();
        formData.append('password', password);
        if (csrfToken) formData.append('_csrf', csrfToken);

        fetch(verifyUrl, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                setBtnState('success');
                setTimeout(function() {
                    window.location.href = keuanganUrl;
                }, 700);
            } else {
                setBtnState('idle');
                showError(data.message || 'Verifikasi gagal.');
            }
        })
        .catch(function() {
            setBtnState('idle');
            showError('Terjadi kesalahan koneksi. Coba lagi.');
        });
    };
})();
");
?>