<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;
$offset = ($page - 1) * $perPage;

$projectFilter = (int)($_GET['project_id'] ?? 0);
$filterClause = $projectFilter ? "AND project_id = :pid" : "";

$countSql = "SELECT COUNT(*) FROM donations WHERE status = 'completed' AND donor_name != 'Anonymous' AND donor_name IS NOT NULL $filterClause";
$countStmt = $pdo->prepare($countSql);
if ($projectFilter) $countStmt->bindValue(':pid', $projectFilter, PDO::PARAM_INT);
$countStmt->execute();
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$sql = "SELECT d.donor_name, d.amount, d.created_at, p.title AS project_title, u.photo
     FROM donations d
     LEFT JOIN users u ON u.id = d.user_id
     JOIN projects p ON p.id = d.project_id
     WHERE d.status = 'completed' AND d.donor_name != 'Anonymous' AND d.donor_name IS NOT NULL $filterClause
     ORDER BY d.created_at DESC
     LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
if ($projectFilter) $stmt->bindValue(':pid', $projectFilter, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$donors = $stmt->fetchAll();

$pageTitle = 'Nos donateurs';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-title-area title-bg-three">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="title-item">
                    <h2>Nos donateurs</h2>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><span>Donateurs</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container ptb-100">
        <?php if ($projectFilter):
        $pname = $pdo->prepare('SELECT title FROM projects WHERE id = :id');
        $pname->execute(['id' => $projectFilter]);
        $pname = $pname->fetchColumn();
    ?>
    <p class="text-muted mb-4">Donateurs du projet « <strong><?= e($pname) ?></strong> » — <?= (int)$totalRows ?> don(s) confirmé(s). <a href="donors.php">Voir tous les donateurs</a></p>
    <?php else: ?>
    <p class="text-muted mb-4"><?= (int)$totalRows ?> don(s) confirmé(s) au total, merci à chacun d'entre eux.</p>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($donors as $d): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="d-flex align-items-center" style="background:#f8f9fa; border-radius:10px; padding:15px; gap:15px;">
                <?php if (!empty($d['photo'])): ?>
                    <img src="<?= e($d['photo']) ?>" style="width:55px;height:55px;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <div style="width:55px;height:55px;border-radius:50%;background:var(--hf-navy);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="icofont-user-alt-3" style="color:var(--hf-gold); font-size:22px;"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <div style="font-weight:700; color:var(--hf-navy);"><?= e($d['donor_name']) ?></div>
                    <small class="text-muted"><?= money((float)$d['amount']) ?> — <?= e($d['project_title']) ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (!$donors): ?>
        <div class="col-12 text-center text-muted py-5">Aucun donateur pour le moment.</div>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>