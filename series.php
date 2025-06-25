<?php
session_start();

function getSeriesDetails($seriesId) {
    $filename = 'search_results.json';
    if (file_exists($filename)) {
        $searchResults = json_decode(file_get_contents($filename), true);
        if (isset($searchResults['posters']) && is_array($searchResults['posters'])) {
            foreach ($searchResults['posters'] as $item) {
                if ($item['id'] == $seriesId && $item['type'] === 'serie') {
                    return $item;
                }
            }
        }
    }

    $sources = [
        'created' => rand(1, 10),
        'rating' => 1
    ];

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
                if ($item['id'] == $seriesId && $item['type'] === 'serie') {
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

    if ($err) {
        error_log("cURL Error: " . $err);
        return ['error' => 'Connection error: ' . $err];
    }

    if (empty($response)) {
        error_log("Empty response from API");
        return ['error' => 'Empty response from server'];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        return ['error' => 'Invalid response format: ' . json_last_error_msg()];
    }

    return $data;
}

function safeOutput($data) {
    if (is_array($data)) {
        return implode(', ', array_map('htmlspecialchars', $data));
    }
    return htmlspecialchars($data ?? '');
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
            padding-top: 60px;
            scroll-behavior: smooth;
        }

        .top-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: linear-gradient(90deg, #e6b600, #b29300);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .top-bar button {
            background: transparent;
            border: 2px solid #000;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            font-weight: bold;
            color: #000;
        }

        .top-bar button:hover {
            background-color: #fff;
            color: #b29300;
        }

        .background-blur {
            position: fixed;
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
            position: fixed;
            top: 75vh; left: 0; right: 0; bottom: 0;
            background-color: #000;
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .series-header {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .series-info {
            flex: 1;
        }

        .series-info h1 {
            color: #e6b600;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .series-meta span {
            display: inline-block;
            margin-right: 10px;
            font-size: 1rem;
            color: #e6b600;
        }

        .genre-box {
            display: inline-block;
            background-color: rgba(230,182,0,0.2);
            border: 1px solid rgba(230,182,0,0.4);
            padding: 5px 10px;
            border-radius: 8px;
            margin: 5px 5px 0 0;
        }

        .series-poster {
            width: 280px;
            height: 420px;
            object-fit: cover;
            border-radius: 10px;
        }

        .seasons-tabs {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            margin: 30px 0 20px;
        }

        .season-tab {
            padding: 10px 20px;
            background: rgba(230,182,0,0.2);
            border-radius: 20px;
            cursor: pointer;
            color: #fff;
            white-space: nowrap;
        }

        .season-tab.active {
            background-color: #e6b600;
            color: #000;
            font-weight: bold;
        }

        .episodes-grid {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

.episodes-grid.active {
    display: flex;
    flex-direction: column;
    gap: 15px;
    background-color: #111;
    padding: 20px;
    border-radius: 15px;
}


.episode-card {
    background-color: #2a2a2a;
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 15px;
    width: 100%;
    box-sizing: border-box;
}

.episode-number {
    width: 40px;
    height: 40px;
    background-color: #d80000;
    color: white;
    font-weight: bold;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.episode-details {
    flex: 1;
    color: #fff;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.episode-details h3 {
    margin: 0;
    font-size: 1.2rem;
}

.episode-details p {
    margin: 0;
    color: #ccc;
    font-size: 0.95rem;
}

.episode-link {
    background-color: #d80000;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    color: #fff;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    transition: background 0.3s ease;
}

.episode-link:hover {
    background-color: #ff1a1a;
}

.episode-link::before {
    content: '▶';
    font-size: 13px;
}



        .episode-card:hover {
            background-color: #2a2a2a;
        }

        .episode-link {
            display: block;
            margin-top: 15px;
            padding: 12px;
            background: linear-gradient(135deg, #FF3C3C, #B22222);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            width: 100%;
            box-sizing: border-box;
        }

        .episode-link:hover {
            background: linear-gradient(135deg, #ff5c5c, #d03030);
        }

        .back-button {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #e6b600, #b29300);
            color: #000;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .series-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .series-info {
                width: 100%;
            }

            .series-poster {
                width: 100%;
                max-width: 300px;
                height: auto;
            }
        }
        .trailer-button {
    display: inline-block;
    margin-top: 15px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #ff5500, #cc4400);
    color: white;
    font-weight: bold;
    border-radius: 10px;
    text-decoration: none;
    font-size: 1rem;
    transition: background 0.3s ease;
}

.trailer-button:hover {
    background: linear-gradient(135deg, #ff7733, #e65c00);
}
.trailer-overlay {
    position: fixed;
    top: 0; right: 0; bottom: 0; left: 0;
    background-color: rgba(0, 0, 0, 0.85);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.trailer-content {
    position: relative;
    max-width: 90%;
    width: 720px;
    aspect-ratio: 16 / 9;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 30px rgba(0,0,0,0.6);
}

.trailer-close {
    position: absolute;
    top: -15px;
    left: -15px;
    background: #ff3c3c;
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    font-size: 20px;
    cursor: pointer;
    z-index: 10000;
}


    </style>
</head>
<body>

<div class="top-bar">
    <button onclick="location.href='index.php';">FX2</button>
    <div>
    <button onclick="location.href='index.php';">FX2</button>
        <button onclick="alert('قريبا')">أفلام</button>
    </div>
</div>

<div class="background-blur"></div>
<div class="background-black"></div>

<div class="container">
    <?php if (!empty($error)): ?>
        <h2>خطأ</h2>
        <p><?php echo safeOutput($error); ?></p>
        <a href="index.php" class="back-button">العودة للرئيسية</a>
    <?php elseif ($seriesDetails): ?>
        <div class="series-header">
            <img src="<?php echo safeOutput($seriesDetails['image']); ?>" class="series-poster" alt="Poster">
            <div class="series-info">
                <h1><?php echo safeOutput($seriesDetails['title']); ?></h1>
                <div class="series-meta">
                    <span>السنة: <?php echo safeOutput($seriesDetails['year']); ?></span>
                    <span>المدة: <?php echo safeOutput($seriesDetails['duration']); ?></span>
                    <span>IMDb: <?php echo safeOutput($seriesDetails['imdb']); ?> ★</span>
                </div>
                <div>
                    <?php
                    $genres = $seriesDetails['genres'] ?? [];
                    foreach ((array)$genres as $genre) {
                        echo '<span class="genre-box">' . safeOutput($genre) . '</span>';
                    }
                    ?>
                </div>
                <p><?php echo safeOutput($seriesDetails['description']); ?></p>
                <?php if (!empty($seriesDetails['trailer']['url'])): ?>
    <button class="trailer-button" onclick="openTrailer('<?php echo safeOutput($seriesDetails['trailer']['url']); ?>')">🎬 مشاهدة التريلر</button>
<?php endif; ?>

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
                            <h3><?php echo safeOutput($episode['title']); ?></h3>
                            <?php if (!empty($episode['description'])): ?>
                                <p><?php echo safeOutput($episode['description']); ?></p>
                            <?php endif; ?>
                         <a href="links.php?id=<?= safeOutput($episode['id']) ?>&series_id=<?= safeOutput($seriesDetails['id']) ?>&type=serie" class="episode-link">شاهد الآن</a>


                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php else: ?>
        <p>تعذر العثور على المسلسل.</p>
        <a href="index.php" class="back-button">العودة للرئيسية</a>
    <?php endif; ?>
</div>

<footer>
    © 2025 Watch Series. Created by ✨DFKZ✨
</footer>

<!-- تريلر overlay -->
<div class="trailer-overlay" id="trailerOverlay">
    <div class="trailer-content">
        <button class="trailer-close" onclick="closeTrailer()">×</button>
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

function closeTrailer() {
    const overlay = document.getElementById('trailerOverlay');
    const frame = document.getElementById('trailerFrame');
    overlay.style.display = 'none';
    frame.src = '';
}
</script>


<script>
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
</script>

</body>
</html>
