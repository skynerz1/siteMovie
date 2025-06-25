<?php
function getMovieDetails($movieId) {
    $filename = 'search_results.json';
    if (file_exists($filename)) {
        $searchResults = json_decode(file_get_contents($filename), true);
        if (isset($searchResults['posters']) && is_array($searchResults['posters'])) {
            foreach ($searchResults['posters'] as $movie) {
                if ($movie['id'] == $movieId) {
                    return $movie;
                }
            }
        }
    }
    return null;
}

function getMovieLinks($movieId) {
    $url = "https://app.arabypros.com/api/movie/source/by/{$movieId}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
    
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
        error_log("cURL Error: " . $err);
        return ['error' => 'Connection error'];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Decode Error: " . json_last_error_msg());
        return ['error' => 'Invalid response format'];
    }

    return $data;
}

$movieLinks = [];
$error = '';
$movieDetails = null;

if (isset($_GET['id'])) {
    $movieId = $_GET['id'];
    
    $movieDetails = getMovieDetails($movieId);
    if (!$movieDetails) {
        $error = 'Movie details not found';
    } else {
        $movieLinks = getMovieLinks($movieId);
        if (isset($movieLinks['error'])) {
            $error = $movieLinks['error'];
            $movieLinks = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($movieDetails['title']) ? htmlspecialchars($movieDetails['title']) : 'Movie Details'; ?> - Watch Movies</title>
    <link rel="icon" type="image/png" href="a.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .background-blur {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('<?php echo isset($movieDetails['cover']) ? htmlspecialchars($movieDetails['cover']) : ''; ?>');
            background-size: cover;
            background-position: center;
            filter: blur(10px);
            opacity: 0.5;
            z-index: -1;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        .movie-content {
            background-color: rgba(20, 20, 20, 0.8);
            border-radius: 8px;
            padding: 20px;
            backdrop-filter: blur(5px);
        }
        h1, h2 {
            color: #e50914;
        }
        .movie-details {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .movie-poster {
            width: 300px;
            height: 450px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .movie-info {
            flex: 1;
        }
        .movie-title {
            font-size: 2rem;
            margin: 0 0 15px 0;
        }
        .server-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        .server-button {
            background-color: #333;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .server-button:hover, .server-button.active {
            background-color: #e50914;
        }
        .player-container {
            margin-top: 20px;
            aspect-ratio: 16/9;
            background-color: #000;
            border-radius: 8px;
            overflow: hidden;
        }
        .player-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .refresh-button, .back-button {
            background-color: #e50914;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 20px;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .refresh-button:hover, .back-button:hover {
            background-color: #ff0f1f;
        }
        @media (max-width: 768px) {
            .movie-details {
                flex-direction: column;
            }
            .movie-poster {
                width: 100%;
                max-width: 300px;
                height: auto;
                margin: 0 auto 20px;
            }
            .movie-info {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="background-blur"></div>
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="movie-content">
                <h1>Error</h1>
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="index.php" class="back-button">Back to Home</a>
            </div>
        <?php elseif ($movieDetails): ?>
            <div class="movie-content">
                <h1><?php echo htmlspecialchars($movieDetails['title']); ?></h1>
                <div class="movie-details">
                    <img src="<?php echo htmlspecialchars($movieDetails['image']); ?>" alt="<?php echo htmlspecialchars($movieDetails['title']); ?>" class="movie-poster">
                    <div class="movie-info">
                        <p><strong>Year:</strong> <?php echo htmlspecialchars($movieDetails['year']); ?></p>
                        <p><strong>IMDB:</strong> <?php echo htmlspecialchars($movieDetails['imdb']); ?></p>
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($movieDetails['duration']); ?></p>
                        <p><strong>Classification:</strong> <?php echo htmlspecialchars($movieDetails['classification']); ?></p>
                        <p><?php echo htmlspecialchars($movieDetails['description']); ?></p>
                        
                    </div>
                </div>

                <div id="server-container">
                    <h2>Available Servers</h2>
                    <div class="server-grid">
                        <?php foreach ($movieLinks as $index => $link): ?>
                            <?php if (isset($link['url']) && !empty($link['url'])): ?>
                                <button class="server-button" onclick="loadServer('<?php echo htmlspecialchars($link['url']); ?>', this)">
                                    Server <?php echo $index + 1; ?> (<?php echo htmlspecialchars($link['type']); ?>)
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="player-container" class="player-container" style="display: none;">
                    <iframe id="player-iframe" class="player-iframe" allowfullscreen></iframe>
                </div>
                <button id="refresh-button" class="refresh-button" onclick="refreshServers()">Refresh Servers</button>
                <a href="index.php" class="back-button">Back to Home</a>
            </div>
        <?php else: ?>
            <div class="movie-content">
                <h1>Movie Not Found</h1>
                <p>Sorry, we couldn't find the movie you're looking for.</p>
                <a href="index.php" class="back-button">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function loadServer(url, button) {
            const serverContainer = document.getElementById('server-container');
            const playerContainer = document.getElementById('player-container');
            const playerIframe = document.getElementById('player-iframe');
            const refreshButton = document.getElementById('refresh-button');
            
            playerIframe.src = url;
            playerContainer.style.display = 'block';
            serverContainer.style.display = 'none';
            refreshButton.style.display = 'block';
        }

        function refreshServers() {
            const serverContainer = document.getElementById('server-container');
            const playerContainer = document.getElementById('player-container');
            const playerIframe = document.getElementById('player-iframe');
            const refreshButton = document.getElementById('refresh-button');
            
            playerIframe.src = '';
            playerContainer.style.display = 'none';
            serverContainer.style.display = 'block';
            refreshButton.style.display = 'none';
        }

        // Hide refresh button initially
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('refresh-button').style.display = 'none';
        });
    </script>
</body>
</html>

