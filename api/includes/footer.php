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
<div id="optionsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:var(--color-sauce-cream); padding:30px; border-radius:8px; max-width:500px; width:90%; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h2 id="modalMenuTitle" style="color:var(--color-grill-red); margin-top:0;">Composez votre menu</h2>
        <form id="optionsForm">
            <input type="hidden" id="modalProductId" name="id_produit" value="">
            <div id="optionsContainer" style="margin-top: 20px;"></div>
            
            <div style="display:flex; gap:10px; margin-top:25px;">
                <button type="button" onclick="closeOptionsModal()" style="padding:12px; border:1px solid #ccc; background:#eee; border-radius:4px; cursor:pointer; font-weight:bold;">Annuler</button>
                <button type="button" onclick="submitOptionsMenu()" class="btn-primary" style="flex:1; padding:12px;">Ajouter au panier 🛒</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Ouvre la fenêtre et génère les listes déroulantes
    function showOptionsModal(productId, productName, optionsJsonString) {
        document.getElementById('optionsModal').style.display = 'flex';
        document.getElementById('modalMenuTitle').innerText = productName;
        document.getElementById('modalProductId').value = productId;
        
        let options = [];
        try { options = JSON.parse(optionsJsonString || '[]'); } catch(e) {}
        
        let container = document.getElementById('optionsContainer');
        container.innerHTML = '';
        
        options.forEach((opt, index) => {
            let html = `<div style="margin-bottom: 15px;">
                <label style="font-weight:bold; display:block; margin-bottom:5px; color:var(--color-coal-black);">${opt.titre} :</label>
                <select class="option-select" style="width:100%; padding:10px; border:1px solid var(--color-stone-gray); border-radius:4px; font-family:inherit;" required>
                    <option value="">-- Sélectionnez votre choix --</option>`;
            opt.choix.forEach(choix => {
                html += `<option value="${opt.titre}: ${choix}">${choix}</option>`;
            });
            html += `</select></div>`;
            container.innerHTML += html;
        });
    }
    
    function closeOptionsModal() {
        document.getElementById('optionsModal').style.display = 'none';
    }

    // Envoie les données au panier
    function submitOptionsMenu() {
        let selects = document.querySelectorAll('.option-select');
        let optionsChoisies = [];
        let valid = true;
        
        selects.forEach(sel => {
            if(sel.value === "") valid = false;
            else optionsChoisies.push(sel.value);
        });
        
        if(!valid) {
            alert("Veuillez remplir toutes les options du menu.");
            return;
        }
        
        let formData = new FormData();
        formData.append('id_produit', document.getElementById('modalProductId').value);
        formData.append('quantite', 1);
        formData.append('options', JSON.stringify(optionsChoisies));
        
        fetch('/api/ajouter_panier.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) window.location.href = '/api/panier.php';
            else alert("Erreur : " + data.message);
        });
    }
</script>

<script src="/public/js/script.js"></script>
<?php if (isset($additionalJs)): ?>
    <?php foreach ($additionalJs as $js): ?>
        <script src="<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>