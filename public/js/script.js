// ============================================================
// DARK MODE — Toggle + Cookie
// ============================================================
document.addEventListener("DOMContentLoaded", () => {

  const btn = document.getElementById("toggle-dark-mode");

  // --- Lecture du cookie au chargement ---
  function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? match[2] : null;
  }

  function setCookie(name, value, days = 365) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${value}; expires=${expires}; path=/`;
  }

  // Appliquer le mode sauvegardé
  const savedMode = getCookie("theme");
  if (savedMode === "dark") {
    document.body.classList.add("dark-mode");
    if (btn) btn.textContent = "☀️ Mode Clair";
  } else {
    document.body.classList.remove("dark-mode");
    if (btn) btn.textContent = "🌙 Mode Sombre";
  }

  // --- Clic sur le bouton ---
  if (btn) {
    btn.addEventListener("click", () => {
      const isDark = document.body.classList.toggle("dark-mode");
      setCookie("theme", isDark ? "dark" : "light");
      btn.textContent = isDark ? "☀️ Mode Clair" : "🌙 Mode Sombre";
    });
  }
});