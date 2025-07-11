<!-- loader.php -->
<style>
/* شاشة التحميل */
#global-loader {
  display: none;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0f0f0f, #1c1c1c);
  z-index: 99999;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.loading-logo {
  width: 80px;
  height: 80px;
  border: 6px solid #eee;
  border-top: 6px solid #ff4444;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

.loading-text {
  color: #fff;
  font-size: 1.2rem;
  font-weight: bold;
  font-family: 'Tahoma', sans-serif;
  letter-spacing: 1px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<div id="global-loader">
  <div class="loading-overlay">
    <div class="loading-logo"></div>
    <div class="loading-text">جاري التحميل...</div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const loader = document.getElementById("global-loader");

  document.body.addEventListener("click", function (e) {
    const target = e.target.closest("a, button, input[type='submit']");

    if (!target) return;

    // 1. تجاهل إذا فيه onclick يدوي (زي زر التريلر)
    if (target.hasAttribute("onclick")) return;

    // 2. تجاهل الفورمات (زي زر المفضلة)
    if (target.tagName.toLowerCase() === "button" || target.tagName.toLowerCase() === "input") {
      if (target.closest("form")) return;
    }

    // 3. إذا العنصر رابط:
    if (target.tagName.toLowerCase() === "a") {
      const href = target.getAttribute("href");
      const isSamePage = href === window.location.pathname || href === window.location.href || href === "#";

      // تجاهل إذا يفتح نافذة جديدة أو نفس الصفحة أو JS
      if (target.getAttribute("target") === "_blank" || !href || href.startsWith("javascript:") || isSamePage) {
        return;
      }
    }

    // ✅ أظهر شاشة التحميل
    loader.style.display = "block";
  });

  window.addEventListener("load", function () {
    loader.style.display = "none";
  });
});
</script>

