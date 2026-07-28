<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$sent = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $mail = trim($_POST['mail'] ?? '');

    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE mail = :mail');
        $stmt->execute(['mail' => $mail]);
        $user = $stmt->fetch();

        // Message identique que le compte existe ou non (évite de révéler quels emails sont inscrits)
        $sent = true;

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $pdo->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :token, :exp)')
                ->execute(['uid' => $user['id'], 'token' => $token, 'exp' => $expires]);

            $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/reset-password.php?token=' . $token;

            sendPasswordResetEmail($mail, $resetLink);
        }
    }
}

$token = csrfToken();
$pageTitle = 'Mot de passe oublié';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hf-theme.css">
    <title>Mot de passe oublié — Hazina Funding</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>
<body>

<div class="user-form-area">
    <div class="container-fluid p-0">
        <div class="row m-0">
            <div class="col-lg-6 p-0 d-none d-lg-block">
                <div class="user-img">
                    <img src="assets/img/user-form-bg.jpg" alt="Hazina Funding">
                </div>
            </div>
            <div class="col-lg-6 p-0">
                <div class="user-content">
                    <div class="d-table">
                        <div class="d-table-cell">
                            <div class="user-content-inner" style="padding: 40px 60px;">
                                <div class="top">
                                    <a href="index.php">
                                        <img src="assets/img/logo.png" class="logo-one" alt="Logo">
                                    </a>
                                    <h2>Mot de passe oublié</h2>
                                </div>

                                <?php if ($sent): ?>
                                    <div class="alert alert-success">
                                        Si un compte existe avec cet email, un lien de réinitialisation vient d'être envoyé. Vérifie ta boîte de réception (et tes spams).
                                    </div>
                                <?php else: ?>
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?= e($error) ?></div>
                                    <?php endif; ?>
                                    <p class="text-muted mb-3">Entre ton email, on t'enverra un lien pour choisir un nouveau mot de passe.</p>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                                        <div class="form-group mb-3">
                                            <input type="email" name="mail" class="form-control" placeholder="Ton email" required>
                                        </div>
                                        <button type="submit" class="btn common-btn w-100">Envoyer le lien</button>
                                    </form>
                                <?php endif; ?>

                                <div class="bottom mt-3">
                                    <p><a href="login.php">Retour à la connexion</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>