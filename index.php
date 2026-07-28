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
                    <h3><a href="volunteer.php">Devenir bénévole</a></h3>
                    <p>Rejoins notre réseau de bénévoles et aide-nous à porter nos actions sur le terrain.</p>
                    <a class="feature-btn" href="volunteer.php">Rejoindre</a>
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

<?php
$totalRaised = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status = 'completed'")->fetchColumn();
$totalDonors = (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM donations WHERE status = 'completed'")->fetchColumn();
$totalProjectsCompleted = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'completed'")->fetchColumn();
$totalProjectsActive = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn();
?>

<!-- Compteurs d'impact -->
<section class="pt-70 pb-70" style="background: var(--hf-navy);">
    <div class="container">
        <div class="row justify-content-center text-center g-4">
            <div class="col-6 col-lg-3">
                <i class="icofont-dollar" style="font-size:36px; color:var(--hf-gold);"></i>
                <h2 style="color:#fff; margin:10px 0 5px;">
                    $<span class="hf-counter" data-final="<?= (int)$totalRaised ?>">0</span>
                </h2>
                <p style="color:var(--hf-silver); margin:0;">Collectés au total</p>
            </div>
            <div class="col-6 col-lg-3">
                <i class="icofont-users-alt-4" style="font-size:36px; color:var(--hf-gold);"></i>
                <h2 style="color:#fff; margin:10px 0 5px;">
                    <span class="hf-counter" data-final="<?= $totalDonors ?>">0</span>
                </h2>
                <p style="color:var(--hf-silver); margin:0;">Donateurs généreux</p>
            </div>
            <div class="col-6 col-lg-3">
                <i class="icofont-check-circled" style="font-size:36px; color:var(--hf-gold);"></i>
                <h2 style="color:#fff; margin:10px 0 5px;">
                    <span class="hf-counter" data-final="<?= $totalProjectsCompleted ?>">0</span>
                </h2>
                <p style="color:var(--hf-silver); margin:0;">Projets réalisés</p>
            </div>
            <div class="col-6 col-lg-3">
                <i class="icofont-fire-burn" style="font-size:36px; color:var(--hf-gold);"></i>
                <h2 style="color:#fff; margin:10px 0 5px;">
                    <span class="hf-counter" data-final="<?= $totalProjectsActive ?>">0</span>
                </h2>
                <p style="color:var(--hf-silver); margin:0;">Causes actives</p>
            </div>
        </div>
    </div>
</section>
<!-- End Compteurs -->

<script>
(function() {
    const counters = document.querySelectorAll('.hf-counter');
    let animated = false;

    function animateCounters() {
        if (animated) return;
        animated = true;
        counters.forEach(el => {
            const final = parseInt(el.getAttribute('data-final'), 10) || 0;
            const duration = 1500;
            const startTime = performance.now();

            function step(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                const value = Math.floor(progress * final);
                el.textContent = value.toLocaleString('fr-FR');
                if (progress < 1) requestAnimationFrame(step);
                else el.textContent = final.toLocaleString('fr-FR');
            }
            requestAnimationFrame(step);
        });
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.disconnect();
            }
        });
    }, { threshold: 0.3 });

    if (counters.length) {
        observer.observe(counters[0].closest('section'));
    }
})();
</script>


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

       <?php
$dreamMessages = $pdo->query(
    "SELECT d.id, d.donor_name, d.message, d.amount, p.title AS project_title
     FROM donations d
     JOIN projects p ON p.id = d.project_id
     WHERE d.status = 'completed'
       AND d.donor_name != 'Anonymous'
       AND d.message IS NOT NULL
       AND d.message != ''
     ORDER BY d.created_at DESC
     LIMIT 3"
)->fetchAll();
?>


<section class="dream-area pt-100 pb-70">
    <div class="container">
        <div class="section-title">
            <span class="sub-title">Notre mission</span>
            <h2>Ensemble, changeons les choses</h2>
            <p>Chaque contribution, petite ou grande, a un impact réel sur la vie de quelqu'un. Voici ce que nos donateurs en disent.</p>
        </div>
        <?php if ($dreamMessages): ?>
        <div class="row d-flex align-items-stretch">
            <?php foreach ($dreamMessages as $i => $dm):
                $isLong = mb_strlen($dm['message']) > 100;
                $shortMsg = $isLong ? mb_substr($dm['message'], 0, 100) . '...' : $dm['message'];
            ?>
            <div class="col-sm-6 col-lg-4 d-flex">
                <div class="dream-item w-100 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <h3>« <?= e($shortMsg) ?> »</h3>
                        <?php if ($isLong): ?>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#msgModal<?= (int)$dm['id'] ?>" style="color:var(--hf-gold); font-weight:600; font-size:14px;">Lire plus</a>
                        <?php endif; ?>
                        <p class="mt-2">— <?= e($dm['donor_name']) ?>, à propos de <em><?= e($dm['project_title']) ?></em></p>
                    </div>
                    <div>
                        <h4><span>*Don de</span> <?= money((float)$dm['amount']) ?></h4>
                        <span class="sub-span"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                    </div>
                </div>
            </div>

            <?php if ($isLong): ?>
            <div class="modal fade" id="msgModal<?= (int)$dm['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius:12px;">
                        <div class="modal-header" style="background:var(--hf-navy); color:#fff; border-radius:12px 12px 0 0;">
                            <h5 class="modal-title"><?= e($dm['donor_name']) ?></h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p style="font-style:italic;">« <?= e($dm['message']) ?> »</p>
                            <p class="text-muted mb-0">À propos de <strong><?= e($dm['project_title']) ?></strong> — Don de <?= money((float)$dm['amount']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-center text-muted">Sois le premier à laisser un message en soutenant l'un de nos projets !</p>
        <?php endif; ?>
    </div>
</section>

<!-- End Dream -->   

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

        <!--=== End Blog ===-->

    <?php
$promoProjects = $pdo->query(
    "SELECT id, title, image, goal_amount, raised_amount
     FROM projects
     WHERE status = 'active'
     ORDER BY created_at DESC
     LIMIT 5"
)->fetchAll();
?>

<?php if ($promoProjects): ?>
<!-- Widget promo flottant -->
<div id="promoWidget" style="display:none; position:fixed; bottom:20px; right:20px; width:260px; z-index:99998; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); overflow:hidden;">
    <button id="promoCloseBtn" style="position:absolute; top:8px; right:8px; z-index:2; background:rgba(11,15,42,.7); color:#fff; border:none; width:26px; height:26px; border-radius:50%; cursor:pointer; font-size:16px; line-height:1;">&times;</button>

    <div id="promoSlides">
        <?php foreach ($promoProjects as $i => $pp):
            $ppct = $pp['goal_amount'] > 0 ? min(100, round($pp['raised_amount'] / $pp['goal_amount'] * 100)) : 0;
        ?>
        <a href="donation-details.php?id=<?= (int)$pp['id'] ?>" class="promo-slide" data-index="<?= $i ?>" style="display:<?= $i === 0 ? 'block' : 'none' ?>; text-decoration:none; color:inherit;">
            <img src="<?= e($pp['image'] ?: 'assets/img/donation/donation1.jpg') ?>" alt="<?= e($pp['title']) ?>" style="width:100%; height:140px; object-fit:cover;" onerror="this.onerror=null;this.src='assets/img/donation/donation1.jpg';">
            <div style="padding:12px;">
                <div style="font-weight:700; font-size:14px; color:var(--hf-navy); margin-bottom:6px;"><?= e(mb_strimwidth($pp['title'], 0, 40, '...')) ?></div>
                <div style="background:#eee; border-radius:10px; height:6px; overflow:hidden; margin-bottom:6px;">
                    <div style="width:<?= $ppct ?>%; background:var(--hf-gold); height:100%;"></div>
                </div>
                <div style="font-size:12px; color:#777;"><?= $ppct ?>% collecté — <span style="color:var(--hf-gold); font-weight:600;">Faire un don →</span></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <div style="display:flex; justify-content:center; gap:5px; padding-bottom:10px;">
        <?php foreach ($promoProjects as $i => $pp): ?>
            <span class="promo-dot" data-index="<?= $i ?>" style="width:6px; height:6px; border-radius:50%; background:<?= $i === 0 ? 'var(--hf-gold)' : '#ddd' ?>; transition:.3s;"></span>
        <?php endforeach; ?>
    </div>
</div>

<script>
(function() {
    const widget = document.getElementById('promoWidget');
    const dismissed = sessionStorage.getItem('hf_promo_dismissed');

    if (!dismissed) {
        setTimeout(() => { widget.style.display = 'block'; }, 1500);
        }

        document.getElementById('promoCloseBtn').addEventListener('click', () => {
        widget.style.display = 'none';
        widget.classList.remove('hf-mobile-open');
        sessionStorage.setItem('hf_promo_dismissed', '1');
    });

    document.getElementById('promoBubble')?.addEventListener('click', () => {
        widget.classList.toggle('hf-mobile-open');
    });

    const slides = document.querySelectorAll('.promo-slide');
    const dots = document.querySelectorAll('.promo-dot');
    let current = 0;

    if (slides.length > 1) {
        setInterval(() => {
            slides[current].style.display = 'none';
            dots[current].style.background = '#ddd';
            current = (current + 1) % slides.length;
            slides[current].style.display = 'block';
            dots[current].style.background = 'var(--hf-gold)';
        }, 4000);
    }
})();
</script>
<?php endif; ?>

<!-- Fil d'activité en direct -->
<div id="activityFeed" style="display:none; position:fixed; bottom:20px; left:20px; width:300px; z-index:99997; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.2); overflow:hidden;">
    <div style="background:var(--hf-navy); color:#fff; padding:10px 15px; display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:13px; font-weight:600;"><span style="display:inline-block; width:8px; height:8px; background:#28a745; border-radius:50%; margin-right:6px; animation:hf-pulse-dot 1.5s infinite;"></span>Activité en direct</span>
        <button id="activityCloseBtn" style="background:transparent; color:#fff; border:none; font-size:16px; cursor:pointer;">&times;</button>
    </div>
    <div id="activityContent" style="max-height:260px; overflow-y:auto;"></div>
</div>

<style>
@keyframes hf-pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
.activity-item {
    padding: 10px 15px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
    animation: hf-slide-in 0.4s ease;
}
@keyframes hf-slide-in {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>

<script>
(function() {
    const feed = document.getElementById('activityFeed');
    const content = document.getElementById('activityContent');
    const dismissed = sessionStorage.getItem('hf_activity_dismissed');

    function loadActivity() {
        fetch('activity-feed.php')
            .then(res => res.json())
            .then(data => {
                if (!data.length) return;

                content.innerHTML = data.map(item => `
                    <div class="activity-item" style="color:#333 !important; background:#fff;">
                        <strong style="color:var(--hf-navy) !important;">${item.name}</strong> <span style="color:#333 !important;">a donné</span>
                        <strong style="color:var(--hf-gold) !important;">${item.amount}$</strong>
                        <span style="color:#333 !important;">pour</span> <em style="color:#555 !important;">${item.project}</em>
                        <div style="color:#999 !important; font-size:11px; margin-top:2px;">${item.time}</div>
                    </div>
                `).join('');

                if (!dismissed && feed.style.display === 'none') {
                    feed.style.display = 'block';
                }
            })
            .catch(() => {});
    }

    loadActivity();
    setInterval(loadActivity, 30000); // rafraîchit toutes les 30 secondes

    document.getElementById('activityCloseBtn').addEventListener('click', () => {
    feed.style.display = 'none';
    feed.classList.remove('hf-mobile-open');
    sessionStorage.setItem('hf_activity_dismissed', '1');
});

document.getElementById('activityBubble')?.addEventListener('click', () => {
    feed.classList.toggle('hf-mobile-open');
});
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
