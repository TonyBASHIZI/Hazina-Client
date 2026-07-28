<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

header('Content-Type: application/json');

$activities = $pdo->query(
    "SELECT d.donor_name, d.amount, d.created_at, p.title AS project_title
     FROM donations d
     JOIN projects p ON p.id = d.project_id
     WHERE d.status = 'completed'
     ORDER BY d.created_at DESC
     LIMIT 8"
)->fetchAll();

$result = [];
foreach ($activities as $a) {
    $result[] = [
        'name' => $a['donor_name'] ?: 'Un généreux donateur',
        'amount' => number_format((float)$a['amount'], 0),
        'project' => mb_strimwidth($a['project_title'], 0, 35, '...'),
        'time' => timeAgo($a['created_at']),
    ];
}

echo json_encode($result);
exit;