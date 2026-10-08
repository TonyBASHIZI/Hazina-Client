<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();
$isAjaxRequest = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
$sendError = static function (string $message, string $redirectUrl) use ($isAjaxRequest): void {
    if ($isAjaxRequest) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
    $_SESSION['donation_error'] = $message;
    header('Location: ' . $redirectUrl);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$projectId = (int)($_POST['project_id'] ?? 0);
$redirectBack = 'donation-details.php?id=' . $projectId;

if (!isClientLoggedIn()) {
    if ($isAjaxRequest) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Ta session a expiré. Reconnecte-toi puis réessaie.']);
        exit;
    }
    header('Location: login.php?redirect=' . urlencode($redirectBack));
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    $sendError('Jeton de sécurité invalide. Réessaie.', $redirectBack);
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id AND status = 'active'");
$stmt->execute(['id' => $projectId]);
$project = $stmt->fetch();

if (!$project) {
    $sendError('Ce projet est introuvable ou n\'accepte plus de dons.', 'index.php');
}

$amount = $_POST['amount'] ?? '';
if (!is_numeric($amount) || (float)$amount <= 0) {
    $sendError('Merci de choisir ou saisir un montant valide.', $redirectBack);
}

$allowedMethods = ['mpesa', 'airtel', 'orange', 'visa', 'ussd'];
$paymentMethod = $_POST['payment_method'] ?? '';
$paymentGroup = $_POST['payment_group'] ?? '';
// The radio choice is authoritative for payment types with their own flow.
// payment_method is still used to carry the chosen mobile operator.
if (in_array($paymentGroup, ['ussd', 'visa'], true)) {
    $paymentMethod = $paymentGroup;
}
if (!in_array($paymentMethod, $allowedMethods, true)) {
    $paymentMethod = 'mpesa';
}

$message = trim($_POST['message'] ?? '');
$client = currentClientUser();

$isAnonymous = !empty($_POST['is_anonymous']);

$donorPhone = $isAnonymous ? null : (trim($_POST['payment_phone'] ?? '') ?: $client['telephone']);

if ($paymentMethod === 'ussd') {
    $phoneForUssd = trim($_POST['payment_phone'] ?? '') ?: $client['telephone'];
    $currency = strtoupper($_POST['ussd_currency'] ?? 'USD');
    if (!in_array($currency, ['USD', 'CDF'], true)) {
        $currency = 'USD';
    }
    $ussdResult = initiateUssdPayment($phoneForUssd, (float)$amount, $currency);

    if (!$ussdResult['success']) {
        $sendError('Paiement USSD : ' . $ussdResult['message'], $redirectBack);
    }

    $insert = $pdo->prepare(
        "INSERT INTO ussd_payment_attempts (user_id, project_id, amount_usd, amount_cdf, donor_name, donor_email, donor_phone, message, order_number, reference, status)
         VALUES (:user_id, :project_id, :amount_usd, :amount_cdf, :donor_name, :donor_email, :donor_phone, :message, :order_number, :reference, 'pending')"
    );
    $insert->execute([
        'user_id' => $client['id'],
        'project_id' => $projectId,
        // Project totals are maintained in USD; the CDF equivalent is also kept for payment records.
        'amount_usd' => $ussdResult['amount_usd'],
        'amount_cdf' => $ussdResult['amount_cdf'],
        'donor_name' => $isAnonymous ? 'Anonymous' : ($client['full_name'] ?: $client['username']),
        'donor_email' => $isAnonymous ? null : $client['mail'],
        'donor_phone' => $donorPhone,
        'message' => $message !== '' ? $message : null,
        'order_number' => $ussdResult['orderNumber'],
        'reference' => $ussdResult['reference'],
    ]);

    $_SESSION['ussd_attempt_id'] = (int)$pdo->lastInsertId();
    if ($isAjaxRequest) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'attempt_id' => (int)$_SESSION['ussd_attempt_id'],
            'message' => $ussdResult['message'],
        ]);
        exit;
    }
    $_SESSION['ussd_push_message'] = $ussdResult['message'];
    header('Location: donation-details.php?id=' . $projectId . '&ussd_push=1');
    exit;
}

$insert = $pdo->prepare(
    'INSERT INTO donations (user_id, project_id, amount, donor_name, donor_email, donor_phone, message, status, payment_method)
     VALUES (:user_id, :project_id, :amount, :donor_name, :donor_email, :donor_phone, :message, "pending", :payment_method)'
);
$insert->execute([
    'user_id' => $client['id'],
    'project_id' => $projectId,
    'amount' => $amount,
    'donor_name' => $isAnonymous ? 'Anonymous' : ($client['full_name'] ?: $client['username']),
    'donor_email' => $isAnonymous ? null : $client['mail'],
    'donor_phone' => $donorPhone,
    'message' => $message !== '' ? $message : null,
    'payment_method' => $paymentMethod,
]);

header('Location: donation-details.php?id=' . $projectId . '&thanks=1');
exit;
