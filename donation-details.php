
<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*,
        (SELECT COUNT(DISTINCT donor_email)
         FROM donations
         WHERE project_id = p.id
           AND status = 'completed'
           AND donor_email != '') +
        (SELECT COUNT(*)
         FROM donations
         WHERE project_id = p.id
           AND status = 'completed'
           AND (donor_email = '' OR donor_email IS NULL)) AS donor_count
    FROM projects p
    WHERE p.id = :id
");
$stmt->execute(['id' => $id]);
$project_details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project_details) {
    header('Location: index.php');
    exit;
}

$gallery = $pdo->prepare('SELECT * FROM project_images WHERE project_id = :pid ORDER BY is_default DESC, created_at ASC');
$gallery->execute(['pid' => $id]);
$galleryImages = $gallery->fetchAll();

$pct = $project_details['goal_amount'] > 0 ? min(100, round($project_details['raised_amount'] / $project_details['goal_amount'] * 100)) : 0;
$client = currentClientUser();
$flashError = $_SESSION['donation_error'] ?? null;
unset($_SESSION['donation_error']);

$pageTitle = $project_details['title'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Title -->
<div class="page-title-area title-bg-three">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="title-item">
                    <h2><?= e($project_details['title']) ?></h2>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><span>Détails du don</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page Title -->

<!-- Donation Details -->
<div class="donation-details-area ptb-100">
    <div class="container">
        <div class="row">

            <div class="col-lg-8">
                <div class="details-item">

                    <div class="details-img">
                        <img src="<?= e($project_details['image'] ?: 'assets/img/donation/donation-details1.jpg') ?>" alt="<?= e($project_details['title']) ?>" onerror="this.onerror=null;this.src='assets/img/donation/donation-details1.jpg';">
                        <h2><?= e($project_details['title']) ?></h2>
                        <p><?= nl2br(e($project_details['description'])) ?></p>
                    </div>

                    <?php if (count($galleryImages) > 1): ?>
                    <div class="row mb-4">
                        <?php foreach ($galleryImages as $img): ?>
                            <?php if ($img['image_path'] === $project_details['image']) continue; ?>
                            <div class="col-4 mb-3">
                                <img src="<?= e($img['image_path']) ?>" alt="Galerie" style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <blockquote>
                        <i class="icofont-quote-left"></i>
                        Chaque don compte. Ensemble, nous pouvons faire une vraie différence pour ce projet et les personnes qu'il soutient.
                    </blockquote>
                    <p>Objectif de collecte : <strong><?= money((float)$project_details['goal_amount']) ?></strong> — déjà <strong><?= money((float)$project_details['raised_amount']) ?></strong> récoltés grâce à la générosité de <?= (int)$project_details['donor_count'] ?> donateur(s).</p>

                    <?php if (!empty($project_details['video'])): ?>
                    <div class="mb-4">
                        <h4 class="mb-3">Vidéo du projet</h4>
                        <video src="<?= e($project_details['video']) ?>" controls style="width:100%; border-radius:10px;"></video>
                    </div>
                    <?php endif; ?>

                    <div class="details-share">
                        <div class="row">
                            <div class="col-sm-6 col-lg-6">
                                <div class="left">
                                    <ul>
                                        <li><span>Partager :</span></li>
                                        <li><a href="#" target="_blank"><i class="icofont-facebook"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="icofont-twitter"></i></a></li>
                                        <li><a href="#" target="_blank"><i class="icofont-instagram"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-6">
                                <div class="right">
                                    <ul>
                                        <li><span>Progression :</span></li>
                                        <li><a href="#"><?= $pct ?>% atteint</a></li>
                                        <li><a href="#"><?= money((float)$project_details['raised_amount']) ?> / <?= money((float)$project_details['goal_amount']) ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="details-payment">
                        <h3>Faire un don pour ce projet</h3>

                        <?php if ($flashError): ?>
                            <div class="alert alert-danger"><?= e($flashError) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($_GET['thanks'])): ?>
                            <div class="alert alert-success">
                                Merci pour ton don ! Il est enregistré et sera confirmé sous peu par notre équipe.
                            </div>
                        <?php endif; ?>

                        <?php if ($project_details['status'] === 'completed'): ?>
                            <div class="alert alert-success">
                                <i class="icofont-check-circled"></i>
                                Ce projet a atteint son objectif et la collecte est maintenant <strong>terminée</strong>. Merci à tous les donateurs qui ont rendu ça possible !
                            </div>
                            <div class="text-center">
                                <a href="index.php#projects" class="btn common-btn">Découvrir d'autres projets actifs</a>
                            </div>

                        <?php elseif ($project_details['status'] === 'inactive'): ?>
                            <div class="alert alert-secondary">
                                Ce projet n'accepte pas de dons pour le moment.
                            </div>

                        <?php elseif (!$client): ?>
                            <div class="alert alert-warning">
                                Tu dois être connecté pour faire un don, afin qu'on puisse te contacter et suivre ta contribution.
                            </div>
                            <div class="text-center" style="gap:10px; display:flex; justify-content:center;">
                                <a class="btn common-btn" href="login.php?redirect=<?= urlencode('donation-details.php?id=' . $id) ?>">Se connecter</a>
                                <a class="btn common-btn" href="register.php?redirect=<?= urlencode('donation-details.php?id=' . $id) ?>">Créer un compte</a>
                            </div>

                        <?php else: ?>
                        <form method="post" action="process_donation.php">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="project_id" value="<?= (int)$id ?>">

                            <!-- Méthode de paiement -->
                            <div class="form-radio-area">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_group" id="payMobile" value="mobile" checked onchange="togglePaymentUI()">
                                    <label class="form-check-label" for="payMobile">Mobile Money</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_group" id="payVisa" value="visa" onchange="togglePaymentUI()">
                                    <label class="form-check-label" for="payVisa">Carte Visa</label>
                                </div>
                            </div>

                            <input type="hidden" name="payment_method" id="paymentMethodField" value="mpesa">

                            <div id="mobileOperators" class="mb-3 mt-3">
                                <label class="d-block mb-2"><strong>Choisis ton opérateur</strong></label>
                                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                    <div class="operator-box" data-value="mpesa" onclick="selectOperator('mpesa', this)"
                                         style="cursor:pointer; border:2px solid #ff6015; border-radius:8px; padding:10px 16px; min-width:110px; text-align:center;">
                                        <div style="background:#4caf50; color:#fff; font-weight:700; border-radius:6px; padding:8px 0; margin-bottom:6px;">M-PESA</div>
                                        <small>Vodacom</small>
                                    </div>
                                    <div class="operator-box" data-value="airtel" onclick="selectOperator('airtel', this)"
                                         style="cursor:pointer; border:2px solid #e5e7eb; border-radius:8px; padding:10px 16px; min-width:110px; text-align:center;">
                                        <div style="background:#e60000; color:#fff; font-weight:700; border-radius:6px; padding:8px 0; margin-bottom:6px;">Airtel Money</div>
                                        <small>Airtel</small>
                                    </div>
                                    <div class="operator-box" data-value="orange" onclick="selectOperator('orange', this)"
                                         style="cursor:pointer; border:2px solid #e5e7eb; border-radius:8px; padding:10px 16px; min-width:110px; text-align:center;">
                                        <div style="background:#ff7900; color:#fff; font-weight:700; border-radius:6px; padding:8px 0; margin-bottom:6px;">Orange Money</div>
                                        <small>Orange</small>
                                    </div>
                                </div>
                            </div>

                            <div id="visaCardFields" class="mb-3 mt-3" style="display:none;">
                                <label class="d-block mb-2"><strong>Informations de la carte</strong></label>
                                <div class="form-group mb-2">
                                    <input type="text" id="cardHolder" class="form-control" placeholder="Nom sur la carte" autocomplete="cc-name">
                                </div>
                                <div class="form-group mb-2">
                                    <input type="text" id="cardNumber" class="form-control" placeholder="Numéro de carte" maxlength="19" autocomplete="cc-number">
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="text" id="cardExpiry" class="form-control" placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
                                    </div>
                                    <div class="col-6">
                                        <input type="text" id="cardCvv" class="form-control" placeholder="CVV" maxlength="4" autocomplete="cc-csc">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">🔒 Paiement sécurisé — tes informations de carte ne sont pas stockées sur nos serveurs.</small>
                            </div>

                            <script>
                                function selectOperator(value, el) {
                                    document.getElementById('paymentMethodField').value = value;
                                    document.querySelectorAll('#mobileOperators .operator-box').forEach(b => b.style.borderColor = '#e5e7eb');
                                    el.style.borderColor = '#ff6015';
                                }
                                function togglePaymentUI() {
                                    const isMobile = document.getElementById('payMobile').checked;
                                    document.getElementById('mobileOperators').style.display = isMobile ? 'block' : 'none';
                                    document.getElementById('visaCardFields').style.display = isMobile ? 'none' : 'block';
                                    if (!isMobile) {
                                        document.getElementById('paymentMethodField').value = 'visa';
                                    } else {
                                        const current = document.getElementById('paymentMethodField').value;
                                        document.getElementById('paymentMethodField').value = (current === 'visa' ? 'mpesa' : current);
                                    }
                                }
                                document.getElementById('cardNumber')?.addEventListener('input', function () {
                                    this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
                                });
                                document.getElementById('cardExpiry')?.addEventListener('input', function () {
                                    this.value = this.value.replace(/\D/g, '').replace(/(\d{2})(\d)/, '$1/$2').slice(0, 5);
                                });
                                togglePaymentUI();
                            </script>

                            <div class="mb-3 mt-3">
                                <label class="d-block mb-2"><strong>Choisis un montant ($)</strong></label>
                                <div id="amountButtons" style="display:flex; flex-wrap:wrap; gap:8px;">
                                    <?php foreach ([10, 20, 30, 50, 100] as $amt): ?>
                                        <button type="button" class="amount-btn" data-amount="<?= $amt ?>"
                                            style="padding:10px 18px; border:2px solid #ff6015; background:#fff; color:#ff6015; border-radius:6px; font-weight:600; cursor:pointer;">
                                            <?= $amt ?>$
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="number" step="0.01" min="1" name="amount" id="amountInput" class="form-control mt-2" placeholder="Ou saisis un autre montant" required>
                            </div>
                            <script>
                                document.querySelectorAll('.amount-btn').forEach(btn => {
                                    btn.addEventListener('click', () => {
                                        document.querySelectorAll('.amount-btn').forEach(b => {
                                            b.style.background = '#fff';
                                            b.style.color = '#ff6015';
                                        });
                                        btn.style.background = '#ff6015';
                                        btn.style.color = '#fff';
                                        document.getElementById('amountInput').value = btn.dataset.amount;
                                    });
                                });
                            </script>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="isAnonymous">
                                <label class="form-check-label" for="isAnonymous">
                                    Faire ce don anonymement (ton nom ne sera pas affiché publiquement)
                                </label>
                            </div>

                            <div class="form-input-area">
                                <div class="form-group">
                                    <label><i class="icofont-user-alt-3"></i></label>
                                    <input type="text" class="form-control" value="<?= e($client['username']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label><i class="icofont-ui-email"></i></label>
                                    <input type="email" class="form-control" value="<?= e($client['mail']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label><i class="icofont-ui-call"></i></label>
                                    <input type="text" class="form-control" value="<?= e($client['telephone']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label><i class="icofont-comment"></i></label>
                                    <input type="text" name="message" class="form-control" placeholder="Message (optionnel)" maxlength="255">
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn common-btn">Confirmer le don</button>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="widget-area">

                    <div class="search widget-item">
                        <form method="get" action="index.php">
                            <input type="text" name="q" class="form-control" placeholder="Rechercher un projet...">
                            <button type="submit" class="btn">
                                <i class="icofont-search-1"></i>
                            </button>
                        </form>
                    </div>

                    <?php
                    $popularProjects = $pdo->query(
                        "SELECT id, title, image,
                            CASE WHEN goal_amount > 0 THEN (raised_amount / goal_amount) * 100 ELSE 0 END AS pct
                         FROM projects
                         WHERE status = 'active'
<<<<<<< HEAD
                         ORDER BY pct DESC
=======
                         ORDER BY pct ASC
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                         LIMIT 4"
                    )->fetchAll();
                    ?>
                    <div class="post widget-item">
                        <h3>Projets populaires</h3>
                        <?php foreach ($popularProjects as $pp): ?>
                        <div class="post-inner">
                            <ul class="align-items-center">
                                <li>
                                    <img src="<?= e($pp['image'] ?: 'assets/img/blog/blog-details1.jpg') ?>" alt="<?= e($pp['title']) ?>" onerror="this.onerror=null;this.src='assets/img/blog/blog-details1.jpg';">
                                </li>
                                <li>
                                    <h4>
                                        <a href="donation-details.php?id=<?= (int)$pp['id'] ?>"><?= e($pp['title']) ?></a>
                                    </h4>
                                    <p><?= round($pp['pct']) ?>% collecté</p>
                                </li>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                        <?php if (!$popularProjects): ?>
                            <p class="text-muted">Aucun autre projet pour le moment.</p>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="index.php#projects" class="common-btn" style="padding:8px 20px; font-size:14px;">Voir tous les projets</a>
                        </div>
                    </div>

                    <?php
                    $archives = $pdo->query(
                        "SELECT DATE_FORMAT(updated_at, '%Y-%m') AS ym, DATE_FORMAT(updated_at, '%M %Y') AS label, COUNT(*) AS nb
                         FROM projects
                         WHERE status = 'completed'
                         GROUP BY ym, label
                         ORDER BY ym DESC
                         LIMIT 6"
                    )->fetchAll();
                    ?>
                    <div class="common-right-content widget-item">
                        <h3>Archives</h3>
                        <ul>
                            <?php foreach ($archives as $arc): ?>
                            <li>
                                <a href="index.php#events"><?= e($arc['label']) ?> (<?= (int)$arc['nb'] ?>)</a>
                            </li>
                            <?php endforeach; ?>
                            <?php if (!$archives): ?>
                                <li><span class="text-muted">Aucun projet terminé pour le moment</span></li>
                            <?php endif; ?>
                        </ul>
                    </div>

<<<<<<< HEAD
                    <?php
                        $recentDonorsStmt = $pdo->prepare(
                            "SELECT donor_name FROM donations
                            WHERE status = 'completed' AND donor_name != 'Anonymous' AND donor_name IS NOT NULL AND project_id = :pid
                            ORDER BY created_at DESC
                            LIMIT 20"
                        );
                        $recentDonorsStmt->execute(['pid' => $id]);
                        $recentDonors = $recentDonorsStmt->fetchAll(PDO::FETCH_COLUMN);
                        ?>

                        <div class="common-right-content widget-item">
                            <h3>Derniers donateurs</h3>
                            <ul>
                                <?php foreach ($recentDonors as $name): ?>
                                <li>
                                    <a href="#"><i class="icofont-user-alt-3"></i> <?= e($name) ?></a>
                                </li>
                                <?php endforeach; ?>
                                <?php if (!$recentDonors): ?>
                                    <li><span class="text-muted">Aucun donateur pour le moment</span></li>
                                <?php endif; ?>
                            </ul>
                            <div class="text-center mt-3">
                                <a href="donors.php?project_id=<?= (int)$id ?>" class="common-btn" style="padding:8px 20px; font-size:14px;">Voir plus</a>
                            </div>
                        </div>

=======
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Donation Details -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>