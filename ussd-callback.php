<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$raw = file_get_contents('php://input');
error_log('USSD CALLBACK RECEIVED: ' . $raw);

$data = json_decode($raw, true);
$transaction = is_array($data['transaction'] ?? null) ? $data['transaction'] : [];
$orderNumber = $transaction['orderNumber'] ?? $data['orderNumber'] ?? null;

if (!$data || empty($orderNumber)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Payload invalide']);
    exit;
}

$status = $transaction['status'] ?? $data['status'] ?? null;
$status = is_scalar($status) ? (string)$status : '';
$isSuccess = in_array(strtolower($status), ['success', 'completed', 'paid', '0'], true);
$isFailure = in_array(strtolower($status), ['failed', 'failure', 'rejected', 'declined', 'cancelled', 'canceled', '1', '5'], true);

$stmt = $pdo->prepare('SELECT id FROM ussd_payment_attempts WHERE order_number = :order');
$stmt->execute(['order' => $orderNumber]);
$donation = $stmt->fetch();

if (!$donation) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Don introuvable']);
    exit;
}

if ($isSuccess) {
    completeUssdPaymentAttempt($pdo, $orderNumber);
} elseif ($isFailure) {
    $pdo->prepare("UPDATE ussd_payment_attempts SET status = 'failed' WHERE id = :id")
        ->execute(['id' => $donation['id']]);
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
exit;
