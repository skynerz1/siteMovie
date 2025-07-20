<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Footer Example</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #1c2229;;
      color: #fff;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .content {
      flex: 1;
      padding: 20px;
      text-align: center;
    }

    footer {
      background: #1c2229;;
      color: #ddd;
      font-size: 15px;
      padding: 30px 20px;
      border-top: 1px solid #222;
    }

    .footer-links a {
      color: #fff;
      text-decoration: none;
      transition: color 0.3s ease;
      font-weight: 500;
    }

    .footer-links a:hover {
      color: #e50914;
    }

    .footer-container {
      max-width: 1200px;
      margin: auto;
    }

    .footer-text {
      text-align: center;
      margin-bottom: 25px;
      color: #999;
      font-size: 14px;
    }

    .footer-sections {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 20px;
    }

    .footer-section {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    @media (max-width: 768px) {
      .footer-sections {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .footer-section {
        justify-content: center;
      }
    }
  </style>
</head>
<body>

<!-- footer -->

  <footer>
    <div class="footer-container">
      <!-- Top Text -->
      <div class="footer-text">
        &copy; 2025 Watch Series. All rights reserved.<br>
        Created by <span style="color: #e50914; font-weight: bold;">✨DFKZ✨</span>
      </div>

      <!-- Bottom Links -->
      <div class="footer-sections footer-links">
        <!-- Left -->
        <div class="footer-section">
          <a href="/index.php"><i class="fas fa-home"></i> Home</a>
          <a href="/index.php"><i class="fas fa-tv"></i> Series</a>
          <a href="/movie"><i class="fas fa-film"></i> Movies</a>
          <a href="/favorites.php"><i class="fas fa-heart"></i> Favorites</a>
        </div>

        <!-- Right -->
        <div class="footer-section">
          <a href="https://t.me/MTVMSLSL1" target="_blank"><i class="fab fa-telegram"></i> Telegram Bot</a>
          <a href="https://t.me/DFKZ" target="_blank"><i class="fas fa-code"></i> Dev</a>
        </div>
      </div>
    </div>
  </footer>

</body>
</html>
