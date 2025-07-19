<?php
session_start();
require_once 'functions.php';

include 'includes/header.php';

// تحميل المفضلة من الكوكيز إذا لم تكن موجودة في الجلسة
if (!isset($_SESSION['favorites'])) {
    if (isset($_COOKIE['favorites'])) {
        $_SESSION['favorites'] = json_decode($_COOKIE['favorites'], true) ?? [];
    } else {
        $_SESSION['favorites'] = [];
    }
}

// تحديث المفضلة (إضافة/إزالة)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'], $_POST['favorite_id'])) {
    $favId = $_POST['favorite_id'];

    if (in_array($favId, $_SESSION['favorites'])) {
        // إزالة من المفضلة
        $_SESSION['favorites'] = array_values(array_filter($_SESSION['favorites'], fn($id) => $id !== $favId));
    } else {
        // إضافة إلى المفضلة
        $_SESSION['favorites'][] = $favId;
    }

    // حفظ المفضلة في الكوكيز (لمدة 30 يوم)
    setcookie('favorites', json_encode($_SESSION['favorites']), time() + (86400 * 30), "/");

    header("Location: favorites.php");
    exit;
}

// تحميل تفاصيل المفضلات
$favorites = [];
foreach ($_SESSION['favorites'] as $favId) {
    $details = getSeriesDetails($favId);
    if ($details) {
        $favorites[] = $details;
    }
}
?>



<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>المفضلة - FX2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="a.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Cairo', sans-serif;
            background-color: #111;
            color: #ddd;
            padding-top: 80px;
        }

        .navbar {
            background-color: #1c1c1c;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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
            transition: all 0.3s ease;
        }

        .nav-links li:hover a {
            color: #e50914;
            transform: scale(1.05);
        }

        /* @media (max-width: 768px) {
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
        } */

        h1 {
            text-align: center;
            font-size: 2rem;
            color: #f5c518;
            margin: 30px 0;
        }

        .series-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
            padding: 0 20px 40px;
        }

        .series-card {
            background-color: #222;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .series-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }

        .series-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .series-card .info {
            padding: 12px;
            text-align: center;
        }

        .series-card .info h3 {
            margin: 0;
            font-size: 1rem;
            color: #eee;
        }

        .remove-fav-form {
            position: absolute;
            top: 10px;
            left: 10px;
        }

        .remove-fav-form button {
            background-color: #ff3c3c;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .remove-fav-form button:hover {
            background-color: #e60000;
        }

        p.empty {
            text-align: center;
            font-size: 1.3rem;
            padding: 40px;
            color: #aaa;
        }
    </style>
    <script>
        function toggleNav() {
            var navLinks = document.getElementById("navLinks");
            navLinks.classList.toggle("active");
        }
    </script>
</head>
<body>


<h1>قائمة المفضلة ❤️</h1>

<?php if (empty($favorites)): ?>
    <p class="empty">لا يوجد مسلسلات مضافة للمفضلة.</p>
<?php else: ?>
    <div class="series-grid">
        <?php foreach ($favorites as $series): ?>
            <div class="series-card">
                <form method="post" class="remove-fav-form">
                    <input type="hidden" name="favorite_id" value="<?php echo safeOutput($series['id']); ?>">
                    <button type="submit" name="toggle_favorite" title="إزالة من المفضلة">×</button>
                </form>
                <a href="series.php?id=<?php echo safeOutput($series['id']); ?>">
                    <img src="<?php echo safeOutput($series['image']); ?>" alt="<?php echo safeOutput($series['title']); ?>">
                    <div class="info">
                        <h3><?php echo safeOutput($series['title']); ?></h3>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
