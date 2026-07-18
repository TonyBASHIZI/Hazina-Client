<?php
require_once __DIR__ . '/includes/functions.php';

if (!isClientLoggedIn()) {
    header('Location: login.php?redirect=profile.php');
    exit;
}

$pdo = getPDO();
$client = currentClientUser();

// Statistiques globales
$stats = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_dons,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) AS total_donne,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) AS dons_confirmes,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) AS dons_attente,
        COUNT(DISTINCT project_id) AS projets_soutenus
     FROM donations
     WHERE user_id = :uid"
);
$stats->execute(['uid' => $client['id']]);
$stats = $stats->fetch();

$totalDonne = (float)$stats['total_donne'];

// Badge selon le montant total donné
if ($totalDonne >= 200) {
    $badge = ['label' => 'Donateur Or', 'color' => '#D4AF37'];
} elseif ($totalDonne >= 50) {
    $badge = ['label' => 'Donateur Argent', 'color' => '#B0B0B0'];
} elseif ($totalDonne > 0) {
    $badge = ['label' => 'Donateur Bronze', 'color' => '#CD7F32'];
} else {
    $badge = ['label' => 'Nouveau membre', 'color' => '#0B0F2A'];
}

// Historique complet des dons
$history = $pdo->prepare(
    "SELECT d.*, p.title AS project_title, p.image AS project_image
     FROM donations d
     JOIN projects p ON p.id = d.project_id
     WHERE d.user_id = :uid
     ORDER BY d.created_at DESC"
);
$history->execute(['uid' => $client['id']]);
$history = $history->fetchAll();

$pageTitle = 'Mon tableau de bord';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Title -->
<div class="page-title-area title-bg-three">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="title-item">
                    <h2>Mon tableau de bord</h2>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><span>Mon compte</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page Title -->

<div class="container ptb-100">

    <div class="d-flex align-items-center justify-content-between flex-wrap mb-4" style="gap:10px;">
        <div>
            <h3 class="mb-1">Bonjour, <?= e($client['username']) ?> 👋</h3>
            <a href="edit-profile.php" class="d-inline-block mt-2" style="color:var(--hf-navy); text-decoration:underline;">Modifier mon profil</a>
            <span class="badge" style="background-color: <?= $badge['color'] ?>; color:#fff; padding:6px 14px; border-radius:20px; font-size:13px;">
                <?= e($badge['label']) ?>
            </span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div style="background: var(--hf-navy); color:#fff; border-radius:12px; padding:22px;">
                <div style="font-size:14px; opacity:.8;">Total donné</div>
                <div style="font-size:26px; font-weight:700; color: var(--hf-gold);"><?= money($totalDonne) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div style="background:#f4f6f9; border-radius:12px; padding:22px; border:1px solid #e5e7eb;">
                <div style="font-size:14px; color:#666;">Dons effectués</div>
                <div style="font-size:26px; font-weight:700; color: var(--hf-navy);"><?= (int)$stats['total_dons'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div style="background:#f4f6f9; border-radius:12px; padding:22px; border:1px solid #e5e7eb;">
                <div style="font-size:14px; color:#666;">En attente de confirmation</div>
                <div style="font-size:26px; font-weight:700; color: var(--hf-navy);"><?= (int)$stats['dons_attente'] ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div style="background:#f4f6f9; border-radius:12px; padding:22px; border:1px solid #e5e7eb;">
                <div style="font-size:14px; color:#666;">Projets soutenus</div>
                <div style="font-size:26px; font-weight:700; color: var(--hf-navy);"><?= (int)$stats['projets_soutenus'] ?></div>
            </div>
        </div>
    </div>

    <!-- Historique -->
    <h4 class="mb-3">Historique de mes dons</h4>

    <?php if ($history): ?>
    <div class="table-responsive">
        <table class="table align-middle" style="background:#fff;">
            <thead style="background:var(--hf-navy); color:#fff;">
<<<<<<< HEAD
    <tr>
        <th>Projet</th>
        <th>Montant</th>
        <th>Méthode</th>
        <th>Statut</th>
        <th>Date</th>
        <th>Certificat</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($history as $don): ?>
    <?php
        $statusLabel = ['completed' => 'Confirmé', 'pending' => 'En attente', 'cancelled' => 'Annulé'][$don['status']] ?? $don['status'];
        $statusColor = ['completed' => '#28a745', 'pending' => '#D4AF37', 'cancelled' => '#6c757d'][$don['status']] ?? '#6c757d';
    ?>
    <tr>
        <td>
            <a href="donation-details.php?id=<?= (int)$don['project_id'] ?>" style="color:var(--hf-navy); font-weight:600;">
                <?= e($don['project_title']) ?>
            </a>
        </td>
        <td><?= money((float)$don['amount']) ?></td>
        <td class="text-capitalize"><?= e($don['payment_method'] ?: '—') ?></td>
        <td>
            <span style="background: <?= $statusColor ?>; color:#fff; padding:4px 12px; border-radius:14px; font-size:12px;">
                <?= e($statusLabel) ?>
            </span>
        </td>
        <td><?= date('d/m/Y', strtotime($don['created_at'])) ?></td>
        <td>
            <?php if ($don['status'] === 'completed'): ?>
                <a href="certificate.php?id=<?= (int)$don['id'] ?>" target="_blank" style="color:var(--hf-gold); font-weight:600;">📄 Certificat</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
=======
                <tr>
                    <th>Projet</th>
                    <th>Montant</th>
                    <th>Méthode</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $don): ?>
                <?php
                    $statusLabel = ['completed' => 'Confirmé', 'pending' => 'En attente', 'cancelled' => 'Annulé'][$don['status']] ?? $don['status'];
                    $statusColor = ['completed' => '#28a745', 'pending' => '#D4AF37', 'cancelled' => '#6c757d'][$don['status']] ?? '#6c757d';
                ?>
                <tr>
                    <td>
                        <a href="donation-details.php?id=<?= (int)$don['project_id'] ?>" style="color:var(--hf-navy); font-weight:600;">
                            <?= e($don['project_title']) ?>
                        </a>
                    </td>
                    <td><?= money((float)$don['amount']) ?></td>
                    <td class="text-capitalize"><?= e($don['payment_method'] ?: '—') ?></td>
                    <td>
                        <span style="background: <?= $statusColor ?>; color:#fff; padding:4px 12px; border-radius:14px; font-size:12px;">
                            <?= e($statusLabel) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($don['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
        </table>
    </div>
    <?php else: ?>
        <div class="text-center py-5">
            <p class="text-muted mb-3">Tu n'as encore fait aucun don.</p>
            <a href="index.php#projects" class="common-btn">Découvrir les projets</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>