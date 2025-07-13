<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DFKZ WATCH MOVIE & SERIES & CHANNEL</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f9f9f9;
    }

    header {
      background-color: #111;
      padding: 15px 30px;
      position: relative;
      z-index: 100;
    }

    .header-content {
      max-width: 1200px;
      margin: auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo-text {
      color: red;
      font-size: 26px;
      font-weight: bold;
      text-decoration: none;
    }

    .main-nav {
      align-items: center;
      gap: 20px;
    }

    .main-nav ul.nav-list {
      display: flex;
      list-style: none;
      gap: 20px;
      flex-wrap: wrap;
    }

    .main-nav ul.nav-list li a {
      color: white;
      text-decoration: none;
      font-size: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 6px;
      transition: background 0.3s ease, color 0.3s ease;
    }

    .main-nav ul.nav-list li a:hover {
      background-color: #222;
      color: red;
    }

    .menu-search-form {
      display: none;
    }

    .menu-search-form form {
      display: flex;
      align-items: center;
    }

    .menu-search-form input {
      padding: 8px;
      border: none;
      border-radius: 4px 0 0 4px;
      font-size: 14px;
    }

    .menu-search-form button {
      padding: 8px 10px;
      border: none;
      background-color: red;
      color: white;
      font-size: 14px;
      border-radius: 0 4px 4px 0;
      cursor: pointer;
    }

    /* Hide mobile menu by default */
    .mobile-only {
      display: none;
    }

    .desktop-only {
      display: flex;
    }

    /* Hamburger */
    .hamburger {
      display: none;
      flex-direction: column;
      gap: 5px;
      cursor: pointer;
    }

    .hamburger .bar {
      width: 25px;
      height: 3px;
      background-color: white;
      border-radius: 3px;
    }

    @media (max-width: 768px) {
      .desktop-only {
        display: none;
      }

      .mobile-only {
        display: flex;
      }

      .hamburger {
        display: flex;
      }

      .main-nav.mobile-only {
        display: none;
        flex-direction: column;
        position: fixed;
        top: 0;
        right: -100%;
        width: 80%;
        height: 100%;
        background-color: #111;
        padding: 60px 20px 100px;
        gap: 20px;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.4);
        transition: right 0.3s ease;
        z-index: 999;
      }

      .main-nav.mobile-only.show {
        right: 0;
        display: flex;
      }

      .main-nav.mobile-only ul.nav-list {
        flex-direction: column;
      }

      .menu-search-form {
        display: block;
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #333;
      }

      .menu-search-form form {
        width: 100%;
      }

      .menu-search-form input {
        flex: 1;
      }
    }
  </style>
</head>
<body>
<!-- header -->
<header>
  <div class="header-content">
    <!-- Logo -->
    <a href="index.php" class="logo-text">DFKZ</a>

    <!-- Desktop Nav -->
    <nav class="main-nav desktop-only">
      <ul class="nav-list">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="/movie"><i class="fas fa-film"></i> Movies</a></li>
        <li><a href="index.php"><i class="fas fa-tv"></i> Series</a></li>
        <li><a href=""><i class="fas fa-broadcast-tower"></i> Channels</a></li>
        <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
      </ul>
    </nav>

    <!-- Hamburger (mobile only) -->
    <div class="hamburger mobile-only" id="hamburger-btn">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </div>
  </div>

  <!-- Mobile Nav -->
  <nav class="main-nav mobile-only" id="mobile-menu">
    <ul class="nav-list">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="/movie"><i class="fas fa-film"></i> Movies</a></li>
      <li><a href="index.php"><i class="fas fa-tv"></i> Series</a></li>
      <li><a href=""><i class="fas fa-broadcast-tower"></i> Channels</a></li>
      <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
      <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
    </ul>

    <!-- Search (mobile only) -->
      <div class="menu-search-form">
        <form method="GET" action="index.php">
          <input type="hidden" name="page" value="2">
          <input type="text" name="search" placeholder="Search..." required />
          <button type="submit"><i class="fas fa-search"></i></button>
        </form>
      </div>



  </nav>
</header>

<script>
  const hamburger = document.getElementById("hamburger-btn");
  const mobileMenu = document.getElementById("mobile-menu");

  hamburger.addEventListener("click", () => {
    mobileMenu.classList.toggle("show");
  });
</script>

</body>
</html>
