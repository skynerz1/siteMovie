<!-- loader.php -->
<!-- loader.php -->
<style>
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
  width: 100px;
  height: 100px;
  animation: pulse 1.5s infinite ease-in-out;
  margin-bottom: 20px;
}

.loading-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

/* نبض ناعم للتحميل */
@keyframes pulse {
  0% { transform: scale(1); opacity: 0.9; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); opacity: 0.9; }
}

.loading-text {
  color: #fff;
  font-size: 1.2rem;
  font-weight: bold;
  font-family: 'Tahoma', sans-serif;
  letter-spacing: 1px;
}
</style>

<div id="global-loader">
  <div class="loading-overlay">
    <div class="loading-logo">
      <img src="../includes/img/dfkz.png" alt="جاري التحميل">
    </div>
    <div class="loading-text">جاري التحميل...</div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const loader = document.getElementById("global-loader");

  document.body.addEventListener("click", function (e) {
    const target = e.target.closest("a, button, input[type='submit']");

    if (!target) return;

    if (target.hasAttribute("onclick")) return;

    if (target.tagName.toLowerCase() === "button" || target.tagName.toLowerCase() === "input") {
      if (target.closest("form")) return;
    }

    if (target.tagName.toLowerCase() === "a") {
      const href = target.getAttribute("href");
      const isSamePage = href === window.location.pathname || href === window.location.href || href === "#";
      if (target.getAttribute("target") === "_blank" || !href || href.startsWith("javascript:") || isSamePage) {
        return;
      }
    }

    loader.style.display = "block";
  });

  window.addEventListener("load", function () {
    loader.style.display = "none";
  });
});
</script>
