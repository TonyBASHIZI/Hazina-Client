<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$search = trim($_GET['q'] ?? '');

$sql = "SELECT p.*,
    (SELECT COUNT(DISTINCT donor_email) FROM donations WHERE project_id = p.id AND status = 'completed' AND donor_email != '') +
    (SELECT COUNT(*) FROM donations WHERE project_id = p.id AND status = 'completed' AND (donor_email = '' OR donor_email IS NULL)) AS donor_count
 FROM projects p
 WHERE p.status = 'active'";

$params = [];
if ($search !== '') {
    $sql .= " AND (p.title LIKE :q1 OR p.category LIKE :q2 OR p.description LIKE :q3)";
    $params['q1'] = "%$search%";
    $params['q2'] = "%$search%";
    $params['q3'] = "%$search%";
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

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
            <?php if ($search !== ''): ?>
                <p>Résultats pour "<strong><?= e($search) ?></strong>" — <a href="index.php">réinitialiser</a></p>
            <?php else: ?>
                <p>Chaque projet ci-dessous est vérifié par notre équipe. Choisis une cause et fais la différence dès aujourd'hui.</p>
            <?php endif; ?>
</div>
        <div class="row">

            <?php foreach ($projects as $p):
    $pct = $p['goal_amount'] > 0 ? min(100, round($p['raised_amount'] / $p['goal_amount'] * 100)) : 0;
$pctDisplay = ($pct == 0 && $p['raised_amount'] > 0) ? 2 : $pct;
    $imgSrc = resolveImagePath($p['image']);
    $excerpt = mb_strimwidth(strip_tags($p['description']), 0, 50, '...');
    $detailsUrl = 'donation-details.php?id=' . (int)$p['id'];
    $donateUrl = isClientLoggedIn() ? $detailsUrl : 'login.php?redirect=' . urlencode($detailsUrl);
?>
<div class="col-sm-6 col-lg-4">
    <div class="donation-item">
        <div class="img">
            <img src="<?= e($imgSrc) ?>" alt="<?= e($p['title']) ?>" style="width:100%; height:250px; object-fit:cover;" onerror="this.onerror=null;this.src='assets/img/donation/donation1.jpg';">
            <a class="common-btn" href="<?= e($donateUrl) ?>">Faire un don</a>
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
                            <div class="skill" style="position:relative; margin:0 8px 20px 8px; padding-top:22px;">
                                    <div class="skill-bar" style="width: <?= $pctDisplay ?>%; background:#ff6015; height:8px; border-radius:30px; position:relative;">
                                    <span style="position:absolute; top:-24px; right:-8px; font-size:15px; font-weight:600; color:#302c51;"><?= $pct ?>%</span>
                                </div>
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

  <!-- Gallery -->
        <?php
        $galleryPhotos = $pdo->query(
            "SELECT pi.image_path, p.title
            FROM project_images pi
            JOIN projects p ON p.id = pi.project_id
            WHERE p.status = 'active'
            ORDER BY pi.created_at DESC
            LIMIT 12"
        )->fetchAll();
        ?>
<section class="gallery-area two pt-100 pb-70">
    <div class="container-fluid">
        <div class="section-title">
            <span class="sub-title">Notre galerie</span>
            <h2>Découvre ce que nous faisons sur le terrain</h2>
            <p>Quelques images de nos actions et de nos campagnes récentes auprès des communautés que nous soutenons.</p>
        </div>
        <div class="gallery-slider owl-theme owl-carousel">
            <?php foreach ($galleryPhotos as $photo): ?>
            <div class="gallery-item">
                <a href="<?= e(resolveImagePath($photo['image_path'])) ?>" data-lightbox="hazina-gallery">
                    <img src="<?= e(resolveImagePath($photo['image_path'])) ?>" alt="<?= e($photo['title']) ?>" style="width:100%; height:260px; object-fit:cover; display:block; border-radius:8px;" onerror="this.onerror=null;this.src='assets/img/gallery/gallery1.jpg';">
                    <i class="icofont-eye"></i>
                </a>
            </div>
            <?php endforeach; ?>

            <?php if (!$galleryPhotos): ?>
                <div class="gallery-item">
                    <a href="assets/img/gallery/gallery1.jpg" data-lightbox="hazina-gallery">
                        <img src="assets/img/gallery/gallery1.jpg" alt="Galerie">
                        <i class="icofont-eye"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

        <!-- End Gallery -->

        <!-- Dream -->
        <section class="dream-area pt-100 pb-70">
            <div class="container">
                <div class="section-title">
                    <span class="sub-title">Fulfill our dream</span>
                    <h2>Let's make a change</h2>
                    <p>We exist for non-profits, social enterprises, community groups, activists,lorem politicians and individual citizens that are making.</p>
                </div>
                <div class="row">

                    <div class="col-sm-6 col-lg-4">
                        <div class="dream-item">
                            <h3>
                                <a href="donations.html">Over 20M+ people around the world is having good life because of Findo</a>
                            </h3>
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots.</p>
                            <h4><span>*50</span>country served world wide</h4>
                            <span class="sub-span">01</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="dream-item">
                            <h3>
                                <a href="donations.html">We are supporting the poor and homeless people by providing food</a>
                            </h3>
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots.</p>
                            <h4><span>*Food</span>served world wide</h4>
                            <span class="sub-span">02</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="dream-item">
                            <h3>
                                <a href="donations.html">First time a non- profitable organization is fighting against the poverty</a>
                            </h3>
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots.</p>
                            <h4><span>*Finance</span>collecting & donating</h4>
                            <span class="sub-span">03</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="dream-item">
                            <h3>
                                <a href="donations.html">Over 1200+ volunteer working for Findo around the world</a>
                            </h3>
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots.</p>
                            <h4><span>*Volunteer</span>in every Country</h4>
                            <span class="sub-span">04</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="dream-item">
                            <h3>
                                <a href="donations.html">Hands move to support in Yemen combat covid-19 by donating face masks</a>
                            </h3>
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots.</p>
                            <h4><span>*Lockdown</span>covid-19 helping</h4>
                            <span class="sub-span">05</span>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="dream-item">
                            <h3>
                                <a href="donations.html">This project seeks to build houses for reduce their suffering allow them to live</a>
                            </h3>
                            <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots.</p>
                            <h4><span>*150</span>house project</h4>
                            <span class="sub-span">06</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- End Dream -->

        <!-- Benefit -->
        <div class="benefit-area two pt-100 pb-70">
            <div class="container">
                <div class="row align-items-center">

                    <div class="col-lg-6">
                        <div class="benefit-img">
                            <img src="assets/img/benefit-main1.jpg" alt="Benefit">
                            <img src="assets/img/benefit-shape1.png" alt="Benefit">
                            <div class="video-wrap">
                                <button class="js-modal-btn" data-video-id="uemObN8_dcw">
                                    <i class="icofont-ui-play"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="section-title">
<<<<<<< HEAD
                            <span class="sub-title">Notre mission ensemble</span>
                            <h2>Dignité-Espoir-Vie-Avenir</h2>
=======
                            <span class="sub-title">Core features</span>
                            <h2>Mission to make a smile</h2>
                            <p>We exist for non-profits, social enterprises, community groups, activists,lorem politicians and individual citizens that are making.</p>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item">
                                    <i class="flaticon-house"></i>
<<<<<<< HEAD
                                    <h3>Habitat & Logement</h3>
                                    <p>Offrir un logement sûr et digne aux familles les plus vulnérables afin de leur garantir sécurité et stabilité</p>
=======
                                    <h3>Build home</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                                </div>
                            </div>
        
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item two">
                                    <i class="flaticon-hospital"></i>
<<<<<<< HEAD
                                    <h3>Santé & Soins</h3>
                                    <p>Faciliter l'accès aux soins médicaux essentiels et améliorer le bien-être des personnes dans le besoin</p>
=======
                                    <h3>Medical facilities</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                                </div>
                            </div>
        
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item three">
                                    <i class="flaticon-fast-food"></i>
<<<<<<< HEAD
                                    <h3>Alimentation & Eau potable</h3>
                                    <p>Fournir une alimentation équilibrée et un accès durable à une eau potable de qualité</p>
=======
                                    <h3>Food & water</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                                </div>
                            </div>
        
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item four">
                                    <i class="flaticon-graduation-cap"></i>
<<<<<<< HEAD
                                    <h3>Éducation & Formation</h3>
                                    <p>Donner aux enfants et aux adultes les moyens d'apprendre, de se former et de construire un meilleur avenir.</p>
=======
                                    <h3>Education facilities</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Benefit -->

        <!-- Events -->
        
        <?php
        $finishedProjects = $pdo->query(
            "SELECT id, title, image, category, goal_amount, updated_at
            FROM projects
            WHERE status = 'completed'
            ORDER BY updated_at DESC
            LIMIT 6"
        )->fetchAll();
        $eventsLeft = array_slice($finishedProjects, 0, 3);
        $eventsRight = array_slice($finishedProjects, 3, 3);
        ?>

        <!-- Events (projets terminés) -->
        <section class="event-area pt-100 pb-70" id="events">
            <div class="container">
                <div class="section-title">
                    <span class="sub-title">Nos réalisations</span>
                    <h2>Projets menés à terme grâce à vous</h2>
                </div>

                <?php if ($finishedProjects): ?>
                <div class="row align-items-center">

                    <div class="col-lg-6">
                        <?php foreach ($eventsLeft as $ev): ?>
                        <div class="event-item">
                            <img src="<?= e(resolveImagePath($ev['image'])) ?>" alt="<?= e($ev['title']) ?>" onerror="this.onerror=null;this.src='assets/img/event/event1.jpg';">
                            <div class="inner">
                                <h4><?= date('d', strtotime($ev['updated_at'])) ?> <span><?= date('M', strtotime($ev['updated_at'])) ?></span></h4>
                                <h3>
                                    <a href="donation-details.php?id=<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a>
                                </h3>
                                <ul>
                                    <li>
                                        <i class="icofont-money-bag"></i>
                                        <span><?= money((float)$ev['goal_amount']) ?> collecté</span>
                                    </li>
                                    <?php if ($ev['category']): ?>
                                    <li>
                                        <i class="icofont-location-pin"></i>
                                        <span><?= e($ev['category']) ?></span>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="col-lg-6">
                        <?php foreach ($eventsRight as $ev): ?>
                        <div class="event-item-right">
                            <h4><?= date('d', strtotime($ev['updated_at'])) ?> <span><?= date('M', strtotime($ev['updated_at'])) ?></span></h4>
                            <h3>
                                <a href="donation-details.php?id=<?= (int)$ev['id'] ?>"><?= e($ev['title']) ?></a>
                            </h3>
                            <ul>
                                <li>
                                    <i class="icofont-money-bag"></i>
                                    <span><?= money((float)$ev['goal_amount']) ?> collecté</span>
                                </li>
                                <?php if ($ev['category']): ?>
                                <li>
                                    <i class="icofont-location-pin"></i>
                                    <span><?= e($ev['category']) ?></span>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
                <?php else: ?>
                    <p class="text-center text-muted">Aucun projet terminé pour le moment — reviens bientôt voir nos premières réussites !</p>
                <?php endif; ?>

            </div>
        </section>

        
        <!-- End Events -->

        <!--=== Team ===-->
<<<<<<< HEAD
        <?php
$donors = $pdo->query(
    "SELECT DISTINCT u.id, u.username, u.photo, MAX(d.created_at) AS last_don
     FROM donations d
     JOIN users u ON u.id = d.user_id
     WHERE d.status = 'completed' AND d.donor_name != 'Anonymous'
     GROUP BY u.id, u.username, u.photo
     ORDER BY last_don DESC
     LIMIT 6"
)->fetchAll();
?>

<!--=== Team / Donateurs ===-->
<section class="team-area pt-100 pb-70">
    <div class="container">
        <div class="section-title">
            <span class="sub-title">Nos donateurs</span>
            <h2>Merci à ceux qui rendent tout ça possible</h2>
            <p>Ils ont choisi de soutenir nos causes. Rejoins-les et fais toi aussi la différence aujourd'hui.</p>
        </div>

        <div class="text-center mb-4">
            <a href="donors.php" class="common-btn" style="padding:10px 26px; font-size:14px;">Voir tous les donateurs</a>
        </div>
        <?php if ($donors): ?>
        <div class="row justify-content-center">
            <?php foreach ($donors as $donor): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="team-item">
                    <div class="top">
                        <?php if (!empty($donor['photo'])): ?>
                            <img src="<?= e($donor['photo']) ?>" alt="<?= e($donor['username']) ?>" style="width:100%; height:260px; object-fit:cover;" onerror="this.onerror=null;this.src='assets/img/team/team1.jpg';">
                        <?php else: ?>
                            <div style="width:100%; height:260px; background:var(--hf-navy); display:flex; align-items:center; justify-content:center;">
                                <i class="icofont-user-alt-3" style="font-size:70px; color:var(--hf-gold);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="bottom">
                        <h3><?= e($donor['username']) ?></h3>
                        <span>Donateur</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-center text-muted">Sois le premier à apparaître ici en soutenant l'un de nos projets !</p>
        <?php endif; ?>
    </div>
</section>
    <!--=== End Team / Donateurs ===-->

        <!--=== Blog ===-->
        <?php
$blogPosts = $pdo->query(
    "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3"
)->fetchAll();
?>

<!-- Blog -->
<section class="blog-area pt-100 pb-70">
    <div class="container">
        <div class="section-title">
            <span class="sub-title">Actualités</span>
            <h2>Nos dernières actualités</h2>
            <p>Suis nos actions, nos campagnes et les histoires des personnes que tu aides grâce à tes dons.</p>
        </div>

        <?php if ($blogPosts): ?>
        <div class="row justify-content-center">
            <?php foreach ($blogPosts as $post): ?>
            <div class="col-sm-6 col-lg-4">
                <div class="blog-item">
                    <div class="top">
                        <a href="blog-details.php?id=<?= (int)$post['id'] ?>">
                            <img src="<?= e($post['image'] ?: 'assets/img/blog/blog1.jpg') ?>" alt="<?= e($post['title']) ?>" style="width:100%; height:220px; object-fit:cover;" onerror="this.onerror=null;this.src='assets/img/blog/blog1.jpg';">
                        </a>
                    </div>
                    <div class="bottom">
                        <ul>
                            <li>
                                <i class="icofont-calendar"></i>
                                <span><?= date('d M, Y', strtotime($post['created_at'])) ?></span>
                            </li>
                            <li>
                                <i class="icofont-user-alt-3"></i>
                                <span>Par :</span>
                                <a href="#"><?= e($post['author']) ?></a>
                            </li>
                        </ul>
                        <h3>
                            <a href="blog-details.php?id=<?= (int)$post['id'] ?>"><?= e($post['title']) ?></a>
                        </h3>
                        <p><?= e(mb_strimwidth(strip_tags($post['excerpt'] ?: $post['content']), 0, 110, '...')) ?></p>
                        <a class="blog-btn" href="blog-details.php?id=<?= (int)$post['id'] ?>">Lire la suite</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucun article publié pour le moment.</p>
        <?php endif; ?>

    </div>
</section>

=======
        <section class="team-area pt-100 pb-70">
            <div class="container">
                <div class="section-title">
                    <span class="sub-title">Volunteer</span>
                    <h2>Meet our excellent volunteers</h2>
                    <p>We exist for non-profits, social enterprises, community groups, activists,lorem politicians and individual citizens that are making.</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-sm-6 col-lg-4">
                        <div class="team-item">
                            <div class="top">
                                <img src="assets/img/team/team1.jpg" alt="Team">
                                <ul>
                                    <li>
                                        <a href="https://www.facebook.com/" target="_blank">
                                            <i class="icofont-facebook"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.twitter.com/" target="_blank">
                                            <i class="icofont-twitter"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/" target="_blank">
                                            <i class="icofont-youtube-play"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/" target="_blank">
                                            <i class="icofont-instagram"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="bottom">
                                <h3>Jenas handar</h3>
                                <span>CEO & Founder</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="team-item">
                            <div class="top">
                                <img src="assets/img/team/team2.jpg" alt="Team">
                                <ul>
                                    <li>
                                        <a href="https://www.facebook.com/" target="_blank">
                                            <i class="icofont-facebook"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.twitter.com/" target="_blank">
                                            <i class="icofont-twitter"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/" target="_blank">
                                            <i class="icofont-youtube-play"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/" target="_blank">
                                            <i class="icofont-instagram"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="bottom">
                                <h3>Smithy alisha</h3>
                                <span>Manager</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="team-item">
                            <div class="top">
                                <img src="assets/img/team/team3.jpg" alt="Team">
                                <ul>
                                    <li>
                                        <a href="https://www.facebook.com/" target="_blank">
                                            <i class="icofont-facebook"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.twitter.com/" target="_blank">
                                            <i class="icofont-twitter"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.youtube.com/" target="_blank">
                                            <i class="icofont-youtube-play"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/" target="_blank">
                                            <i class="icofont-instagram"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="bottom">
                                <h3>Johan mendal</h3>
                                <span>Volunteer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--=== End Team ===-->

        <!--=== Blog ===-->
        <section class="blog-area pt-100 pb-70">
            <div class="container">
                <div class="section-title">
                    <span class="sub-title">Latest news & blog</span>
                    <h2>Latest charity blog</h2>
                    <p>We exist for non-profits, social enterprises, community groups, activists,lorem politicians and individual citizens that are making.</p>
                </div>
                <div class="row justify-content-center">
                    <div class="col-sm-6 col-lg-4">
                        <div class="blog-item">
                            <div class="top">
                                <a href="blog-details.html">
                                    <img src="assets/img/blog/blog1.jpg" alt="Blog">
                                </a>
                            </div>
                            <div class="bottom">
                                <ul>
                                    <li>
                                        <i class="icofont-calendar"></i>
                                        <span>21 Jan, 2024</span>
                                    </li>
                                    <li>
                                        <i class="icofont-user-alt-3"></i>
                                        <span>By:</span>
                                        <a href="#">Admin</a>
                                    </li>
                                </ul>
                                <h3>
                                    <a href="blog-details.html">Donate for nutration less poor people</a>
                                </h3>
                                <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Amet cupiditate sit ducimus dolor laudantium distinction</p>
                                <a class="blog-btn" href="blog-details.html">Read More</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="blog-item">
                            <div class="top">
                                <a href="blog-details.html">
                                    <img src="assets/img/blog/blog2.jpg" alt="Blog">
                                </a>
                            </div>
                            <div class="bottom">
                                <ul>
                                    <li>
                                        <i class="icofont-calendar"></i>
                                        <span>22 Jan, 2024</span>
                                    </li>
                                    <li>
                                        <i class="icofont-user-alt-3"></i>
                                        <span>By:</span>
                                        <a href="#">Admin</a>
                                    </li>
                                </ul>
                                <h3>
                                    <a href="blog-details.html">Charity meetup in Berline next year</a>
                                </h3>
                                <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Amet cupiditate sit ducimus dolor laudantium distinction</p>
                                <a class="blog-btn" href="blog-details.html">Read More</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="blog-item">
                            <div class="top">
                                <a href="blog-details.html">
                                    <img src="assets/img/blog/blog3.jpg" alt="Blog">
                                </a>
                            </div>
                            <div class="bottom">
                                <ul>
                                    <li>
                                        <i class="icofont-calendar"></i>
                                        <span>23 Jan, 2024</span>
                                    </li>
                                    <li>
                                        <i class="icofont-user-alt-3"></i>
                                        <span>By:</span>
                                        <a href="#">Admin</a>
                                    </li>
                                </ul>
                                <h3>
                                    <a href="blog-details.html">Donate for the poor people to help them</a>
                                </h3>
                                <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Amet cupiditate sit ducimus dolor laudantium distinction</p>
                                <a class="blog-btn" href="blog-details.html">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
>>>>>>> 6ff63bdabcb66e48e31154a65002232bf281b3f1
        <!--=== End Blog ===-->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
