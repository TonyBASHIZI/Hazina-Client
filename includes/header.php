<?php
require_once __DIR__ . '/functions.php';
$client = currentClientUser();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/icofont.min.css">
    <link rel="stylesheet" href="assets/css/meanmenu.css">
    <link rel="stylesheet" href="assets/css/modal-video.min.css">
    <link rel="stylesheet" href="assets/fonts/flaticon.css">
    <link rel="stylesheet" href="assets/css/animate.min.css">
    <link rel="stylesheet" href="assets/css/lightbox.min.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="assets/css/odometer.min.css">
    <link rel="stylesheet" href="assets/css/nice-select.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/hf-theme.css">
    <link rel="stylesheet" href="assets/css/theme-dark.css">

    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>Hazina Funding</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>

<body>
    <div class="loader" style="display:flex; align-items:center; justify-content:center; background-color:#0B0F2A;">
            <div class="spinner-border" style="color:#D4AF37; width:3rem; height:3rem;" role="status"></div>
        </div>
    </div>
</div>

    <!-- Header -->
    <div class="header-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="left">
                        <ul>
                            <li><i class="icofont-location-pin"></i> <a href="#">RD Congo</a></li>
                            <li><i class="icofont-ui-call"></i> <a href="tel:0123456987">+243-000-000-000</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="right">
                        <ul>
                            <li><span>Suivez-nous :</span></li>
                            <li><a href="#" target="_blank"><i class="icofont-facebook"></i></a></li>
                            <li><a href="#" target="_blank"><i class="icofont-twitter"></i></a></li>
                            <li><a href="#" target="_blank"><i class="icofont-instagram"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Header -->

    <!-- Navbar -->
    <div class="navbar-area sticky-top">
        <div class="mobile-nav">
            <a href="index.php" class="logo">
                <img src="assets/img/logo-two.png" alt="Logo">
            </a>
        </div>

        <div class="main-nav">
            <div class="container">
                <nav class="navbar navbar-expand-md navbar-light">
                    <a class="navbar-brand" href="index.php">
                        <img src="assets/img/logo.png" class="logo-one" alt="Logo">
                        <img src="assets/img/logo-two.png" class="logo-two" alt="Logo">
                    </a>
                    <div class="collapse navbar-collapse mean-menu" id="navbarSupportedContent">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Accueil</a>
                            </li>
                            <li class="nav-item">
                                <a href="index.php#projects" class="nav-link">Projets</a>
                            </li>
                            <li class="nav-item">
                                <a href="about.php" class="nav-link">À propos</a>
                            </li>
                            <li class="nav-item">
                                <a href="contact.php" class="nav-link">Contact</a>
                            </li>
                            <?php if ($client): ?>
                            <li class="nav-item d-md-none">
                                <a href="profile.php" class="nav-link"><i class="icofont-user-alt-3"></i> <?= e($client['username']) ?></a>
                            </li>
                            <li class="nav-item d-md-none">
                                <a href="logout.php" class="nav-link"><i class="icofont-logout"></i> Déconnexion</a>
                            </li>
                            <?php else: ?>
                            <li class="nav-item d-md-none">
                                <a href="login.php" class="nav-link"><i class="icofont-heart-alt"></i> Connexion</a>
                            </li>
                            <?php endif; ?>
                            </ul>
                        <div class="side-nav">
                            <?php if ($client): ?>
                                <a class="donate-btn" href="profile.php">
                                    <i class="icofont-user-alt-3"></i> <?= e($client['username']) ?>
                                </a>
                                <a class="donate-btn" href="logout.php" style="margin-left:8px;">
                                    Déconnexion <i class="icofont-logout"></i>
                                </a>
                            <?php else: ?>
                                <a class="donate-btn" href="login.php">
                                    Connexion <i class="icofont-heart-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <!-- End Navbar -->
