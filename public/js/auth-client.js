// public/js/auth-client.js

document.addEventListener("DOMContentLoaded", () => {
    // 1. On cible le formulaire (Attention au tiret, j'ai mis login-form !)
    const loginForm = document.getElementById("login-form"); 

    // GESTION DE LA CONNEXION avec Fetch API
    if (loginForm) {
        loginForm.addEventListener("submit", async (e) => {
            // On empêche la page de se recharger brutalement
            e.preventDefault(); 

            // 2. Création dynamique de la zone de message si elle n'existe pas
            let messageBox = document.getElementById("messageBox");
            if (!messageBox) {
                messageBox = document.createElement("div");
                messageBox.id = "messageBox";
                messageBox.style.textAlign = "center";
                messageBox.style.marginBottom = "15px";
                messageBox.style.fontWeight = "bold";
                loginForm.parentNode.insertBefore(messageBox, loginForm);
            }

            // 3. On récupère les données
            const formData = new FormData(loginForm);

            try {
                // 4. L'appel asynchrone ultra-léger vers le serveur
                const response = await fetch("../../api/login.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // C'est validé !
                    messageBox.innerHTML = "<p style='color:green;'>✅ Connexion réussie ! Redirection...</p>";
                    
                    // Redirection selon le profil
                    setTimeout(() => {
                        if (data.role === 'Administrateur' || data.role === 'admin') {
                            window.location.href = "admin.html";
                        } else if (data.role === 'Livreur' || data.role === 'livreur') {
                            window.location.href = "livreur.html";
                        } else {
                            window.location.href = "profil.html";
                        }
                    }, 1000);

                } else {
                    // Erreur d'identifiants
                    messageBox.innerHTML = `<p style='color:red;'>❌ ${data.message}</p>`;
                }

            } catch (error) {
                console.error("Erreur de communication :", error);
                messageBox.innerHTML = "<p style='color:red;'>Erreur du serveur. Vérifiez que PHP tourne.</p>";
            }
        });
    }
});