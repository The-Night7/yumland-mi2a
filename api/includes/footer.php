</main>

<footer>
    <div class="footer-content">
        <p>&copy; <?= date('Y') ?> Le Grand Miam - Projet Creative Yumland (CY Tech)</p>
        <div class="footer-links">
            <a href="/api/pages/mentions.php">Mentions Légales</a> |
            <a href="/api/pages/inscription.php">Devenir Membre</a>
        </div>
    </div>
</footer>

<!-- Modal de sélection des Options de Menus -->
<div id="optionsModal" class="modal-overlay">
    <div class="modal-container">
        <h2 id="modalMenuTitle" class="modal-title">Composez votre menu</h2>
        <form id="optionsForm">
            <input type="hidden" id="modalProductId" name="id_produit" value="">
            <input type="hidden" id="modalPrixMiams" name="prix_miams" value="0">
            <input type="hidden" id="modalCartIndex" name="cart_index" value="">
            <input type="hidden" id="modalOptionsDispos" name="options_dispos" value="">
            <div id="optionsContainer" class="modal-options-container"></div>
            
            <div class="modal-actions">
                <button type="button" onclick="closeOptionsModal()" class="btn-cancel">Annuler</button>
                <button type="button" id="btnSubmitModal" onclick="submitOptionsMenu()" class="btn-primary btn-modal-submit">Ajouter au panier 🛒</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Ouvre la fenêtre et génère les listes déroulantes
    function showOptionsModal(productId, productName, optionsJsonString, prixMiams = 0, cartIndex = '') {
        document.getElementById('optionsModal').style.display = 'flex';
        document.getElementById('modalMenuTitle').innerText = prixMiams > 0 ? productName + " 🎁" : productName;
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalPrixMiams').value = prixMiams;
        document.getElementById('modalCartIndex').value = cartIndex;
        document.getElementById('modalOptionsDispos').value = optionsJsonString || '[]';
        
        // Changer le texte du bouton si c'est une modification
        document.getElementById('btnSubmitModal').innerHTML = cartIndex !== '' ? '<i class="fas fa-sync"></i> Mettre à jour' : 'Ajouter au panier 🛒';
        
        let options = [];
        try { options = JSON.parse(optionsJsonString || '[]'); } catch(e) {}
        
        let container = document.getElementById('optionsContainer');
        container.innerHTML = '';
        
        options.forEach((opt, index) => {
            let conditionAttr = opt.condition ? `data-condition='${JSON.stringify(opt.condition).replace(/'/g, "&#39;")}'` : '';
            let html = `<div class="modal-option-group" ${conditionAttr}>
                <label class="modal-option-label">${opt.titre} :</label>
                <select class="option-select modal-option-select" data-titre="${opt.titre}" onchange="updateConditionalOptions()" required>
                    <option value="">-- Sélectionnez votre choix --</option>`;
            opt.choix.forEach(choix => {
                html += `<option value="${opt.titre}: ${choix}">${choix}</option>`;
            });
            html += `</select></div>`;
            container.innerHTML += html;
        });
        
        // Force la vérification des conditions dès l'ouverture
        setTimeout(updateConditionalOptions, 0);
    }
    
    function closeOptionsModal() {
        document.getElementById('optionsModal').style.display = 'none';
    }
    
    // Masque ou affiche les options en fonction du plat sélectionné
    function updateConditionalOptions() {
        let currentSelections = {};
        
        document.querySelectorAll('.option-select').forEach(select => {
            if (!select.disabled && select.value !== "") {
                let parts = select.value.split(': ');
                parts.shift(); // Enlève le préfixe
                currentSelections[select.dataset.titre] = parts.join(': '); 
            }
        });

        document.querySelectorAll('.modal-option-group').forEach(group => {
            let conditionStr = group.getAttribute('data-condition');
            
            if (conditionStr && conditionStr !== 'undefined') {
                let condition = JSON.parse(conditionStr);
                let isVisible = true;
                
                for (let key in condition) {
                    if (!currentSelections[key] || !condition[key].includes(currentSelections[key])) {
                        isVisible = false;
                    }
                }
                
                group.style.display = isVisible ? 'block' : 'none';
                
                let select = group.querySelector('select');
                if (select) {
                    select.disabled = !isVisible;
                    if (!isVisible) select.value = ""; // Remet le select à vide s'il est masqué
                }
            }
        });
    }

    // Envoie les données au panier
    function submitOptionsMenu() {
        let selects = document.querySelectorAll('.option-select');
        let optionsChoisies = [];
        let valid = true;
        
        selects.forEach(sel => {
            if (!sel.disabled) { // Seulement si le select n'est pas caché
                if(sel.value === "") valid = false;
                else optionsChoisies.push(sel.value);
            }
        });
        
        if(!valid) {
            alert("Veuillez remplir toutes les options du menu.");
            return;
        }
        
        let formData = new FormData();
        formData.append('id_produit', document.getElementById('modalProductId').value);
        formData.append('quantite', 1);
        formData.append('prix_miams', document.getElementById('modalPrixMiams').value);
        formData.append('cart_index', document.getElementById('modalCartIndex').value);
        formData.append('options_dispos', document.getElementById('modalOptionsDispos').value);
        
        // Envoi des options sous forme de tableau PHP (options[])
        optionsChoisies.forEach(opt => {
            formData.append('options[]', opt);
        });
        
        fetch('/api/ajouter_panier.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) window.location.href = '/api/panier.php';
            else alert("Erreur : " + data.message);
        })
        .catch(err => {
            console.error("Erreur d'ajout :", err);
            alert("Une erreur est survenue lors de l'ajout au panier. Veuillez vérifier votre connexion.");
        });
    }
</script>

<script src="/public/js/script.js"></script>
<script src="/public/js/cookie-consent.js"></script>
<?php if (isset($additionalJs)): ?>
    <?php foreach ($additionalJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>