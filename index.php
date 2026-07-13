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
        <section class="gallery-area two pt-100 pb-70">
            <div class="container-fluid">
                <div class="section-title">
                    <span class="sub-title">Our gallery</span>
                    <h2>Discover the best things we do</h2>
                    <p>We exist for non-profits, social enterprises, community groups, activists,lorem politicians and individual citizens that are making.</p>
                </div>
                <div class="gallery-slider owl-theme owl-carousel">

                    <div class="gallery-item">
                        <a href="assets/img/gallery/gallery1.jpg" data-lightbox="roadtrip">
                            <img src="assets/img/gallery/gallery1.jpg" alt="Gallery">
                            <i class="icofont-eye"></i>
                        </a>
                    </div>

                    <div class="gallery-item">
                        <a href="assets/img/gallery/gallery2.jpg" data-lightbox="roadtrip">
                            <img src="assets/img/gallery/gallery2.jpg" alt="Gallery">
                            <i class="icofont-eye"></i>
                        </a>
                    </div>

                    <div class="gallery-item">
                        <a href="assets/img/gallery/gallery3.jpg" data-lightbox="roadtrip">
                            <img src="assets/img/gallery/gallery3.jpg" alt="Gallery">
                            <i class="icofont-eye"></i>
                        </a>
                    </div>

                    <div class="gallery-item">
                        <a href="assets/img/gallery/gallery4.jpg" data-lightbox="roadtrip">
                            <img src="assets/img/gallery/gallery4.jpg" alt="Gallery">
                            <i class="icofont-eye"></i>
                        </a>
                    </div>

                    <div class="gallery-item">
                        <a href="assets/img/gallery/gallery5.jpg" data-lightbox="roadtrip">
                            <img src="assets/img/gallery/gallery5.jpg" alt="Gallery">
                            <i class="icofont-eye"></i>
                        </a>
                    </div>

                    <div class="gallery-item">
                        <a href="assets/img/gallery/gallery6.jpg" data-lightbox="roadtrip">
                            <img src="assets/img/gallery/gallery6.jpg" alt="Gallery">
                            <i class="icofont-eye"></i>
                        </a>
                    </div>

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
                            <span class="sub-title">Core features</span>
                            <h2>Mission to make a smile</h2>
                            <p>We exist for non-profits, social enterprises, community groups, activists,lorem politicians and individual citizens that are making.</p>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item">
                                    <i class="flaticon-house"></i>
                                    <h3>Build home</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
                                </div>
                            </div>
        
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item two">
                                    <i class="flaticon-hospital"></i>
                                    <h3>Medical facilities</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
                                </div>
                            </div>
        
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item three">
                                    <i class="flaticon-fast-food"></i>
                                    <h3>Food & water</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
                                </div>
                            </div>
        
                            <div class="col-sm-6 col-sm-6">
                                <div class="benefit-item four">
                                    <i class="flaticon-graduation-cap"></i>
                                    <h3>Education facilities</h3>
                                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Similique illum excepturi</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- End Benefit -->

        <!-- Events -->
        <section class="event-area pt-100 pb-70">
            <div class="container">
                <div class="section-title">
                    <span class="sub-title">Our events</span>
                    <h2>Upcoming events near you</h2>
                </div>
                <div class="row align-items-center">

                    <div class="col-lg-6">
                        <div class="event-item">
                            <img src="assets/img/event/event1.jpg" alt="Event">
                            <div class="inner">
                                <h4>04 <span>Jan</span></h4>
                                <h3>
                                    <a href="event-details.html">Fundraising for MQ</a>
                                </h3>
                                <ul>
                                    <li>
                                        <i class="icofont-stopwatch"></i>
                                        <span>2.00pm - 5.00pm</span>
                                    </li>
                                    <li>
                                        <i class="icofont-location-pin"></i>
                                        <span>Australia</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="event-item">
                            <img src="assets/img/event/event2.jpg" alt="Event">
                            <div class="inner">
                                <h4>05 <span>Jan</span></h4>
                                <h3>
                                    <a href="event-details.html">Shout about it with us</a>
                                </h3>
                                <ul>
                                    <li>
                                        <i class="icofont-stopwatch"></i>
                                        <span>1.00pm - 2.00pm</span>
                                    </li>
                                    <li>
                                        <i class="icofont-location-pin"></i>
                                        <span>Canada</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="event-item">
                            <img src="assets/img/event/event3.jpg" alt="Event">
                            <div class="inner">
                                <h4>10 <span>Jan</span></h4>
                                <h3>
                                    <a href="event-details.html">Relief giving - Providing relief</a>
                                </h3>
                                <ul>
                                    <li>
                                        <i class="icofont-stopwatch"></i>
                                        <span>3.00pm - 4.00pm</span>
                                    </li>
                                    <li>
                                        <i class="icofont-location-pin"></i>
                                        <span>USA</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">

                        <div class="event-item-right">
                            <h4>06 <span>Jan</span></h4>
                            <h3>
                                <a href="event-details.html">Challenge is right for you</a>
                            </h3>
                            <ul>
                                <li>
                                    <i class="icofont-stopwatch"></i>
                                    <span>10.00am - 11.00am</span>
                                </li>
                                <li>
                                    <i class="icofont-location-pin"></i>
                                    <span>UK</span>
                                </li>
                            </ul>
                        </div>

                        <div class="event-item-right">
                            <h4>07 <span>Jan</span></h4>
                            <h3>
                                <a href="event-details.html">Fundraising is going</a>
                            </h3>
                            <ul>
                                <li>
                                    <i class="icofont-stopwatch"></i>
                                    <span>11.00am - 12.00pm</span>
                                </li>
                                <li>
                                    <i class="icofont-location-pin"></i>
                                    <span>France</span>
                                </li>
                            </ul>
                        </div>

                        <div class="event-item-right">
                            <h4>08 <span>Jan</span></h4>
                            <h3>
                                <a href="event-details.html">Bowling for a cause</a>
                            </h3>
                            <ul>
                                <li>
                                    <i class="icofont-stopwatch"></i>
                                    <span>1.00pm - 1.30pm</span>
                                </li>
                                <li>
                                    <i class="icofont-location-pin"></i>
                                    <span>Spain</span>
                                </li>
                            </ul>
                        </div>

                    </div>

                </div>
            </div>
        </section>
        <!-- End Events -->

        <!--=== Team ===-->
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
        <!--=== End Blog ===-->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
