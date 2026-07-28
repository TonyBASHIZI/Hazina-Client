<?php
require_once __DIR__ . '/includes/functions.php';

if (isClientLoggedIn()) {
    header('Location: index.php');
    exit;
}

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? 'index.php';
$errors = [];
$data = ['username' => '', 'mail' => '', 'telephone' => '', 'adresse' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $pdo = getPDO();

    $data['username'] = trim($_POST['username'] ?? '');
    $data['mail'] = trim($_POST['mail'] ?? '');
    $data['telephone'] = trim($_POST['telephone'] ?? '');
    $data['adresse'] = trim($_POST['adresse'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($data['username'] === '') $errors[] = "Le nom d'utilisateur est obligatoire.";
    if (!filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (!preg_match('/^\+[0-9]{8,15}$/', $data['telephone'])) {
    $errors[] = 'Numéro de téléphone invalide.';
    }
    if ($data['adresse'] === '') $errors[] = "L'adresse est obligatoire.";
    if (strlen($password) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u OR mail = :m');
        $check->execute(['u' => $data['username'], 'm' => $data['mail']]);
        if ($check->fetchColumn() > 0) {
            $errors[] = 'Ce nom d\'utilisateur ou cet email est déjà utilisé.';
        }
    }

    if (!$errors) {
    // Rien n'est encore inséré en base — on garde tout en session jusqu'à vérification complète
    $_SESSION['pending_registration'] = [
        'username' => $data['username'],
        'mail' => $data['mail'],
        'telephone' => $data['telephone'],
        'adresse' => $data['adresse'],
        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
    ];
    $_SESSION['pending_redirect'] = $redirect;

    $code = generatePendingCode();
    sendVerificationEmail($data['mail'], $code);

    header('Location: verify-email.php');
    exit;
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
    <title>Inscription — Hazina Funding</title>
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
                                    <h2>Créer un compte</h2>
                                </div>

                                <?php if ($errors): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
                                    </div>
                                <?php endif; ?>

                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
                                    <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required value="<?= e($data['username']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="email" name="mail" class="form-control" placeholder="Email" required value="<?= e($data['mail']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <div class="d-flex" style="gap:8px;">
                                                    <select id="regPhoneCountry" class="form-control" style="max-width:110px; flex:0 0 auto;">
                                                        <option value="+243" selected>🇨🇩 +243</option>
                                                        <option value="+242">🇨🇬 +242</option>
                                                        <option value="+250">🇷🇼 +250</option>
                                                        <option value="+257">🇧🇮 +257</option>
                                                        <option value="+256">🇺🇬 +256</option>
                                                        <option value="+255">🇹🇿 +255</option>
                                                        <option value="+260">🇿🇲 +260</option>
                                                        <option value="+33">🇫🇷 +33</option>
                                                        <option value="+1">🇺🇸 +1</option>
                                                        <option value="+44">🇬🇧 +44</option>
                                                    </select>
                                                    <input type="text" id="regPhone" name="telephone_local" class="form-control" placeholder="Numéro sans indicatif (ex: 891234567)" required>
                                                </div>
                                                <input type="hidden" id="regPhoneFull" name="telephone" value="<?= e($data['telephone']) ?>">
                                                <small id="phoneFeedback" class="text-danger" style="display:none;">Numéro invalide (9 à 10 chiffres attendus)</small>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="text" name="adresse" class="form-control" placeholder="Adresse" required value="<?= e($data['adresse']) ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required minlength="6">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="form-group">
                                                <input type="password" name="password_confirm" class="form-control" placeholder="Confirmer le mot de passe" required minlength="6">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <button type="submit" class="btn common-btn">S'inscrire</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="bottom">
                                    <p>Déjà un compte ? <a href="login.php?redirect=<?= urlencode($redirect) ?>">Se connecter</a></p>
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
<script>
const phoneInput = document.getElementById('regPhone');
const phoneCountry = document.getElementById('regPhoneCountry');
const phoneFullField = document.getElementById('regPhoneFull');
const phoneFeedback = document.getElementById('phoneFeedback');

function validatePhone() {
    const clean = phoneInput.value.replace(/[\s\-]/g, '').replace(/^0+/, '');
    const valid = /^[0-9]{7,10}$/.test(clean);
    phoneFeedback.style.display = (phoneInput.value.trim() && !valid) ? 'block' : 'none';
    phoneInput.classList.toggle('is-invalid', phoneInput.value.trim() && !valid);

    if (valid) {
        phoneFullField.value = phoneCountry.value + clean;
    }
    return valid;
}

phoneInput?.addEventListener('input', validatePhone);
phoneCountry?.addEventListener('change', validatePhone);

document.querySelector('form')?.addEventListener('submit', (e) => {
    if (!validatePhone()) {
        e.preventDefault();
    }
});
</script>
</body>
</html>
