<?php
session_start();


function saveToSearchResults($newItems) {
    $filename = 'search_results.json';

    // قراءة الملف الحالي إن وُجد
    $existing = [];
    if (file_exists($filename)) {
        $json = file_get_contents($filename);
        $data = json_decode($json, true);
        if (isset($data['posters']) && is_array($data['posters'])) {
            $existing = $data['posters'];
        }
    }

    // دمج العناصر الجديدة بدون تكرار (حسب ID)
    $ids = array_column($existing, 'id');
    foreach ($newItems as $item) {
        if (!in_array($item['id'], $ids)) {
            $existing[] = $item;
            $ids[] = $item['id'];
        }
    }

    // حفظ الملف بشكل دائم (لا يُعاد تعيينه)
    file_put_contents($filename, json_encode(['posters' => $existing], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}




function fetchSeries($type, $page = 1) {
    $baseUrl = "https://app.arabypros.com/api/serie/by/filtres/0/{$type}/{$page}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
    
    $headers = [
        'User-Agent: okhttp/4.8.0',
        'Accept-Encoding: gzip'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => 'gzip'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("cURL Error ($type, page $page): " . $err);
        return ['error' => 'Connection error: ' . $err];
    }

    if (empty($response)) {
        error_log("Empty response from API ($type, page $page)");
        return ['error' => 'Empty response from server'];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error ($type, page $page): " . json_last_error_msg());
        return ['error' => 'Invalid response format: ' . json_last_error_msg()];
    }

    return $data;
}

function searchMovies($query) {
    $encodedQuery = rawurlencode($query);
    $url = "https://app.arabypros.com/api/search/{$encodedQuery}/0/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
    
    $headers = [
        'User-Agent: okhttp/4.8.0',
        'Accept-Encoding: gzip'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => 'gzip'
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log('Curl error: ' . $err);
        return ['error' => 'Connection error: ' . $err];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON decode error: ' . json_last_error_msg());
        return ['error' => 'Invalid response format: ' . json_last_error_msg()];
    }

    return $data;
}

function saveSearchResults($results) {
    $filename = 'search_results.json';
    $permanentFile = 'search_results_permanent.json';

    // تأكد أن النتائج داخل 'posters'
    $formattedResults = ['posters' => is_array($results) && isset($results['posters']) ? $results['posters'] : $results];

    // 1. حفظ الملف العادي (يُكتب من جديد كل مرة)
    file_put_contents($filename, json_encode($formattedResults, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // 2. حفظ في الملف الدائم (لا يحذف، فقط يضيف الجديد)
    $newItems = $formattedResults['posters'];
    $existing = [];

    if (file_exists($permanentFile)) {
        $json = file_get_contents($permanentFile);
        $data = json_decode($json, true);
        if (isset($data['posters']) && is_array($data['posters'])) {
            $existing = $data['posters'];
        }
    }

    // تجنب التكرار حسب id
    $existingIds = array_column($existing, 'id');
    foreach ($newItems as $item) {
        if (!in_array($item['id'], $existingIds)) {
            $existing[] = $item;
            $existingIds[] = $item['id'];
        }
    }

    // حفظ الملف الدائم
    file_put_contents($permanentFile, json_encode(['posters' => $existing], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}


function getNewReleases() {
    $page = rand(1,10);
    $data = fetchSeries('created', $page);
    if (!isset($data['error'])) {
        saveSearchResults($data);
    }
    return $data;
}
function filterGulfSeries($seriesArray) {
    return array_filter($seriesArray, function($series) {
        if (!isset($series['genres']) || !is_array($series['genres'])) {
            return true;
        }
        foreach ($series['genres'] as $genre) {
            if (
                $genre['title'] === "مسلسلات آسيوية" ||
                $genre['title'] === "مسلسلات أجنبية" ||
                $genre['title'] === "مسلسلات تركية" 
                
            ) {
                return false;
            }
        }
        return true;
    });
}



function filterAsianSeries($seriesArray) {
    return array_filter($seriesArray, function($series) {
        if (!isset($series['genres']) || !is_array($series['genres'])) {
            return true;
        }
        foreach ($series['genres'] as $genre) {
            if (
                $genre['title'] === "مسلسلات " ||
                $genre['title'] === "مسلسلات "
            ) {
                return false;
            }
        }
        return true;
    });
}


$seriesData = getNewReleases();
$error = '';

if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $seriesData = searchMovies($searchQuery);
    if (isset($seriesData['error'])) {
        $error = $seriesData['error'];
    } else {
        saveSearchResults($seriesData);
    }
} elseif (isset($_GET['action'])) {
    if ($_GET['action'] === 'new-releases') {
        $seriesData = getNewReleases();
    } elseif ($_GET['action'] === 'popular') {
        $seriesData = fetchSeries('rating', 1);
        if (!isset($seriesData['error'])) {
            saveSearchResults($seriesData);
        }
    }
    if (isset($seriesData['error'])) {
        $error = $seriesData['error'];
    }
}

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Series</title>
    <link rel="icon" type="image/png" href="a.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: rgba(20, 20, 20, 0.9);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        .nav-links li {
            position: relative;
            padding: 5px 0;
        }
        .nav-links li a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .nav-links li::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: #e50914;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .nav-links li:hover::after {
            width: 70%;
        }
        .nav-links li:hover a {
            color: #fff;
            transform: scale(1.05);
        }
        .hero {
            background-image: url('https://assets.nflxext.com/ffe/siteui/vlv3/f841d4c7-10e1-40af-bcae-07a3f8dc141a/f6d7434e-d6de-4185-a6d4-c77a2d08737b/US-en-20220502-popsignuptwoweeks-perspective_alpha_website_medium.jpg');
            background-size: cover;
            background-position: center;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero .search-form {
            width: 100%;
            max-width: 600px;
            display: flex;
        }
        .hero .search-input {
            width: 70%;
            padding: 15px;
            font-size: 1.1rem;
            border: none;
            border-radius: 30px 0 0 30px;
            outline: none;
        }
        .hero .search-button {
            width: 30%;
            padding: 15px;
            font-size: 1.1rem;
            border: none;
            border-radius: 0 30px 30px 0;
            background-color: #e50914;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .hero .search-button:hover {
            background-color: #ff0f1f;
        }
        .movie-section {
            padding: 40px 0;
        }
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .movie-card {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }
        .movie-card:hover {
            transform: scale(1.05);
        }
        .movie-poster {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .movie-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            transition: transform 0.3s ease;
        }
        .movie-card:hover .movie-info {
            transform: translateY(100%);
        }
        .movie-details {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .movie-card:hover .movie-details {
            opacity: 1;
        }
        .btn-watch {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #e50914;
            color: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .movie-card:hover .btn-watch {
            opacity: 1;
        }
        .content-type {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e50914;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .error-message, .no-results {
            text-align: center;
            font-size: 1.2rem;
            margin: 20px 0;
        }
        .footer {
            background-color: rgba(20, 20, 20, 0.8);
            padding: 20px;
            text-align: center;
            margin-top: 40px;
        }
        .footer p {
            margin: 5px 0;
        }
        .slider-container {
    display: flex;
    overflow-x: auto;
    gap: 16px;
    scroll-behavior: smooth;
    padding-bottom: 20px;
}
.slider-container::-webkit-scrollbar {
    height: 8px;
}
.slider-container::-webkit-scrollbar-thumb {
    background: #444;
    border-radius: 4px;
}
.movie-card {
    flex: 0 0 auto;
    width: 200px;
    background: #111;
    border-radius: 12px;
    overflow: hidden;
    color: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
    transition: transform 0.3s ease;
}
.movie-card:hover {
    transform: scale(1.05);
}

    .navbar {
    background-color: rgba(20, 20, 20, 0.95);
    padding: 15px 20px;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.navbar .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}
.logo {
    font-size: 1.8rem;
    font-weight: bold;
    color: #fff;
    text-decoration: none;
}
nav {
    display: flex;
    align-items: center;
}
.nav-toggle {
    display: none;
    font-size: 2rem;
    color: #fff;
    cursor: pointer;
}
.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
    margin: 0;
    padding: 0;
}
.nav-links li a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 12px;
    display: inline-block;
    transition: all 0.3s ease;
}
.nav-links li:hover a {
    color: #e50914;
    transform: scale(1.05);
}

/* responsive styles */
@media (max-width: 768px) {
    .nav-toggle {
        display: block;
    }
    nav {
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
    }
    .nav-links {
        flex-direction: column;
        width: 100%;
        display: none;
        margin-top: 10px;
    }
    .nav-links.active {
        display: flex;
    }
    .nav-links li {
        width: 100%;
    }
    .nav-links li a {
        width: 100%;
        padding: 10px 0;
    }
}


    </style>
</head>
<body>
<header class="navbar">
    <div class="container">
        <a href="/" class="logo">Watch Series</a>
        <div class="nav-toggle" onclick="toggleNav()">☰</div>
        <nav>
            <ul class="nav-links" id="navLinks">
                <li><a href="?action=home">Home</a></li>
                <li><a href="/favorites.php" class="external-link">favorites</a></li>
                <li><a href="?action=new-releases">New Releases</a></li>
                <li><a href="/movie" class="external-link">Movies</a></li>
            </ul>
        </nav>
    </div>
</header>


    <main class="main-content">
        <section class="hero">
            <div class="container">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Search for TV series..." required>
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
        </section>

  <section id="movie-section" class="movie-section">
    <div class="container">
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>

        <?php elseif (isset($_GET['search']) && !empty($seriesData)): ?>
            <h2 class="section-title">نتائج البحث</h2>
            <?php 
                $seriesArray = isset($seriesData['posters']) ? $seriesData['posters'] : $seriesData;
                $filteredSeries = filterAsianSeries($seriesArray);
                if (empty($filteredSeries)): ?>
                    <p class="no-results">لم يتم العثور على نتائج.</p>
                <?php else: ?>
                    <div class="movie-grid">
                        <?php foreach ($filteredSeries as $series): ?>
                            <div class="movie-card">
                                <div class="content-type">Series</div>
                                <img src="<?php echo htmlspecialchars($series['image']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>" class="movie-poster" loading="lazy">
                                <div class="movie-info">
                                    <h3 class="movie-title"><?php echo htmlspecialchars($series['title']); ?></h3>
                                    <p class="movie-year"><?php echo htmlspecialchars($series['year']); ?></p>
                                </div>
                                <div class="movie-details">
                                    <p>Year: <?php echo htmlspecialchars($series['year']); ?></p>
                                    <p>IMDB: <?php echo htmlspecialchars($series['imdb']); ?></p>
                                    <p>Classification: <?php echo htmlspecialchars($series['classification']); ?></p>
                                </div>
                                <a href="series.php?id=<?php echo htmlspecialchars($series['id']); ?>" class="btn-watch">View Series</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

        <?php else: ?>
   
            <!-- سلايدر جديد -->
            <h2 class="section-title">New-Series</h2>
            <?php
                $newReleases = getNewReleases();
                $seriesArray = isset($newReleases['posters']) ? $newReleases['posters'] : $newReleases;
                $filteredSeries = filterAsianSeries($seriesArray);
                if (!empty($filteredSeries)):
            ?>
                <div class="slider-container">
                    <?php foreach ($filteredSeries as $series): ?>
                        <div class="movie-card">
                            <div class="content-type">Series</div>
                            <img src="<?php echo htmlspecialchars($series['image']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>" class="movie-poster" loading="lazy">
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo htmlspecialchars($series['title']); ?></h3>
                                <p class="movie-year"><?php echo htmlspecialchars($series['year']); ?></p>
                            </div>
                            <div class="movie-details">
                                <p>Year: <?php echo htmlspecialchars($series['year']); ?></p>
                                <p>IMDB: <?php echo htmlspecialchars($series['imdb']); ?></p>
                                <p>Classification: <?php echo htmlspecialchars($series['classification']); ?></p>
                            </div>
                            <a href="series.php?id=<?php echo htmlspecialchars($series['id']); ?>" class="btn-watch">View Series</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
    <!-- ✅ سلايدر جديد لمسلسلات "خليجية" بناءً على نفس الفلترة -->
    <h2 class="section-title">arab Series</h2>
<?php
$limit = 15; // كم مسلسل تبي تعرض
$collected = [];

for ($i = 0; $i < 3; $i++) { // نحاول 3 صفحات فقط كحد أقصى
    $page = rand(1, 15);
    $gulfData = fetchSeries('created', $page);
    $gulfArray = isset($gulfData['posters']) ? $gulfData['posters'] : $gulfData;
    $filteredGulf = filterGulfSeries($gulfArray);

    // ندمج اللي لقيناه مع الموجود
    $collected = array_merge($collected, $filteredGulf);

    // إذا وصلنا للعدد المطلوب نوقف
    if (count($collected) >= $limit) {
        break;
    }
}

// نأخذ العدد المحدد فقط
$limitedGulf = array_slice($collected, 0, $limit);

// 🟢 نحفظهم في ملف
file_put_contents('search_arab.json', json_encode(['posters' => $limitedGulf], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

// الملف الدائم
$permanentFile = 'search_arab_permanent.json';

// قراءة المحتوى الحالي من الملف الدائم
$existing = [];
if (file_exists($permanentFile)) {
    $json = file_get_contents($permanentFile);
    $data = json_decode($json, true);
    if (isset($data['posters']) && is_array($data['posters'])) {
        $existing = $data['posters'];
    }
}

// دمج العناصر الجديدة بدون تكرار (حسب id مثلاً)
foreach ($limitedGulf as $newItem) {
    $found = false;
    foreach ($existing as $item) {
        if (isset($item['id']) && $item['id'] == $newItem['id']) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $existing[] = $newItem;
    }
}

// حفظ في الملف الدائم بعد الدمج
file_put_contents($permanentFile, json_encode(['posters' => $existing], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));


if (!empty($limitedGulf)):
?>
<div class="slider-container">
    <?php foreach ($limitedGulf as $series): ?>
        <div class="movie-card">
            <div class="content-type">Series</div>
            <img src="<?php echo htmlspecialchars($series['image']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>" class="movie-poster" loading="lazy">
            <div class="movie-info">
                <h3 class="movie-title"><?php echo htmlspecialchars($series['title']); ?></h3>
                <p class="movie-year"><?php echo htmlspecialchars($series['year']); ?></p>
            </div>
            <div class="movie-details">
                <p>Year: <?php echo htmlspecialchars($series['year']); ?></p>
                <p>IMDB: <?php echo htmlspecialchars($series['imdb']); ?></p>
                <p>Classification: <?php echo htmlspecialchars($series['classification']); ?></p>
            </div>
            <a href="series.php?id=<?php echo htmlspecialchars($series['id']); ?>" class="btn-watch">View Series</a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>



            <!-- سلايدر الأكثر شهرة -->
            <h2 class="section-title">Popular Series</h2>
            <?php
                $popularData = fetchSeries('rating', 1);
                $popularArray = isset($popularData['posters']) ? $popularData['posters'] : $popularData;
                $filteredPopular = filterAsianSeries($popularArray);
                if (!empty($filteredPopular)):
            ?>
                <div class="slider-container">
                    <?php foreach ($filteredPopular as $series): ?>
                        <div class="movie-card">
                            <div class="content-type">Series</div>
                            <img src="<?php echo htmlspecialchars($series['image']); ?>" alt="<?php echo htmlspecialchars($series['title']); ?>" class="movie-poster" loading="lazy">
                            <div class="movie-info">
                                <h3 class="movie-title"><?php echo htmlspecialchars($series['title']); ?></h3>
                                <p class="movie-year"><?php echo htmlspecialchars($series['year']); ?></p>
                            </div>
                            <div class="movie-details">
                                <p>Year: <?php echo htmlspecialchars($series['year']); ?></p>
                                <p>IMDB: <?php echo htmlspecialchars($series['imdb']); ?></p>
                                <p>Classification: <?php echo htmlspecialchars($series['classification']); ?></p>
                            </div>
                            <a href="series.php?id=<?php echo htmlspecialchars($series['id']); ?>" class="btn-watch">View Series</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>


    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2025 Watch Series. All rights reserved.</p>
            <p>Created by ✨DFKZ✨</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('.nav-links a:not(.external-link)');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    fetch(url)
                        .then(response => response.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newMovieSection = doc.getElementById('movie-section');
                            if (newMovieSection) {
                                document.getElementById('movie-section').innerHTML = newMovieSection.innerHTML;
                                document.getElementById('movie-section').scrollIntoView({ behavior: 'smooth' });
                            }
                        });
                });
            });

            // Handle long press for Movies link
            const movieLink = document.querySelector('.nav-links a.external-link');
            if (movieLink) {
                let pressTimer;
                const longPressDuration = 50; // 0.5 seconds

                // Mouse events
                movieLink.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    pressTimer = setTimeout(() => {
                        window.location.href = movieLink.getAttribute('href');
                    }, longPressDuration);
                });

                movieLink.addEventListener('mouseup', function() {
                    clearTimeout(pressTimer);
                });

                movieLink.addEventListener('mouseleave', function() {
                    clearTimeout(pressTimer);
                });

                // Touch events for mobile
                movieLink.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    pressTimer = setTimeout(() => {
                        window.location.href = movieLink.getAttribute('href');
                    }, longPressDuration);
                });

                movieLink.addEventListener('touchend', function() {
                    clearTimeout(pressTimer);
                });

                movieLink.addEventListener('touchcancel', function() {
                    clearTimeout(pressTimer);
                });

                // Prevent regular click
                movieLink.addEventListener('click', function(e) {
                    e.preventDefault();
                });
            }

            const form = document.querySelector('.search-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                fetch('?' + new URLSearchParams(formData).toString())
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newMovieSection = doc.getElementById('movie-section');
                        if (newMovieSection) {
                            document.getElementById('movie-section').innerHTML = newMovieSection.innerHTML;
                            document.getElementById('movie-section').scrollIntoView({ behavior: 'smooth' });
                        }
                    });
            });
        });
   function toggleNav() {
        const navLinks = document.getElementById('navLinks');
        navLinks.classList.toggle('active');
    }
        
    </script>
</body>
</html>
