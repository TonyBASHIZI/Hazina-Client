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
