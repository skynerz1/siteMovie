    <?php
    // session_start();
    include 'includes/header.php';

    function getEpisodeSources($episodeId) {
        $url = "https://app.arabypros.com/api/episode/source/by/{$episodeId}/4F5A9C3D9A86FA54EACEDDD635185/d506abfd-9fe2-4b71-b979-feff21bcad13/";
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
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid response format: ' . json_last_error_msg()];
        }
        return $data;
    }

    function getDownloadLink($links) {
        foreach ($links as $link) {
            if (isset($link['url']) && strpos($link['url'], 'cybervynx.com/e/') !== false) {
                return str_replace('/e/', '/f/', $link['url']);
            }
        }
        return null;
    }

    function getSeriesEpisodes($seriesId) {
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
        curl_close($ch);

        return json_decode($response, true);
    }

    $episodeLinks = [];
    $error = '';
    $downloadLink = null;
    $seriesId = $_GET['series_id'] ?? null;
    $type = $_GET['type'] ?? 'serie';
    $episodeId = $_GET['id'] ?? null;
    $backLink = $seriesId ? ($type === 'movie' ? "movie.php?id=" . urlencode($seriesId) : "series.php?id=" . urlencode($seriesId)) : 'index.php';

    $episodesList = [];
    if ($type === 'serie' && $seriesId) {
        $seasonsData = getSeriesEpisodes($seriesId);
        foreach ($seasonsData as $season) {
            foreach ($season['episodes'] as $ep) {
                $episodesList[] = $ep;
            }
        }
    }

    if ($episodeId) {
        $episodeLinks = getEpisodeSources($episodeId);

        if (is_array($episodeLinks)) {
            if (isset($episodeLinks['error'])) {
                $error = $episodeLinks['error'];
                $episodeLinks = [];
            } else {
                // فلترة السيرفرات حسب النوع باستخدام function عادية
                $movServers = array_filter($episodeLinks, function($link) {
                    return strtolower($link['type'] ?? '') === 'mov';
                });
                $m3u8Servers = array_filter($episodeLinks, function($link) {
                    return strtolower($link['type'] ?? '') === 'm3u8';
                });
                $otherServers = array_filter($episodeLinks, function($link) {
                    $t = strtolower($link['type'] ?? '');
                    return $t !== 'mov' && $t !== 'm3u8';
                });

                // دمج السيرفرات حسب الترتيب المطلوب
                $episodeLinks = array_merge($movServers, $m3u8Servers, $otherServers);

                // تعديل أسماء أول سيرفرين إذا موجودين
                if (isset($episodeLinks[0])) {
                    $episodeLinks[0]['type'] = 'سيرفر دفكز';
                }
                if (isset($episodeLinks[1])) {
                    $episodeLinks[1]['type'] = 'سيرفر دفكز 2';
                }
                if (isset($episodeLinks[2])) {
                    $episodeLinks[2]['type'] = 'سيرفر دفكز 3';
                }

                $downloadLink = getDownloadLink($episodeLinks);
            }
        } else {
            $error = 'تعذر جلب روابط السيرفرات';
            $episodeLinks = [];
        }
    }



    else {
        $error = 'ID is required';
    }



    function isServerAlive($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($httpCode >= 200 && $httpCode < 400);
    }

$initialServer = '';

foreach ($episodeLinks as $link) {
    if (!empty($link['url']) && isServerAlive($link['url'])) {
        $initialServer = $link['url'];
        // تحويل الرابط ليشتغل عبر mycime
        $initialServer = "https://mycime.online/e.php?url=" . urlencode($initialServer);
        break; // أول سيرفر شغال، نوقف
    }
}






    function getSeriesDetails($seriesId) {
        $localFiles = ['search_results_permanent.json', 'search_arab_permanent.json', 'save.json', 'browser.json'];

        foreach ($localFiles as $file) {
            if (!file_exists($file)) continue;

            $content = file_get_contents($file);
            $data = json_decode($content, true);

            if (!is_array($data)) continue;

            foreach ($data as $item) {
                if (isset($item['id']) && $item['id'] == $seriesId) {
                    return $item; // لقينا المسلسل
                }
            }
        }

        return null; // ما لقينا
    }

    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <meta charset="UTF-8">
        <title>مشاهدة - DfKzz</title>
        <link rel="icon" type="image/png" href="a.png">
        <style>
            html, body {
              height: 100%;
              margin: 0;
              padding: 0;
            }

            body {
              display: flex;
              flex-direction: column;
              background-color: #000;
              color: #fff;
              font-family: 'Segoe UI', sans-serif;
            }

            .container {
              max-width: 1100px;
              margin: auto;
              padding: 15px;
              flex: 1; /* هذا يدفع الفوتر لتحت */
              width: 100%;
              box-sizing: border-box;
            }

    .back-button {
        background-color: #e50914;
        color: #fff;
        padding: 10px 18px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
        margin-bottom: 20px;
    }
    .back-button:hover { background-color: #ff1a25; }

    .player-box {
        background-color: #1a1a1a;
        border-radius: 10px;
        padding: 15px;
    }
    .player-iframe {
        width: 100%;
        aspect-ratio: 16/9;
        border: none;
        border-radius: 8px;
        background-color: #000;
    }

    .button-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }
    .action-button {
        padding: 10px 15px;
        border-radius: 5px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        flex: 1 1 30%;
        min-width: 120px;
    }
    .toggle-button { background-color: #e50914; color: #fff; }
    .toggle-button:hover { background-color: #ff1a25; }
    .report-button { background-color: #444; color: #fff; }
    .report-button:hover { background-color: #666; }
    .fullscreen-button { background-color: #007bff; color: #fff; }
    .fullscreen-button:hover { background-color: #0056b3; }

    .server-selection {
        background: #121212;
        border: 1px solid #e50914;
        border-radius: 12px;
        padding: 20px;
        margin-top: 25px;
    }
    .server-selection h3 {
        color: #e50914;
        margin-bottom: 20px;
        font-size: 18px;
    }
    .server-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    .server-button {
        background: #2a2a2a;
        border: none;
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: background 0.3s;
        color: #fff;
    }
    .server-button:hover { background: #3a3a3a; }
    .server-icon i { color: #e50914; font-size: 22px; }
    .server-info {
        flex-grow: 1;
        padding: 0 12px;
        text-align: right;
    }
    .server-name { font-weight: bold; display: block; }
    .server-quality { font-size: 12px; color: #ccc; }
    .status-indicator {
        width: 10px;
        height: 10px;
        background: #00e676;
        border-radius: 50%;
    }
    .download-button {
        display: block;
        margin: 20px auto;
        padding: 12px 20px;
        background-color: #4CAF50;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
    }
    .download-button:hover { background-color: #45a049; }

    .episode-selector {
        margin-top: 40px;
        background: #1c2229;
        padding: 20px;
        border-radius: 10px;
    }
    .episode-selector h3 {
        color: #e50914;
        margin-bottom: 15px;
    }
    .episode-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .episode-link {
        background: #333;
        padding: 10px 15px;
        border-radius: 8px;
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s;
        flex: 1 1 100px;
        text-align: center;
    }
    .episode-link:hover {
        background: #e50914;
        color: #fff;
    }

    /* استجابة للجوال */
    @media (max-width: 768px) {
        .button-row {
            flex-direction: column;
            align-items: stretch;
        }
        .action-button {
            width: 100%;
            font-size: 14px;
        }
        .server-grid {
            grid-template-columns: 1fr;
        }
        .episode-link {
            flex: 1 1 45%;
        }
    }



    .corner-ad {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(20, 20, 20, 0.92);
        color: white;
        padding: 14px 16px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        z-index: 99;
        box-shadow: 0 4px 12px rgba(0,0,0,0.6);
        max-width: 340px;
        width: 100%;
        box-sizing: border-box;
    }

    .corner-ad a {
        display: flex;
        text-decoration: none;
        color: inherit;
        align-items: center;
        width: 100%;
    }

    .corner-ad img {
        width: 55px;
        height: 55px;
        margin-right: 12px;
        border-radius: 12px;
        flex-shrink: 0;
    }

    .corner-ad-text {
        display: flex;
        flex-direction: column;
        font-size: 15px;
    }

    .corner-ad-text strong {
        font-size: 16px;
        color: #00acee;
        margin-bottom: 3px;
    }

    .close-corner-ad {
        position: absolute;
        top: 6px;
        right: 8px;
        background: none;
        border: none;
        color: #ccc;
        font-size: 18px;
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .corner-ad {
            bottom: 10px;
            right: 10px;
            max-width: 90%;
            padding: 10px 12px;
            flex-direction: row;
        }

        .corner-ad img {
            width: 45px;
            height: 45px;
            margin-right: 10px;
        }

        .corner-ad-text {
            font-size: 13px;
        }

        .corner-ad-text strong {
            font-size: 14px;
        }

        .close-corner-ad {
            font-size: 16px;
            top: 4px;
            right: 6px;
        }
    }

            .share-wrapper-center {
              text-align: center;
              margin: 20px 0;
            }

            .share-btn {
              background: linear-gradient(to right, #0c9, var(--tw-gradient-from-position, #0c9)); /* احتياطي إذا المتغير غير معرف */
              color: #fff;
              border: none;
              padding: 8px 16px;
              border-radius: 6px;
              font-size: 15px;
              cursor: pointer;
              display: inline-flex;
              align-items: center;
              gap: 6px;
              direction: rtl;
              transition: background 0.3s ease;
            }

            .share-btn:hover {
              filter: brightness(1.1);
            }

            .modal {
              position: fixed;
              top: 0; right: 0; bottom: 0; left: 0;
              background: rgba(0,0,0,0.5);
              display: flex;
              align-items: center;
              justify-content: center;
              z-index: 9999;
            }

            .modal-content {
              background: #fff;
              padding: 20px;
              border-radius: 10px;
              width: 90%;
              max-width: 400px;
              direction: rtl;
              text-align: right;
            }
            .modal-content select,
            .modal-content textarea {
              width: 100%;
              margin: 5px 0 15px;
              padding: 8px;
              border: 1px solid #ccc;
              border-radius: 4px;
            }
            .modal-content button {
              margin-left: 10px;
              padding: 8px 12px;
              cursor: pointer;
            }
        </style>
    </head>
    <body>

            <body>
            <!-- الهيدر -->


                <div class="container" style="padding-top: 100px;">

                <?php if (!empty($error)): ?>
                    <h2>خطأ</h2>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php elseif (empty($episodeLinks)): ?>
                    <h2>لا توجد روابط</h2>
                    <p>لم يتم العثور على مصادر تشغيل.</p>
                <?php else: ?><?php endif; ?>

                <!-- مشغل الفيديو في الأعلى -->
                    <a href="<?= $backLink ?>" class="back-button"><i class="fas fa-arrow-right"></i> رجوع</a>

                <div class="player-box" style="position: relative;">
                    <iframe id="player-iframe" class="player-iframe" allowfullscreen src="<?= htmlspecialchars($initialServer) ?>"></iframe>

                    <!-- إعلان تليجرام -->
                    <div class="corner-ad" id="cornerAd">
                        <button class="close-corner-ad" onclick="document.getElementById('cornerAd').style.display='none'">×</button>
                        <a href="https://t.me/mtvmslsl1" target="_blank">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg" alt="إعلان" />
                            <div class="corner-ad-text">
                                <strong>تابعنا على تليجرام</strong>
                                <span>جديد الحلقات يومياً وبجودة عالية</span>
                            </div>
                        </a>
                    </div>
                </div>



                    <div class="button-row">
                        <button class="action-button toggle-button" onclick="toggleServers(this)" id="server-toggle">
                            <i class="fas fa-server"></i> اختيار السيرفر
                        </button>
                        <button class="action-button report-button" onclick="reportIssue()">
                          <i class="fas fa-flag"></i> بلاغ عن مشكلة
                        </button>
                        <button class="action-button fullscreen-button" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i> ملء الشاشة
                        </button>
                    </div>
                    <div class="server-selection" id="server-menu" style="display:none;">
                        <h3><i class="fas fa-server"></i> اختر السيرفر البديل</h3>
                        <div class="server-grid">
                            <?php foreach ($episodeLinks as $index => $link): ?>
                                <?php if (!empty($link['url'])): ?>
                                        <button class="server-button" 
                                        id="server-<?= $index ?>"
                                        data-url="<?= htmlspecialchars($link['url']) ?>" 
                                        onclick="loadServer('<?= htmlspecialchars($link['url']) ?>', this)">

                                        <div class="server-icon">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <div class="server-info">
                                            <span class="server-name"><?= htmlspecialchars($link['type']) ?></span>
                                            <span class="server-quality">HD</span>
                                        </div>
                                        <div class="server-status">
                                            <div class="status-indicator"></div>
                                        </div>
                                    </button>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php
                $episodeUrl = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                ?>

                <div class="share-wrapper-center">
                  <button class="share-btn" data-url="<?= htmlspecialchars($episodeUrl) ?>" title="مشاركة الحلقة">
                    📤 مشاركة الحلقة
                  </button>
                </div>


            <?php
            $currentSeason = null;
            if (!empty($seasonsData) && $episodeId) {
                foreach ($seasonsData as $season) {
                    foreach ($season['episodes'] as $ep) {
                        if ($ep['id'] == $episodeId) {
                            $currentSeason = $season;
                            break 2; // أخرج من اللوبين
                        }
                    }
                }
            }
            ?>

            <?php if (!empty($currentSeason)): ?>
                <div class="episode-selector">
                    <h3><?= '' . htmlspecialchars($currentSeason['title']) ?></h3>
                    <div class="episode-list">
                        <?php foreach ($currentSeason['episodes'] as $ep): ?>
                            <?php
                                $isCurrent = $ep['id'] == $episodeId;
                                $extraStyle = $isCurrent ? 'background: #e50914; color: #fff;' : '';
                            ?>
                            <a class="episode-link" href="links.php?id=<?= urlencode($ep['id']) ?>&series_id=<?= urlencode($seriesId) ?>&type=serie" style="<?= $extraStyle ?>">
                                <?= htmlspecialchars($ep['title']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>


                <div id="reportModal" class="modal" style="display:none;">
                  <div class="modal-content">
                    <h3>بلاغ عن مشكلة</h3>

                    <label>نوع البلاغ:</label>
                    <select id="reportType" onchange="updateReasons()">
                      <option value="">اختر النوع</option>
                      <option value="video">مشكلة في الفيديو</option>
                      <option value="title">خطأ في العنوان</option>
                      <option value="other">أخرى</option>
                    </select>

                    <label>السبب:</label>
                    <select id="reportReason">
                      <option value="">اختر السبب</option>
                    </select>

                    <label>سبب آخر (اختياري):</label>
                    <textarea id="customReason" placeholder="اكتب السبب هنا..."></textarea>

                    <button onclick="submitReport()">إرسال</button>
                    <button onclick="closeReportModal()">إغلاق</button>
                  </div>
                </div>



        </div>
        <script>
            document.querySelectorAll('.share-btn').forEach(btn => {
              btn.addEventListener('click', () => {
                const url = btn.getAttribute('data-url');
                if (navigator.share) {
                  navigator.share({
                    title: 'شارك الحلقة',
                    url: url
                  }).catch(console.error);
                } else {
                  navigator.clipboard.writeText(url).then(() => {
                    alert('تم نسخ رابط الحلقة!');
                  });
                }
              });
            });

            function loadServer(url, button) {
                const iframe = document.getElementById('player-iframe');
                iframe.src = url;
                document.querySelectorAll('.server-button').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            }

            function toggleServers(btn) {
                const menu = document.getElementById('server-menu');
                const isVisible = menu.style.display === 'block';
                menu.style.display = isVisible ? 'none' : 'block';
                btn.innerHTML = isVisible
                    ? '<i class="fas fa-server"></i> اختيار السيرفر'
                    : '<i class="fas fa-times"></i> إخفاء السيرفرات';
            }

            function reportIssue() {
              document.getElementById('reportModal').style.display = 'flex';
            }

            function closeReportModal() {
              document.getElementById('reportModal').style.display = 'none';
            }

            function updateReasons() {
              const type = document.getElementById('reportType').value;
              const reasonSelect = document.getElementById('reportReason');
              reasonSelect.innerHTML = '<option value="">اختر السبب</option>';

              let reasons = [];

              if (type === 'video') {
                reasons = ['لا يعمل', 'صوت غير واضح', 'تقطيع', 'رابط خاطئ'];
              } else if (type === 'title') {
                reasons = ['عنوان غير صحيح', 'موسم خاطئ', 'حلقات ناقصة'];
              } else if (type === 'other') {
                reasons = ['محتوى غير لائق', 'مشكلة أخرى'];
              }

              reasons.forEach(reason => {
                const option = document.createElement('option');
                option.value = reason;
                option.textContent = reason;
                reasonSelect.appendChild(option);
              });
            }

            function submitReport() {
              const type = document.getElementById('reportType').value;
              const reason = document.getElementById('reportReason').value;
              const custom = document.getElementById('customReason').value.trim();

              if (!type || (!reason && !custom)) {
                alert('يرجى اختيار نوع البلاغ وسبب أو كتابة سبب آخر.');
                return;
              }

              let fullReason = "";

              if (reason && custom) {
                fullReason = reason + "\n" + "سبب إضافي: " + custom;
              } else if (reason) {
                fullReason = reason;
              } else if (custom) {
                fullReason = "سبب مخصص: " + custom;
              }

              const currentUrl = window.location.href;

              const message = `
            🚨 بلاغ جديد

            📌 النوع: ${type}
            📝 السبب:
            ${fullReason}
            🔗 الرابط: ${currentUrl}
            `.trim();

              const botToken = '6345801560:AAH2rkXSmDeYT0pbpBBt6ID06PuIeX5F8uw';
              const chatId = '1965941065';

              const url = `https://api.telegram.org/bot${botToken}/sendMessage`;
              const params = {
                chat_id: chatId,
                text: message,
                parse_mode: 'HTML'
              };

              fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(params)
              })
              .then(res => res.json())
              .then(data => {
                if (data.ok) {
                  alert('✅ تم إرسال البلاغ بنجاح، شكراً لتعاونك!');
                } else {
                  alert('❌ حدث خطأ أثناء إرسال البلاغ، حاول لاحقًا.');
                  console.error(data);
                }
                closeReportModal();
              })
              .catch(error => {
                alert('⚠️ تعذر الاتصال بتيليجرام.');
                console.error(error);
                closeReportModal();
              });
            }




            function toggleFullscreen() {
                const iframe = document.getElementById('player-iframe');
                if (iframe.requestFullscreen) {
                    iframe.requestFullscreen();
                } else if (iframe.webkitRequestFullscreen) {
                    iframe.webkitRequestFullscreen();
                } else if (iframe.msRequestFullscreen) {
                    iframe.msRequestFullscreen();
                }
            }


            document.addEventListener("DOMContentLoaded", () => {
              document.querySelectorAll(".server-button").forEach(button => {
                const url = button.dataset.url;

                fetch(url, { method: "HEAD", mode: "no-cors" })
                  .then(() => {
                    // يعتبر حي
                  })
                  .catch(() => {
                    button.remove(); // نحذف السيرفر الغير شغال
                  });
              });
            });

            (function () {
              const currentHost = window.location.host;
              const originalLocation = window.location.href;
              const originalWindowOpen = window.open;
              let lastClickTime = 0;

              const allowedLinks = ['t.me/MTVMSLSL1'];

              // ✅ 1. منع فتح روابط خارجية عند الضغط (باستثناء المسموح بها)
              document.addEventListener('click', function (e) {
                const target = e.target.closest('a');
                const now = Date.now();

                if (now - lastClickTime < 1500) {
                  console.warn('⏳ Blocked rapid click redirect');
                  e.preventDefault();
                  e.stopPropagation();
                  return;
                }
                lastClickTime = now;

                if (!target) return;

                try {
                  const linkHref = target.href;
                  const url = new URL(linkHref);
                  const linkHostPath = url.host + url.pathname;

                  if (url.host !== currentHost && !allowedLinks.includes(linkHostPath)) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.warn('❌ Blocked external link:', linkHref);
                  }
                } catch (err) {
                  console.warn('⚠️ Invalid or blocked link:', err);
                  e.preventDefault();
                  e.stopPropagation();
                }
              });

              // ✅ 2. منع أي محاولة تغيير غير مباشرة للموقع (window.location)
              setInterval(() => {
                if (window.location.href !== originalLocation) {
                  console.warn('❌ Blocked forced redirect to:', window.location.href);
                  window.location.href = originalLocation;
                }
              }, 150);

              // ✅ 3. حظر window.open على روابط خارجية (باستثناء المسموح بها)
              window.open = function (url, ...args) {
                try {
                  const parsedUrl = new URL(url);
                  const fullPath = parsedUrl.host + parsedUrl.pathname;

                  if (parsedUrl.host !== currentHost && !allowedLinks.includes(fullPath)) {
                    console.warn('❌ Blocked window.open redirect to:', url);
                    return null;
                  }
                } catch (err) {
                  console.warn('⚠️ Invalid URL or blocked:', url);
                  return null;
                }

                return originalWindowOpen.call(window, url, ...args);
              };

              // ✅ 4. منع التحويل عند فقدان التركيز قبل الخروج
              window.addEventListener("beforeunload", function (e) {
                if (!document.hasFocus()) {
                  e.preventDefault();
                  e.returnValue = '';
                  console.warn('⚠️ Blocked suspicious unload redirect');
                }
              });
            })();



        </script>
                <?php include 'includes/footer.php'; ?>
    </body>
    </html>
