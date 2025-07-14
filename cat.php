<?php
session_start();
include 'includes/header.php';

$category = $_GET['category'] ?? 'series'; // 'series' أو 'movies'
$type = $_GET['type'] ?? 'created';
$page = $_GET['page'] ?? 1;

$KEY1 = "4F5A9C3D9A86FA54EACEDDD635185";
$KEY2 = "d506abfd-9fe2-4b71-b979-feff21bcad13";

$items = [];
$cacheDir = "cache";

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

if ($type === 'ramadan2025' && $category === 'series') {
    $jsonFile = "{$cacheDir}/{$category}-ramadan2025.json";

    if (!file_exists($jsonFile) || isset($_GET['refresh'])) {
        $allItems = [];

        for ($p = 1; $p <= 30; $p++) {
            $apiUrl = "https://app.arabypros.com/api/serie/by/filtres/0/year/{$p}/{$KEY1}/{$KEY2}/";
            $headers = ["User-Agent: okhttp/4.8.0", "Accept-Encoding: gzip"];

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => 'gzip'
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                $pageItems = isset($data[0]['id']) ? $data : ($data['posters'] ?? []);
                foreach ($pageItems as $item) {
                    if (stripos($item['title'], 'رمضان 2025') !== false) {
                        $allItems[] = $item;
                    }
                }
            }
        }

        file_put_contents($jsonFile, json_encode($allItems, JSON_UNESCAPED_UNICODE));
    }

    if (file_exists($jsonFile)) {
        $items = json_decode(file_get_contents($jsonFile), true);
    }
} else {
    $jsonFile = "{$cacheDir}/{$category}-{$type}-{$page}.json";

    $baseUrl = $category === 'series' 
        ? "https://app.arabypros.com/api/serie/by/filtres/0/{$type}/{$page}/{$KEY1}/{$KEY2}/"
        : "https://app.arabypros.com/api/movie/by/filtres/0/{$type}/{$page}/{$KEY1}/{$KEY2}/";

    if (!file_exists($jsonFile) || isset($_GET['refresh'])) {
        $headers = ["User-Agent: okhttp/4.8.0", "Accept-Encoding: gzip"];

        $ch = curl_init($baseUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => 'gzip'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            file_put_contents($jsonFile, $response);
        } else {
            echo "<div style='color: red;'>❌ فشل في جلب البيانات من API.</div>";
        }
    }

    if (file_exists($jsonFile)) {
        $data = json_decode(file_get_contents($jsonFile), true);
        $items = isset($data[0]['id']) ? $data : ($data['posters'] ?? []);
    }
}
?>

<div class="container">
    <h2>🎬 <?= $category === 'movies' ? 'الأفلام' : 'المسلسلات' ?> - حسب: <?= htmlspecialchars($type) ?> <?= ($type === 'ramadan2025' && $category === 'series') ? '' : "(صفحة $page)" ?></h2>

    <div class="categories">
        <strong>التصنيف:</strong>
        <a href="?category=series&type=<?= $type ?>" class="<?= $category === 'series' ? 'active' : '' ?>">📺 مسلسلات</a>
        <a href="?category=movies&type=<?= $type ?>" class="<?= $category === 'movies' ? 'active' : '' ?>">🎥 أفلام</a>
    </div>

    <div class="filters">
        
        <strong>فرز حسب:</strong>
        <a href="?category=<?= $category ?>&type=created" class="<?= $type === 'created' ? 'active' : '' ?>">🆕 الأحدث</a>
        <a href="?category=<?= $category ?>&type=rating" class="<?= $type === 'rating' ? 'active' : '' ?>">⭐ الأعلى تقييماً</a>
        <a href="?category=<?= $category ?>&type=views" class="<?= $type === 'views' ? 'active' : '' ?>">🔥 الأكثر مشاهدة</a>
        <a href="?category=<?= $category ?>&type=year" class="<?= $type === 'year' ? 'active' : '' ?>">📅 الأحدث سنة</a>
        <?php if ($category === 'series'): ?>
            <a href="?category=series&type=ramadan2025" class="<?= $type === 'ramadan2025' ? 'active' : '' ?>">🌙 رمضان 2025</a>
        <?php endif; ?>
        <a href="?category=<?= $category ?>&type=<?= $type ?>&page=<?= $page ?>&refresh=1">🔄 تحديث</a>
    </div>

    <?php if (empty($items)): ?>
        <div style="color:red;">⚠️ لا توجد بيانات متوفرة.</div>
    <?php endif; ?>

    <div class="series-grid">
        <?php foreach ($items as $index => $item): ?>
            <div class="movie-card">
                <a href="<?= $category === 'movies' ? 'movie/links.php?id=' : 'series.php?id=' ?><?= $item['id'] ?>">
                    <?php
                    $hasTopBadge = ($index < 5);
                    ?>

                    <div class="movie-thumb <?= $hasTopBadge ? 'has-top-badge' : 'no-top-badge' ?>">
                        <?php if ($hasTopBadge): ?>
                            <div class="top-badge">TOP <?= $index + 1 ?></div>
                        <?php endif; ?>

                        <?php if (!empty($item['label'])): ?>
                            <div class="label-badge"><?= htmlspecialchars($item['label']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($item['sublabel'])): ?>
                            <div class="sub-badge"><?= htmlspecialchars($item['sublabel']) ?></div>
                        <?php endif; ?>

                        <img src="<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>">

                        <div class="rating-overlay">
                            ⭐ <?= 
                                (isset($item['rating']) && is_numeric($item['rating'])) ? $item['rating'] : 
                                ((isset($item['rate']) && is_numeric($item['rate'])) ? $item['rate'] : 'N/A') 
                            ?>
                        </div>

                        <div class="watch-overlay">
                          <i class="fa fa-play play-icon" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div class="movie-info">
                        <div class="movie-title"><?= htmlspecialchars($item['title']) ?></div>
                        <div class="movie-meta">
                            <?= $item['year'] ?? '----' ?> • <?= $category === 'movies' ? 'Movie' : 'Serie' ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>





    <?php if (!($type === 'ramadan2025' && $category === 'series')): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?category=<?= $category ?>&type=<?= $type ?>&page=<?= $page - 1 ?>">⬅️ السابقة</a>
            <?php endif; ?>
            <a href="?category=<?= $category ?>&type=<?= $type ?>&page=<?= $page + 1 ?>">التالي ➡️</a>
        </div>
    <?php endif; ?>
</div>


<style>
    body {
        background-color: #121212;
        color: #fff;
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        padding: 30px;
    }

    h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #fff;
    }

    a {
        color: #f44336;
        text-decoration: none;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }

    .series-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px;
    }

    .movie-card {
        position: relative;
        background-color: #1e1e1e;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
        transition: transform 0.2s ease;
    }

    .movie-card:hover {
        transform: scale(1.03);
    }

    .movie-thumb {
        position: relative;
    }

    .movie-thumb img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        display: block;
    }

    .watch-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #f44336;
        color: white;
        font-weight: bold;
        width: 50px;
        height: 50px;
        font-size: 0;
        border-radius: 50%;
        display: none;  /* خليها مخفية افتراضياً */
        z-index: 2;
        cursor: pointer;
        justify-content: center;
        align-items: center;
        box-shadow: 0 0 12px rgba(244, 67, 54, 0.7);
        transition: background-color 0.3s ease;
    }

    .movie-card:hover .movie-thumb .watch-overlay {
        display: flex;
    }

    .watch-overlay:hover {
        background-color: #d32f2f;
    }

    /* أيقونة بلاي */
    .watch-overlay i.fa-play {
        color: white;
        font-size: 26px;
        text-shadow: 0 0 8px rgba(255, 255, 255, 0.9);
    }

    .rating-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        background: rgba(0,0,0,0.7);
        color: gold;
        padding: 4px 8px;
        font-size: 13px;
        border-top-right-radius: 8px;
        display: none;
        z-index: 2;
    }

    .movie-card:hover .rating-overlay {
        display: block;
    }


    .top-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #f44336;
        color: white;
        font-weight: bold;
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 8px;
        z-index: 4;
        box-shadow: 0 0 6px rgba(244, 67, 54, 0.8);
    }

    .label-badge, .sub-badge {
        max-width: 120px;  /* أو القيمة اللي تناسب تصميمك */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: bold;
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 8px;
        color: white;
        box-shadow: 0 0 6px rgba(0,0,0,0.5);
    }

    /* خاص بالـ label */
    .label-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #2196f3;
        z-index: 5;
        box-shadow: 0 0 6px rgba(33, 150, 243, 0.8);
    }

    /* خاص بالـ sub-badge */
    .sub-badge {
        position: absolute;
        left: 10px;
        z-index: 4;
    }

    /* sub-badge مع top-badge */
    .movie-thumb.has-top-badge .sub-badge {
        top: 40px;
        background: #555;
        box-shadow: 0 0 6px rgba(85, 85, 85, 0.8);
    }

    /* sub-badge بدون top-badge */
    .movie-thumb.no-top-badge .sub-badge {
        top: 10px;
        background: #f44336;
        box-shadow: 0 0 6px rgba(244, 67, 54, 0.8);
    }


    /* sub-badge تحت top-badge */
    .movie-thumb.has-top-badge .sub-badge {
        top: 40px; /* تحت التوب بادج */
        background: #555; /* رمادي */
        box-shadow: 0 0 6px rgba(85, 85, 85, 0.8);
    }

    /* sub-badge فوق لما ما في top-badge */
    .movie-thumb.no-top-badge .sub-badge {
        top: 10px;
        background: #f44336; /* أحمر */
        box-shadow: 0 0 6px rgba(244, 67, 54, 0.8);
    }

    

    .movie-info {
        padding: 10px;
        font-size: 14px;
        text-align: center;
    }

    .movie-title {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 6px;
        color: #fff;
    }

    .movie-meta {
        font-size: 12px;
        color: #aaa;
    }




    .filters, .categories {
        margin-bottom: 20px;
    }

    .filters a, .categories a {
        background-color: #2a2a2a;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        margin: 4px;
        display: inline-block;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    .filters a:hover, .categories a:hover {
        background-color: #f44336;
    }

    .filters a.active, .categories a.active {
        background-color: #f44336;
    }

    .pagination {
        text-align: center;
        margin-top: 30px;
    }

    .pagination a {
        background-color: #2a2a2a;
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
        margin: 0 5px;
        font-weight: bold;
        display: inline-block;
    }

    .pagination a:hover {
        background-color: #f44336;
    }

   

</style>
