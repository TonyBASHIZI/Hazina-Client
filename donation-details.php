
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
    ORDER BY p.created_at DESC
");

$stmt->execute(['id' => $id]);

$project_details = $stmt->fetch(PDO::FETCH_ASSOC);

//var_dump($project_details);die();
$pageTitle = 'Donation_details';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Title -->
        <div class="page-title-area title-bg-three">
            <div class="d-table">
                <div class="d-table-cell">
                    <div class="container">
                        <div class="title-item">
                            <h2>Donation Details</h2>
                            <ul>
                                <li>
                                    <a href="index.html">Home</a>
                                </li>
                                <li>
                                    <span>Donation Details</span>
                                </li>
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
                                </blockquote>
                                
                            </div>

                            <div class="details-share">
                                <div class="row">

                                    <div class="col-sm-6 col-lg-6">
                                        <div class="left">
                                            <ul>
                                                <li>
                                                    <span>Share:</span>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank">
                                                        <i class="icofont-facebook"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank">
                                                        <i class="icofont-twitter"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank">
                                                        <i class="icofont-youtube-play"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank">
                                                        <i class="icofont-instagram"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="col-sm-6 col-lg-6">
                                        <div class="right">
                                            <ul>
                                                <li>
                                                    <span>Tags:</span>
                                                </li>
                                                <li>
                                                    <a href="#">#Donation</a>
                                                </li>
                                                <li>
                                                    <a href="#">#Food</a>
                                                </li>
                                                <li>
                                                    <a href="#">#Help</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>

    <div class="details-payment">
    <h3>Faire un don pour ce projet</h3>

    <?php $client = currentClientUser(); ?>

    <?php if (!empty($_SESSION['donation_error'])): ?>
        <div class="alert alert-danger"><?= e($_SESSION['donation_error']) ?></div>
        <?php unset($_SESSION['donation_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_GET['thanks'])): ?>
        <div class="alert alert-success">Merci pour ton don ! Il est enregistré et sera confirmé sous peu par notre équipe.</div>
    <?php endif; ?>

    <?php if (!$client): ?>
        <div class="alert alert-warning">
            Tu dois être connecté pour faire un don.
        </div>
        <div class="text-center" style="display:flex; gap:10px; justify-content:center;">
            <a class="btn common-btn" href="login.php?redirect=<?= urlencode('donation-details.php?id=' . $id) ?>">Se connecter</a>
            <a class="btn common-btn" href="register.php?redirect=<?= urlencode('donation-details.php?id=' . $id) ?>">Créer un compte</a>
        </div>
    <?php else: ?>
    <form method="post" action="process_donation.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="project_id" value="<?= (int)$id ?>">
        <input type="hidden" name="payment_method" id="paymentMethodInput" value="mobile_money:mpesa">

        
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

<!-- Champ réellement envoyé au serveur -->
<input type="hidden" name="payment_method" id="paymentMethodField" value="mpesa">

<!-- Choix de l'opérateur mobile money -->
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
<!-- Champs carte Visa (affichés seulement si Visa est choisi) -->
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
        document.querySelector(`#mobileOperators .operator-box[data-value="${document.getElementById('paymentMethodField').value}"]`)?.style.setProperty('border-color', '#ff6015');
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

        <!-- Choix visible seulement si "Carte Visa" -->
        <div id="visaBlock" style="display:none; margin:12px 0;">
            <div class="alert alert-info mb-0">Paiement par carte Visa — tu seras contacté pour finaliser la transaction en toute sécurité.</div>
        </div>

        <!-- Montants prédéfinis -->
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
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="isAnonymous">
                <label class="form-check-label" for="isAnonymous">
                    Faire ce don anonymement (ton nom ne sera pas affiché publiquement)
                </label>
            </div>
            <div class="text-center">
                <button type="submit" class="btn common-btn">Confirmer le don</button>
            </div>
        </div>
    </form>

    <script>
        // Sélection du montant
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

        // Sélection de l'opérateur mobile money
        const providerInput = document.getElementById('paymentMethodInput');
        document.querySelectorAll('.provider-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.provider-btn').forEach(b => {
                    b.style.opacity = '0.55';
                });
                btn.style.opacity = '1';
                providerInput.value = btn.dataset.provider;
            });
        });
        // Marque M-Pesa comme actif par défaut
        document.querySelector('.provider-btn[data-provider="mobile_money:mpesa"]').style.opacity = '1';
        document.querySelectorAll('.provider-btn:not([data-provider="mobile_money:mpesa"])').forEach(b => b.style.opacity = '0.55');

        // Basculer Mobile Money / Visa
        document.querySelectorAll('.pay-toggle').forEach(radio => {
            radio.addEventListener('change', () => {
                const isMobile = document.getElementById('payMobile').checked;
                document.getElementById('mobileProviders').style.display = isMobile ? 'flex' : 'none';
                document.getElementById('visaBlock').style.display = isMobile ? 'none' : 'block';
                if (!isMobile) providerInput.value = 'visa';
                else providerInput.value = document.querySelector('.provider-btn[style*="opacity: 1"]')?.dataset.provider || 'mobile_money:mpesa';
            });
        });
    </script>
    <?php endif; ?>
</div>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="widget-area">

                            <div class="search widget-item">
                                <form>
                                    <input type="text" class="form-control" placeholder="Search...">
                                    <button type="submit" class="btn">
                                        <i class="icofont-search-1"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="post widget-item">
                                <h3>Popular Post</h3>
                                <div class="post-inner">
                                    <ul class="align-items-center">
                                        <li>
                                            <img src="assets/img/blog/blog-details1.jpg" alt="Details">
                                        </li>
                                        <li>
                                            <h4>
                                                <a href="#">Donate for nutrition less poor people</a>
                                            </h4>
                                            <p>By - <a href="#">Admin</a></p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="post-inner">
                                    <ul class="align-items-center">
                                        <li>
                                            <img src="assets/img/blog/blog-details2.jpg" alt="Details">
                                        </li>
                                        <li>
                                            <h4>
                                                <a href="#">Charity meetup in Berlin next year</a>
                                            </h4>
                                            <p>By - <a href="#">Admin</a></p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="post-inner">
                                    <ul class="align-items-center">
                                        <li>
                                            <img src="assets/img/blog/blog-details3.jpg" alt="Details">
                                        </li>
                                        <li>
                                            <h4>
                                                <a href="#">Donate for poor people for food & water</a>
                                            </h4>
                                            <p>By - <a href="#">Admin</a></p>
                                        </li>
                                    </ul>
                                </div>
                                <div class="post-inner">
                                    <ul class="align-items-center">
                                        <li>
                                            <img src="assets/img/blog/blog-details4.jpg" alt="Details">
                                        </li>
                                        <li>
                                            <h4>
                                                <a href="#">Little Sanjana joined in a charity to help people</a>
                                            </h4>
                                            <p>By - <a href="#">Admin</a></p>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="common-right-content widget-item">
                                <h3>Archives</h3>
                                <ul>
                                    <li>
                                        <a href="#">January 2024</a>
                                    </li>
                                    <li>
                                        <a href="#">May 2024</a>
                                    </li>
                                    <li>
                                        <a href="#">April 2024</a>
                                    </li>
                                    <li>
                                        <a href="#">June 2024</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="common-right-content widget-item">
                                <h3>Categories</h3>
                                <ul>
                                    <li>
                                        <a href="#">Education (10)</a>
                                    </li>
                                    <li>
                                        <a href="#">Medical (25)</a>
                                    </li>
                                    <li>
                                        <a href="#">Food & Water (14)</a>
                                    </li>
                                    <li>
                                        <a href="#">National Charity (2)</a>
                                    </li>
                                    <li>
                                        <a href="#">Cloth (4)</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="instagram widget-item">
                                <h3>Instagram post</h3>
                                <div class="row m-0">

                                    <div class="col-4 col-sm-3 col-lg-4 p-0">
                                        <div class="instagram-item">
                                            <img src="assets/img/blog/instagram1.jpg" alt="Instagram">
                                            <a href="#">
                                                <i class="icofont-instagram"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-3 col-lg-4 p-0">
                                        <div class="instagram-item">
                                            <img src="assets/img/blog/instagram2.jpg" alt="Instagram">
                                            <a href="#">
                                                <i class="icofont-instagram"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-3 col-lg-4 p-0">
                                        <div class="instagram-item">
                                            <img src="assets/img/blog/instagram3.jpg" alt="Instagram">
                                            <a href="#">
                                                <i class="icofont-instagram"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-3 col-lg-4 p-0">
                                        <div class="instagram-item">
                                            <img src="assets/img/blog/instagram4.jpg" alt="Instagram">
                                            <a href="#">
                                                <i class="icofont-instagram"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-3 col-lg-4 p-0">
                                        <div class="instagram-item">
                                            <img src="assets/img/blog/instagram5.jpg" alt="Instagram">
                                            <a href="#">
                                                <i class="icofont-instagram"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-4 col-sm-3 col-lg-4 p-0">
                                        <div class="instagram-item">
                                            <img src="assets/img/blog/instagram6.jpg" alt="Instagram">
                                            <a href="#">
                                                <i class="icofont-instagram"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Donation Details -->






<?php require_once __DIR__ . '/includes/footer.php'; ?>