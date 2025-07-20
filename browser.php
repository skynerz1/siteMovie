<?php
include 'load.php';
include 'includes/header.php';

$platform = $_GET['platform'] ?? 'netflix';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

$sourceFile = "browser.json";
$cacheDir = "cache";
$cacheFile = "{$cacheDir}/{$platform}_page_{$page}.json";
$cacheMeta = "{$cacheDir}/{$platform}_pages.txt";

if (!file_exists($cacheDir)) mkdir($cacheDir, 0777, true);

if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($sourceFile)) {
    $showsPage = json_decode(file_get_contents($cacheFile), true);
    $totalPages = (int)file_get_contents($cacheMeta);
} else {
    $data = json_decode(file_get_contents($sourceFile), true);
    $shows = $data[$platform] ?? [];

    $totalShows = count($shows);
    $totalPages = max(1, ceil($totalShows / $perPage));

    if ($page < 1) $page = 1;
    if ($page > $totalPages) $page = $totalPages;

    $start = ($page - 1) * $perPage;
    $showsPage = array_slice($shows, $start, $perPage);

    file_put_contents($cacheFile, json_encode($showsPage, JSON_UNESCAPED_UNICODE));
    file_put_contents($cacheMeta, $totalPages);
}
?>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: #121212;
    color: #fff;
    line-height: 1.6;
}



.cards-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    padding: 20px;
}



.card img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}



.pagination {
    margin: 30px auto;
    text-align: center;
}

.pagination a {
    display: inline-block;
    margin: 0 6px;
    padding: 8px 14px;
    background: #2a2a2a;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s ease;
}



.category-container {
    display: flex;
    justify-content: center;
    gap: 16px;
    padding: 0 20px 30px;
    flex-wrap: wrap;
}

.category-card {
    width: 140px;
    height: 80px;
    border-radius: 12px;
    background-size: cover;
    background-position: center;
    border: 2px solid transparent;
    transition: 0.3s ease all;
}



@media (max-width: 768px) {
    .card {
        width: 90vw;
    }

    .card img {
        height: auto;
    }

    h2.platform-title {
        margin-top: 100px;
    }
}
</style>

<h2 class="platform-title">Choose Platform</h2>

<div class="category-container">
  <a href="?platform=netflix&page=1" class="category-card <?= $platform === 'netflix' ? 'active' : '' ?>" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGhnm_NIUms1oIl6QLrxjZzws8wLW_MVPOyw&s');" title="Netflix"></a>
  <a href="?platform=shahid&page=1" class="category-card <?= $platform === 'shahid' ? 'active' : '' ?>" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRlwV1US7Ou5Sa4bd8ALXdp1QVcpQV9rPRr_A&s');" title="Shahid"></a>
  <a href="?platform=kids&page=1" class="category-card <?= $platform === 'kids' ? 'active' : '' ?>" style="background-image: url('https://i.pinimg.com/736x/e6/84/49/e68449b851a8ffb8256a71daab209775.jpg');" title="Kids"></a>
</div>

<div class="cards-container">
<?php foreach ($showsPage as $show): ?>
    <?php
        $isMovie = isset($show['type']) && $show['type'] === 'movie';
        $link = $isMovie
            ? 'movie/links.php?id=' . urlencode($show['id'])
            : 'series.php?id=' . urlencode($show['id']);
    ?>
    <a href="<?= $link ?>" class="card" title="<?= htmlspecialchars($show['title']) ?>">
        <img src="<?= htmlspecialchars($show['image']) ?>" alt="<?= htmlspecialchars($show['title']) ?>" />
        <div class="rating">⭐ <?= htmlspecialchars($show['rating']) ?>/5</div>
        <div class="card-title"><?= htmlspecialchars($show['title']) ?></div>
    </a>
<?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?platform=<?= urlencode($platform) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>


</body>
</html>
