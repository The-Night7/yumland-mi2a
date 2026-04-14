// ============================================================
// PROFIL — Modification asynchrone (fetch)
// ============================================================
document.addEventListener("DOMContentLoaded", () => {

  const editBtn   = document.getElementById("btn-edit-profil");
  const saveBtn   = document.getElementById("btn-save-profil");
  const cancelBtn = document.getElementById("btn-cancel-profil");
  const msgBox    = document.getElementById("profil-message");

  // Champs éditables
  const fields = ["nom", "prenom", "tel", "adresse"];

  // Valeurs originales (pour annulation)
  let originalValues = {};

  // ── Passer en mode édition ────────────────────────────────
  if (editBtn) {
    editBtn.addEventListener("click", () => {
      fields.forEach(name => {
        const input = document.querySelector(`[data-field="${name}"]`);
        if (!input) return;
        originalValues[name] = input.value; // Sauvegarder
        input.disabled = false;
        input.classList.add("editing");
      });
      editBtn.style.display   = "none";
      saveBtn.style.display   = "inline-block";
      cancelBtn.style.display = "inline-block";
    });
  }

  // ── Annuler les modifications ─────────────────────────────
  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      fields.forEach(name => {
        const input = document.querySelector(`[data-field="${name}"]`);
        if (!input) return;
        input.value    = originalValues[name]; // Restaurer
        input.disabled = true;
        input.classList.remove("editing");
      });
      editBtn.style.display   = "inline-block";
      saveBtn.style.display   = "none";
      cancelBtn.style.display = "none";
      if (msgBox) msgBox.textContent = "";
    });
  }

  // ── Sauvegarder via fetch ─────────────────────────────────
  if (saveBtn) {
    saveBtn.addEventListener("click", async () => {
      const payload = {};
      fields.forEach(name => {
        const input = document.querySelector(`[data-field="${name}"]`);
        if (input) payload[name] = input.value;
      });

      try {
        const response = await fetch("/api/client/update_profil_ajax.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (msgBox) {
          msgBox.textContent = data.message;
          msgBox.style.color = data.success ? "green" : "red";
        }

        if (data.success) {
          // Repasser en mode lecture
          fields.forEach(name => {
            const input = document.querySelector(`[data-field="${name}"]`);
            if (input) { input.disabled = true; input.classList.remove("editing"); }
          });
          editBtn.style.display   = "inline-block";
          saveBtn.style.display   = "none";
          cancelBtn.style.display = "none";
        }
      } catch (err) {
        if (msgBox) { msgBox.textContent = "Erreur réseau."; msgBox.style.color = "red"; }
      }
    });
  }
});