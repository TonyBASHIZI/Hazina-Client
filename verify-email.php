<?php
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['pending_registration'])) {
    header('Location: register.php');
    exit;
}

$pending = $_SESSION['pending_registration'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    if (isset($_POST['resend'])) {
        $code = generatePendingCode();
        sendVerificationEmail($pending['mail'], $code);
    } else {
        $inputCode = trim($_POST['code'] ?? '');
        if (checkPendingCode($inputCode)) {
            $code = generatePendingCode();
            sendVerificationSMS($pending['telephone'], $code);
            header('Location: verify-phone.php');
            exit;
        }
        $error = 'Code incorrect ou expiré.';
    }
}

$pageTitle = 'Vérification email';
require_once __DIR__ . '/includes/header.php';
?>

<div class="user-form-area">
    <div class="container" style="max-width:500px; padding-top:80px; padding-bottom:80px;">
        <div class="text-center mb-4">
            <i class="icofont-ui-email" style="font-size:50px; color:var(--hf-gold);"></i>
            <h3 class="mt-2">Vérifie ton email</h3>
            <p class="text-muted">On a envoyé un code à 6 chiffres à <strong><?= e($pending['mail']) ?></strong></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="text" name="code" class="form-control text-center mb-3" style="font-size:24px; letter-spacing:8px;" maxlength="6" placeholder="000000" required autofocus>
            <button type="submit" class="btn common-btn w-100 mb-2">Vérifier</button>
        </form>
        <form method="post" class="text-center">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="resend" value="1">
            <button type="submit" class="btn btn-link">Renvoyer le code</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>