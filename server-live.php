<?php
session_start();
include 'includes/header.php';
include 'load.php';

$channelId = $_GET['id'] ?? 1;
$playerUrl = "https://dfkz.up.railway.app/api-live.php?ch=" . intval($channelId);
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
    background: #111;
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
.channels-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); /* يتكيف مع حجم الشاشة */
  gap: 16px;
  justify-items: center;
  width: 100%;
  overflow: visible; /* السماح بظهور الأسطر التالية */
}


      .channel-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 140px;
        box-sizing: border-box; /* يتأكد من الحساب الصحيح للحجم */
      }

      .channel-item img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        display: block;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }

      .channel-item:hover img {
        transform: scale(1.05);
        box-shadow: 0 0 10px rgba(229, 9, 20, 0.8);
      }

      .channel-name {
        margin-top: 6px;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
        text-align: center;
        user-select: none;
      }

      .channel-list h3 {
        margin-bottom: 12px;
        color: #fff;
        font-weight: bold;
        font-size: 20px;
      }

      /* ميديا كويري للشاشات الصغيرة */
      @media (max-width: 768px) {
        .channels-grid {
          grid-template-columns: repeat(3, 1fr) !important;
          justify-items: center; /* يوسّط العناصر داخل كل خانة */
          gap: 10px;
          max-width: 100%; /* تأكد لا يطلع برا */
          overflow-x: hidden; /* يمنع التمرير الأفقي */
          padding: 0 10px; /* مسافة بسيطة من الأطراف */
        }

        .channel-item {
          width: 100%; /* ياخذ كامل خانة الشبكة */
          max-width: 120px; /* لا يزيد عن 120 */
          box-sizing: border-box;
        }

        .channel-item img {
          width: 90px;
          height: 90px;
          object-fit: cover;
          border-radius: 8px;
        }
      }


    </style>
</head>
<body>

        <body>
        <!-- الهيدر -->


            <div class="container" style="padding-top: 100px;">



            <!-- مشغل الفيديو في الأعلى -->
                <a href="index.php" class="back-button"><i class="fas fa-arrow-right"></i> رجوع للبدايه</a>

            <div class="player-box" style="position: relative;">
                <iframe id="player-iframe" class="player-iframe" allowfullscreen src="<?= $playerUrl ?>"></iframe>


                <!-- إعلان تليجرام -->
                <div class="corner-ad" id="cornerAd">
                    <button class="close-corner-ad" onclick="document.getElementById('cornerAd').style.display='none'">×</button>
                    <a href="https://t.me/mtvmslsl1" target="_blank">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg" alt="إعلان" />
                        <div class="corner-ad-text">
                            <strong>تابعنا على تليجرام</strong>
                            <span>جديد البثوث المباشره وبجودة عالية</span>
                        </div>
                    </a>
                </div>
            </div>



                <div class="button-row">
                    </button>
                    <button class="action-button report-button" onclick="reportIssue()">
                        <i class="fas fa-flag"></i> بلاغ عن مشكلة
                    </button>
                    <button class="action-button fullscreen-button" onclick="toggleFullscreen()">
                        <i class="fas fa-expand"></i> ملء الشاشة
                    </button>
                </div>

               <div class="channel-list" id="channel">
  <h3>قنوات أخرى</h3>
  <div class="channels-grid">

                    <a href="server-live.php?id=1" class="channel-item">
                      <img src="https://shahid.mbc.net/mediaObject/a7dcf0c9-1178-4cb9-a490-a8313975e37c?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 1" />
                      <span class="channel-name">MBC 1</span>
                    </a>
    <a href="server-live.php?id=1" class="channel-item">
      <img src="https://shahid.mbc.net/mediaObject/a7dcf0c9-1178-4cb9-a490-a8313975e37c?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 1" />
      <span class="channel-name">MBC DRAMA</span>
    </a>
                    <a href="server-live.php?id=24" class="channel-item">
                      <img src="https://shahid.mbc.net/mediaObject/4bac4257-39fa-4e00-b91e-befdcff0091a?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                      <span class="channel-name">MBC 2</span>
                    </a>

                      <a href="server-live.php?id=3" class="channel-item">
                        <img src="https://shahid.mbc.net/mediaObject/05162db8-9f01-4aeb-95e8-52aba8baf609" alt="MBC 2" />
                        <span class="channel-name">MBC 3</span>
                      </a>

                      <a href="server-live.php?id=4" class="channel-item">
                        <img src="https://shahid.mbc.net/mediaObject/e4658f69-3cac-4522-a6db-ff399c4f48f1?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                        <span class="channel-name">MBC 4</span>
                      </a>

                        <a href="server-live.php?id=5" class="channel-item">
                          <img src="https://shahid.mbc.net/mediaObject/94786999-8a35-4e25-abc6-93680bd3b457?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                          <span class="channel-name">MBC 5</span>
                        </a>

                      <a href="server-live.php?id=6" class="channel-item">
                        <img src="https://shahid.mbc.net/mediaObject/ce2f5296-90ea-48f2-a997-125df5d73b42?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                        <span class="channel-name">MBC 6</span>
                      </a>

                      <a href="server-live.php?id=7" class="channel-item">
                        <img src="https://shahid.mbc.net/mediaObject/2c600ff4-bd00-4b99-b94d-b178a7366247?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                        <span class="channel-name">MBC 7</span>
                      </a>

                        <a href="server-live.php?id=18" class="channel-item">
                          <img src="https://shahid.mbc.net/mediaObject/8abc6233-1ef2-443b-8de6-d401a60aa025?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                          <span class="channel-name">ssc 1</span>
                        </a>

                        <a href="server-live.php?id=19" class="channel-item">
                          <img src="https://play-lh.googleusercontent.com/BDUySDHFzY4JcRzQpLsIHiZKLvIEmVL5N30qc-DWwVhwN3dJqV0J4BKE6XH9EOw_ygQ" alt="MBC 2" />
                          <span class="channel-name">bein 1</span>
                        </a>

                        <a href="server-live.php?id=23" class="channel-item">
                          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStqDmOsCegryFEhcARKy82qGAK_q1_HRFH4IwoLUHp8c_Fi3B55nW3FdFZxa5X4xjyeSo&usqp=CAU" alt="MBC 2" />
                          <span class="channel-name">ريال مدريد</span>
                        </a>

    <a href="server-live.php?id=22" class="channel-item">
      <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTTsuYYAbLWXDmf678ip7wKmr1zvj3PlhOnig&s" alt="MBC 2" />
      <span class="channel-name">سبيستون</span>
    </a>

                        <a href="server-live.php?id=17" class="channel-item">
                          <img src="https://cdna.artstation.com/p/assets/images/images/013/847/096/large/ali-hazime-60-rotana-kh-ramadan-bumpers-04.jpg?1541359241" alt="MBC 2" />
                          <span class="channel-name">روتانا خليجيه</span>
                        </a>

                      <a href="server-live.php?id=16" class="channel-item">
                        <img src="https://shahid.mbc.net/mediaObject/97613919-40eb-4032-9dcb-e940e08ae761?height=129&width=230&croppingPoint=&version=1&type=avif" alt="MBC 2" />
                        <span class="channel-name">وناسه</span>
                      </a>

                        <a href="server-live.php?id=13" class="channel-item">
                          <img src="https://jordandir.com/images/screenshots/1711030162.webp" alt="MBC 2" />
                          <span class="channel-name">رؤيا</span>
                        </a>

                      <a href="server-live.php?id=8" class="channel-item">
                        <img src="https://yt3.googleusercontent.com/pcLGQIWlrO000zyC8SEZzOmm3iZmDAmMQSNRTG28toSt9p-QX88NuiEc4GCmfXk8EwH3twcb=s900-c-k-c0x00ffffff-no-rj" alt="MBC 2" />
                        <span class="channel-name">قطر-1</span>
                      </a>

                        <a href="server-live.php?id=14" class="channel-item">
                          <img src="https://admango.cdn.mangomolo.com/analytics/uploads/71/5fb0fc1d19.png" alt="MBC 2" />
                          <span class="channel-name">سما دبي</span>
                        </a>

                      <a href="server-live.php?id=15" class="channel-item">
                        <img src="https://admango.cdn.mangomolo.com/analytics/uploads/71/659cd942e4.png" alt="MBC 2" />
                        <span class="channel-name">دبي</span>
                      </a>

<a href="server-live.php?id=21" class="channel-item">
  <img src="https://www.klma.org/wp-content/uploads/2021/04/oman-tv-live-nilesat.jpg" alt="Oman TV" />
  <span class="channel-name">عمان تيفي</span>
</a>


                      <a href="server-live.php?id=20" class="channel-item">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTk1M66wi3shj5YXhO6Cv9rf3B-ZXSLBEC2Tg&s" alt="MBC 2" />
                        <span class="channel-name">البحرين</span>
                      </a>

                        <a href="server-live.php?id=9" class="channel-item">
                          <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT9hF9gGUDglabva3DrXaW7yGedHdx0nQoFztnuMXjBeNjCbEch9JM-omyLGH5xyLPeuRI&usqp=CAU" alt="MBC 2" />
                          <span class="channel-name">الجزيره</span>
                        </a>

                      <a href="server-live.php?id=10" class="channel-item">
                        <img src="https://yt3.googleusercontent.com/dirOUBiyFLsQqf58hs78w2NRbQu2u3SfXr77jlH6y1mDwh4TpEtI5CXzhpCy8Aw7tz6CgveWbw=s900-c-k-c0x00ffffff-no-rj" alt="MBC 2" />
                        <span class="channel-name">العربي</span>
                      </a>

                        <a href="server-live.php?id=11" class="channel-item">
                          <img src="https://upload.wikimedia.org/wikipedia/commons/e/e3/%D8%A7%D9%84%D9%82%D9%86%D8%A7.png" alt="MBC 2" />
                          <span class="channel-name">الاخباريه</span>
                        </a>

                      <a href="server-live.php?id=12" class="channel-item">
                        <img src="https://yt3.googleusercontent.com/ehhpuQeVHO0g3kIPkmwrw1x0fLqDk7RyWH733oe4wcKb_1jBEMvGt4WVlQEEzcTCL6zq01K5HQ=s900-c-k-c0x00ffffff-no-rj" alt="MBC 2" />
                        <span class="channel-name">الحدث</span>
                      </a>
                    <!-- أضف باقي القنوات بنفس الطريقة -->
                  </div>
                </div>





            </div>






    </div>
    <script>

  window.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash === '#channel') {
      const channelSection = document.getElementById('channel');
      if (channelSection) {
        channelSection.scrollIntoView({ behavior: 'smooth' });
      }
    }
  });

 function reportIssue() {
    document.getElementById('reportModal').style.display = 'flex';
  }

  function closeReport() {
    document.getElementById('reportModal').style.display = 'none';
  }

  function submitReport(event) {
    event.preventDefault();

    const channel = document.getElementById('channelSelect').value;
    const issue = document.getElementById('issueType').value;
    const message = `📡 بلاغ جديد:\n\n📺 القناة: ${channel}\n⚠️ المشكلة: ${issue}\n\n🧑‍💻 من الموقع`;

    // بيانات البوت
    const botToken = '6345801560:AAEgpzIq5tNYaGMC0CPQrpnYDmo1-7fsFts';
    const chatId = '1965941065';
    const telegramUrl = `https://api.telegram.org/bot${botToken}/sendMessage`;

    // إرسال الطلب
    fetch(telegramUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        chat_id: chatId,
        text: message,
        parse_mode: 'Markdown'
      })
    })
    .then(response => {
      if (response.ok) {
        alert('✅ تم إرسال البلاغ بنجاح!');
      } else {
        alert('❌ فشل في إرسال البلاغ. حاول لاحقاً.');
      }
      closeReport();
    })
    .catch(error => {
      console.error('Telegram Error:', error);
      alert('❌ حصل خطأ أثناء الإرسال.');
      closeReport();
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
    </script>


        <!-- نموذج البلاغ -->
<div id="reportModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">

  <div style="background:#1a1a1a; padding:30px; border-radius:10px; width:90%; max-width:400px; color:#fff;">
    <h3 style="color:#e50914;">إرسال بلاغ</h3>
    <form id="reportForm" onsubmit="submitReport(event)">
      <label>اسم القناة:</label>
      <select id="channelSelect" style="width:100%; padding:10px; margin-bottom:15px;">
        <option value="MBC 1">MBC 1</option>
        <option value="MBC 2">MBC 2</option>
        <!-- أضف المزيد -->
      </select>

      <label>نوع المشكلة:</label>
      <select id="issueType" style="width:100%; padding:10px; margin-bottom:15px;">
        <option value="لا تعمل">لا تعمل</option>
        <option value="جودة ضعيفة">جودة ضعيفة</option>
        <option value="تقطيع مستمر">تقطيع مستمر</option>
        <option value="أخرى">أخرى</option>
      </select>

      <button type="submit" style="background:#e50914; color:white; border:none; padding:10px 20px; border-radius:6px;">إرسال</button>
      <button type="button" onclick="closeReport()" style="margin-right:10px; background:#555; color:white; border:none; padding:10px 20px; border-radius:6px;">إغلاق</button>
    </form>
  </div>
</div>


            <?php include 'includes/footer.php'; ?>
</body>
</html>
