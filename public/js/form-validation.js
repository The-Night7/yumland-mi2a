// ============================================================
// VALIDATION FORMULAIRES — Inscription + Connexion
// ============================================================
document.addEventListener("DOMContentLoaded", () => {

  // ── Utilitaires ──────────────────────────────────────────
  function showError(input, message) {
    clearError(input);
    input.classList.add("input-error");
    const err = document.createElement("small");
    err.className = "error-msg";
    err.style.color = "red";
    err.style.display = "block";
    err.style.marginTop = "4px";
    err.textContent = message;
    input.parentNode.appendChild(err);
  }

  function clearError(input) {
    input.classList.remove("input-error");
    const existing = input.parentNode.querySelector(".error-msg");
    if (existing) existing.remove();
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function isValidPhone(phone) {
    return /^(\+33|0)[1-9](\d{8})$/.test(phone.replace(/\s/g, ""));
  }

  // ── Icône Œil — Afficher/Cacher mot de passe ─────────────
  document.querySelectorAll(".toggle-password").forEach(btn => {
    btn.addEventListener("click", () => {
      const input = document.querySelector(btn.dataset.target);
      if (!input) return;
      const isHidden = input.type === "password";
      input.type = isHidden ? "text" : "password";
      btn.textContent = isHidden ? "🙈" : "👁️";
    });
  });

  // ── Compteur de caractères ────────────────────────────────
  document.querySelectorAll("[data-maxlength]").forEach(input => {
    const max = parseInt(input.dataset.maxlength);
    const counter = document.createElement("small");
    counter.style.color = "#888";
    counter.textContent = `0 / ${max}`;
    input.parentNode.appendChild(counter);

    input.addEventListener("input", () => {
      const len = input.value.length;
      counter.textContent = `${len} / ${max}`;
      counter.style.color = len >= max ? "red" : "#888";
      if (len > max) input.value = input.value.substring(0, max);
    });
  });

  // ── Validation Formulaire Inscription ────────────────────
  const inscriptionForm = document.getElementById("inscription-form");
  if (inscriptionForm) {
    inscriptionForm.addEventListener("submit", (e) => {
      let valid = true;

      const nom = inscriptionForm.querySelector('[name="nom"]');
      const prenom = inscriptionForm.querySelector('[name="prenom"]');
      const email = inscriptionForm.querySelector('[name="email"]');
      const tel = inscriptionForm.querySelector('[name="tel"]');
      const password = inscriptionForm.querySelector('[name="password"]');
      const confirm = inscriptionForm.querySelector('[name="confirm_password"]');

      // Réinitialiser les erreurs
      [nom, prenom, email, tel, password, confirm].forEach(f => { if (f) clearError(f); });

      if (!nom || nom.value.trim().length < 2) {
        showError(nom, "Le nom doit contenir au moins 2 caractères."); valid = false;
      }
      if (!prenom || prenom.value.trim().length < 2) {
        showError(prenom, "Le prénom doit contenir au moins 2 caractères."); valid = false;
      }
      if (!email || !isValidEmail(email.value)) {
        showError(email, "Adresse email invalide."); valid = false;
      }
      if (tel && tel.value && !isValidPhone(tel.value)) {
        showError(tel, "Numéro de téléphone invalide (ex: 0612345678)."); valid = false;
      }
      if (!password || password.value.length < 8) {
        showError(password, "Le mot de passe doit contenir au moins 8 caractères."); valid = false;
      }
      if (confirm && password && confirm.value !== password.value) {
        showError(confirm, "Les mots de passe ne correspondent pas."); valid = false;
      }

      if (!valid) e.preventDefault(); // Bloquer l'envoi si erreurs
    });
  }

  // ── Validation Formulaire Connexion ──────────────────────
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      let valid = true;

      const email = loginForm.querySelector('[name="email"]');
      const password = loginForm.querySelector('[name="password"]');

      [email, password].forEach(f => { if (f) clearError(f); });

      if (!email || !isValidEmail(email.value)) {
        showError(email, "Adresse email invalide."); valid = false;
      }
      if (!password || password.value.trim() === "") {
        showError(password, "Le mot de passe est requis."); valid = false;
      }

      // NB: auth-client.js gère déjà le fetch — on bloque juste si invalide
      if (!valid) e.preventDefault();
    }, true); // capture: true pour passer avant auth-client.js
  }
});