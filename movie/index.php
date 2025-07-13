<?php
session_start();
include '../load.php';
include '../includes/header.php';

function fetchMovies($type, $page = 1) {
    $baseUrl = "https://app.arabypros.com/api/movie/by/filtres/0/{$type}/{$page}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
    
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
    $formattedResults = ['posters' => is_array($results) && isset($results['posters']) ? $results['posters'] : $results];
    file_put_contents($filename, json_encode($formattedResults));
}

function getNewReleases() {
    $page = rand(1, 10);
    $data = fetchMovies('created', $page);
    if (!isset($data['error'])) {
        saveSearchResults($data);
    }
    return $data;
}

function filterAsianMovies($movieArray) {
    return array_filter($movieArray, function($movie) {
        if (!isset($movie['genres']) || !is_array($movie['genres'])) {
            return true;
        }
        foreach ($movie['genres'] as $genre) {
            if ($genre['title'] === "أفلام آسيوية") {
                return false;
            }
        }
        return true;
    });
}

$movieData = getNewReleases();
$error = '';

if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    $movieData = searchMovies($searchQuery);
    if (isset($movieData['error'])) {
        $error = $movieData['error'];
    } else {
        saveSearchResults($movieData);
    }
} elseif (isset($_GET['action'])) {
    if ($_GET['action'] === 'new-releases') {
        $movieData = getNewReleases();
    } elseif ($_GET['action'] === 'popular') {
        $movieData = fetchMovies('rating', 1);
        if (!isset($movieData['error'])) {
            saveSearchResults($movieData);
        }
    }
    if (isset($movieData['error'])) {
        $error = $movieData['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Movies</title>
    <link rel="icon" type="image/png" href="a.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
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
    </style>
</head>
<body>


    <main class="main-content">
        <section class="hero">
            <div class="container">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Search for movies..." required>
                    <button type="submit" class="search-button">Search</button>
                </form>
            </div>
        </section>

        <section id="movie-section" class="movie-section">
            <div class="container">
                <?php if (!empty($error)): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                <?php elseif (!empty($movieData) && (isset($movieData['posters']) || is_array($movieData))): ?>
                    <h2><?php 
                        if (isset($_GET['search'])) echo 'Search Results';
                        elseif (isset($_GET['action']) && $_GET['action'] === 'popular') echo 'Popular Movies';
                        else echo 'New Releases';
                    ?></h2>
                    <?php 
                    $movieArray = isset($movieData['posters']) ? $movieData['posters'] : $movieData;
                    $filteredMovies = filterAsianMovies($movieArray);
                    if (empty($filteredMovies)): ?>
                        <p class="no-results">No movies available after filtering.</p>
                    <?php else: ?>
                        <div class="movie-grid">
                            <?php foreach ($filteredMovies as $movie): ?>
                                <div class="movie-card">
                                    <div class="content-type">Movie</div>
                                    <img src="<?php echo htmlspecialchars($movie['image']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
                                    <div class="movie-info">
                                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                                        <p class="movie-year"><?php echo htmlspecialchars($movie['year']); ?></p>
                                    </div>
                                    <div class="movie-details">
                                        <p>Year: <?php echo htmlspecialchars($movie['year']); ?></p>
                                        <p>IMDB: <?php echo htmlspecialchars($movie['imdb']); ?></p>
                                        <p>Classification: <?php echo htmlspecialchars($movie['classification']); ?></p>
                                    </div>
                                    <a href="movie/links.php?id=<?php echo htmlspecialchars($movie['id']); ?>" class="btn-watch">View Movie</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="no-results">No movies available yet. Try searching or browsing categories.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>



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

            // Handle long press for Series link
            const seriesLink = document.querySelector('.nav-links a.external-link');
            if (seriesLink) {
                let pressTimer;
                const longPressDuration = 50; // 0.5 seconds

                // Mouse events
                seriesLink.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    pressTimer = setTimeout(() => {
                        window.location.href = seriesLink.getAttribute('href');
                    }, longPressDuration);
                });

                seriesLink.addEventListener('mouseup', function() {
                    clearTimeout(pressTimer);
                });

                seriesLink.addEventListener('mouseleave', function() {
                    clearTimeout(pressTimer);
                });

                // Touch events for mobile
                seriesLink.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    pressTimer = setTimeout(() => {
                        window.location.href = seriesLink.getAttribute('href');
                    }, longPressDuration);
                });

                seriesLink.addEventListener('touchend', function() {
                    clearTimeout(pressTimer);
                });

                seriesLink.addEventListener('touchcancel', function() {
                    clearTimeout(pressTimer);
                });

                // Prevent regular click
                seriesLink.addEventListener('click', function(e) {
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
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
