<?php
session_start();


require_once 'functions.php';
include 'load.php';
include 'includes/header.php';
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'], $_POST['favorite_id'])) {
    $favId = $_POST['favorite_id'];
    if (in_array($favId, $_SESSION['favorites'])) {
        $_SESSION['favorites'] = array_filter($_SESSION['favorites'], fn($id) => $id !== $favId);
    } else {
        $_SESSION['favorites'][] = $favId;
    }
}

    function getSeriesDetails($seriesId) {
        $localFiles = ['search_results_permanent.json', 'search_arab_permanent.json', 'save.json', 'browser.json'];

        // 1) البحث في الملفات المحلية العادية
        foreach ($localFiles as $filename) {
            if (!file_exists($filename)) continue;

            $jsonData = json_decode(file_get_contents($filename), true);
            if (!is_array($jsonData)) continue;

            // إذا فيه posters
            if (isset($jsonData['posters']) && is_array($jsonData['posters'])) {
                foreach ($jsonData['posters'] as $item) {
                    if (isset($item['id'], $item['type']) && $item['id'] == $seriesId && $item['type'] === 'serie') {
                        return $item;
                    }
                }
            }

            // باقي المفاتيح (مثل netflix، shahid، إلخ)
            foreach ($jsonData as $key => $items) {
                if ($key === 'posters') continue; // تفادي التكرار
                if (!is_array($items)) continue;

                foreach ($items as $item) {
                    if (isset($item['id'], $item['type']) && $item['id'] == $seriesId && $item['type'] === 'serie') {
                        return $item;
                    }
                }
            }
        }

        // 2) البحث في ملفات الكاش التي تبدأ بـ series داخل مجلد cache
        $cacheDir = 'cache';  // عدل حسب موقع مجلد cache الحقيقي
        $cacheFiles = glob($cacheDir . '/series*.json');
        foreach ($cacheFiles as $filename) {
            if (!file_exists($filename)) continue;

            $jsonData = json_decode(file_get_contents($filename), true);
            if (!is_array($jsonData)) continue;

            // نفس طريقة البحث: ضمن posters أو المصفوفة مباشرة
            $items = [];
            if (isset($jsonData['posters']) && is_array($jsonData['posters'])) {
                $items = $jsonData['posters'];
            } elseif (is_array($jsonData)) {
                $items = $jsonData;
            }

            foreach ($items as $item) {
                if (isset($item['id'], $item['type']) && $item['id'] == $seriesId && $item['type'] === 'serie') {
                    return $item;
                }
            }
        }

        // 3) محاولة من API خارجي إذا ما تم العثور محلياً
        $sources = ['created' => rand(1, 10), 'rating' => 1];
        foreach ($sources as $type => $page) {
            $url = "https://app.arabypros.com/api/serie/by/filtres/0/{$type}/{$page}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
            $headers = ['User-Agent: okhttp/4.8.0', 'Accept-Encoding: gzip'];
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => 'gzip'
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response, true);
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (isset($item['id'], $item['type']) && $item['id'] == $seriesId && $item['type'] === 'serie') {
                        return $item;
                    }
                }
            }
        }

        return null;
    }



function getSeasonsAndEpisodes($seriesId) {
    $url = "https://app.arabypros.com/api/season/by/serie/{$seriesId}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
    $headers = ['User-Agent: okhttp/4.8.0', 'Accept-Encoding: gzip'];
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
    if ($err) return ['error' => 'Connection error: ' . $err];
    if (empty($response)) return ['error' => 'Empty response from server'];
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) return ['error' => 'Invalid response format: ' . json_last_error_msg()];
    return $data;
}

function safeOutput($data) {
    return is_array($data) ? implode(', ', array_map('htmlspecialchars', $data)) : htmlspecialchars($data ?? '');
}

$seriesDetails = null;
$seasons = [];
$error = '';

if (isset($_GET['id'])) {
    $seriesId = $_GET['id'];
    $seriesDetails = getSeriesDetails($seriesId);
    if (!$seriesDetails) {
        $error = 'لم يتم العثور على تفاصيل المسلسل.';
    } else {
        $seasons = getSeasonsAndEpisodes($seriesId);
        if (isset($seasons['error'])) {
            $error = $seasons['error'];
            $seasons = [];
        }
    }
} else {
    $error = 'رقم تعريف المسلسل مطلوب.';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <meta charset="UTF-8">
    <title><?php echo safeOutput($seriesDetails['title'] ?? 'تفاصيل المسلسل'); ?> - FX2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="a.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
          margin: 0;
          font-family: 'Roboto', sans-serif;
          background-color: #000;
          color: #fff;
          scroll-behavior: smooth;
        }

        .top-bar {
            position: fixed; top: 0; left: 0; right: 0; height: 60px;
            background: linear-gradient(90deg, #e6b600, #b29300);
            display: flex; justify-content: space-between; align-items: center;
            padding: 0 20px; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .top-bar button {
            background: transparent; border: 2px solid #000; border-radius: 6px;
            padding: 8px 16px; cursor: pointer; font-weight: bold; color: #000;
        }
        .top-bar button:hover { background-color: #fff; color: #b29300; }
        .background-blur {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 75vh;
            background-image: url('<?php echo safeOutput($seriesDetails['cover'] ?? ''); ?>');
            background-size: cover;
            background-position: center;
            filter: blur(10px);
            opacity: 0.55;
            z-index: -1;
        }

        .background-black {
            background-color: #000;
            position: relative;
            margin-top: -75vh; /* ← اسحب فوق */
            padding-top: 75vh; /* ← وادفع المحتوى */
            z-index: 0;
        }


        .container { max-width: 1200px; margin: auto; padding: 20px; }
        .series-header { display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start; margin-bottom: 40px; }
        .series-info { flex: 1; }
        .series-info h1 { color: #e6b600; font-size: 2.5rem; margin-bottom: 10px; }
        .series-meta span { display: inline-block; margin-right: 10px; font-size: 1rem; color: #e6b600; }
        .genre-box {
            display: inline-block; background-color: rgba(230,182,0,0.2);
            border: 1px solid rgba(230,182,0,0.4); padding: 5px 10px;
            border-radius: 8px; margin: 5px 5px 0 0;
        }
        .series-poster {
            width: 280px;
            height: 420px;
            object-fit: cover;
            border-radius: 10px;
            border: 4px solid #fff; /* ← تمت إضافتها */
        }

        .seasons-tabs { display: flex; gap: 15px; overflow-x: auto; margin: 30px 0 20px; }
        .season-tab {
            padding: 10px 20px; background: rgba(230,182,0,0.2);
            border-radius: 20px; cursor: pointer; color: #fff; white-space: nowrap;
        }
        .season-tab.active { background-color: #e6b600; color: #000; font-weight: bold; }
        .episodes-grid {
            display: none; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .episodes-grid.active {
            display: flex; flex-direction: column; gap: 15px; background-color: #111;
            padding: 20px; border-radius: 15px;
        }
        .episode-card {
            background-color: #2a2a2a; border-radius: 10px; padding: 20px;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; gap: 15px; width: 100%; box-sizing: border-box;
        }
        .episode-details { flex: 1; color: #fff; display: flex; flex-direction: column; gap: 5px; }
        .episode-details h3 { margin: 0; font-size: 1.2rem; }
        .episode-details p { margin: 0; color: #ccc; font-size: 0.95rem; }
        .episode-link {
            background-color: #d80000; padding: 10px 16px; border-radius: 6px;
            text-decoration: none; color: #fff; font-weight: bold;
            display: flex; align-items: center; gap: 5px; white-space: nowrap;
            transition: background 0.3s ease;
        }
        .episode-link:hover { background-color: #ff1a1a; }
        .episode-link::before { content: '▶'; font-size: 13px; }
        .back-button {
            display: inline-block; margin-top: 40px; padding: 12px 24px;
            background: linear-gradient(135deg, #e6b600, #b29300);
            color: #000; border-radius: 25px; font-weight: bold; text-decoration: none;
        }
        .trailer-button {
            display: inline-block; margin-top: 15px; padding: 12px 20px;
            background: linear-gradient(135deg, #ff5500, #cc4400);
            color: white; font-weight: bold; border-radius: 10px;
            text-decoration: none; font-size: 1rem; transition: background 0.3s ease;
        }
        .trailer-button:hover { background: linear-gradient(135deg, #ff7733, #e65c00); }
        .trailer-overlay {
            position: fixed; top: 0; right: 0; bottom: 0; left: 0;
            background-color: rgba(0, 0, 0, 0.85); display: none;
            align-items: center; justify-content: center; z-index: 9999;
        }
        .trailer-content {
            position: relative; max-width: 90%; width: 720px;
            aspect-ratio: 16 / 9; background: #000;
            border-radius: 10px; overflow: hidden;
        }
        .trailer-close {
            position: absolute; top: -15px; left: -15px; background: #ff3c3c;
            color: white; border: none; border-radius: 50%;
            width: 35px; height: 35px; font-size: 20px; cursor: pointer; z-index: 10000;
        }


        .favorite-btn {
    background: none;
    border: 2px solid #e6b600;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    color: #e6b600;
    font-size: 1.2rem;
    cursor: pointer;
    transition: background 0.3s ease;
}
.favorite-btn:hover {
    background-color: #e6b600;
    color: black;
}
.favorite-btn i.fas {
    color: red;

}
    </style>
</head>
<body>




<div class="background-blur"></div>
<div class="background-black"></div>

    <div class="container" style="padding: 0 20px; max-width: 1200px; margin: 0 auto;">
        <?php if (!empty($error)): ?>
            <h2>خطأ</h2>
            <p><?php echo safeOutput($error); ?></p>
            <a href="index.php" class="back-button">العودة للرئيسية</a>
        <?php elseif ($seriesDetails): ?>
            <div class="series-header" style="display: flex; gap: 30px; flex-wrap: wrap;">
                <!-- قسم الصورة والبادجات -->
                <div style="display: flex; flex-direction: column; align-items: flex-start; margin-top: 60px;">
                <img src="<?php echo safeOutput($seriesDetails['image']); ?>" class="series-poster" alt="Poster" style="max-width: 300px; border-radius: 6px;">


                    <!-- البادجات مباشرة تحت الصورة -->
                    <div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if (!empty($seriesDetails['label'])): ?>
                            <span style="background: #007bff; color: white; padding: 6px 14px; border-radius: 20px; font-size: 14px;">
                                <?= safeOutput($seriesDetails['label']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($seriesDetails['sublabel'])): ?>
                            <span style="background: #28a745; color: white; padding: 6px 14px; border-radius: 20px; font-size: 14px;">
                                <?= safeOutput($seriesDetails['sublabel']) ?>
                            </span>
                        <?php endif; ?>
                        <span style="background: #6c757d; color: white; padding: 6px 14px; border-radius: 20px; font-size: 14px;">مسلسل</span>
                    </div>
                </div>


                <!-- معلومات المسلسل -->
                <div class="series-info" style="flex: 1; min-width: 300px; margin-top: 60px;">
                    <h1 style="display: flex; align-items: center; gap: 10px; font-size: 2rem;">
                        <?php echo safeOutput($seriesDetails['title']); ?>
                    </h1>

                    <div class="series-meta" style="margin: 10px 0; font-size: 15px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <span><?= safeOutput($seriesDetails['year']) ?></span>
                        <span><?= safeOutput($seriesDetails['duration']) ?></span>
                        <span><i class="fas fa-star" style="color: gold;"></i> <?= safeOutput($seriesDetails['rating']) ?></span>
                    </div>
                    <!-- بقية المحتوى -->



                    <!-- عنوان معلومات المسلسل -->
                    <h2 style="font-size: 24px; font-weight: bold; position: relative; display: inline-block; direction: rtl; margin-top: 30px; margin-bottom: 15px;">
                        معلومات المسلسل
                        <span style="position: absolute; bottom: -6px; right: 100px; height: 4px; width: 90px; background: linear-gradient(to left, #00ff88, #007744); border-radius: 2px;"></span>
                    </h2>

                    <!-- النوع والتصنيفات -->
                    <div style="margin-top: 0; font-size: 15px; color: #fff;">
                        <?php if (!empty($seriesDetails['classification'])): ?>
                            <div style="margin-bottom: 10px;"><strong>النوع:</strong> <?= safeOutput($seriesDetails['classification']) ?></div>
                        <?php endif; ?>

                        <!-- السنة كبادج -->
                        <?php if (!empty($seriesDetails['year'])): ?>
                            <div style="margin-bottom: 10px;">
                                <strong>السنة:</strong>
                                <span style="background: rgba(108,117,125,0.5); color: white; padding: 6px 12px; border-radius: 20px; font-size: 14px;">
                                    <?= safeOutput($seriesDetails['year']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- الأنواع -->
                        <?php if (!empty($seriesDetails['genres'])): ?>
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 5px;">
                                <?php foreach ($seriesDetails['genres'] as $genre): ?>
                                    <?php
                                        $title = is_array($genre) && isset($genre['title']) ? $genre['title'] : $genre;
                                    ?>
                                    <span style="background: rgba(108,117,125,0.5); color: white; padding: 6px 12px; border-radius: 20px; font-size: 14px;">
                                        <?= safeOutput($title) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- عنوان القصة -->
                    <h2 style="font-size: 24px; font-weight: bold; position: relative; display: inline-block; margin-top: 40px; margin-bottom: 15px; direction: rtl;">
                        القصة
                        <span style="position: absolute; bottom: -6px; right: 25px; height: 4px; width: 45px; background: linear-gradient(to left, #00ff88, #007744); border-radius: 2px;"></span>
                    </h2>

                    <!-- وصف القصة -->
                    <p style="margin-top: 10px; line-height: 1.7; font-size: 1.1rem; color: #fff; max-height: 250px; overflow-y: auto; padding-right: 8px;">
                        <?= nl2br(safeOutput($seriesDetails['description'])) ?>
                    </p>

                    <!-- زر التريلر والمفضلة -->
                    <div style="display: flex; gap: 10px; margin-top: 25px; align-items: center;">
                        <?php if (!empty($seriesDetails['trailer']['url'])): ?>
                            <button class="trailer-button" onclick="openTrailer('<?php echo safeOutput($seriesDetails['trailer']['url']); ?>')">
                                🎬 مشاهدة التريلر
                            </button>
                        <?php endif; ?>

                        <form method="post">
                            <input type="hidden" name="favorite_id" value="<?php echo safeOutput($seriesDetails['id']); ?>">
                            <button type="submit" name="toggle_favorite"
                                    class="favorite-btn"
                                    data-type="show"
                                    data-id="<?php echo safeOutput($seriesDetails['id']); ?>"
                                    data-info='<?= json_encode([
                                        'title' => $seriesDetails['title'],
                                        'image' => $seriesDetails['image'],
                                        'year'  => $seriesDetails['year'],
                                        'type'  => 'serie'
                                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>'
                                    title="<?php echo in_array($seriesDetails['id'], $_SESSION['favorites']) ? 'إزالة من المفضلة' : 'أضف إلى المفضلة'; ?>">
                                <i class="fa-heart <?php echo in_array($seriesDetails['id'], $_SESSION['favorites']) ? 'fas' : 'far'; ?>"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- المواسم والحلقات -->
            <div class="seasons-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; margin-bottom: 10px;">
                <div style="text-align: right;">
                    <h2 style="
                        margin: 0; 
                        color: #fff; 
                        font-size: 1.6rem; 
                        display: inline-block; 
                        position: relative;
                        padding-bottom: 12px;
                        background:
                          linear-gradient(to left, #00ff88, #00cc44) no-repeat;
                        background-position: left 0px bottom 0px;
                        background-size: 40px 4px;
                        white-space: nowrap;
                    ">
                        المواسم والحلقات
                    </h2>
                </div>
                <div style="text-align: left;">
                    <h3 style="margin: 0; color: #e6b600; font-size: 1.2rem;">
                        جميع حلقات مسلسل <?php echo safeOutput($seriesDetails['title']); ?>
                    </h3>
                </div>
            </div>

            <?php if (!empty($seasons)): ?>
                <div class="seasons-tabs">
                    <?php foreach ($seasons as $index => $season): ?>
                        <div class="season-tab <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                            <?php echo safeOutput($season['title']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($seasons as $index => $season): ?>
                    <div class="episodes-grid <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                        <?php foreach ($season['episodes'] as $episode): ?>
                            <div class="episode-card">
                                <div class="episode-details">
                                    <h3><?php echo safeOutput($episode['title']); ?></h3>
                                    <?php if (!empty($episode['description'])): ?>
                                        <p><?php echo safeOutput($episode['description']); ?></p>
                                    <?php endif; ?>
                                    <a href="links.php?id=<?= safeOutput($episode['id']) ?>&series_id=<?= safeOutput($seriesDetails['id']) ?>&type=serie" class="episode-link">شاهد الآن</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>




<!-- تريلر overlay -->
    <div class="trailer-overlay" id="trailerOverlay" style="
        position: fixed; top: 0; right: 0; bottom: 0; left: 0;
        background-color: rgba(0,0,0,0.85);
        display: none; align-items: center; justify-content: center; z-index: 9999;
        flex-direction: column;
    ">

        <!-- شريط الخروج فوق الفيديو بعرض النص فقط -->
        <div id="trailerExit" style="
            background-color: #777; /* لون رصاصي */
            color: #fff;
            font-size: 1.3rem;
            font-weight: bold;
            text-align: center;
            padding: 8px 20px;
            cursor: pointer;
            user-select: none;
            font-family: 'Roboto', sans-serif;
            border-radius: 12px 12px 0 0;
            position: relative;
            top: 0;
            margin-bottom: 0;
            display: inline-block;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(0,0,0,0.6);
        ">
            خروج من التريلر
        </div>

        <!-- محتوى التريلر -->
        <div class="trailer-content" style="
            position: relative;
            max-width: 90%;
            width: 720px;
            aspect-ratio: 16 / 9;
            background: #000;
            border-radius: 0 0 10px 10px;
            overflow: hidden;
            margin-top: 0;
        ">
            <iframe id="trailerFrame" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>
        </div>

    </div>


<script>
function openTrailer(url) {
    const overlay = document.getElementById('trailerOverlay');
    const frame = document.getElementById('trailerFrame');
    const videoId = url.split('v=')[1]?.split('&')[0];
    const embedUrl = videoId ? `https://www.youtube.com/embed/${videoId}?autoplay=1` : url;
    frame.src = embedUrl;
    overlay.style.display = 'flex';
}
    document.getElementById('trailerExit').addEventListener('click', closeTrailer);

function closeTrailer() {
    document.getElementById('trailerOverlay').style.display = 'none';
    document.getElementById('trailerFrame').src = '';
}
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.season-tab');
    const grids = document.querySelectorAll('.episodes-grid');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const index = tab.dataset.index;
            tabs.forEach(t => t.classList.remove('active'));
            grids.forEach(g => g.classList.remove('active'));
            tab.classList.add('active');
            document.querySelector(`.episodes-grid[data-index="${index}"]`).classList.add('active');
        });
    });
});

function toggleNav() {
  const navLinks = document.getElementById('navLinks');
  navLinks.classList.toggle('active');
}


</script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
