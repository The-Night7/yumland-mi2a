/**
 * AUTH CLIENT - GESTION DE LA SESSION ET DES RÔLES (PHASE 1)
 * Version "Robustesse Maximale" pour garantir la connexion.
 */

// 1. DONNÉES PAR DÉFAUT (MOCK DATA)
// Ces données servent de référence absolue.
const DEFAULT_USERS = [
    {
        id: 1,
        nom: "Dupont",
        prenom: "Jean",
        email: "client@yumland.com",
        password: "123",
        role: "client",
        points: 150,
        tel: "0601020304",
        // Champs d'adresse ajoutés
        adresse: "12 Rue du Port, Cergy",
        complement: "Interphone A123, 3ème étage"
    },
    {
        id: 2,
        nom: "System",
        prenom: "Admin",
        email: "admin@yumland.com",
        password: "admin",
        role: "admin",
        points: 0,
        adresse: "",
        complement: ""
    },
    {
        id: 3,
        nom: "Bocuse",
        prenom: "Paul",
        email: "chef@yumland.com",
        password: "chef",
        role: "restaurateur",
        points: 0,
        adresse: "",
        complement: ""
    },
    {
        id: 4,
        nom: "Vitesse",
        prenom: "Max",
        email: "livreur@yumland.com",
        password: "go",
        role: "livreur",
        points: 0,
        secteur: "Cergy Préfecture",
        adresse: "",
        complement: ""
    }
];

// 2. INITIALISATION
function initDatabase() {
    console.log("🔄 Initialisation de la base de données...");
    try {
        // On force l'écriture pour être sûr que les données sont là
        localStorage.setItem('yumland_users', JSON.stringify(DEFAULT_USERS));
        console.log("✅ Base de données locale synchronisée.");
    } catch (e) {
        console.warn("⚠️ LocalStorage inaccessible. Le mode 'Mémoire seule' sera utilisé.");
    }
}

// 3. FONCTION DE CONNEXION (Blindée)
async function loginUser(email, password) {
    console.log(`🔑 Tentative de connexion pour : ${email}`);

    // Simulation réseau (très courte pour ne pas frustrer l'utilisateur)
    await new Promise(r => setTimeout(r, 100));

    // STRATÉGIE HYBRIDE :
    // 1. On essaie de lire le LocalStorage (pour voir les nouveaux inscrits éventuels)
    let users = [];
    try {
        const stored = localStorage.getItem('yumland_users');
        if (stored) users = JSON.parse(stored);
    } catch (e) {
        console.error(e);
    }

    // 2. Si LocalStorage vide ou buggé, on utilise DEFAULT_USERS en secours
    if (!users || users.length === 0) {
        console.log("⚠️ Utilisation des données de secours (DEFAULT_USERS).");
        users = DEFAULT_USERS;
    }

    // Recherche de l'utilisateur (insensible à la casse email)
    // On nettoie les chaînes pour éviter les espaces invisibles
    const cleanEmail = email.trim().toLowerCase();
    const cleanPass = password.trim();

    const user = users.find(u => u.email.toLowerCase() === cleanEmail && u.password == cleanPass);

    if (user) {
        console.log(`✅ Succès ! Rôle détecté : ${user.role}`);

        // 1. Préparer toutes les données nécessaires à la session (sans le mot de passe)
        const userSession = {
            id: user.id,
            nom: user.nom,
            prenom: user.prenom,
            email: user.email,
            tel: user.tel,
            points: user.points || 0,
            role: user.role,
            adresse: user.adresse || '',
            complement: user.complement || ''
        };

        // 2. Sauvegarde dans le stockage local pour la persistance
        localStorage.setItem('yumland_user', JSON.stringify(userSession));
        // 3. Sauvegarde également dans le stockage de session pour l'utilisation immédiate
        sessionStorage.setItem('currentUser', JSON.stringify(userSession));

        // 4. Redirection basée sur le rôle de l'utilisateur
        redirectBasedOnRole(userSession.role);

        return { success: true, user: userSession };
    } else {
        console.warn("❌ Échec : Identifiants invalides.");
        return { success: false, message: "Email ou mot de passe incorrect." };
    }
}

// 4. FONCTION DE REDIRECTION (Explicite)
function redirectBasedOnRole(role) {
    console.log(`🚀 Redirection demandée vers l'espace : ${role.toUpperCase()}`);

    let targetPage = 'profil.html'; // Par défaut

    switch (role) {
        case 'admin':
            targetPage = 'admin.html';
            break;
        case 'restaurateur':
            targetPage = 'restaurateur.html';
            break;
        case 'livreur':
            targetPage = 'livreur.html';
            break;
        case 'client':
            targetPage = 'profil.html';
            break;
        default:
            console.warn(`Rôle inconnu (${role}), redirection vers Profil.`);
            targetPage = 'profil.html';
            break;
    }

    console.log(`👉 Chargement de la page : ${targetPage}`);
    // Utilisation de location.assign pour être sûr que le navigateur traite la demande
    window.location.assign(targetPage);
}

/**
 * Vérifie si l'utilisateur est connecté et retourne ses infos
 * @returns {Object|null} L'objet utilisateur ou null
 */
const getConnectedUser = () => {
    const userString = localStorage.getItem('yumland_user');
    if (userString) {
        return JSON.parse(userString);
    }
    return null;
};

const updateMenuForUser = (user) => {
    // Cibler les éléments du DOM
    const btnLogin = document.querySelector('.btn-login'); // Ton bouton de connexion actuel
    
    if (btnLogin) {
        // Changer le texte et le lien
        btnLogin.textContent = 'Mon Profil';
        btnLogin.href = 'profil.html';
        
        // Optionnel : Ajouter un bouton de déconnexion
        const navUl = document.querySelector('.nav-links'); // Ta liste <ul>
        const liLogout = document.createElement('li');
        
        const btnLogout = document.createElement('a');
        btnLogout.textContent = 'Déconnexion';
        btnLogout.href = '#';
        btnLogout.style.cursor = 'pointer';
        
        // Gérer la déconnexion
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
        
        liLogout.appendChild(btnLogout);
        navUl.appendChild(liLogout);
    }
};

const logout = () => {
    // 1. Supprimer la sauvegarde
    localStorage.removeItem('yumland_user');
    // 2. Recharger la page ou aller à l'accueil
    window.location.href = '../../index.html';
};

// Exécution au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    const user = getConnectedUser();

    if (user) {
        console.log(`Bon retour, ${user.nom} !`);
        updateMenuForUser(user);
    } else {
        console.log("Utilisateur non connecté");
        // Optionnel : Rediriger vers connexion si la page est privée (ex: profil.html)
        // if (window.location.pathname.includes('profil.html')) {
        //    window.location.href = 'connexion.html';
        // }
    }
    
    const loginForm = document.getElementById('login-form');

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            // 1. Empêcher le rechargement de la page (comportement par défaut)
            event.preventDefault();

            // 2. Récupération des données du formulaire (Syntaxe ES6+)
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // 3. Appel de votre fonction de connexion
            const result = await loginUser(email, password);

            // 4. Gestion du résultat
            if (result.success) {
                // Utilisation de votre fonction de redirection déjà existante
                redirectBasedOnRole(result.user.role);
            } else {
                alert(result.message); // Simple alert pour le moment
            }
        });
    }
});

// Auto-init au chargement du fichier
initDatabase();