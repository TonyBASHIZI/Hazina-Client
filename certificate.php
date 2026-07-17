<?php
require_once __DIR__ . '/includes/functions.php';

if (!isClientLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$client = currentClientUser();
$donationId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT d.*, p.title AS project_title
     FROM donations d
     JOIN projects p ON p.id = d.project_id
     WHERE d.id = :id AND d.user_id = :uid AND d.status = 'completed'"
);
$stmt->execute(['id' => $donationId, 'uid' => $client['id']]);
$donation = $stmt->fetch();

if (!$donation) {
    header('Location: profile.php');
    exit;
}

$reference = 'HF-' . str_pad($donation['id'], 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Certificat de don — <?= e($reference) ?></title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: 'Georgia', serif;
        background: #eef0f5;
        margin: 0;
        padding: 40px 20px;
    }
    .certificate {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        border: 10px solid #0B0F2A;
        padding: 60px;
        position: relative;
        text-align: center;
    }
    .certificate::before {
        content: "";
        position: absolute;
        top: 15px; left: 15px; right: 15px; bottom: 15px;
        border: 2px solid #D4AF37;
        pointer-events: none;
    }
    .logo {
        width: 70px;
        margin-bottom: 10px;
    }
    .org-name {
        color: #0B0F2A;
        font-size: 14px;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 30px;
    }
    h1 {
        color: #0B0F2A;
        font-size: 36px;
        margin: 0 0 10px;
        letter-spacing: 1px;
    }
    .subtitle {
        color: #D4AF37;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 40px;
    }
    .donor-name {
        font-size: 30px;
        color: #0B0F2A;
        font-weight: bold;
        margin: 20px 0;
        border-bottom: 2px solid #D4AF37;
        display: inline-block;
        padding-bottom: 8px;
    }
    .description {
        font-size: 16px;
        color: #333;
        line-height: 1.8;
        max-width: 550px;
        margin: 20px auto;
    }
    .amount {
        font-size: 42px;
        color: #D4AF37;
        font-weight: bold;
        margin: 20px 0;
    }
    .meta {
        display: flex;
        justify-content: center;
        gap: 60px;
        margin-top: 50px;
        font-size: 13px;
        color: #555;
    }
    .meta div strong {
        display: block;
        color: #0B0F2A;
        font-size: 15px;
        margin-bottom: 4px;
    }
    .actions {
        text-align: center;
        margin-top: 30px;
    }
    .btn-print {
        background: #D4AF37;
        color: #0B0F2A;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        font-size: 15px;
        font-family: Arial, sans-serif;
    }

    @media print {
        body { background: #fff; padding: 0; }
        .certificate { border-width: 6px; }
        .actions { display: none; }
    }
</style>
</head>
<body>

<div class="certificate">
    <img src="assets/img/logo.png" class="logo" alt="Hazina Funding">
    <div class="org-name">Hazina Funding</div>

    <h1>Certificat de don</h1>
    <div class="subtitle">Avec toute notre gratitude</div>

    <p style="font-size:15px; color:#555;">Ce certificat est décerné à</p>
    <div class="donor-name"><?= e($donation['donor_name'] ?: $client['username']) ?></div>

    <p class="description">
        pour son généreux don en soutien au projet<br>
        <strong>« <?= e($donation['project_title']) ?> »</strong>
    </p>

    <div class="amount"><?= money((float)$donation['amount']) ?></div>

    <div class="meta">
        <div>
            <strong><?= date('d/m/Y', strtotime($donation['created_at'])) ?></strong>
            Date du don
        </div>
        <div>
            <strong><?= e($reference) ?></strong>
            Référence
        </div>
        <div>
            <strong>Confirmé</strong>
            Statut
        </div>
    </div>
</div>

<div class="actions">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Enregistrer en PDF</button>
</div>

</body>
</html>