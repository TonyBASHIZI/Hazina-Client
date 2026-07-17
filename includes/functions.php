<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** L'utilisateur est-il connecté côté client ? */
function isClientLoggedIn(): bool
{
    return !empty($_SESSION['client_id']);
}

/** Récupère l'utilisateur connecté (tableau) ou null */
function currentClientUser(): ?array
{
    if (!isClientLoggedIn()) {
        return null;
    }

    static $user = null;
    if ($user === null) {
        $stmt = getPDO()->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $_SESSION['client_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

/** Échappement HTML rapide */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Formate un montant en devise */
function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/**
 * Résout le chemin public d'une image enregistrée par l'admin.
 * Gère les variations historiques ('/uploads/x.jpg', 'assets/uploads/x.jpg', chemins absolus...)
 */
function resolveImagePath(?string $path, string $fallback = 'assets/img/donation/donation1.jpg'): string
{
    return $path ?: $fallback;
}

/** Jeton CSRF */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfCheck(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Jeton de sécurité invalide. Recharge la page et réessaie.');
    }
}
/** Upload sécurisé d'une photo de profil, retourne le chemin ou null */
function handleClientPhotoUpload(string $inputName): ?string
{
    if (empty($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$inputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['profile_error'] = "Erreur lors de l'upload de la photo.";
        return null;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed, true)) {
        $_SESSION['profile_error'] = "Format d'image non autorisé (jpg, jpeg, png, gif, webp uniquement).";
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['profile_error'] = "L'image dépasse la taille maximale de 5 Mo.";
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = 'img_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $_SESSION['profile_error'] = "Impossible d'enregistrer la photo.";
        return null;
    }

    return UPLOAD_URL . $filename;
}
