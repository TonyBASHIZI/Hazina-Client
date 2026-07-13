<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$projects = $pdo->query(
    "SELECT p.*,
        (SELECT COUNT(DISTINCT donor_email) FROM donations WHERE project_id = p.id AND status = 'completed' AND donor_email != '') +
        (SELECT COUNT(*) FROM donations WHERE project_id = p.id AND status = 'completed' AND (donor_email = '' OR donor_email IS NULL)) AS donor_count
     FROM projects p
     WHERE p.status = 'active'
     ORDER BY p.created_at DESC"
)->fetchAll();

$pageTitle = 'Accueil';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner -->
<div class="banner-area-two">
    <div class="banner-slider owl-theme owl-carousel">
        <div class="banner-slider-item banner-img-one">
            <div class="banner-shape">
                <img src="assets/img/banner/banner-shape1.png" alt="Shape">
            </div>
            <div class="d-table">
                <div class="d-table-cell">
                    <div class="container">
                        <div class="banner-content">
                            <span>Ensemble, changeons des vies</span>
                            <h1>Tends la main à ceux qui en ont besoin</h1>
                            <p>Chaque don compte. Découvre nos campagnes actives et soutiens une cause qui te tient à cœur.</p>
                            <div class="banner-btn-area">
                                <a class="common-btn banner-btn" href="#projects">Voir les projets</a>
                                <?php if (isClientLoggedIn()): ?>
                                    <a class="common-btn" href="#projects">Faire un don</a>
                                <?php else: ?>
                                    <a class="common-btn" href="login.php">Faire un don</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Banner -->

<!--=== Feature ===-->
<div class="feature-area two pb-70">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-6 col-lg-4">
                <div class="feature-item">
                    <i class="flaticon-solidarity"></i>
                    <h3><a href="register.php">Devenir bénévole</a></h3>
                    <p>Rejoins notre réseau de bénévoles et aide-nous à porter nos actions sur le terrain.</p>
                    <a class="feature-btn" href="register.php">Rejoindre</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="feature-item two">
                    <i class="flaticon-donation"></i>
                    <h3><a href="#projects">Faire un don</a></h3>
                    <p>Choisis une cause parmi nos projets actifs et fais un don en quelques clics.</p>
                    <a class="feature-btn" href="#projects">Donner maintenant</a>
                </div>
            </div>

            <div class="col-sm-6 col-lg-4">
                <div class="feature-item three">
                    <i class="flaticon-love"></i>
                    <h3><a href="about.php">Montre ton soutien</a></h3>
                    <p>Partage nos campagnes autour de toi pour amplifier l'impact de chaque don.</p>
                    <a class="feature-btn" href="about.php">Partager</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!--=== End Feature ===-->

<!-- About -->
<div class="about-area two pb-70">
    <div class="container">
        <div class="row align-items-center">

            <div class="col-lg-6">
                <div class="about-content">
                    <div class="section-title">
                        <span class="sub-title">À propos de nous</span>
                        <h2>Nous agissons pour des causes sociales</h2>
                    </div>
                    <p>Hazina Funding met en relation des donateurs et des causes vérifiées à travers la RD Congo. Chaque contribution est suivie de manière transparente, du don jusqu'à son impact réel sur le terrain.</p>
                    <ul>
                        <li><span>01</span> Collecte de fonds depuis différentes sources</li>
                        <li><span>02</span> Aide apportée dans les zones rurales et déplacées</li>
                        <li><span>03</span> Suivi transparent de chaque don reçu</li>
                        <li><span>04</span> Intervention là où le besoin est le plus urgent</li>
                    </ul>
                    <div class="about-btn-area">
                        <a class="common-btn about-btn" href="#projects">Voir les projets</a>
                        <a class="common-btn" href="about.php">En savoir plus</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="about-img">
                    <img src="assets/img/about/about-main2.jpg" alt="À propos">
                </div>
            </div>

        </div>
    </div>
</div>
<!-- End About -->

<!-- Donation -->
<section class="donations-area two pt-100 pb-70" id="projects">
    <div class="container">
        <div class="section-title">
            <span class="sub-title">Nos causes</span>
            <h2>Sois la raison du sourire de quelqu'un</h2>
            <p>Chaque projet ci-dessous est vérifié par notre équipe. Choisis une cause et fais la différence dès aujourd'hui.</p>
        </div>
        <div class="row">

            <?php foreach ($projects as $p):
                $pct = $p['goal_amount'] > 0 ? min(100, round($p['raised_amount'] / $p['goal_amount'] * 100)) : 0;
                $imgSrc = resolveImagePath($p['image']);
                $excerpt = mb_strimwidth(strip_tags($p['description']), 0, 110, '...');
            ?>
            <div class="col-sm-6 col-lg-4">
                <div class="donation-item">
                    <div class="img">
                        <img src="<?= e($imgSrc) ?>" alt="<?= e($p['title']) ?>" onerror="this.onerror=null;this.src='assets/img/donation/donation1.jpg';">
                        <a class="common-btn" href="donation-details.php?id=<?= (int)$p['id'] ?>">Faire un don</a>
                    </div>
                    <div class="inner">
                        <div class="top">
                            <?php if ($p['category']): ?>
                                <a class="tags" href="#">#<?= e($p['category']) ?></a>
                            <?php endif; ?>
                            <h3>
                                <a href="donation-details.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a>
                            </h3>
                            <p><?= e($excerpt) ?></p>
                        </div>
                        <div class="bottom">
                            <div class="skill">
                                <div class="skill-bar" style="position:relative; background:#eee; border-radius:10px; height:8px; overflow:hidden;">
                                    <div style="width:<?= $pct ?>%; background:#d62828; height:100%;"></div>
                                </div>
                                <span class="skill-count1"><?= $pct ?>%</span>
                            </div>
                            <ul>
                                <li>Collecté : <?= money((float)$p['raised_amount']) ?></li>
                                <li>Objectif : <?= money((float)$p['goal_amount']) ?></li>
                            </ul>
                            <h4>Soutenu par <span><?= (int)$p['donor_count'] ?> personne(s)</span></h4>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (!$projects): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">Aucun projet actif pour le moment. Reviens bientôt !</p>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
<!-- End Donation -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
