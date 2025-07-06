<?php
$platform = $_GET['platform'] ?? 'netflix';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

// المسارات
$sourceFile = "browser.json";
$cacheDir = "cache";
$cacheFile = "{$cacheDir}/{$platform}_page_{$page}.json";
$cacheMeta = "{$cacheDir}/{$platform}_pages.txt";

// تحقق من الكاش أو أنشئه
if (!file_exists($cacheDir)) mkdir($cacheDir, 0777, true);

// إذا الكاش موجود وحديث، استخدمه
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

    // خزّن النتائج في ملفات كاش
    file_put_contents($cacheFile, json_encode($showsPage, JSON_UNESCAPED_UNICODE));
    file_put_contents($cacheMeta, $totalPages);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>تصفح المسلسلات</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #0d0d0d;
            color: #fff;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 30px;
        }

        .platform-selector {
            margin-bottom: 30px;
        }

        .select-box {
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 8px;
            border: none;
            background-color: #1e1e1e;
            color: white;
            cursor: pointer;
        }

        .cards-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
        }

        .card {
            position: relative;
            width: 280px;
            background-color: #141414;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.8);
            cursor: pointer;
            transition: transform 0.3s ease;
            text-align: center;
            color: #e5e5e5;
            user-select: none;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 30px rgba(229, 9, 20, 0.8);
        }

        .card img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }

        .rating {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(229, 9, 20, 0.85);
            padding: 5px 12px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 0 8px rgba(229, 9, 20, 0.7);
            z-index: 10;
            color: white;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin: 15px 10px 20px 10px;
            color: #e50914;
            text-shadow: 0 0 6px rgba(229, 9, 20, 0.7);
        }

        .pagination {
            margin-top: 30px;
            user-select: none;
        }

        .pagination a {
            display: inline-block;
            margin: 0 6px;
            padding: 8px 14px;
            background: #333;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 400;
            transition: background-color 0.3s ease;
        }

        .pagination a.active,
        .pagination a:hover {
            background: #e50914;
            font-weight: 700;
            box-shadow: 0 0 10px #e50914;
        }

        @media (max-width: 768px) {
            .card {
                width: 90vw;
                height: auto;
            }

            .card img {
                height: auto;
            }
        }
        .category-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .category-card {
            position: relative;
            width: 250px;
            height: 140px;
            border-radius: 16px;
            overflow: hidden;
            text-decoration: none;
            color: white;
            background-size: cover;
            background-position: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

     

        .category-card:hover,
        .category-card.active {
            transform: scale(1.05);
            box-shadow: 0 0 20px #e50914;
        }

    </style>
</head>
<body>

    <h1>📺 اختر المنصة</h1>

    <div class="category-container">
        <a href="?platform=netflix&page=1" class="category-card <?= $platform == 'netflix' ? 'active' : '' ?>" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGhnm_NIUms1oIl6QLrxjZzws8wLW_MVPOyw&s');">
        </a>
        <a href="?platform=shahid&page=1" class="category-card <?= $platform == 'shahid' ? 'active' : '' ?>" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRlwV1US7Ou5Sa4bd8ALXdp1QVcpQV9rPRr_A&s');">
        </a>
    </div>



<div class="cards-container">
<?php foreach ($showsPage as $show): ?>
    <a href="series.php?id=<?= $show['id'] ?>" class="card" title="<?= htmlspecialchars($show['title']) ?>">
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

</body>
</html>
