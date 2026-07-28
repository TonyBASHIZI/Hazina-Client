<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = :id AND status = 'published'");
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$recentPosts = $pdo->prepare(
    "SELECT id, title, image, created_at FROM blog_posts
     WHERE status = 'published' AND id != :id
     ORDER BY created_at DESC LIMIT 4"
);
$recentPosts->execute(['id' => $id]);
$recentPosts = $recentPosts->fetchAll();

$pageTitle = $post['title'];
$pageDescription = mb_strimwidth(strip_tags($post['excerpt'] ?: $post['content']), 0, 160, '...');
$pageImage = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . ($post['image'] ?: '/client/assets/img/logo-two.png');
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Title -->
<div class="page-title-area title-bg-three">
    <div class="d-table">
        <div class="d-table-cell">
            <div class="container">
                <div class="title-item">
                    <h2><?= e($post['title']) ?></h2>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><span>Actualités</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page Title -->

<div class="donation-details-area ptb-100">
    <div class="container">
        <div class="row">

            <div class="col-lg-8">
                <div class="details-item">

                    <div class="details-img">
                        <img src="<?= e($post['image'] ?: 'assets/img/blog/blog-details1.jpg') ?>" alt="<?= e($post['title']) ?>" onerror="this.onerror=null;this.src='assets/img/blog/blog-details1.jpg';">
                        <h2><?= e($post['title']) ?></h2>

                        <div class="mb-3" style="color:#777; font-size:14px;">
                            <i class="icofont-calendar"></i> <?= date('d M, Y', strtotime($post['created_at'])) ?>
                            &nbsp; · &nbsp;
                            <i class="icofont-user-alt-3"></i> Par <?= e($post['author']) ?>
                        </div>

                        <p><?= nl2br(e($post['content'])) ?></p>
                    </div>

                    <?php
                        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                        $shareText = $post['title'];
                        $fbShare = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($currentUrl);
                        $twShare = 'https://twitter.com/intent/tweet?url=' . urlencode($currentUrl) . '&text=' . urlencode($shareText);
                        $waShare = 'https://wa.me/?text=' . urlencode($shareText . ' ' . $currentUrl);
                        ?>

                        <div class="details-share">
                            <div class="row">
                                <div class="col-12">
                                    <div class="left">
                                        <ul>
                                            <li><span>Partager :</span></li>
                                            <li><a href="<?= e($fbShare) ?>" target="_blank" rel="noopener" style="background-color:#1877F2 !important; border-color:#1877F2 !important;"><i class="icofont-facebook" style="color:#fff;"></i></a></li>
                                            <li><a href="<?= e($twShare) ?>" target="_blank" rel="noopener" style="background-color:#1DA1F2 !important; border-color:#1DA1F2 !important;"><i class="icofont-twitter" style="color:#fff;"></i></a></li>
                                            <li><a href="<?= e($waShare) ?>" target="_blank" rel="noopener" style="background-color:#25D366 !important; border-color:#25D366 !important;"><i class="icofont-brand-whatsapp" style="color:#fff;"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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

                    <div class="post widget-item">
                        <h3>Autres actualités</h3>
                        <?php foreach ($recentPosts as $rp): ?>
                        <div class="post-inner">
                            <ul class="align-items-center">
                                <li>
                                    <img src="<?= e($rp['image'] ?: 'assets/img/blog/blog1.jpg') ?>" alt="<?= e($rp['title']) ?>" onerror="this.onerror=null;this.src='assets/img/blog/blog1.jpg';">
                                </li>
                                <li>
                                    <h4>
                                        <a href="blog-details.php?id=<?= (int)$rp['id'] ?>"><?= e($rp['title']) ?></a>
                                    </h4>
                                    <p><?= date('d M, Y', strtotime($rp['created_at'])) ?></p>
                                </li>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                        <?php if (!$recentPosts): ?>
                            <p class="text-muted">Aucune autre actualité pour le moment.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>