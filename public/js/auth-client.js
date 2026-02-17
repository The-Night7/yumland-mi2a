/**
 * AUTH CLIENT - GESTION DE LA SESSION, DES RÔLES ET DES FORMULAIRES
 * Simule une base de données avec localStorage.
 */

// 1. DONNÉES PAR DÉFAUT (MOCK DATA)
const DEFAULT_USERS = [
    { id: 1, nom: "Dupont", prenom: "Jean", email: "client@yumland.com", password: "123", role: "client", points: 150, tel: "0601020304" },
    { id: 2, nom: "System", prenom: "Admin", email: "admin@yumland.com", password: "admin", role: "admin", points: 0 },
    { id: 3, nom: "Bocuse", prenom: "Paul", email: "chef@yumland.com", password: "chef", role: "restaurateur", points: 0 },
    { id: 4, nom: "Vitesse", prenom: "Max", email: "livreur@yumland.com", password: "go", role: "livreur", points: 0, secteur: "Cergy Préfecture" }
];

// 2. INITIALISATION ET HELPERS
function initDatabase() {
    if (!localStorage.getItem('yumland_users')) {
        console.log("Initialisation de la base de données locale...");
        localStorage.setItem('yumland_users', JSON.stringify(DEFAULT_USERS));
    }
}

function getUsers() {
    const usersJSON = localStorage.getItem('yumland_users');
    return usersJSON ? JSON.parse(usersJSON) : [];
}

// 3. FONCTIONS D'AUTHENTIFICATION
async function loginUser(email, password) {
    const users = getUsers();
    const user = users.find(u => u.email === email && u.password === password);

    if (user) {
        const { password, ...safeUser } = user;
        sessionStorage.setItem('currentUser', JSON.stringify(safeUser));
        return { success: true, user: safeUser };
    } else {
        return { success: false, message: "Email ou mot de passe incorrect." };
    }
}

function redirectBasedOnRole(role) {
    switch (role) {
        case 'admin': window.location.href = 'admin.html'; break;
        case 'restaurateur': window.location.href = 'restaurateur.html'; break;
        case 'livreur': window.location.href = 'livreur.html'; break;
        case 'client': default: window.location.href = 'profil.html'; break;
    }
}

// 4. GESTION DE LA SESSION
function logoutUser() {
    sessionStorage.removeItem('currentUser');
    window.location.href = 'connexion.html';
}

function getCurrentUser() {
    const user = sessionStorage.getItem('currentUser');
    return user ? JSON.parse(user) : null;
}

function isUserLoggedIn() {
    return getCurrentUser() !== null;
}

// 5. PROTECTION DES PAGES (GARDE)
function protectPage(allowedRoles) {
    const currentUser = getCurrentUser();
    if (!currentUser) {
        alert("Vous devez être connecté pour accéder à cette page.");
        window.location.href = 'connexion.html';
        return;
    }
    if (allowedRoles && allowedRoles.length > 0 && !allowedRoles.includes(currentUser.role)) {
        alert("Accès non autorisé.");
        redirectBasedOnRole(currentUser.role);
    }
}

// 6. AUTO-EXÉCUTION ET GESTIONNAIRES D'ÉVÉNEMENTS
initDatabase();

document.addEventListener('DOMContentLoaded', () => {
    // GESTIONNAIRE POUR LE FORMULAIRE DE CONNEXION
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const result = await loginUser(email, password);
            if (result.success) {
                console.log("Connexion réussie :", result.user.role);
                redirectBasedOnRole(result.user.role);
            } else {
                alert(result.message);
            }
        });
    }

    // GESTIONNAIRE POUR LE FORMULAIRE D'INSCRIPTION
    const inscriptionForm = document.querySelector('form');
    if (inscriptionForm && document.getElementById('nom')) { // Détecte si on est sur la page d'inscription
        inscriptionForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            const users = getUsers();
            if (users.find(u => u.email === email)) {
                alert("Cet email est déjà utilisé !");
                return;
            }

            const newUser = {
                id: users.length > 0 ? Math.max(...users.map(u => u.id)) + 1 : 1,
                nom,
                prenom,
                email,
                password,
                role: "client",
                points: 0,
                tel: ""
            };

            users.push(newUser);
            localStorage.setItem('yumland_users', JSON.stringify(users));

            alert("Compte créé avec succès ! Vous pouvez maintenant vous connecter.");
            window.location.href = "connexion.html";
        });
    }
});