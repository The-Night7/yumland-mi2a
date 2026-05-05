// public/js/cookie-consent.js

document.addEventListener("DOMContentLoaded", () => {
    // 1. On crée le code HTML du bandeau
    const bannerHTML = `
        <div id="cookieBanner" class="cookie-banner">
            <div class="cookie-content">
                <strong>🍪 Gestion des cookies</strong>
                <p>Nous utilisons des cookies techniques nécessaires au bon fonctionnement du site (gestion du panier, connexion). En poursuivant votre navigation, vous acceptez l'utilisation de ces cookies essentiels.</p>
            </div>
            <div class="cookie-actions">
                <button id="btnRefuseCookies" class="btn btn-secondary-action btn-cookie-refuse">Refuser</button>
                <button id="btnAcceptCookies" class="btn btn-cookie-accept">Accepter</button>
            </div>
        </div>
    `;

    // 2. On injecte le bandeau dans le document
    document.body.insertAdjacentHTML('beforeend', bannerHTML);

    const banner = document.getElementById("cookieBanner");
    
    // 3. On vérifie si l'utilisateur a déjà répondu dans le passé
    if (!localStorage.getItem("cookieConsent")) {
        // On l'affiche avec un petit délai pour l'animation
        setTimeout(() => banner.classList.add("show"), 500);
    }

    // 4. Écouteurs de clics pour accepter/refuser
    document.getElementById("btnAcceptCookies").addEventListener("click", () => {
        localStorage.setItem("cookieConsent", "accepted");
        banner.classList.remove("show");
    });

    document.getElementById("btnRefuseCookies").addEventListener("click", () => {
        localStorage.setItem("cookieConsent", "refused");
        banner.classList.remove("show");
    });
});