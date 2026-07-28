<?php
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['pending_registration'])) {
    header('Location: register.php');
    exit;
}

$pdo = getPDO();
$pending = $_SESSION['pending_registration'];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    if (isset($_POST['resend'])) {
        $code = generatePendingCode();
        sendVerificationSMS($pending['telephone'], $code);
    } else {
        $inputCode = trim($_POST['code'] ?? '');
        if (checkPendingCode($inputCode)) {
            // Vérification complète → on crée VRAIMENT le compte maintenant
            $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u OR mail = :m');
            $check->execute(['u' => $pending['username'], 'm' => $pending['mail']]);
            if ($check->fetchColumn() > 0) {
                $error = 'Ce nom d\'utilisateur ou cet email a été pris entre-temps. Recommence l\'inscription.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO users (username, mail, telephone, adresse, password, role, email_verified, phone_verified)
                     VALUES (:username, :mail, :telephone, :adresse, :password, "user", 1, 1)'
                );
                $stmt->execute([
                    'username' => $pending['username'],
                    'mail' => $pending['mail'],
                    'telephone' => $pending['telephone'],
                    'adresse' => $pending['adresse'],
                    'password' => $pending['password_hash'],
                ]);

                $newId = (int)$pdo->lastInsertId();
                $redirect = $_SESSION['pending_redirect'] ?? 'index.php';

                unset($_SESSION['pending_registration'], $_SESSION['pending_code'], $_SESSION['pending_code_expires'], $_SESSION['pending_redirect']);

                session_regenerate_id(true);
                $_SESSION['client_id'] = $newId;
                $_SESSION['client_username'] = $pending['username'];

                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Code incorrect ou expiré.';
        }
    }
}

$pageTitle = 'Vérification téléphone';
require_once __DIR__ . '/includes/header.php';
?>

<div class="user-form-area">
    <div class="container" style="max-width:500px; padding-top:80px; padding-bottom:80px;">
        <div class="text-center mb-4">
            <i class="icofont-ui-call" style="font-size:50px; color:var(--hf-gold);"></i>
            <h3 class="mt-2">Vérifie ton téléphone</h3>
            <p class="text-muted">On a envoyé un code à 6 chiffres au <strong><?= e($pending['telephone']) ?></strong></p>
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