<?php
require_once __DIR__ . '/includes/functions.php';

if (!isClientLoggedIn()) {
    header('Location: login.php?redirect=edit-profile.php');
    exit;
}

$pdo = getPDO();
$client = currentClientUser();
$errors = [];
$success = false;
$data = $client;

$profileError = $_SESSION['profile_error'] ?? null;
unset($_SESSION['profile_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();

    $data['username'] = trim($_POST['username'] ?? '');
    $data['mail'] = trim($_POST['mail'] ?? '');
    $data['telephone'] = trim($_POST['telephone'] ?? '');
    $data['adresse'] = trim($_POST['adresse'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if ($data['username'] === '') $errors[] = "Le nom d'utilisateur est obligatoire.";
    if (!filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if ($data['telephone'] === '') $errors[] = 'Le téléphone est obligatoire.';
    if ($data['adresse'] === '') $errors[] = "L'adresse est obligatoire.";
    if ($newPassword !== '' && strlen($newPassword) < 6) $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    if ($newPassword !== '' && $newPassword !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = :u OR mail = :m) AND id != :id');
        $check->execute(['u' => $data['username'], 'm' => $data['mail'], 'id' => $client['id']]);
        if ($check->fetchColumn() > 0) {
            $errors[] = 'Ce nom d\'utilisateur ou cet email est déjà utilisé par un autre compte.';
        }
    }

    if (!$errors) {
        $photoPath = $client['photo'];
        $newPhoto = handleClientPhotoUpload('photo');
        if ($newPhoto) {
            if ($photoPath) {
                $oldPath = dirname(__DIR__) . $photoPath;
                if (is_file($oldPath)) @unlink($oldPath);
            }
            $photoPath = $newPhoto;
        }

        if ($newPassword !== '') {
            $upd = $pdo->prepare(
                'UPDATE users SET username=:username, mail=:mail, telephone=:telephone, adresse=:adresse, photo=:photo, password=:password WHERE id=:id'
            );
            $upd->execute([
                'username' => $data['username'],
                'mail' => $data['mail'],
                'telephone' => $data['telephone'],
                'adresse' => $data['adresse'],
                'photo' => $photoPath,
                'password' => password_hash($newPassword, PASSWORD_BCRYPT),
                'id' => $client['id'],
            ]);
        } else {
            $upd = $pdo->prepare(
                'UPDATE users SET username=:username, mail=:mail, telephone=:telephone, adresse=:adresse, photo=:photo WHERE id=:id'
            );
            $upd->execute([
                'username' => $data['username'],
                'mail' => $data['mail'],
                'telephone' => $data['telephone'],
                'adresse' => $data['adresse'],
                'photo' => $photoPath,
                'id' => $client['id'],
            ]);
        }

        $_SESSION['client_username'] = $data['username'];
        $success = true;
        $client = currentClientUser(); // rafraîchit (note: nécessite de relire en base si mise en cache)
        $data = $pdo->query('SELECT * FROM users WHERE id = ' . (int)$client['id'])->fetch();
    }
}

$pageTitle = 'Modifier mon profil';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-title-area title-bg-three">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="title-item">
                    <h2>Modifier mon profil</h2>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="profile.php">Mon compte</a></li>
                        <li><span>Modifier</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container ptb-100" style="max-width:700px;">

    <?php if ($success): ?>
        <div class="alert alert-success">Profil mis à jour avec succès.</div>
    <?php endif; ?>
    <?php if ($profileError): ?>
        <div class="alert alert-danger"><?= e($profileError) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="text-center mb-4">
            <?php if (!empty($data['photo'])): ?>
                <img src="<?= e($data['photo']) ?>" style="width:100px;height:100px;object-fit:cover;border-radius:50%;border:3px solid var(--hf-gold);">
            <?php else: ?>
                <div style="width:100px;height:100px;border-radius:50%;background:var(--hf-navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:36px;margin:0 auto;">
                    <?= e(mb_strtoupper(mb_substr($data['username'], 0, 1))) ?>
                </div>
            <?php endif; ?>
            <div class="mt-2">
                <input type="file" name="photo" accept="image/*" class="form-control">
            </div>
        </div>

        <div class="form-group mb-3">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" required value="<?= e($data['username']) ?>">
        </div>
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="mail" class="form-control" required value="<?= e($data['mail']) ?>">
        </div>
        <div class="form-group mb-3">
            <label>Téléphone</label>
            <input type="text" name="telephone" class="form-control" required value="<?= e($data['telephone']) ?>">
        </div>
        <div class="form-group mb-3">
            <label>Adresse</label>
            <input type="text" name="adresse" class="form-control" required value="<?= e($data['adresse']) ?>">
        </div>
        <div class="form-group mb-3">
            <label>Nouveau mot de passe</label>
            <input type="password" name="password" class="form-control" minlength="6" placeholder="Laisser vide pour ne pas changer">
        </div>
        <div class="form-group mb-3">
            <label>Confirmer le nouveau mot de passe</label>
            <input type="password" name="password_confirm" class="form-control" minlength="6">
        </div>

        <div class="text-center">
            <button type="submit" class="btn common-btn">Enregistrer les modifications</button>
        </div>
    </form>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>