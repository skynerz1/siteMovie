<?php
session_start();
include 'includes/header.php';

$category = $_GET['category'] ?? 'series'; // 'series' أو 'movies'
$type = $_GET['type'] ?? 'created';
$subtype = $_GET['subtype'] ?? 'all'; // 'khaleeji', 'araby', 'all'
$page = (int)($_GET['page'] ?? 1);

$KEY1 = "4F5A9C3D9A86FA54EACEDDD635185";
$KEY2 = "d506abfd-9fe2-4b71-b979-feff21bcad13";

$items = [];
$cacheDir = "cache";

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// دالة فلترة genre عامة
function filterByGenre($items, $selectedGenre) {
    return array_filter($items, function($item) use ($selectedGenre) {
        if (empty($item['classification'])) return false;
        return mb_strpos(mb_strtolower($item['classification']), mb_strtolower($selectedGenre)) !== false;
    });
}

// جلب بيانات رمضان 2025 لمسلسلات فقط
if ($type === 'ramadan' && $category === 'series' && isset($_GET['ramadanYear'])) {
    $ramadanYear = preg_replace('/[^0-9]/', '', $_GET['ramadanYear']);
    $jsonFile = "{$cacheDir}/{$category}-ramadan{$ramadanYear}.json";

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
                    $hasRamadanGenre = false;
                    if (isset($item['genres']) && is_array($item['genres'])) {
                        foreach ($item['genres'] as $g) {
                            if (isset($g['title']) && trim($g['title']) === "مسلسلات رمضان {$ramadanYear}") {
                                $hasRamadanGenre = true;
                                break;
                            }
                        }
                    }
                    if ($hasRamadanGenre) {
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

    // تطبيق الفلترة حسب subtype
    function filterRamadanKhaleeji($items) {
        $khaleeji = ['السعودية', 'الامارات', 'الكويت'];
        return array_filter($items, function($item) use ($khaleeji) {
            if (empty($item['classification'])) return false;

            $classification = mb_strtolower(trim($item['classification']));
            foreach ($khaleeji as $country) {
                if (mb_strpos($classification, mb_strtolower($country)) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    function filterRamadanAraby($items) {
        $araby = ['مصر', 'سوريا', 'العراق', 'تونس'];
        return array_filter($items, function($item) use ($araby) {
            if (empty($item['classification'])) return false;

            $classification = mb_strtolower(trim($item['classification']));
            foreach ($araby as $country) {
                if (mb_strpos($classification, mb_strtolower($country)) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    if ($subtype === 'khaleeji') {
        $items = filterRamadanKhaleeji($items);
    } elseif ($subtype === 'araby') {
        $items = filterRamadanAraby($items);
    }

// فلترة genre
$selectedGenre = $_GET['genre'] ?? 'all';
if ($selectedGenre !== 'all') {
    $items = filterByGenre($items, $selectedGenre);
}

// ✅ فلترة classification
$currentClassification = $_GET['classification'] ?? 'all';
if ($currentClassification !== 'all') {
    $items = array_filter($items, function($item) use ($currentClassification) {
        return isset($item['classification']) &&
               mb_stripos($item['classification'], $currentClassification) !== false;
    });
}


} else {
    $selectedGenre = $_GET['genre'] ?? 'all';

    if ($selectedGenre !== 'all') {
        // جلب من عدة صفحات (مثلاً 5 صفحات) وتجميعها
        $pagesToFetch = 5;
        $allItems = [];

        for ($p = 1; $p <= $pagesToFetch; $p++) {
            $jsonFile = "{$cacheDir}/{$category}-{$type}-{$p}.json";

            $baseUrl = $category === 'series' 
                ? "https://app.arabypros.com/api/serie/by/filtres/0/{$type}/{$p}/{$KEY1}/{$KEY2}/"
                : "https://app.arabypros.com/api/movie/by/filtres/0/{$type}/{$p}/{$KEY1}/{$KEY2}/";

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
                    echo "<div style='color: red;'>❌ فشل في جلب البيانات من API الصفحة $p.</div>";
                    continue;
                }
            }

            if (file_exists($jsonFile)) {
                $data = json_decode(file_get_contents($jsonFile), true);
                $pageItems = isset($data[0]['id']) ? $data : ($data['posters'] ?? []);
                $allItems = array_merge($allItems, $pageItems);
            }
        }

        // تطبيق فلترة genre على كل العناصر المجمعة
        $items = filterByGenre($allItems, $selectedGenre);

    } else {
        // لو مافيش فلترة genre، نكتفي بصفحة واحدة فقط
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
}

?>


<div class="container">
    <h2>🎬 <?= $category === 'movies' ? 'الأفلام' : 'المسلسلات' ?> - حسب: <?= htmlspecialchars($type) ?> 
        <?= ($type === 'ramadan2025' && $category === 'series') ? ($subtype !== 'all' ? "($subtype)" : '') : "(صفحة $page)" ?>
    </h2>

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
        <a href="?category=series&type=ramadan&ramadanYear=2025" class="<?= $type === 'ramadan' ? 'active' : '' ?>">🌙 رمضان</a>
    <?php endif; ?>
    <a href="?category=<?= $category ?>&type=<?= $type ?>&page=<?= $page ?>&refresh=1">🔄 تحديث</a>
</div>

<?php
$seriesGenres = ['دراما', 'اثارة', 'جريمة', 'غموض', 'اكشن'];
$movieGenres  = ['رعب', 'مغامرة', 'رومانسي', 'دراما', 'علمي', 'خيال', 'كوميديا', 'غموض', 'اثارة'];

$currentGenres = $category === 'series' ? $seriesGenres : $movieGenres;
$currentGenre = $_GET['genre'] ?? 'all';

if ($category === 'series') {
    $classifications = [
        'all' => 'الكل',
        'مسلسلات تركية' => 'مسلسلات تركية',
        'مسلسلات عربية' => 'مسلسلات عربية',
        'مسلسلات أجنبية' => 'مسلسلات أجنبية',
        'مسلسلات آسيوية' => 'مسلسلات آسيوية',
    ];
} else {
    $classifications = [
        'all' => 'الكل',
        'أفلام أجنبية' => 'أفلام أجنبية',
        'أفلام عربية' => 'أفلام عربية',
        'أفلام آسيوية' => 'أفلام آسيوية',
    ];
}

$currentClassification = $_GET['classification'] ?? 'all';

// شرط ما يظهر الفلاتر في حالة رمضان
if ($type !== 'ramadan'):
 
?>

<div class="filters">
    <strong>فلترة حسب النوع:</strong>
    <select id="genreFilter" style="padding:5px 10px; font-size:16px;">
        <option value="all" <?= $currentGenre === 'all' ? 'selected' : '' ?>>الكل</option>
        <?php foreach ($currentGenres as $genre): ?>
            <option value="<?= htmlspecialchars($genre) ?>" <?= $currentGenre === $genre ? 'selected' : '' ?>>
                <?= htmlspecialchars($genre) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <strong>فلترة حسب التصنيف:</strong>
    <select id="classificationFilter" style="padding:5px 10px; font-size:16px;">
        <?php foreach ($classifications as $key => $label): ?>
            <option value="<?= htmlspecialchars($key) ?>" <?= $currentClassification === $key ? 'selected' : '' ?>>
                <?= htmlspecialchars($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<script>
document.getElementById('genreFilter').addEventListener('change', function() {
    const genre = this.value;
    const params = new URLSearchParams(window.location.search);
    params.set('genre', genre);
    params.set('page', '1');
    window.location.search = params.toString();
});

document.getElementById('classificationFilter').addEventListener('change', function() {
    const classification = this.value;
    const params = new URLSearchParams(window.location.search);
    params.set('classification', classification);
    params.set('page', '1');
    window.location.search = params.toString();
});
</script>

<?php endif; ?>




<?php if ($type === 'ramadan' && $category === 'series'): ?>
    <div class="filters">
        <strong>رمضان:</strong>
        <?php 
        $ramadanYears = [2025, 2024];
        foreach ($ramadanYears as $year): 
        ?>
            <a href="?category=series&type=ramadan&ramadanYear=<?= $year ?>&subtype=<?= $subtype ?>" 
               class="<?= ($_GET['ramadanYear'] ?? '') == $year ? 'active' : '' ?>">🌙 رمضان <?= $year ?></a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($type === 'ramadan' && $category === 'series' && isset($_GET['ramadanYear'])): ?>
    <div class="filters">
        <strong>عرض:</strong>
        <a href="?category=series&type=ramadan&ramadanYear=<?= $_GET['ramadanYear'] ?>&subtype=all" class="<?= $subtype === 'all' ? 'active' : '' ?>">الكل</a>
        <a href="?category=series&type=ramadan&ramadanYear=<?= $_GET['ramadanYear'] ?>&subtype=khaleeji" class="<?= $subtype === 'khaleeji' ? 'active' : '' ?>">الخليجي</a>
        <a href="?category=series&type=ramadan&ramadanYear=<?= $_GET['ramadanYear'] ?>&subtype=araby" class="<?= $subtype === 'araby' ? 'active' : '' ?>">العربي</a>
    </div>
<?php endif; ?>


    <?php if (empty($items)): ?>
        <div style="color:red;">⚠️ لا توجد بيانات متوفرة.</div>
    <?php endif; ?>

    <div class="series-grid">
        <?php foreach ($items as $index => $item): ?>
            <div class="movie-card">
                <a href="<?= $category === 'movies' ? 'movie/links.php?id=' : 'series.php?id=' ?><?= $item['id'] ?>">
                    <?php $hasTopBadge = ($index < 5); ?>
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

    .filters select {
        background-color: #2a2a2a;
        color: #fff;
        padding: 8px 14px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s ease;
        min-width: 150px;
    }

    .filters select:hover {
        background-color: #f44336;
        color: #fff;
    }

    .filters select:focus {
        outline: none;
        background-color: #f44336;
        color: #fff;
    }


</style>
