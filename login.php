<?php
require_once __DIR__ . '/includes/functions.php';

if (isClientLoggedIn()) {
    header('Location: index.php');
    exit;
}

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Merci de renseigner tous les champs.';
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :id1 OR mail = :id2 LIMIT 1');
        $stmt->execute(['id1' => $identifier, 'id2' => $identifier]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['client_id'] = $user['id'];
            $_SESSION['client_username'] = $user['username'];
            header('Location: ' . $redirect);
            exit;
        }

        $error = 'Identifiants incorrects.';
    }
}

$token = csrfToken();
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
    <title>Connexion — Hazina Funding</title>
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
                                    <h2>Connexion</h2>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= e($error) ?></div>
                                <?php endif; ?>

                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                                    <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="text" name="identifier" class="form-control" placeholder="Nom d'utilisateur ou email" required autofocus value="<?= e($_POST['identifier'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn common-btn">Se connecter</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="bottom">
                                    <p><a href="forgot-password.php">Mot de passe oublié ?</a></p>
                                    <p>Pas encore de compte ? <a href="register.php?redirect=<?= urlencode($redirect) ?>">S'inscrire</a></p>
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
