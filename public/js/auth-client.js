// public/js/auth-client.js

/**
 * Fonction utilitaire pour récupérer les utilisateurs stockés
 * Retourne un tableau d'objets
 */
function getUsers() {
    const usersJSON = localStorage.getItem('yumland_users');
    return usersJSON ? JSON.parse(usersJSON) : [];
}

/**
 * GESTION DE L'INSCRIPTION
 */
const formInscription = document.querySelector('form');

// On vérifie qu'on est bien sur la page d'inscription (présence du champ "nom")
if (formInscription && document.getElementById('nom')) {

    formInscription.addEventListener('submit', (e) => {
        e.preventDefault(); // Bloque l'envoi classique

        // 1. Récupérer les valeurs
        const newUser = {
            nom: document.getElementById('nom').value,
            prenom: document.getElementById('prenom').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value // En vrai projet, on ne stocke jamais ça en clair !
        };

        // 2. Vérifier si l'email existe déjà
        const users = getUsers();
        const userExists = users.find(u => u.email === newUser.email);

        if (userExists) {
            alert("Cet email est déjà utilisé !");
            return;
        }

        // 3. Sauvegarder dans le LocalStorage
        users.push(newUser);
        localStorage.setItem('yumland_users', JSON.stringify(users));

        // 4. Feedback et Redirection
        alert("Compte créé avec succès (Local) !");
        window.location.href = "connexion.html";
    });
}

/**
 * GESTION DE LA CONNEXION
 */
// On vérifie qu'on est sur la page de connexion (présence du champ "email-login")
if (formInscription && document.getElementById('email-login')) {

    formInscription.addEventListener('submit', (e) => {
        e.preventDefault();

        const emailInput = document.getElementById('email-login').value;
        const passInput = document.getElementById('password-login').value;

        // 1. Récupérer la "base de données" locale
        const users = getUsers();

        // 2. Chercher l'utilisateur
        const validUser = users.find(u => u.email === emailInput && u.password === passInput);

        if (validUser) {
            // 3. Simuler une session (optionnel, pour l'affichage du profil plus tard)
            sessionStorage.setItem('currentUser', JSON.stringify(validUser));

            alert(`Bonjour ${validUser.prenom}, connexion réussie !`);
            window.location.href = "profil.html";
        } else {
            alert("Email ou mot de passe incorrect.");
        }
    });
}