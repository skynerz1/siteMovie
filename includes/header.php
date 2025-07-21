<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DFKZ WATCH MOVIE & SERIES & CHANNEL</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="styles.css">
 <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #1c2229;
    }

    header {
      background-color: #1c2229;
      padding: 6px 30px; /* تقليل الـ padding من 15px إلى 6px */
      position: relative;
      z-index: 100;
    }

    .logo-text img {
      height: 45px; /* تقليل ارتفاع الصورة من 85px إلى 45px */
      vertical-align: middle;
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
      background-color: #1c2229;
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
        background-color: #1c2229;
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

    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background: #1c2229;
      display: flex;
      justify-content: space-around;
      border-top: 1px solid #333;
      z-index: 999;
      padding: 6px 0;
    }

    .bottom-nav .nav-item {
      flex: 1;
      text-align: center;
      color: white;
      text-decoration: none;
      font-size: 11px;
      transition: color 0.3s ease;
    }

    .bottom-nav .nav-item i {
      display: block;
      font-size: 20px;
      margin-bottom: 3px;
    }

    .bottom-nav .nav-item:hover {
      color: red;
    }

    @media (min-width: 769px) {
      .bottom-nav {
        display: none;
      }
    }

    /* نموذج البحث المنبثق */
    .mobile-search-overlay {
      display: none;
      position: fixed;
      bottom: 60px;
      left: 0;
      width: 100%;
      background: #1c2229;
      padding: 10px;
      border-top: 1px solid #333;
      z-index: 1000;
    }

    .mobile-search-form {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .mobile-search-form input {
      flex: 1;
      padding: 8px 10px;
      border: none;
      border-radius: 6px;
      font-size: 14px;
      background: #2a2f36;
      color: white;
    }

    .mobile-search-form button {
      padding: 8px 12px;
      border: none;
      background-color: red;
      color: white;
      border-radius: 6px;
      cursor: pointer;
    }

    body {
      padding-bottom: 60px;
    }
    .desktop-search-overlay {
      display: none;
      background: #1c2229;
      padding: 10px 20px;
      border-bottom: 1px solid #333;
      z-index: 999;
    }

    #desktop-search-btn.active i {
      color: red !important;
    }


    @media (max-width: 768px) {
      .desktop-search-overlay {
        display: none !important;
      }
    }
    .search-icon-btn {
      text-decoration: none;
      outline: none;
      box-shadow: none;
    }
    .search-icon-btn:focus {
      outline: none;
    }



  </style>
</head>
<body>
<!-- header -->
<header>
  <div class="header-content">
    <!-- Logo -->
    <a href="index.php" class="logo-text">
      <img src="../includes/img/dfkz.png" alt="dfkz Logo">
    </a>

    <!-- زر البحث بجانب اللوقو -->
    <a href="#" id="desktop-search-btn" class="desktop-only search-icon-btn" style="margin-left: 10px;">
      <i class="fas fa-search" style="color:white; font-size: 20px;"></i>
    </a>



    





    <!-- Desktop Nav -->
    <nav class="main-nav desktop-only">
      <ul class="nav-list">
        <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="../movie"><i class="fas fa-film"></i> Movies</a></li>
        <li><a href="../index.php"><i class="fas fa-tv"></i> Series</a></li>
        <li><a href="../cat.php?category=channels"><i class="fas fa-broadcast-tower"></i> Channels</a></li>
        <li><a href="../favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
        <li><a href="../contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
      </ul>
    </nav>



  <!-- Mobile Nav -->
  <!-- Bottom Navigation (Mobile Only) -->
  <nav class="bottom-nav mobile-only">
    <a href="../favorites.php" class="nav-item">
      <i class="fas fa-heart"></i>
      <span>المفضله</span>
    </a>
    <a href="#" class="nav-item" id="toggle-search">
      <i class="fas fa-search"></i>
      <span>بحث</span>
    </a>
    <a href="../index.php" class="nav-item">
      <i class="fas fa-tv"></i>
      <span>مسلسلات</span>
    </a>
    <a href="../movie" class="nav-item">
      <i class="fas fa-film"></i>
      <span>أفلام</span>
    </a>
    <a href="../cat.php?category=channels" class="nav-item">
      <i class="fas fa-broadcast-tower"></i>
      <span>لايف</span>
    </a>
  </nav>

  <!-- نموذج البحث (يظهر عند الضغط على زر البحث) -->
  <div class="mobile-search-overlay" id="search-overlay">
    <form method="GET" action="index.php" class="mobile-search-form">
      <input type="hidden" name="page" value="2">
      <input type="text" name="search" placeholder="ابحث عن فيلم أو مسلسل..." required />
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
  </div>
    <!-- نموذج البحث المنبثق (للديسكتوب) -->
    <div class="desktop-search-overlay" id="desktop-search-overlay">
      <form method="GET" action="index.php" class="mobile-search-form" style="padding: 10px;">
        <input type="hidden" name="page" value="2">
        <input type="text" name="search" placeholder="ابحث عن فيلم أو مسلسل..." required />
        <button type="submit"><i class="fas fa-search"></i></button>
      </form>
    </div>


</header>

    <script>
      // بحث الجوال
      const toggleSearchBtn = document.getElementById("toggle-search");
      const searchOverlay = document.getElementById("search-overlay");

      toggleSearchBtn?.addEventListener("click", (e) => {
        e.preventDefault();
        searchOverlay.style.display = searchOverlay.style.display === "block" ? "none" : "block";
      });

      // بحث الديسكتوب
      const desktopSearchBtn = document.getElementById("desktop-search-btn");
      const desktopSearchOverlay = document.getElementById("desktop-search-overlay");

      desktopSearchBtn?.addEventListener("click", (e) => {
        e.preventDefault();

        const isVisible = desktopSearchOverlay.style.display === "block";

        desktopSearchOverlay.style.display = isVisible ? "none" : "block";

        // تغيير لون الأيقونة
        if (isVisible) {
          desktopSearchBtn.classList.remove("active");
        } else {
          desktopSearchBtn.classList.add("active");
        }
      });
    </script>



</body>
</html>
