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

$pageTitle = $project_details['title'] . ' — ' . $pct . '% collecté';
$pageDescription = 'Objectif : ' . money((float)$project_details['goal_amount']) . ' — Déjà collecté : ' . money((float)$project_details['raised_amount']) . ' (' . $pct . '%). ' . mb_strimwidth(strip_tags($project_details['description']), 0, 100, '...');
$pageImage = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $project_details['image'];
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

                    <?php
                    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $shareText = 'Soutiens ce projet : ' . $project_details['title'];
                    $fbShare = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($currentUrl);
                    $twShare = 'https://twitter.com/intent/tweet?url=' . urlencode($currentUrl) . '&text=' . urlencode($shareText);
                    $waShare = 'https://wa.me/?text=' . urlencode($shareText . ' ' . $currentUrl);
                    ?>

                    <div class="details-share">
                        <div class="row">
                            <div class="col-sm-6 col-lg-6">
                                <div class="left">
                                    <ul>
                                        <li><span>Partager :</span></li>
                                        <li><a href="<?= e($fbShare) ?>" target="_blank" rel="noopener" style="background-color:#1877F2 !important; border-color:#1877F2 !important;"><i class="icofont-facebook" style="color:#fff;"></i></a></li>
                                        <li><a href="<?= e($twShare) ?>" target="_blank" rel="noopener" style="background-color:#1DA1F2 !important; border-color:#1DA1F2 !important;"><i class="icofont-twitter" style="color:#fff;"></i></a></li>
                                        <li><a href="<?= e($waShare) ?>" target="_blank" rel="noopener" style="background-color:#25D366 !important; border-color:#25D366 !important;"><i class="icofont-brand-whatsapp" style="color:#fff;"></i></a></li>
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

                        <?php if (!empty($_GET['ussd_push'])):
                                $attemptId = (int)($_SESSION['ussd_attempt_id'] ?? 0);
                                $attemptQuery = $pdo->prepare('SELECT id FROM ussd_payment_attempts WHERE id = :id AND user_id = :uid AND project_id = :pid');
                                $attemptQuery->execute(['id' => $attemptId, 'uid' => $client['id'], 'pid' => $id]);
                                $ussdAttemptId = (int)$attemptQuery->fetchColumn();
                            ?>
                                <div class="alert alert-warning" id="ussdPendingAlert">
                                    <i class="icofont-mobile"></i> <span id="ussdPendingMessage"><?= e($_SESSION['ussd_push_message'] ?? 'Transaction envoyée. Valide le push message sur ton téléphone pour finaliser ton don.') ?></span>
                                    <div class="mt-2"><span class="spinner-border spinner-border-sm"></span> Vérification automatique en cours...</div>
                                </div>
                                <div class="alert alert-success" id="ussdSuccessAlert" style="display:none;">
                                    <i class="icofont-check-circled"></i> Paiement confirmé ! Merci pour ton don 🎉
                                </div>
                                <div class="alert alert-danger" id="ussdFailedAlert" style="display:none;">
                                    Le paiement n'a pas abouti. Tu peux réessayer.
                                </div>
                                <?php unset($_SESSION['ussd_push_message']); ?>

                                <script>
                                (function() {
                                    const attemptId = <?= (int)$ussdAttemptId ?>;
                                    if (!attemptId) return;

                                    let attempts = 0;
                                    const maxAttempts = 20; // ~2 minutes à 6s d'intervalle

                                    const interval = setInterval(() => {
                                        attempts++;
                                        fetch('includes/ussd-check-status.php?attempt_id=' + attemptId)
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.status === 'completed') {
                                                    clearInterval(interval);
                                                    document.getElementById('ussdPendingAlert').style.display = 'none';
                                                    document.getElementById('ussdSuccessAlert').style.display = 'block';
                                                    setTimeout(() => {
                                                        const url = new URL(location.href);
                                                        url.searchParams.delete('ussd_push');
                                                        history.replaceState({}, '', url);
                                                        location.reload();
                                                    }, 2000);
                                                } else if (data.status === 'awaiting_callback') {
                                                    document.getElementById('ussdPendingMessage').textContent = 'Paiement confirmé par FlexPay. En attente de la confirmation finale pour enregistrer le don...';
                                                } else if (data.status === 'cancelled' || data.status === 'refunded') {
                                                    clearInterval(interval);
                                                    document.getElementById('ussdPendingAlert').style.display = 'none';
                                                    document.getElementById('ussdFailedAlert').style.display = 'block';
                                                    if (data.status === 'refunded') {
                                                        document.getElementById('ussdFailedAlert').textContent = 'Le paiement a été remboursé.';
                                                    }
                                                } else if (attempts >= maxAttempts) {
                                                    clearInterval(interval);
                                                }
                                            })
                                            .catch(() => {});
                                    }, 6000);
                                })();
                                </script>
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
                        <form method="post" action="process_donation.php" id="donationForm">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="project_id" value="<?= (int)$id ?>">

                            <!-- Méthode de paiement -->
                            <div class="form-radio-area">
                                <div class="form-check form-check-inline" style="display:none;">
                                    <input class="form-check-input" type="radio" name="payment_group" id="payMobile" value="mobile" onchange="togglePaymentUI(this.value)">
                                    <label class="form-check-label" for="payMobile">Mobile Money</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="payment_group" id="payUssd" value="ussd" checked onchange="togglePaymentUI(this.value)">
                                        <label class="form-check-label" for="payUssd">Paiement USSD (push)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="payment_group" id="payVisa" value="visa" onchange="togglePaymentUI(this.value)">
                                        <label class="form-check-label" for="payVisa">Carte Visa</label>
                                    </div>
                                </div>

                                <div id="ussdInfo" class="mb-3 mt-3">
                                    <div class="alert alert-info" style="font-size:14px;">
                                        <i class="icofont-info-circle"></i> Tu recevras une notification sur ton téléphone pour valider le paiement directement par USSD, sans quitter le site.
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-3 p-3" style="gap:16px; background:#f6f8fb; border-radius:8px;">
                                        <div>
                                            <label for="ussdCurrency" class="mb-0"><strong>Devise du paiement</strong></label>
                                            <small class="text-muted d-block">Choisis la devise de ton don.</small>
                                        </div>
                                        <select name="ussd_currency" id="ussdCurrency" class="form-select" style="max-width:145px; flex-shrink:0;">
                                            <option value="USD" selected>USD ($)</option>
                                            <option value="CDF">CDF (FC)</option>
                                        </select>
                                    </div>
                                </div>

                            <input type="hidden" name="payment_method" id="paymentMethodField" value="ussd">
                            <input type="hidden" name="ussd_operator" id="ussdOperatorField" value="mpesa">

                            <div id="mobileOperators" class="mb-3 mt-3">
                                <label class="d-block mb-2"><strong>Choisis ton réseau</strong></label>
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
                                <small class="text-muted d-block mt-2">Choisis le réseau associé au numéro de paiement.</small>
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

                            <div class="mb-3 mt-3">
                                <label class="d-block mb-2"><strong id="amountLabel">Choisis un montant ($)</strong></label>
                                <div id="amountButtons" style="display:flex; flex-wrap:wrap; gap:8px;">
                                    <?php foreach ([10, 20, 30, 50, 100] as $amt): ?>
                                        <button type="button" class="amount-btn" data-usd="<?= $amt ?>" data-amount="<?= $amt ?>"
                                            style="padding:10px 18px; border:2px solid #ff6015; background:#fff; color:#ff6015; border-radius:6px; font-weight:600; cursor:pointer;">
                                            <?= $amt ?>$
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <input type="number" step="0.01" min="1" name="amount" id="amountInput" class="form-control mt-2" placeholder="Ou saisis un autre montant" required>
                            </div>

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
                                    <input type="text" id="paymentPhoneDisplay" class="form-control" value="<?= e($client['telephone']) ?>" readonly style="background:#f4f6f9;">
                                    <input type="hidden" id="paymentPhoneField" name="payment_phone" value="<?= e($client['telephone']) ?>">
                                </div>
                                <div class="form-group">
                                    <label><i class="icofont-comment"></i></label>
                                    <input type="text" name="message" class="form-control" placeholder="Message (optionnel)" maxlength="255">
                                </div>
                                <div class="text-center">
                                    <button type="button" id="openPaymentConfirm" class="btn common-btn">Confirmer le don</button>
                                </div>
                            </div>
                        </form>

                        <div class="modal fade" id="paymentPhoneModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius:12px;">
                                    <div class="modal-header" style="background:var(--hf-navy); color:#fff; border-radius:12px 12px 0 0;">
                                        <h5 class="modal-title">Confirmation du paiement</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Vous allez payer avec ce numéro :</p>
                                        <p style="font-size:20px; font-weight:700; color:var(--hf-navy);" id="modalPhoneDisplay"><?= e($client['telephone']) ?></p>

                                        <div id="phoneChangeArea" style="display:none;" class="mt-3">
                                            <label class="form-label">Nouveau numéro</label>
                                            <div class="d-flex" style="gap:8px;">
                                                <select id="newPhoneCountry" class="form-select" style="max-width:110px;">
                                                    <option value="+243" selected>🇨🇩 +243</option>
                                                    <option value="+242">🇨🇬 +242</option>
                                                    <option value="+250">🇷🇼 +250</option>
                                                    <option value="+257">🇧🇮 +257</option>
                                                    <option value="+256">🇺🇬 +256</option>
                                                    <option value="+255">🇹🇿 +255</option>
                                                    <option value="+260">🇿🇲 +260</option>
                                                    <option value="+33">🇫🇷 +33</option>
                                                    <option value="+1">🇺🇸 +1</option>
                                                    <option value="+44">🇬🇧 +44</option>
                                                </select>
                                                <input type="text" id="newPhoneNumber" class="form-control" placeholder="Numéro sans indicatif">
                                            </div>
                                        </div>

                                        <div class="mt-4 d-flex" style="gap:10px;">
                                            <button type="button" id="phoneYesBtn" class="btn common-btn flex-fill">Oui, c'est correct</button>
                                            <button type="button" id="phoneNoBtn" class="btn btn-outline-secondary flex-fill">Non, changer</button>
                                        </div>
                                        <button type="button" id="phoneConfirmChangeBtn" class="btn common-btn w-100 mt-2" style="display:none;">Valider ce numéro</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="ussdProcessingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content text-center" style="border-radius:12px;">
                                    <div class="modal-body p-4">
                                        <div id="ussdProcessingSpinner" class="spinner-border text-warning mb-3" role="status">
                                            <span class="visually-hidden">Traitement...</span>
                                        </div>
                                        <h5 id="ussdProcessingTitle">Traitement du paiement</h5>
                                        <p id="ussdProcessingMessage" class="mb-3">Veuillez confirmer la demande sur votre téléphone. Gardez cette page ouverte.</p>
                                        <div class="d-flex justify-content-center" style="gap:10px;">
                                            <button type="button" id="ussdProcessingRetry" class="btn common-btn" style="display:none;">Réessayer</button>
                                            <button type="button" id="ussdProcessingClose" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="display:none;">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                       <script>
                            function selectOperator(value, el) {
                                document.getElementById('ussdOperatorField').value = value;
                                document.querySelectorAll('#mobileOperators .operator-box').forEach(b => b.style.borderColor = '#e5e7eb');
                                el.style.borderColor = '#ff6015';
                            }

                            function togglePaymentUI(selectedMethod) {
                                // Use the changed radio's value directly so the payment method
                                // does not depend on a stale hidden field or a stale checked read.
                                if (selectedMethod) {
                                    const selectedRadio = document.querySelector('input[name="payment_group"][value="' + selectedMethod + '"]');
                                    if (selectedRadio) selectedRadio.checked = true;
                                }
                                const isMobile = document.getElementById('payMobile').checked;
                                const isUssd = document.getElementById('payUssd').checked;
                                const isVisa = document.getElementById('payVisa').checked;

                                document.getElementById('mobileOperators').style.display = isUssd ? 'block' : 'none';
                                document.getElementById('visaCardFields').style.display = isVisa ? 'block' : 'none';
                                document.getElementById('ussdInfo').style.display = isUssd ? 'block' : 'none';
                                updateDonationCurrencyUI();

                                if (isVisa) {
                                    document.getElementById('paymentMethodField').value = 'visa';
                                } else if (isUssd) {
                                    document.getElementById('paymentMethodField').value = 'ussd';
                                } else {
                                    const current = document.getElementById('paymentMethodField').value;
                                    document.getElementById('paymentMethodField').value = (['visa','ussd'].includes(current) ? 'mpesa' : current);
                                }
                            }

                            function updateDonationCurrencyUI() {
                                const isCdf = document.getElementById('payUssd').checked &&
                                    document.getElementById('ussdCurrency').value === 'CDF';
                                const rate = <?= (int)USD_TO_CDF_RATE ?>;
                                const amountInput = document.getElementById('amountInput');
                                document.getElementById('amountLabel').textContent = isCdf
                                    ? 'Choisis un montant (FC)' : 'Choisis un montant ($)';
                                amountInput.step = isCdf ? '1' : '0.01';
                                amountInput.placeholder = isCdf
                                    ? 'Ou saisis un montant en CDF'
                                    : 'Ou saisis un montant en USD';
                                document.querySelectorAll('.amount-btn').forEach(btn => {
                                    const usd = Number(btn.dataset.usd);
                                    const shown = isCdf ? Math.round(usd * rate) : usd;
                                    btn.dataset.amount = shown;
                                    btn.textContent = isCdf
                                        ? shown.toLocaleString('fr-FR') + ' FC'
                                        : usd + '$';
                                });
                                amountInput.value = '';
                            }

                            document.getElementById('ussdCurrency').addEventListener('change', updateDonationCurrencyUI);

                            document.getElementById('cardNumber')?.addEventListener('input', function () {
                                this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim().slice(0, 19);
                            });
                            document.getElementById('cardExpiry')?.addEventListener('input', function () {
                                this.value = this.value.replace(/\D/g, '').replace(/(\d{2})(\d)/, '$1/$2').slice(0, 5);
                            });

                            document.querySelectorAll('.amount-btn').forEach(btn => {
                                btn.addEventListener('click', () => {
                                    document.querySelectorAll('.amount-btn').forEach(b => {
                                        b.style.background = '#fff';
                                        b.style.color = '#ff6015';
                                    });
                                    btn.style.background = '#ff6015';
                                    btn.style.color = '#fff';
                                    const isCdf = document.getElementById('payUssd').checked &&
                                        document.getElementById('ussdCurrency').value === 'CDF';
                                    const usdPreset = Number(btn.dataset.usd);
                                    const selectedAmount = isCdf
                                        ? Math.round(usdPreset * <?= (int)USD_TO_CDF_RATE ?>)
                                        : usdPreset;
                                    document.getElementById('amountInput').value = selectedAmount;
                                });
                            });

                            togglePaymentUI(document.querySelector('input[name="payment_group"]:checked')?.value || 'mobile');

                            const openBtn = document.getElementById('openPaymentConfirm');
                            const modalEl = document.getElementById('paymentPhoneModal');
                            const donationForm = document.getElementById('donationForm');
                            const processingModalEl = document.getElementById('ussdProcessingModal');
                            let processingModal;
                            const processingTitle = document.getElementById('ussdProcessingTitle');
                            const processingMessage = document.getElementById('ussdProcessingMessage');
                            const processingSpinner = document.getElementById('ussdProcessingSpinner');
                            const processingClose = document.getElementById('ussdProcessingClose');
                            const processingRetry = document.getElementById('ussdProcessingRetry');
                            let bsModal;

                            function finishUssdProcessing(title, message, canRetry = false) {
                                processingTitle.textContent = title;
                                processingMessage.textContent = message;
                                processingSpinner.style.display = 'none';
                                processingClose.style.display = 'inline-block';
                                processingRetry.style.display = canRetry ? 'inline-block' : 'none';
                                openBtn.disabled = false;
                            }

                            async function checkUssdAttempt(attemptId) {
                                try {
                                    const response = await fetch('includes/ussd-check-status.php?attempt_id=' + encodeURIComponent(attemptId), {
                                        headers: {'Accept': 'application/json'}
                                    });
                                    const data = await response.json();
                                    if (data.status === 'completed') {
                                        finishUssdProcessing('Paiement confirmé', 'Merci ! Ton don a été enregistré avec succès.');
                                        return;
                                    }
                                    if (data.status === 'cancelled' || data.status === 'refunded') {
                                        finishUssdProcessing('Paiement non abouti', 'Le paiement n’a pas été confirmé. Tu peux réessayer ou fermer cette fenêtre.', true);
                                        return;
                                    }
                                    if (data.status === 'awaiting_callback') {
                                        processingMessage.textContent = 'Paiement confirmé par FlexPay. En attente de la confirmation finale…';
                                    }
                                } catch (error) {
                                    // Keep checking while the donor remains on the page.
                                }
                                window.setTimeout(() => checkUssdAttempt(attemptId), 6000);
                            }

                            function submitConfirmedDonation() {
                                if (!donationForm.reportValidity()) return;
                                if (!document.getElementById('payUssd').checked) {
                                    donationForm.submit();
                                    return;
                                }

                                openBtn.disabled = true;
                                processingTitle.textContent = 'Traitement du paiement';
                                processingMessage.textContent = 'Veuillez confirmer la demande sur votre téléphone. Gardez cette page ouverte.';
                                processingSpinner.style.display = 'inline-block';
                                processingClose.style.display = 'none';
                                processingRetry.style.display = 'none';
                                if (!processingModal) {
                                    processingModal = new bootstrap.Modal(processingModalEl);
                                }
                                processingModal.show();

                                fetch(donationForm.action, {
                                    method: 'POST',
                                    body: new FormData(donationForm),
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    }
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (!data.success) {
                                            finishUssdProcessing('Paiement non lancé', data.message || 'Impossible de démarrer le paiement.', true);
                                            return;
                                        }
                                        processingMessage.textContent = data.message || 'Demande envoyée. Confirme le paiement sur ton téléphone.';
                                        checkUssdAttempt(data.attempt_id);
                                    })
                                    .catch(() => {
                                        finishUssdProcessing('Erreur de connexion', 'La réponse du paiement n’a pas pu être vérifiée. Ferme cette fenêtre et vérifie son statut avant de réessayer.');
                                    });
                            }

                            processingRetry.addEventListener('click', submitConfirmedDonation);

                            openBtn.addEventListener('click', () => {
                                if (!bsModal) {
                                    bsModal = new bootstrap.Modal(modalEl);
                                }
                                document.getElementById('modalPhoneDisplay').textContent = document.getElementById('paymentPhoneField').value;
                                document.getElementById('phoneChangeArea').style.display = 'none';
                                document.getElementById('phoneYesBtn').style.display = 'inline-block';
                                document.getElementById('phoneNoBtn').style.display = 'inline-block';
                                document.getElementById('phoneConfirmChangeBtn').style.display = 'none';
                                bsModal.show();
                            });

                            document.getElementById('phoneYesBtn').addEventListener('click', () => {
                                bsModal.hide();
                                window.setTimeout(submitConfirmedDonation, 250);
                            });

                            document.getElementById('phoneNoBtn').addEventListener('click', () => {
                                document.getElementById('phoneChangeArea').style.display = 'block';
                                document.getElementById('phoneYesBtn').style.display = 'none';
                                document.getElementById('phoneNoBtn').style.display = 'none';
                                document.getElementById('phoneConfirmChangeBtn').style.display = 'block';
                            });

                            document.getElementById('phoneConfirmChangeBtn').addEventListener('click', () => {
                                const country = document.getElementById('newPhoneCountry').value;
                                const number = document.getElementById('newPhoneNumber').value.trim();

                                if (!number) {
                                    alert('Merci de saisir un numéro.');
                                    return;
                                }

                                const fullNumber = country + number.replace(/^0+/, '');
                                document.getElementById('paymentPhoneField').value = fullNumber;
                                document.getElementById('paymentPhoneDisplay').value = fullNumber;

                                bsModal.hide();
                                window.setTimeout(submitConfirmedDonation, 250);
                            });
                            </script>
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
                         ORDER BY pct ASC
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

                    <?php
                    $donorMessages = $pdo->prepare(
                        "SELECT donor_name, message, created_at FROM donations
                         WHERE status = 'completed'
                           AND donor_name != 'Anonymous'
                           AND message IS NOT NULL
                           AND message != ''
                           AND project_id = :pid
                         ORDER BY created_at DESC
                         LIMIT 10"
                    );
                    $donorMessages->execute(['pid' => $id]);
                    $donorMessages = $donorMessages->fetchAll();
                    ?>
                    <div class="common-right-content widget-item">
                        <h3>Messages de soutien</h3>
                        <?php if ($donorMessages): ?>
                            <?php foreach ($donorMessages as $dm): ?>
                            <div class="mb-3 pb-3" style="border-bottom:1px solid #eee;">
                                <p style="font-style:italic; color:#333; margin-bottom:5px;">« <?= e($dm['message']) ?> »</p>
                                <small style="color:var(--hf-gold); font-weight:600;">— <?= e($dm['donor_name']) ?></small>
                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($dm['created_at'])) ?></small>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Aucun message pour le moment. Sois le premier à en laisser un !</p>
                        <?php endif; ?>
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

                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Donation Details -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
