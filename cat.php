<?php
session_start();
include 'includes/header.php';

$category = $_GET['category'] ?? 'series';
$type = $_GET['type'] ?? 'created';
$subtype = $_GET['subtype'] ?? 'all';
$page = max(1, (int)($_GET['page'] ?? 1));  // تأكد أن الصفحة عدد صحيح ≥ 1
$ramadanYear = $_GET['ramadan_year'] ?? '2025';

$selectedClassification = $_GET['classification'] ?? 'all';
$selectedGenre = $_GET['genre'] ?? 'all';

$KEY1 = "4F5A9C3D9A86FA54EACEDDD635185";
$KEY2 = "d506abfd-9fe2-4b71-b979-feff21bcad13";

$items = [];
$cacheDir = "cache";

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// تعريفات التصنيفات والأنواع حسب الفئة
$classificationsList = [
    'series' => ['all', 'دراما', 'اثارة', 'جريمة', 'غموض', 'اكشن'],
    'movies' => ['all', 'رعب', 'مغامرة', 'رومانسي', 'دراما', 'علمي', 'خيال', 'كوميديا', 'غموض', 'اثارة']
];

$genresList = [
    'series' => [
        'all' => 'الكل',
        'مسلسلات تركية' => 'مسلسلات تركية',
        'مسلسلات عربية' => 'مسلسلات عربية',
        'مسلسلات أجنبية' => 'مسلسلات أجنبية',
        'مسلسلات آسيوية' => 'مسلسلات آسيوية',
    ],
    'movies' => [
        'all' => 'الكل',
        'أفلام أجنبية' => 'أفلام أجنبية',
        'أفلام عربية' => 'أفلام عربية',
        'أفلام آسيوية' => 'أفلام آسيوية',
    ]
];

// دالة مساعدة لجلب البيانات من API مع التخزين المؤقت
function fetchDataWithCache($url, $cacheFile) {
    if (!file_exists($cacheFile) || isset($_GET['refresh'])) {
        $headers = ["User-Agent: okhttp/4.8.0", "Accept-Encoding: gzip"];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => 'gzip'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if ($response) {
            file_put_contents($cacheFile, $response);
        } else {
            echo "<div style='color: red;'>❌ فشل في جلب البيانات من API.</div>";
            return null;
        }
    }
    return json_decode(file_get_contents($cacheFile), true);
}

// دوال الفلترة الخاصة برمضان
function filterByClassificationKeywords($items, $keywords) {
    return array_filter($items, function($item) use ($keywords) {
        if (empty($item['classification'])) return false;
        $classification = mb_strtolower(trim($item['classification']));
        foreach ($keywords as $word) {
            if (mb_strpos($classification, mb_strtolower($word)) !== false) {
                return true;
            }
        }
        return false;
    });
}

// حالة النوع رمضاني
if ($type === 'ramadan' && $category === 'series') {
    $jsonFile = "{$cacheDir}/{$category}-ramadan-{$ramadanYear}.json";

    if (!file_exists($jsonFile) || isset($_GET['refresh'])) {
        $allItems = [];

        for ($p = 1; $p <= 30; $p++) {
            $apiUrl = "https://app.arabypros.com/api/serie/by/filtres/0/year/{$p}/{$KEY1}/{$KEY2}/";
            $data = fetchDataWithCache($apiUrl, "{$cacheDir}/temp_{$p}.json");
            if (!$data) continue;

            $pageItems = isset($data[0]['id']) ? $data : ($data['posters'] ?? []);
            foreach ($pageItems as $item) {
                if (!empty($item['genres']) && is_array($item['genres'])) {
                    foreach ($item['genres'] as $g) {
                        if (isset($g['title']) && trim($g['title']) === "مسلسلات رمضان {$ramadanYear}") {
                            $allItems[] = $item;
                            break;
                        }
                    }
                }
            }
        }

        file_put_contents($jsonFile, json_encode($allItems, JSON_UNESCAPED_UNICODE));
        // حذف الملفات المؤقتة
        for ($p = 1; $p <= 30; $p++) {
            @unlink("{$cacheDir}/temp_{$p}.json");
        }
    }

    if (file_exists($jsonFile)) {
        $items = json_decode(file_get_contents($jsonFile), true);
    }

    if ($subtype === 'khaleeji') {
        $items = filterByClassificationKeywords($items, ['السعودية', 'الامارات', 'الكويت']);
    } elseif ($subtype === 'araby') {
        $items = filterByClassificationKeywords($items, ['مصر', 'سوريا', 'العراق', 'تونس']);
    }

    // حالة الأنواع الأخرى (غير رمضاني)
    } else {
        $block = max(1, (int)($_GET['block'] ?? 1));  // رقم المجموعة الحالية (1, 2, 3, ...)
        $pagesPerBlock = 10;
        $startPage = ($block - 1) * $pagesPerBlock + 1;
        $endPage = $block * $pagesPerBlock;

        $allItems = [];

        for ($p = $startPage; $p <= $endPage; $p++) {
            $jsonFile = "{$cacheDir}/{$category}-{$type}-page{$p}.json";

            $apiUrl = $category === 'series' 
                ? "https://app.arabypros.com/api/serie/by/filtres/0/{$type}/{$p}/{$KEY1}/{$KEY2}/"
                : "https://app.arabypros.com/api/movie/by/filtres/0/{$type}/{$p}/{$KEY1}/{$KEY2}/";

            $data = fetchDataWithCache($apiUrl, $jsonFile);
            if (!$data) {
                break;  // لو فشل في جلب صفحة، نوقف اللوب
            }

            $pageItems = isset($data[0]['id']) ? $data : ($data['posters'] ?? []);
            $allItems = array_merge($allItems, $pageItems);
        }

        // فلترة حسب التصنيف إذا اخترت غير all
        if ($selectedClassification !== 'all') {
            $allItems = array_filter($allItems, function($item) use ($selectedClassification) {
                if (empty($item['classification'])) return false;
                return mb_strtolower(trim($item['classification'])) === mb_strtolower($selectedClassification);
            });
        }

        // فلترة حسب النوع genre إذا اخترت غير all
        if ($selectedGenre !== 'all') {
            $allItems = array_filter($allItems, function($item) use ($selectedGenre) {
                if (empty($item['genres']) || !is_array($item['genres'])) return false;
                foreach ($item['genres'] as $g) {
                    if (isset($g['title']) && mb_strtolower(trim($g['title'])) === mb_strtolower($selectedGenre)) {
                        return true;
                    }
                }
                return false;
            });
        }

        $items = array_values($allItems);

       
    }
if (!($type === 'ramadan' && $category === 'series')) {
    // تفعيل التقسيم الداخلي داخل البلوك
    $pageInBlock = max(1, (int)($_GET['page_in_block'] ?? 1));
    $itemsPerPage = 18;

    $totalItems = count($items);
    $totalPagesInBlock = ceil($totalItems / $itemsPerPage);

    // قص النتائج حسب الصفحة الحالية داخل البلوك
    $startIndex = ($pageInBlock - 1) * $itemsPerPage;
    $items = array_slice($items, $startIndex, $itemsPerPage);
}



?>





<!-- واجهة المستخدم -->
<div class="container">
    <h2>🎬 <?= $category === 'movies' ? 'الأفلام' : 'المسلسلات' ?> - حسب: <?= htmlspecialchars($type) ?> 
        <?= ($type === 'ramadan' && $category === 'series') ? "(رمضان $ramadanYear" . ($subtype !== 'all' ? " - $subtype" : '') . ")" : "(صفحة $page)" ?>
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
            <a href="?category=series&type=ramadan&ramadan_year=2025" class="<?= $type === 'ramadan' ? 'active' : '' ?>">🌙 رمضان</a>
        <?php endif; ?>
        <a href="?category=<?= $category ?>&type=<?= $type ?>&page=<?= $page ?>&refresh=1">🔄 تحديث</a>
    </div>

    <?php if (!($type === 'ramadan' && $category === 'series')): ?>
        <div class="sub-filters" style="margin: 20px 0 30px 0; display: flex; gap: 12px; flex-wrap: wrap;">

            <!-- Dropdown: النوع -->
            <strong>فلترة حسب النوع:</strong>
            <select onchange="location = this.value;">
                <option disabled selected>فلترة حسب النوع</option>
                <?php foreach ($genresList[$category] as $key => $label): ?>
                    <option 
                        value="?category=<?= $category ?>&type=<?= $type ?>&classification=<?= $selectedClassification ?>&genre=<?= $key ?>"
                        <?= $key === $selectedGenre ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Dropdown: التصنيف -->
            <strong>فلترة حسب التصنيف:</strong>
            <select onchange="location = this.value;">
                <option disabled selected>فلترة حسب التصنيف</option>
                <?php foreach ($classificationsList[$category] as $class): ?>
                    <option 
                        value="?category=<?= $category ?>&type=<?= $type ?>&classification=<?= $class ?>&genre=<?= $selectedGenre ?>"
                        <?= $class === $selectedClassification ? 'selected' : '' ?>>
                        <?= htmlspecialchars($class === 'all' ? 'الكل' : $class) ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>
    <?php endif; ?>




    <?php if ($type === 'ramadan' && $category === 'series'): ?>
        <div class="filters">
            <strong>السنة:</strong>
            <a href="?category=series&type=ramadan&ramadan_year=2025" class="<?= $ramadanYear == '2025' ? 'active' : '' ?>">2025</a>
            <a href="?category=series&type=ramadan&ramadan_year=2024" class="<?= $ramadanYear == '2024' ? 'active' : '' ?>">2024</a>
        </div>
        <div class="filters">
            <strong>عرض:</strong>
            <a href="?category=series&type=ramadan&ramadan_year=<?= $ramadanYear ?>&subtype=all" class="<?= $subtype === 'all' ? 'active' : '' ?>">الكل</a>
            <a href="?category=series&type=ramadan&ramadan_year=<?= $ramadanYear ?>&subtype=khaleeji" class="<?= $subtype === 'khaleeji' ? 'active' : '' ?>">الخليجي</a>
            <a href="?category=series&type=ramadan&ramadan_year=<?= $ramadanYear ?>&subtype=araby" class="<?= $subtype === 'araby' ? 'active' : '' ?>">العربي</a>
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

    <?php if (!($type === 'ramadan' && $category === 'series')): ?>
        <div class="pagination">
            <?php if ($block > 1): ?>
                <a href="?category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>&classification=<?= urlencode($selectedClassification) ?>&genre=<?= urlencode($selectedGenre) ?>&block=<?= $block - 1 ?>" class="prev">⬅️ السابقة</a>
            <?php endif; ?>
            <a href="?category=<?= urlencode($category) ?>&type=<?= urlencode($type) ?>&classification=<?= urlencode($selectedClassification) ?>&genre=<?= urlencode($selectedGenre) ?>&block=<?= $block + 1 ?>" class="next">التالي ➡️</a>
        </div>
    <?php endif; ?>

</div>

<script>
// دالة لتطبيق الفلاتر وإعادة تحميل الصفحة مع المعاملات الجديدة
function applyFilters() {
    const classification = document.getElementById('classification-select').value;
    const genre = document.getElementById('genre-select').value;

    const params = new URLSearchParams(window.location.search);

    params.set('classification', classification);
    params.set('genre', genre);
    params.set('page', 1); // ارجع للصفحة 1 لما تغير الفلتر

    window.location.search = params.toString();
}
</script>




<style>
.sort-buttons a {
    display: inline-block;
    padding: 6px 12px;
    margin: 5px 5px 10px 0;
    background-color: #f0f0f0;
    border-radius: 6px;
    border: 1px solid #ccc;
    text-decoration: none;
    color: #333;
    font-weight: 600;
    transition: background-color 0.3s;
}
.sort-buttons a.active,
.sort-buttons a:hover {
    background-color: #4caf50;
    color: white;
    border-color: #4caf50;
}

    .sub-filters select {
        background-color: #2c2c2c;
        color: #fff;
        border: none;
        padding: 10px 14px;
        border-radius: 10px;
        font-size: 15px;
        min-width: 180px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg fill="%23fff" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
        background-repeat: no-repeat;
        background-position: left 10px center;
        padding-left: 35px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .sub-filters select:hover {
        background-color: #3a3a3a;
    }


    body {

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

    /* على الشاشات الصغيرة (مثلاً أقل من 600px) */
    @media (max-width: 600px) {
        .series-grid {
            grid-template-columns: repeat(2, 1fr); /* يعرض عنصرين في الصف */
        }
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




    
    

   

</style>
