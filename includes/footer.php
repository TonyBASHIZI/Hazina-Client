<?php
require_once __DIR__ . '/functions.php';
$urgentCauses = getPDO()->query(
    "SELECT id, title, image FROM projects WHERE status = 'active' ORDER BY created_at DESC LIMIT 2"
)->fetchAll();
?>
    <!-- Footer -->
    <footer class="footer-area pt-100">
        <div class="container">
            <div class="row">

                <div class="col-sm-6 col-lg-3">
                    <div class="footer-item">
                        <div class="footer-logo">
                            <a class="logo" href="index.php">
                                <img src="assets/img/logo-two.png" alt="Logo">
                            </a>
                            <p>Hazina Funding aide les communautés vulnérables à travers des campagnes de dons transparentes et vérifiées.</p>
                            <ul>
                                <li><a href="#" target="_blank"><i class="icofont-facebook"></i></a></li>
                                <li><a href="#" target="_blank"><i class="icofont-twitter"></i></a></li>
                                <li><a href="#" target="_blank"><i class="icofont-instagram"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="footer-item">
                        <div class="footer-causes">
                            <h3>Causes urgentes</h3>
                            <?php foreach ($urgentCauses as $cause): ?>
                            <div class="cause-inner">
                                <ul class="align-items-center">
                                    <li>
                                        <img src="<?= e(resolveImagePath($cause['image'])) ?>" alt="Cause" onerror="this.onerror=null;this.src='assets/img/footer-thumb1.jpg';">
                                    </li>
                                    <li>
                                        <h3>
                                            <a href="donation-details.php?id=<?= (int)$cause['id'] ?>"><?= e($cause['title']) ?></a>
                                        </h3>
                                    </li>
                                </ul>
                            </div>
                            <?php endforeach; ?>
                            <?php if (!$urgentCauses): ?>
                                <p class="text-white-50">Aucune campagne active pour le moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="footer-item">
                        <div class="footer-links">
                            <h3>Liens rapides</h3>
                            <ul>
                                <li><a href="about.php"><i class="icofont-simple-right"></i> À propos</a></li>
                                <li><a href="index.php#projects"><i class="icofont-simple-right"></i> Projets</a></li>
                                <li><a href="contact.php"><i class="icofont-simple-right"></i> Contact</a></li>
                                <?php if (!isClientLoggedIn()): ?>
                                <li><a href="login.php"><i class="icofont-simple-right"></i> Connexion</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="footer-item">
                        <div class="footer-contact">
                            <h3>Contact</h3>
                            <div class="contact-inner">
                                <ul>
                                    <li><i class="icofont-location-pin"></i> <a href="#">RD Congo</a></li>
                                    <li><i class="icofont-ui-call"></i> <a href="tel:0123456987">+243-000-000-000</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="copyright-area">
                <p>Copyright &copy; <?= date('Y') ?> Hazina Funding. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <div class="go-top">
        <i class="icofont-arrow-up"></i>
        <i class="icofont-arrow-up"></i>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/form-validator.min.js"></script>
    <script src="assets/js/contact-form-script.js"></script>
    <script src="assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="assets/js/jquery.meanmenu.js"></script>
    <script src="assets/js/jquery-modal-video.min.js"></script>
    <script src="assets/js/wow.min.js"></script>
    <script src="assets/js/lightbox.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/odometer.min.js"></script>
    <script src="assets/js/jquery.appear.min.js"></script>
    <script src="assets/js/jquery.nice-select.min.js"></script>
    <script src="assets/js/custom.js"></script>
</body>

</html>
