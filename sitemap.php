<?php
require_once __DIR__ . '/includes/functions.php';
$pdo = getPDO();

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$base = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/client';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <url>
        <loc><?= e($base) ?>/index.php</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc><?= e($base) ?>/donors.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>

    <?php
    $projects = $pdo->query("SELECT id, updated_at FROM projects WHERE status != 'inactive'")->fetchAll();
    foreach ($projects as $p):
    ?>
    <url>
        <loc><?= e($base) ?>/donation-details.php?id=<?= (int)$p['id'] ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>

    <?php
    $posts = $pdo->query("SELECT id, updated_at FROM blog_posts WHERE status = 'published'")->fetchAll();
    foreach ($posts as $post):
    ?>
    <url>
        <loc><?= e($base) ?>/blog-details.php?id=<?= (int)$post['id'] ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($post['updated_at'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

</urlset>