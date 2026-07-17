<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$projectId = (int)($_POST['project_id'] ?? 0);
$redirectBack = 'donation-details.php?id=' . $projectId;

if (!isClientLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($redirectBack));
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    $_SESSION['donation_error'] = 'Jeton de sécurité invalide. Réessaie.';
    header('Location: ' . $redirectBack);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND status = 'active'");
$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    $_SESSION['donation_error'] = 'Ce projet est introuvable ou n\'accepte plus de dons.';
    header('Location: index.php');
    exit;
}

$amount = $_POST['amount'] ?? '';
if (!is_numeric($amount) || (float)$amount <= 0) {
    $_SESSION['donation_error'] = 'Merci de choisir ou saisir un montant valide.';
    header('Location: ' . $redirectBack);
    exit;
}

$allowedMethods = ['mpesa', 'airtel', 'orange', 'visa'];
$paymentMethod = $_POST['payment_method'] ?? '';
if (!in_array($paymentMethod, $allowedMethods, true)) {
    $paymentMethod = 'mpesa';
}

$message = trim($_POST['message'] ?? '');
$client = currentClientUser();

$isAnonymous = !empty($_POST['is_anonymous']);

$insert = $pdo->prepare(
    'INSERT INTO donations (user_id, project_id, amount, donor_name, donor_email, donor_phone, message, status, payment_method)
     VALUES (:user_id, :project_id, :amount, :donor_name, :donor_email, :donor_phone, :message, "pending", :payment_method)'
);
$insert->execute([
    'user_id' => $client['id'], // toujours lié en interne, pour ton suivi admin
    'project_id' => $projectId,
    'amount' => $amount,
    'donor_name' => $isAnonymous ? 'Anonymous' : $client['username'],
    'donor_email' => $isAnonymous ? null : $client['mail'],
    'donor_phone' => $isAnonymous ? null : $client['telephone'],
    'message' => $message !== '' ? $message : null,
    'payment_method' => $paymentMethod,
]);

header('Location: donation-details.php?id=' . $projectId . '&thanks=1');
exit;