<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

<meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DFkz Player مع اختيار القنوات والإعدادات</title>
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<!-- FontAwesome للأيقونات -->
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
/>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
<style>
  * {
    box-sizing: border-box;
  }
  html, body {
    margin: 0; padding: 0; height: 100%;
    background-color: #111;
    font-family: 'Cairo', sans-serif;
    color: white;
    overflow: hidden;
  }
  body {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .player-wrapper {
    position: relative;
    width: 100vw;
    height: 100vh;
    background-color: black;
    overflow: hidden;
    outline: none;
  }
  video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: black;
  }

  /* شريط التحكم */
  .custom-controls {
    position: absolute;
    bottom: 0;
    width: 100%;
    background: transparent;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
  }

  /* عكس الاتجاه: الجهة اليمنى أصبحت على اليسار والعكس */
  .controls-left,
  .controls-right {
    display: flex;
    align-items: center;
    gap: 20px;
  }
  .controls-left {
    order: 1;
  }
  .controls-right {
    order: 2;
  }

  /* أزرار التحكم */
  .btn {
    background: transparent;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
  }
  .btn:hover,
  .btn:focus {
    color: #ff3c38;
    outline: none;
  }

  /* كلمة LIVE */
  .live-indicator {
    font-weight: bold;
    font-size: 14px;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
    user-select: none;
  }
  .live-indicator .dot {
    width: 12px;
    height: 12px;
    background: red;
    border-radius: 50%;
    box-shadow: 0 0 8px red;
  }

  /* كلمة DFkz player - لون أحمر */
  .logo-text {
    font-weight: 900;
    font-size: 18px;
    color: #ff3c38;
    user-select: none;
  }

  /* شريط الصوت */
  .volume-control {
    position: relative;
    display: flex;
    align-items: center;
  }
  #volumeSlider {
    position: absolute;
    bottom: 40px;
    left: 0;
    width: 120px;
    -webkit-appearance: none;
    height: 6px;
    border-radius: 3px;
    background: #444;
    cursor: pointer;
    display: none;
    z-index: 50;
  }
  #volumeSlider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: #ff3c38;
    border-radius: 50%;
    cursor: pointer;
    border: none;
    box-shadow: 0 0 8px #ff3c38;
  }
  #volumeSlider::-moz-range-thumb {
    width: 18px;
    height: 18px;
    background: #ff3c38;
    border-radius: 50%;
    cursor: pointer;
    border: none;
  }
  .volume-control:hover #volumeSlider {
    display: block;
  }

  /* زر القنوات */
  .channel-toggle-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    background: transparent;
    border: none;
    cursor: pointer;
    width: 36px;
    height: 36px;
    padding: 0;
    transition: filter 0.3s;
    z-index: 1000;
  }
  .channel-toggle-btn:hover {
    filter: brightness(0.8) saturate(2) drop-shadow(0 0 2px #ff3c38);
  }
  .channel-toggle-btn img {
    width: 100%;
    height: 100%;
    object-fit: contain;
  }

  /* القائمتان الجانبيتان */
  .side-panel {
    position: fixed;
    top: 0;
    right: -360px;
    width: 360px;
    height: 100%;
    background: #222;
    box-shadow: -5px 0 15px rgba(0,0,0,0.8);
    transition: right 0.3s ease;
    z-index: 200;
    display: flex;
    flex-direction: column;
  }
  .side-panel.open {
    right: 0;
  }

  /* قائمة القنوات */
  #sidePanelChannels .side-panel-header {
    padding: 18px 20px;
    font-size: 22px;
    font-weight: bold;
    color: #ff3c38;
    border-bottom: 1px solid #444;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  #sidePanelChannels .side-panel-header button {
    background: transparent;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    user-select: none;
    transition: color 0.3s;
  }
  #sidePanelChannels .side-panel-header button:hover {
    color: #ff3c38;
  }
  #sidePanelChannels .channel-list {
    flex: 1;
    overflow-y: auto;
  }
  #sidePanelChannels .channel-item {
    display: flex;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid #333;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
    user-select: none;
  }
  #sidePanelChannels .channel-item:hover {
    background: #ff3c38;
    color: white;
  }
  #sidePanelChannels .channel-item.active {
    background: #ff3c38;
    color: white;
    font-weight: bold;
  }
  #sidePanelChannels .channel-item img {
    width: 100px;
    height: 70px;
    border-radius: 10px;
    object-fit: cover;
    margin-left: 15px;
    transition: transform 0.3s ease;
  }
  #sidePanelChannels .channel-item:hover img {
    transform: scale(1.05);
  }

  #sidePanelChannels .channel-item span {
    flex-grow: 1;
    font-size: 16px;
  }

  /* قائمة الإعدادات */
  #sidePanelSettings {
    position: fixed;
    top: 0;
    right: -360px;
    width: 360px;
    height: 100%;
    background: #222;
    box-shadow: -5px 0 15px rgba(0,0,0,0.8);
    transition: right 0.3s ease;
    z-index: 210;
    display: flex;
    flex-direction: column;
  }
  #sidePanelSettings.open {
    right: 0;
  }
  #sidePanelSettings .side-panel-header {
    padding: 18px 20px;
    font-size: 22px;
    font-weight: bold;
    color: #ff3c38;
    border-bottom: 1px solid #444;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  #sidePanelSettings .side-panel-header button {
    background: transparent;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    user-select: none;
    transition: color 0.3s;
  }
  #sidePanelSettings .side-panel-header button:hover {
    color: #ff3c38;
  }
  #sidePanelSettings .settings-content {
    padding: 20px;
    flex: 1;
    overflow-y: auto;
    font-size: 16px;
  }
  #sidePanelSettings label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #ff3c38;
  }
  #sidePanelSettings select {
    width: 100%;
    padding: 8px 10px;
    font-size: 16px;
    border-radius: 5px;
    border: none;
    background: #333;
    color: white;
    margin-bottom: 20px;
    cursor: pointer;
  }

  /* زر التشغيل */
  #playPauseBtn i {
    font-size: 26px;
  }
  /* زر الصوت */
  #muteBtn i {
    font-size: 26px;
  }
  /* أزرار التحكم الأخرى */
  #settingsBtn i,
  #pipBtn i,
  #fullscreenBtn i {
    font-size: 24px;
  }

  /* استجابة للشاشات الصغيرة */
  @media(max-width: 600px) {
    #sidePanelChannels, #sidePanelSettings {
      width: 100%;
      height: 50%;
      right: 0;
      bottom: -100%;
      top: auto;
      transition: bottom 0.3s ease;
    }

    #sidePanelChannels.open,
    #sidePanelSettings.open {
      bottom: 0;
    }

    /* تعديل الزر أيضًا إن احتجت */
    .channel-toggle-btn {
      bottom: 12px;
      top: auto;
      right: 12px;
    }
  }

  @media (max-width: 1024px) {
    .player-wrapper {
      width: 100vw;
      height: auto;
      aspect-ratio: 16/9;
    }

    video {
      object-fit: contain;
    }

     .custom-controls {
        flex-direction: column;
        align-items: center;
      }

      .controls-left,
      .controls-right {
        flex-direction: row;
        justify-content: center;
        gap: 12px;
        width: 100%;
        margin: 5px 0;
      }

      .btn i {
        font-size: 18px !important;
      }
    }

    .channel-toggle-btn {
      width: 40px;
      height: 40px;
    }

    #volumeSlider {
      width: 80px;
    }
  }

  @media (orientation: portrait) and (max-width: 768px) {
    .player-wrapper {
      height: 55vh;
    }

    #sidePanelChannels,
    #sidePanelSettings {
      height: 50vh;
      padding: 10px;
      font-size: 14px;
    }

    #sidePanelChannels .channel-item img {
      width: 80px;
      height: 55px;
    }

    .channel-toggle-btn {
      top: 12px;
      right: 12px;
    }
  }

</style>
</head>
<body>

<div class="player-wrapper" tabindex="0" aria-label="مشغل فيديو مع اختيار القنوات">
  <video id="video" playsinline></video>

  <!-- زر القنوات -->
  <button class="channel-toggle-btn" id="channelToggleBtn" title="فتح قائمة القنوات" aria-label="فتح قائمة القنوات">
    <img src="https://shahid.mbc.net/staticFiles/production/static/images/chromelessPlayer/playlistIcon.svg" alt="زر القنوات" />
  </button>

  <!-- شريط التحكم -->
  <div class="custom-controls" role="region" aria-label="شريط تحكم الفيديو">
    <div class="controls-left" aria-label="أدوات التحكم اليسرى">
      <div class="logo-text" aria-label="شعار المشغل">DFkz player</div>

      <button class="btn" id="settingsBtn" title="الإعدادات" aria-label="الإعدادات">
        <i class="fa-solid fa-gear"></i>
      </button>

      <button class="btn" id="pipBtn" title="صورة داخل صورة (PiP)" aria-label="وضع صورة داخل صورة">
        <i class="fa-solid fa-box"></i>
      </button>

      <button class="btn" id="fullscreenBtn" title="ملء الشاشة" aria-label="ملء الشاشة">
        <i class="fa-solid fa-expand"></i>
      </button>
    </div>

    <div class="controls-right" aria-label="أزرار التشغيل والصوت">
      <button class="btn play-pause-btn" id="playPauseBtn" title="تشغيل / إيقاف" aria-label="تشغيل أو إيقاف الفيديو">
        <i class="fa-solid fa-play"></i>
      </button>

      <div class="volume-control" aria-label="تحكم الصوت">
        <button class="btn" id="muteBtn" title="كتم / إلغاء الكتم" aria-label="كتم أو إلغاء كتم الصوت">
          <i class="fa-solid fa-volume-high"></i>
        </button>
        <input
          type="range"
          id="volumeSlider"
          min="0"
          max="1"
          step="0.05"
          value="1"
          aria-label="مستوى الصوت"
        />
      </div>

      <div class="live-indicator" aria-live="polite" aria-atomic="true" title="بث مباشر">
        <span class="dot"></span><span>LIVE</span>
      </div>
    </div>
  </div>

  <!-- قائمة القنوات الجانبية -->
  <aside class="side-panel" id="sidePanelChannels" role="region" aria-label="قائمة القنوات">
    <div class="side-panel-header">
      اختر قناة
      <button id="channelCloseBtn" aria-label="إغلاق قائمة القنوات">✖</button>
    </div>
    <div class="channel-list" id="channelList"></div>
  </aside>

  <!-- قائمة الإعدادات الجانبية -->
  <aside class="side-panel" id="sidePanelSettings" role="region" aria-label="إعدادات المشغل">
    <div class="side-panel-header">
      إعدادات المشغل
      <button id="settingsCloseBtn" aria-label="إغلاق الإعدادات">✖</button>
    </div>
    <div class="settings-content">
      <label for="qualitySelect">اختيار جودة البث:</label>
      <select id="qualitySelect" aria-label="اختيار جودة البث">
        <option value="auto">تلقائي</option>
        <!-- سيتم تعبئته برمجياً -->
      </select>

      <label for="speedSelect">اختيار سرعة التشغيل:</label>
      <select id="speedSelect" aria-label="اختيار سرعة التشغيل">
        <option value="0.5">0.5x</option>
        <option value="0.75">0.75x</option>
        <option value="1" selected>1x (الافتراضي)</option>
        <option value="1.25">1.25x</option>
        <option value="1.5">1.5x</option>
        <option value="2">2x</option>
      </select>
    </div>
  </aside>
</div>

<script>
  // بيانات القنوات (مثال مع شعار ورابط بث m3u8)
  const channels = [
    { id: 1, name: "mbc1", url: "https://d3o3cim6uzorb4.cloudfront.net/out/v1/0965e4d7deae49179172426cbfb3bc5e/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/a7dcf0c9-1178-4cb9-a490-a8313975e37c?height=129&width=230&croppingPoint=&version=1&type=avif" },

      { id: 18, name: "ssc-1", url: "https://stream.sainaertebat.com/hls2/ssc1.m3u8", logo: "https://shahid.mbc.net/mediaObject/8abc6233-1ef2-443b-8de6-d401a60aa025?height=129&width=230&croppingPoint=&version=1&type=avif" },

    { id: 19, name: "bein-1", url: "https://wo.cma.footballii.ir/hls2/b1_src.m3u8", logo: "https://upload.wikimedia.org/wikipedia/commons/8/85/Logo_beIN.png" },

    { id: 2, name: "mbc2", url: "https://edge66.magictvbox.com/liveApple/MBC_2/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/0fc148ad-de25-4bf6-8fc8-5f8f97a52e2d?height=129&width=230&croppingPoint=&version=1&type=avif" },

    { id: 3, name: "mbc3", url: "https://mbcbollywood-prod-dub-ak.akamaized.net/out/v1/d5bbe570e1514d3d9a142657d33d85e6/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/05162db8-9f01-4aeb-95e8-52aba8baf609" },

    { id: 4, name: "mbc4", url: "https://shd-gcp-live.edgenextcdn.net/live/bitmovin-mbc-4/24f134f1cd63db9346439e96b86ca6ed/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/e4658f69-3cac-4522-a6db-ff399c4f48f1?height=129&width=230&croppingPoint=&version=1&type=avif" },

    { id: 5, name: "mbc5", url: "https://shd-gcp-live.edgenextcdn.net/live/bitmovin-mbc-5/ee6b000cee0629411b666ab26cb13e9b/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/94786999-8a35-4e25-abc6-93680bd3b457?height=129&width=230&croppingPoint=&version=1&type=avif" },

    { id: 6, name: "mbc6", url:"https://shd-gcp-live.edgenextcdn.net/live/bitmovin-mbc-bollywood/546eb40d7dcf9a209255dd2496903764/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/ce2f5296-90ea-48f2-a997-125df5d73b42?height=129&width=230&croppingPoint=&version=1&type=avif" },

      { id: 7, name: "mbc-masr", url:"https://shls-masr-prod-dub.shahid.net/out/v1/d5036cabf11e45bf9d0db410ca135c18/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/2c600ff4-bd00-4b99-b94d-b178a7366247?height=129&width=230&croppingPoint=&version=1&type=avif" },

        { id: 17, name: "روتانا خليجيه", url:"https://edge66.magictvbox.com/liveApple/rotana_khalijiah/tracks-v1a1/mono.m3u8", logo: "https://www.okaz.com.sa/uploads/images/2020/06/01/1574565.JPG" },

        { id: 16, name: "وناسه", url:"https://shls-wanasah-prod-dub.shahid.net/out/v1/c84ef3128e564b74a6a796e8b6287de6/index.m3u8", logo: "https://shahid.mbc.net/mediaObject/97613919-40eb-4032-9dcb-e940e08ae761?height=129&width=230&croppingPoint=&version=1&type=avif" },

        { id: 13, name: "رؤيا", url:"https://royatv-live.daioncdn.net/royatv/royatv.m3u8", logo: "https://jordandir.com/images/screenshots/1711030162.webp" },

        { id: 8, name: "قطر-1", url:"https://live.kwikmotion.com/qtv1live/qtv1.smil/playlist.m3u8", logo: "https://yt3.googleusercontent.com/pcLGQIWlrO000zyC8SEZzOmm3iZmDAmMQSNRTG28toSt9p-QX88NuiEc4GCmfXk8EwH3twcb=s900-c-k-c0x00ffffff-no-rj" },

      { id: 14, name: "سما دبي", url:"https://dmieigthvllta.cdn.mgmlcdn.com/samadubaiht/smil:samadubai.stream.smil/chunklist_b8500000.m3u8", logo: "https://admango.cdn.mangomolo.com/analytics/uploads/71/5fb0fc1d19.png" },

        { id: 15, name: "دبي", url:"https://dmieigthvllta.cdn.mgmlcdn.com/dubaitvht/smil:dubaitv.stream.smil/chunklist.m3u8", logo: "https://admango.cdn.mangomolo.com/analytics/uploads/71/659cd942e4.png" },

      { id: 9, name: "الجزيره", url:"https://live-hls-apps-aja-fa.getaj.net/AJA/index.m3u8", logo: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT9hF9gGUDglabva3DrXaW7yGedHdx0nQoFztnuMXjBeNjCbEch9JM-omyLGH5xyLPeuRI&usqp=CAU" },

        { id: 10, name: "العربي", url:"https://alarabyta.cdn.octivid.com/alaraby/smil:alaraby.stream.smil/chunklist.m3u8", logo: "https://yt3.googleusercontent.com/dirOUBiyFLsQqf58hs78w2NRbQu2u3SfXr77jlH6y1mDwh4TpEtI5CXzhpCy8Aw7tz6CgveWbw=s900-c-k-c0x00ffffff-no-rj" },

      { id: 11, name: "الاخباريه", url:"https://cdn-globecast.akamaized.net/live/eds/al_ekhbariya/hls_roku/index.m3u8", logo: "https://upload.wikimedia.org/wikipedia/commons/e/e3/%D8%A7%D9%84%D9%82%D9%86%D8%A7.png" },

        { id: 12, name: "الحدث", url:"https://av.alarabiya.net/alarabiapublish/alhadath.smil/playlist.m3u8", logo: "https://yt3.googleusercontent.com/ehhpuQeVHO0g3kIPkmwrw1x0fLqDk7RyWH733oe4wcKb_1jBEMvGt4WVlQEEzcTCL6zq01K5HQ=s900-c-k-c0x00ffffff-no-rj" },
  ];


  function getChannelFromURL() {
    const params = new URLSearchParams(window.location.search);
    const chId = parseInt(params.get("ch"));
    if (!isNaN(chId)) {
      return allChannels.find(ch => ch.id === chId);
    }
    return allChannels[0];
  }

  function updateURL(chId) {
    const url = new URL(window.location.href);
    url.searchParams.set("ch", chId);
    window.history.pushState({}, "", url);
  }


  // العناصر
  const video = document.getElementById('video');
  const channelList = document.getElementById('channelList');
  const sidePanel = document.getElementById('sidePanelChannels');
  const channelToggleBtn = document.getElementById('channelToggleBtn');
  const channelCloseBtn = document.getElementById('channelCloseBtn');

  const settingsPanel = document.getElementById('sidePanelSettings');
  const settingsBtn = document.getElementById('settingsBtn');
  const settingsCloseBtn = document.getElementById('settingsCloseBtn');
  const qualitySelect = document.getElementById('qualitySelect');
  const speedSelect = document.getElementById('speedSelect');

  const playPauseBtn = document.getElementById('playPauseBtn');
  const muteBtn = document.getElementById('muteBtn');
  const volumeSlider = document.getElementById('volumeSlider');
  const fullscreenBtn = document.getElementById('fullscreenBtn');
  const pipBtn = document.getElementById('pipBtn');

  let currentChannelIndex = 0;
  let currentHls = null;

  // إعداد فيديو HLS مع دعم اختيار الجودة
  function setupVideo(url) {
    if (currentHls) {
      currentHls.destroy();
      currentHls = null;
    }
    if (Hls.isSupported()) {
      currentHls = new Hls({ autoStartLoad: true });
      currentHls.loadSource(url);
      currentHls.attachMedia(video);
      currentHls.on(Hls.Events.MANIFEST_PARSED, (_, data) => {
        // تعبئة قائمة الجودات في الإعدادات
        populateQualityOptions(data.levels);
        // تعيين الجودة الافتراضية إلى auto
        qualitySelect.value = "auto";
        video.play();
      });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = url;
      video.play();
      // لن يتم دعم اختيار الجودة في هذه الحالة
      qualitySelect.innerHTML = '<option value="auto" selected>تلقائي</option>';
    } else {
      alert("متصفحك لا يدعم تشغيل هذا النوع من الفيديو.");
    }
  }

  // تعبئة خيارات الجودة من بيانات HLS
  function populateQualityOptions(levels) {
    qualitySelect.innerHTML = '<option value="auto">تلقائي</option>';
    levels.forEach((level, i) => {
      let label = level.height ? level.height + 'p' : 'جودة ' + (i+1);
      qualitySelect.innerHTML += `<option value="${i}">${label}</option>`;
    });
  }

  // اختيار جودة البث
  qualitySelect.addEventListener('change', () => {
    if (!currentHls) return;
    if (qualitySelect.value === "auto") {
      currentHls.currentLevel = -1; // تلقائي
    } else {
      currentHls.currentLevel = parseInt(qualitySelect.value);
    }
  });

  // تغيير سرعة التشغيل
  speedSelect.addEventListener('change', () => {
    video.playbackRate = parseFloat(speedSelect.value);
  });

  // بناء قائمة القنوات الجانبية
  function buildChannelList() {
    channelList.innerHTML = "";
    channels.forEach((ch, i) => {
      const item = document.createElement('div');
      item.className = 'channel-item';
      if(i === currentChannelIndex) item.classList.add('active');
      item.tabIndex = 0;
      item.setAttribute('role', 'button');
      item.setAttribute('aria-pressed', i === currentChannelIndex);
      item.onclick = () => selectChannel(i);
      item.onkeydown = e => {
        if(e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          selectChannel(i);
        }
      };

      // صورة القناة يمين الاسم
      const logo = document.createElement('img');
      logo.src = ch.logo;
      logo.alt = ch.name + " شعار القناة";

      const nameSpan = document.createElement('span');
      nameSpan.textContent = ch.name;

      // ترتيب: صورة يمين، اسم يسار (لأنه RTL)
      item.appendChild(logo);
      item.appendChild(nameSpan);

      channelList.appendChild(item);
    });
  }

  // اختيار قناة
  function selectChannel(index) {
    if(index === currentChannelIndex) return;
    currentChannelIndex = index;
    setupVideo(channels[index].url);
    updateActiveChannel();
    closeSidePanelChannels();
    updateURL(channels[index].id);
  }
  function updateActiveChannel() {
    Array.from(channelList.children).forEach((el, i) => {
      el.classList.toggle('active', i === currentChannelIndex);
      el.setAttribute('aria-pressed', i === currentChannelIndex);
    });
  }

  // تحديث رابط الصفحة مع القناة المختارة بدون إعادة تحميل كاملة (لتسهيل مشاركة الروابط)
  function updateURL(channelId) {
    if(history.pushState) {
      const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?ch=' + channelId;
      window.history.pushState({path:newurl}, '', newurl);
    }
  }

  // زر تشغيل / إيقاف
  function togglePlay() {
    if(video.paused) {
      video.play();
      playPauseBtn.firstElementChild.className = 'fa-solid fa-pause';
    } else {
      video.pause();
      playPauseBtn.firstElementChild.className = 'fa-solid fa-play';
    }
  }
  video.addEventListener('play', () => {
    playPauseBtn.firstElementChild.className = 'fa-solid fa-pause';
  });
  video.addEventListener('pause', () => {
    playPauseBtn.firstElementChild.className = 'fa-solid fa-play';
  });

  // ربط زر التشغيل/الإيقاف بالحدث
  playPauseBtn.addEventListener('click', togglePlay);

  // زر كتم / إلغاء كتم الصوت
  function toggleMute() {
    video.muted = !video.muted;
    updateMuteButtons();
  }
  function updateMuteButtons() {
    if(video.muted || video.volume === 0) {
      muteBtn.firstElementChild.className = 'fa-solid fa-volume-xmark';
      volumeSlider.value = 0;
    } else {
      muteBtn.firstElementChild.className = 'fa-solid fa-volume-high';
      volumeSlider.value = video.volume;
    }
  }

  // تغيير مستوى الصوت عبر السلايدر
  function changeVolume(value) {
    video.volume = value;
    video.muted = value == 0;
    updateMuteButtons();
  }
  volumeSlider.addEventListener('input', e => {
    changeVolume(e.target.value);
  });

  // فتح / إغلاق قائمة القنوات الجانبية
  function openSidePanelChannels() {
    sidePanel.classList.add('open');
    // تكبير صور القنوات
    Array.from(channelList.children).forEach(el => {
      const img = el.querySelector('img');
      if(img) {
        img.style.width = '60px';
        img.style.height = '36px';
      }
    });
  }
  function closeSidePanelChannels() {
    sidePanel.classList.remove('open');
    // إعادة الصور للحجم الأصلي
    Array.from(channelList.children).forEach(el => {
      const img = el.querySelector('img');
      if(img) {
        img.style.width = '40px';
        img.style.height = '24px';
      }
    });
  }
  channelToggleBtn.addEventListener('click', () => {
    if(sidePanel.classList.contains('open')) {
      closeSidePanelChannels();
    } else {
      closeSidePanelSettings();
      openSidePanelChannels();
    }
  });
  channelCloseBtn.addEventListener('click', closeSidePanelChannels);

  // فتح / إغلاق الإعدادات الجانبية
  function openSidePanelSettings() {
    settingsPanel.classList.add('open');
    // إذا كانت قائمة القنوات مفتوحة، أقفلها
    closeSidePanelChannels();
  }
  function closeSidePanelSettings() {
    settingsPanel.classList.remove('open');
  }
  settingsBtn.addEventListener('click', () => {
    if(settingsPanel.classList.contains('open')) {
      closeSidePanelSettings();
    } else {
      openSidePanelSettings();
    }
  });
  settingsCloseBtn.addEventListener('click', closeSidePanelSettings);

  // زر ملء الشاشة
  fullscreenBtn.addEventListener('click', () => {
    if(!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => alert("فشل الدخول لوضع ملء الشاشة"));
    } else {
      document.exitFullscreen();
    }
  });

  // زر صورة داخل صورة
  pipBtn.addEventListener('click', async () => {
    if('pictureInPictureEnabled' in document) {
      try {
        if(document.pictureInPictureElement) {
          await document.exitPictureInPicture();
        } else {
          await video.requestPictureInPicture();
        }
      } catch {
        alert('تعذر تفعيل وضع صورة داخل صورة (PiP)');
      }
    } else {
      alert('جهازك لا يدعم وضع صورة داخل صورة (PiP)');
    }
  });

  // تحميل القناة المبدئية حسب الرابط أو القناة الأولى
  function getChannelIndexFromURL() {
    const params = new URLSearchParams(window.location.search);
    const chId = parseInt(params.get('ch'));
    if(!isNaN(chId)) {
      const idx = channels.findIndex(ch => ch.id === chId);
      if(idx !== -1) return idx;
    }
    return 0;
  }
  currentChannelIndex = getChannelIndexFromURL();
  setupVideo(channels[currentChannelIndex].url);

  buildChannelList();
  updateMuteButtons();

  // اختصارات لوحة المفاتيح
  document.querySelector('.player-wrapper').addEventListener('keydown', e => {
    switch(e.key) {
      case ' ':
        e.preventDefault();
        togglePlay();
        break;
      case 'm':
        toggleMute();
        break;
      case 'f':
        fullscreenBtn.click();
        break;
      case 'p':
        pipBtn.click();
        break;
      case 'c':
        channelToggleBtn.click();
        break;
      case 'x':
        if(sidePanel.classList.contains('open')) closeSidePanelChannels();
        if(settingsPanel.classList.contains('open')) closeSidePanelSettings();
        break;
      case 's':
        settingsBtn.click();
        break;
    }
  });

  // إظهار / إخفاء شريط الصوت عند الإشارة على زر الصوت (مع تأخير الاختفاء)
  let volumeTimeout;
  const volumeControl = document.querySelector('.volume-control');
  volumeControl.addEventListener('mouseenter', () => {
    clearTimeout(volumeTimeout);
    volumeSlider.style.display = 'block';
  });
  volumeControl.addEventListener('mouseleave', () => {
    volumeTimeout = setTimeout(() => {
      volumeSlider.style.display = 'none';
    }, 1500);
  });

  // زر كتم الصوت
  muteBtn.addEventListener('click', toggleMute);

  // تحديث أيقونة الكتم حسب حالة الصوت عند تحميل الصفحة
  function updateMuteButtons() {
    if(video.muted || video.volume === 0) {
      muteBtn.firstElementChild.className = 'fa-solid fa-volume-xmark';
      volumeSlider.value = 0;
    } else {
      muteBtn.firstElementChild.className = 'fa-solid fa-volume-high';
      volumeSlider.value = video.volume;
    }
  }

  // عناصر التحكم التي تختفي/تظهر
  const controls = document.querySelector('.custom-controls');
  const channelBtn = document.getElementById('channelToggleBtn');
  const settingsBtnEl = document.getElementById('settingsBtn');

  let hideControlsTimeout;

  function showControls() {
    controls.style.opacity = '1';
    controls.style.pointerEvents = 'auto';
    channelBtn.style.opacity = '1';
    channelBtn.style.pointerEvents = 'auto';
    settingsBtnEl.style.opacity = '1';
    settingsBtnEl.style.pointerEvents = 'auto';
    // إلغاء أي مؤقت خفي
    clearTimeout(hideControlsTimeout);
    // ضبط مؤقت الإخفاء بعد 4 ثواني
    hideControlsTimeout = setTimeout(() => {
      hideControls();
    }, 4000);
  }

  function hideControls() {
    // فقط إذا الفيديو شغال
    if(!video.paused) {
      controls.style.opacity = '0';
      controls.style.pointerEvents = 'none';
      channelBtn.style.opacity = '0';
      channelBtn.style.pointerEvents = 'none';
      settingsBtnEl.style.opacity = '0';
      settingsBtnEl.style.pointerEvents = 'none';
    }
  }

  // رصد الحركة داخل منطقة المشغل
  const playerWrapper = document.querySelector('.player-wrapper');
  playerWrapper.addEventListener('mousemove', showControls);
  playerWrapper.addEventListener('keydown', showControls);

  // إظهار التحكم فورًا عند تحميل الصفحة
  showControls();

  // إخفاء بعد 4 ثواني إذا الفيديو شغال
  video.addEventListener('play', () => {
    hideControlsTimeout = setTimeout(hideControls, 4000);
  });

  // إظهار شريط التحكم عند إيقاف الفيديو
  video.addEventListener('pause', () => {
    showControls();
  });

</script>

</body>
</html>
