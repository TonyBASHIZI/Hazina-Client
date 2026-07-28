<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success = false;

$stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = :token AND used = 0 AND expires_at > NOW()');
$stmt->execute(['token' => $token]);
$resetRow = $stmt->fetch();

if (!$resetRow) {
    $errors[] = 'Ce lien de réinitialisation est invalide ou a expiré. Refais une demande.';
}

if ($resetRow && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (strlen($password) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (!$errors) {
        $pdo->prepare('UPDATE users SET password = :pwd WHERE id = :id')
            ->execute(['pwd' => password_hash($password, PASSWORD_BCRYPT), 'id' => $resetRow['user_id']]);

        $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = :id')
            ->execute(['id' => $resetRow['id']]);

        $success = true;
    }
}

$csrfTok = csrfToken();
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
    <title>Réinitialiser le mot de passe — Hazina Funding</title>
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
                                    <h2>Nouveau mot de passe</h2>
                                </div>

                                <?php if ($success): ?>
                                    <div class="alert alert-success">
                                        Ton mot de passe a été mis à jour avec succès.
                                    </div>
                                    <a href="login.php" class="btn common-btn w-100">Se connecter</a>
                                <?php else: ?>
                                    <?php if ($errors): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($resetRow): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= e($csrfTok) ?>">
                                            <input type="hidden" name="token" value="<?= e($token) ?>">
                                            <div class="form-group mb-3">
                                                <input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe" minlength="6" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <input type="password" name="password_confirm" class="form-control" placeholder="Confirmer le mot de passe" minlength="6" required>
                                            </div>
                                            <button type="submit" class="btn common-btn w-100">Réinitialiser</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="forgot-password.php" class="btn common-btn w-100">Refaire une demande</a>
                                    <?php endif; ?>
                                <?php endif; ?>
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