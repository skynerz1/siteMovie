<?php
include 'load.php';
include 'includes/header.php';

$platform = $_GET['platform'] ?? 'netflix';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;

$sourceFiles = ["includes/sourse/browser.json", "includes/sourse/browser1.json"];
$cacheDir = "cache";
$cacheFile = "{$cacheDir}/{$platform}_page_{$page}.json";
$cacheMeta = "{$cacheDir}/{$platform}_pages.txt";

if (!file_exists($cacheDir)) mkdir($cacheDir, 0777, true);

// مصفوفة رئيسية لكل البيانات
$allShows = [];

// قراءة ودمج البيانات من الملفات
foreach ($sourceFiles as $file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (isset($data[$platform]) && is_array($data[$platform])) {
            $allShows = array_merge($allShows, $data[$platform]);
        }
    }
}

$totalShows = count($allShows);
$totalPages = max(1, ceil($totalShows / $perPage));

if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;

$start = ($page - 1) * $perPage;
$showsPage = array_slice($allShows, $start, $perPage);

// حفظ الكاش
file_put_contents($cacheFile, json_encode($showsPage, JSON_UNESCAPED_UNICODE));
file_put_contents($cacheMeta, $totalPages);

// جلب أول 5 مسلسلات للسلايدر
$heroShows = array_slice($allShows, 0, 5);
?>



<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Poppins', sans-serif;
    background: #121212;
    color: #fff;
    line-height: 1.6;
}


.hero-slider {
    width: 100%;
    height: 90vh;
    position: relative;
    color: white;
}
.hero-slide {
    position: relative;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, rgba(0,0,0,0.85) 30%, rgba(0,0,0,0.3) 100%);
}
.hero-content {
    position: absolute;
    top: 50%;
    left: 5%;
    transform: translateY(-50%);
    max-width: 600px;
}
.hero-meta {
    font-size: 0.9rem;
    color: #bbb;
    margin-bottom: 10px;
}
.hero-meta span {
    margin-right: 15px;
}
.hero-content h1 {
    font-size: 2.8rem;
    margin-bottom: 15px;
}
.hero-content p {
    font-size: 1.05rem;
    margin-bottom: 20px;
    color: #ddd;
}
.hero-buttons a {
    display: inline-block;
    margin-right: 10px;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    transition: background 0.3s;
}
.btn-watch {
    background: #e50914;
    color: white;
}
.btn-watch:hover {
    background: #f40612;
}
.btn-trailer {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}
.btn-trailer:hover {
    background: rgba(255, 255, 255, 0.35);
}
.hero-classification {
    margin-top: 15px;
    font-size: 0.95rem;
    color: #aaa;
}

/* نافذة البرومو */
.trailer-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.trailer-modal iframe {
    width: 80%;
    height: 70%;
    border: none;
    border-radius: 8px;
}
.close-modal {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 2rem;
    color: white;
    cursor: pointer;
}

.btn-watch {
    background: #e50914;
    color: white;
}
.btn-add {
    background: rgba(255,255,255,0.2);
    color: white;
}
.swiper-button-next,
.swiper-button-prev {
    color: white;
}


.cards-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 25px;
    padding: 20px;
}



.card img {
    width: 100%;
    height: 400px;
    object-fit: cover;
}



.pagination {
    margin: 30px auto;
    text-align: center;
}

.pagination a {
    display: inline-block;
    margin: 0 6px;
    padding: 8px 14px;
    background: #2a2a2a;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s ease;
}
    .pagination span {
        display: inline-block;
        margin: 0 6px;
        padding: 8px 14px;
        background: #fff;
        color: #000;
        border-radius: 6px;
        font-weight: bold;
    }

    .pagination span.dots {
        background: transparent;
        color: #999;
        padding: 8px 10px;
    }




.category-container {
    display: flex;
    justify-content: center;
    gap: 16px;
    padding: 0 20px 30px;
    flex-wrap: wrap;
}

.category-card {
    width: 140px;
    height: 80px;
    border-radius: 12px;
    background-size: cover;
    background-position: center;
    border: 2px solid transparent;
    transition: 0.3s ease all;
}



@media (max-width: 768px) {
    .card {
        width: 90vw;
    }

    .card img {
        height: auto;
    }

    h2.platform-title {
        margin-top: 100px;
    }
}
</style>

<h2 class="platform-title">Choose Platform</h2>

<div class="category-container">
  <a href="?platform=netflix&page=1" class="category-card <?= $platform === 'netflix' ? 'active' : '' ?>" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQGhnm_NIUms1oIl6QLrxjZzws8wLW_MVPOyw&s');" title="Netflix"></a>
  <a href="?platform=shahid&page=1" class="category-card <?= $platform === 'shahid' ? 'active' : '' ?>" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRlwV1US7Ou5Sa4bd8ALXdp1QVcpQV9rPRr_A&s');" title="Shahid"></a>
      <a href="?platform=osn&page=1" class="category-card <?= $platform === 'osn' ? 'active' : '' ?>" style="background-image: url('https://play-lh.googleusercontent.com/1O4pKO7UZtF4lL61zgTeA9aoao3TRCZMgerHrvI-k0DNMvnL2-QQX63l_h2E_ayHvtU');" title="osn"></a>
  <a href="?platform=kids&page=1" class="category-card <?= $platform === 'kids' ? 'active' : '' ?>" style="background-image: url('https://i.pinimg.com/736x/e6/84/49/e68449b851a8ffb8256a71daab209775.jpg');" title="Kids"></a>
</div>


<div class="swiper hero-slider">
    <div class="swiper-wrapper">
        <?php foreach ($heroShows as $show): ?>
            <div class="swiper-slide hero-slide" style="background-image: url('<?= htmlspecialchars($show['image']) ?>');">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <h1><?= htmlspecialchars($show['title']) ?></h1>

                    <!-- القيم فوق الوصف -->
                    <div class="hero-meta">
                        <span>📅 <?= htmlspecialchars($show['year'] ?? '') ?></span>
                        <span>⭐ <?= htmlspecialchars($show['rating'] ?? '') ?></span>
                        <span>⏳ <?= htmlspecialchars($show['duration'] ?? '') ?></span>
                    </div>

                    <!-- الوصف -->
                    <p><?= htmlspecialchars(substr($show['description'] ?? '', 0, 150)) ?>...</p>

                    <!-- القيم تحت الوصف -->
                    <div class="hero-classification">
                        <strong>تصنيف:</strong> <?= htmlspecialchars($show['classification'] ?? '') ?>
                    </div>

<!-- الأزرار -->
<div class="hero-buttons">
    <a href="<?= $isMovie ? 'movie/links.php?id=' . urlencode($show['id']) : 'series.php?id=' . urlencode($show['id']) ?>" 
       class="btn-watch">▶ شاهد الآن</a>

    <button class="btn-trailer" data-trailer="<?= htmlspecialchars($show['trailer_url'] ?? '') ?>">
        ▶ شاهد البرومو
    </button>
</div>

<!-- ... -->

<!-- نافذة عرض البرومو -->
<div id="trailer-modal" class="trailer-modal">
    <span class="close-modal">✖</span>
    <iframe id="trailer-frame" src="" allowfullscreen></iframe>
</div>

<!-- ... -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- أزرار التنقل -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>

<!-- نافذة عرض البرومو -->
<div id="trailer-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close-modal">✖</span>
        <iframe id="trailer-frame" width="100%" height="500" frameborder="0" allowfullscreen></iframe>
    </div>
</div>
<div class="cards-container">
<?php foreach ($showsPage as $show): ?>
    <?php
        $isMovie = isset($show['type']) && $show['type'] === 'movie';
        $link = $isMovie
            ? 'movie/links.php?id=' . urlencode($show['id'])
            : 'series.php?id=' . urlencode($show['id']);
    ?>
    <a href="<?= $link ?>" class="card" title="<?= htmlspecialchars($show['title']) ?>">
        <img src="<?= htmlspecialchars($show['image']) ?>" alt="<?= htmlspecialchars($show['title']) ?>" />
        <div class="rating">⭐ <?= htmlspecialchars($show['rating']) ?>/5</div>
        <div class="card-title"><?= htmlspecialchars($show['title']) ?></div>
    </a>
<?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?platform=<?= urlencode($platform) ?>&page=<?= $page - 1 ?>">⬅️ السابق</a>
    <?php endif; ?>

    <?php
    $range = 2;  // عدد الصفحات قبل وبعد الحالية
    $start = max(2, $page - $range);
    $end = min($totalPages - 1, $page + $range);

    // أول صفحة
    if ($page !== 1) {
        echo '<a href="?platform=' . urlencode($platform) . '&page=1">1</a>';
    }

    // نقاط قبل
    if ($start > 2) {
        echo '<span style="padding:0 5px;">...</span>';
    }

    // الصفحات بين
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            echo '<span style="background:#fff;color:#000;padding:8px 14px;border-radius:6px;">' . $i . '</span>';
        } else {
            echo '<a href="?platform=' . urlencode($platform) . '&page=' . $i . '">' . $i . '</a>';
        }
    }

    // نقاط بعد
    if ($end < $totalPages - 1) {
        echo '<span style="padding:0 5px;">...</span>';
    }

    // آخر صفحة
    if ($page !== $totalPages) {
        echo '<a href="?platform=' . urlencode($platform) . '&page=' . $totalPages . '">' . $totalPages . '</a>';
    }
    ?>

    <?php if ($page < $totalPages): ?>
        <a href="?platform=<?= urlencode($platform) ?>&page=<?= $page + 1 ?>">التالي ➡️</a>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
var swiper = new Swiper('.hero-slider', {
    loop: true,
    speed: 800, // سرعة الانتقال
    autoplay: {
        delay: 4000,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    effect: 'fade', // انتقال ناعم بين الشرائح
    fadeEffect: {
        crossFade: true
    }
});

// إيقاف التشغيل عند مرور الماوس
document.querySelector('.hero-slider').addEventListener('mouseenter', () => {
    swiper.autoplay.stop();
});
document.querySelector('.hero-slider').addEventListener('mouseleave', () => {
    swiper.autoplay.start();
});

// فتح مودال البرومو وعرض الفيديو مع إيقاف السلايدر
document.querySelectorAll('.btn-trailer').forEach(btn => {
    btn.addEventListener('click', () => {
        let trailerUrl = btn.getAttribute('data-trailer');
        if (!trailerUrl) {
            alert('لا يوجد فيديو برومو متاح.');
            return;
        }

        let modal = document.getElementById('trailer-modal');
        let iframe = document.getElementById('trailer-frame');

        // دعم روابط يوتيوب أو روابط فيديو مباشرة
        // أضف ?autoplay=1 تلقائياً عند يوتيوب
        if(trailerUrl.includes('youtube.com') || trailerUrl.includes('youtu.be')) {
            if (!trailerUrl.includes('autoplay=1')) {
                trailerUrl += trailerUrl.includes('?') ? '&autoplay=1&mute=1' : '?autoplay=1&mute=1';
            }
        } else {
            // روابط mp4 مثلاً ممكن تتعامل معها كما هي أو تضيف autoplay
            // لبعض الفيديوهات mp4 autoplay مع muted مطلوب
            // يمكنك تعديل حسب الحاجة
        }

        iframe.src = trailerUrl;
        modal.style.display = 'flex';
        swiper.autoplay.stop();
    });
});

// إغلاق المودال ومسح src لإيقاف الفيديو وإعادة تشغيل السلايدر
document.querySelector('.close-modal').addEventListener('click', () => {
    let modal = document.getElementById('trailer-modal');
    let iframe = document.getElementById('trailer-frame');

    iframe.src = '';
    modal.style.display = 'none';
    swiper.autoplay.start();
});

// إغلاق المودال عند الضغط على الخلفية السوداء (خارج iframe)
document.getElementById('trailer-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.querySelector('.close-modal').click();
    }
});

</script>

</body>
</html>
