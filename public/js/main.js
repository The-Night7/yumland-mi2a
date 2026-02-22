
function filterDishes() {
    // 1. Récupérer le texte saisi
    const searchInput = document.getElementById('homeSearchInput');
    if (!searchInput) return;
    
    const query = searchInput.value.toLowerCase();

    // 2. Récupérer toutes les cartes de plats
    const dishes = document.querySelectorAll('.gallery-grid figure');
    let hasResults = false;

    // 3. Boucler sur chaque plat
    dishes.forEach(dish => {
        const title = dish.querySelector('figcaption').textContent.toLowerCase();

        // 4. Vérifier si le titre contient la recherche
        if (title.includes(query)) {
            dish.style.display = ""; // Afficher (reset CSS)
            hasResults = true;
        } else {
            dish.style.display = "none"; // Masquer
        }
    });

    // 5. Gérer le message "Aucun résultat"
    const noResultsMsg = document.getElementById('no-results');
    if (noResultsMsg) {
        if (hasResults) {
            noResultsMsg.style.display = 'none';
        } else {
            noResultsMsg.style.display = 'block';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    console.log("Main.js chargé");

    // --- GESTION DES ONGLETS (PROFIL) ---
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    if (tabButtons.length > 0) {
        console.log(`${tabButtons.length} onglets trouvés.`);

        // 1. Initialisation : Masquer les contenus non actifs
        tabContents.forEach(content => {
            if (content.classList.contains('active')) {
                content.style.display = 'block';
            } else {
                content.style.display = 'none';
            }
        });

        // 2. Écouteurs d'événements
        tabButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = btn.getAttribute('data-tab');
                console.log(`Clic sur l'onglet : ${targetId}`);

                // Masquer tous les contenus
                tabContents.forEach(content => {
                    content.style.display = 'none';
                    content.classList.remove('active');
                });

                // Désactiver tous les boutons
                tabButtons.forEach(b => b.classList.remove('active'));

                // Afficher le contenu cible
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.style.display = 'block';
                    targetContent.classList.add('active');
                } else {
                    console.error(`Contenu d'onglet non trouvé : ${targetId}`);
                }

                // Activer le bouton cliqué
                btn.classList.add('active');
            });
        });
    }

    // --- LOGIQUE PAGE ADMIN ---
    const isAminPage = document.querySelector('body.admin-container')
    if (isAminPage) {
        const mockUsers = [
            { id: 1, nom: "Admin", prenom: "Super", email: "admin@yumland.com", role: "admin" },
            { id: 2, nom: "Chef", prenom: "Gusteau", email: "chef@yumland.com", role: "restaurateur" },
            { id: 3, nom: "Rapido", prenom: "Luigi", email: "livreur@yumland.com", role: "livreur" },
            { id: 4, nom: "Dupont", prenom: "Jean", email: "jean@client.com", role: "client" }
        ];

        let users = JSON.parse(localStorage.getItem('yumland_users')) || mockUsers;

        if (!localStorage.getItem('yumland_users')) {
            localStorage.setItem('yumland_users', JSON.stringify(users));
        }

        const tbody = document.getElementById('user-list');
        const roleFilter = document.getElementById('role-filter');
        if (document.getElementById('total-users')) {
            document.getElementById('total-users').innerText = users.length;
        }

        const renderUsers = (role = 'all') => {
            if (!tbody) return;
            
            const filteredUsers = (role === 'all')
                ? users
                : users.filter(user => user.role === role);

            tbody.innerHTML = filteredUsers.map(user => `
                <tr>
                    <td>#${user.id || '?'}</td>
                    <td>${user.nom.toUpperCase()} ${user.prenom || ''}</td>
                    <td>${user.email}</td>
                    <td><span class="role-badge role-${user.role}">${user.role}</span></td>
                    <td class="actions-cell">
                        <button class="action-btn edit-btn" onclick="editUser('${user.email}')">Modifier</button>
                        <button class="action-btn delete-btn" onclick="deleteUser('${user.email}')">Supprimer</button>
                        <button class="action-btn hide-btn" onclick="hideUser('${user.email}')">Masquer</button>
                    </td>
                </tr>
            `).join('');
        };

        if (roleFilter) {
            roleFilter.addEventListener('change', (e) => renderUsers(e.target.value));
        }

        renderUsers(); // Premier rendu
    }
    
    // --- LOGIQUE CARTE (FILTRES) ---
    if (document.querySelector('.menu-table')) {
        applyFilters();
    }

    // --- LOGIQUE PROFIL (USER DATA) ---
    if (document.querySelector('.profile-container')) {
        let currentUserJSON = sessionStorage.getItem('currentUser');
        
        // Fallback sur localStorage si sessionStorage est vide (persistance auth-client.js)
        if (!currentUserJSON) {
            const localUser = localStorage.getItem('yumland_user');
            if (localUser) {
                console.log("Récupération de la session depuis localStorage");
                currentUserJSON = localUser;
                sessionStorage.setItem('currentUser', localUser); // Restaurer la session
            }
        }
        
        // Redirection si toujours non connecté
        if (!currentUserJSON) {
            console.warn("Aucun utilisateur connecté, redirection...");
            window.location.href = "connexion.html";
            return;
        }

        // Gestion Déconnexion
        const btnLogout = document.getElementById('btn-logout');
        if (btnLogout) {
            btnLogout.addEventListener('click', (e) => {
                e.preventDefault();
                console.log("Déconnexion...");
                sessionStorage.removeItem('currentUser');
                localStorage.removeItem('currentUser'); // Nettoyage complet
                localStorage.removeItem('yumland_user'); // Nettoyage auth-client.js
                
                alert("Vous avez été déconnecté avec succès. À bientôt chez Le Grand Miam !");
                window.location.href = "connexion.html";
            });
        }

        const user = JSON.parse(currentUserJSON);
        console.log("Utilisateur chargé :", user);

        // Affichage des points
        const miams = user.miams || user.points || 0;
        const pointsDisplay = document.getElementById('points-display');
        const statusDisplay = document.getElementById('status-display');

        if(pointsDisplay) pointsDisplay.textContent = miams;

        if (typeof getStatutFidelite === "function") {
            const statut = getStatutFidelite(miams);
            if(statusDisplay) {
                statusDisplay.textContent = statut.nom;
                statusDisplay.style.color = statut.couleur;
            }
        }

        // Injection des infos
        const welcomeTitle = document.getElementById('welcome-title');
        if (welcomeTitle) welcomeTitle.textContent = `Profil de ${user.prenom}`;
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if(el) el.value = val || '';
        };

        setVal('nom', user.nom);
        setVal('prenom', user.prenom);
        setVal('email', user.email);
        setVal('tel', user.tel);
        setVal('adresse', user.adresse);
        setVal('complement', user.complement);

        // Mode Nuit
        const nightBtn = document.createElement('button');
        nightBtn.innerHTML = "🌙";
        nightBtn.style = "position:fixed; bottom:20px; right:20px; z-index:1000; padding:15px; border-radius:50%; border:none; cursor:pointer; font-size:1.5rem; background:var(--color-secondary); color:var(--color-accent);";
        document.body.appendChild(nightBtn);

        nightBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            nightBtn.innerHTML = document.body.classList.contains('dark-mode') ? "☀️" : "🌙";
        });

        // Mise à jour du profil
        const profileForm = document.getElementById('profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => {
                e.preventDefault();
                user.nom = document.getElementById('nom').value;
                user.prenom = document.getElementById('prenom').value;
                user.tel = document.getElementById('tel').value;
                user.adresse = document.getElementById('adresse').value;
                user.complement = document.getElementById('complement').value;

                const updatedUserJSON = JSON.stringify(user);
                sessionStorage.setItem('currentUser', updatedUserJSON);
                // Mise à jour aussi pour auth-client.js si utilisé
                localStorage.setItem('yumland_user', updatedUserJSON);
                
                alert("Modifications enregistrées !");
            });
        }
    }

    // --- LOGIQUE LIVREUR ---
    if (document.querySelector('.delivery-app')) {
        renderDeliveries();

        const toggleBtn = document.getElementById('toggle-dark-mode');
        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');

                if(document.body.classList.contains('dark-mode')) {
                    toggleBtn.innerHTML = "☀️ MODE JOUR";
                } else {
                    toggleBtn.innerHTML = "🌙 MODE NUIT";
                }
            });
        }
    }

    // --- LOGIQUE NOTATION ---
    if (document.querySelector('.rating-container')) {
        const orderIdInput = document.getElementById('order-id');
        if (orderIdInput) orderIdInput.value = '#CMD-8854';
        
        const orderContentInput = document.getElementById('order-content');
        if (orderContentInput) orderContentInput.textContent = '2 Menus Burger, 1 Salade César, 2 Boissons';

        const setupStarRating = (containerId, hiddenInputId) => {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const stars = container.querySelectorAll('.star');
            const ratingInput = document.getElementById(hiddenInputId);

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = parseInt(star.getAttribute('data-value'));
                    if (ratingInput) ratingInput.value = value;

                    stars.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-value'));
                        if(sVal <= value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
        };

        setupStarRating('delivery-star-rating', 'delivery-rating-value');
        setupStarRating('food-star-rating', 'food-rating-value');

        const ratingForm = document.getElementById('rating-form');
        if (ratingForm) {
            ratingForm.addEventListener('submit', (e) => {
                e.preventDefault();
                // ... (Logique d'envoi d'avis simplifiée)
                alert("Merci pour votre avis ! À bientôt.");
                window.location.href = '../../index.html';
            });
        }
    }

    // --- LOGIQUE RESTAURATEUR ---
    if (document.querySelector('.kitchen-board')) {
        renderOrders();
    }
});

function editUser(email) {
    alert('Action "Modifier" non implémentée pour la phase 1.');
}

function hideUser(email) {
    alert('Action "Masquer" non implémentée pour la phase 1.');
}

function deleteUser(email) {
    if(confirm('Confirmer la suppression de cet utilisateur ?')) {
        let users = JSON.parse(localStorage.getItem('yumland_users'));
        users = users.filter(u => u.email !== email);
        localStorage.setItem('yumland_users', JSON.stringify(users));
        window.location.reload(); // Rechargement simple pour la phase 1
    }
}

function logout() {
    sessionStorage.removeItem('currentUser');
    window.location.href = 'connexion.html';
}

function applyFilters() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const searchQuery = searchInput.value.toLowerCase();
    const selectedCategory = document.getElementById('categoryFilter').value;
    const selectedSpec = document.getElementById('specFilter').value;

    document.querySelectorAll('.menu-table').forEach(table => {
        const tableCategory = table.getAttribute('data-category');
        let tableHasVisibleRows = false;
        let hasRows = false;

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            if (row.classList.contains('separator')) return;
            hasRows = true;

            const text = row.textContent.toLowerCase();
            const specCell = row.querySelector('td[data-spec]');
            const spec = specCell ? specCell.getAttribute('data-spec') : null;

            const searchMatch = text.includes(searchQuery);
            const specMatch = (selectedSpec === 'all' || spec === selectedSpec);

            if (searchMatch && specMatch) {
                row.style.display = '';
                tableHasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });

        if (selectedCategory === 'all') {
            table.style.display = tableHasVisibleRows ? '' : 'none';
        } else {
            if (selectedCategory === tableCategory) {
                 table.style.display = tableHasVisibleRows ? '' : 'none';
            } else {
                table.style.display = 'none';
            }
        }
    });
}

// Livreur page functions
const deliveries = [
    {
        id: 'CMD-002',
        client: 'M. Martin',
        address: '12 Rue du Port, Cergy',
        code: 'A123',
        floor: '3ème étage',
        tel: '06 01 02 03 04',
        comment: 'Sonner fort',
        total: '22.50€'
    },
    {
        id: 'CMD-005',
        client: 'Mme. Durand',
        address: "5 Bd de l'Oise, Cergy",
        code: 'Interphone B',
        floor: '1er étage',
        tel: '06 05 06 07 08',
        comment: '',
        total: '15.00€'
    }
];

function renderDeliveries() {
    const container = document.getElementById('deliveries-container');
    if (!container) return;

    if(deliveries.length === 0) {
        container.innerHTML = '<p style="text-align:center; padding:20px; font-weight:bold;">Aucune commande à livrer pour le moment.</p>';
        return;
    }

    container.innerHTML = deliveries.map(d => `
        <div class="delivery-card">
            <div class="delivery-card-header">
                <h3>${d.client}</h3>
                <span class="delivery-total">${d.total}</span>
            </div>

            <p>📍 <strong>Adresse:</strong> ${d.address}</p>
            <p>🔑 <strong>Accès:</strong> ${d.code} | ${d.floor}</p>
            <p class="delivery-tel">📞 ${d.tel} ${d.comment ? `| ⚠️ ${d.comment}` : ''}</p>

            <div class="delivery-actions">
                <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(d.address)}"
                   target="_blank" class="btn-livreur btn-gps">
                   🗺️ OUVRIR LE GPS
                </a>

                <button class="btn-livreur btn-validate" onclick="confirmDelivery('${d.id}')">
                   ✅ VALIDER LA LIVRAISON
                </button>
            </div>
        </div>
    `).join('');
}

function confirmDelivery(id) {
    if(confirm('Confirmez-vous la remise de la commande ' + id + ' au client ?')) {
        const index = deliveries.findIndex(d => d.id === id);
        if(index > -1) {
            deliveries.splice(index, 1);
            renderDeliveries();
            alert('Commande validée ! Bon travail.');
        }
    }
}

// Restaurateur page functions
const orders = [
    { id: 'CMD-001', items: ['Burger Grand Miam', 'Frites XL'], status: 'waiting', time: '12:30' },
    { id: 'CMD-002', items: ['Salade César', 'Coca Zéro'], status: 'prep', time: '12:32' },
    { id: 'CMD-003', items: ['Entrecôte Saignante', 'Haricots'], status: 'waiting', time: '12:35' }
];

function renderOrders() {
    const listWaiting = document.getElementById('list-waiting');
    const listPrep = document.getElementById('list-prep');
    const listReady = document.getElementById('list-ready');

    if (!listWaiting || !listPrep || !listReady) return;

    listWaiting.innerHTML = '';
    listPrep.innerHTML = '';
    listReady.innerHTML = '';

    orders.forEach((order, index) => {
        const card = document.createElement('div');
        card.className = 'order-card';

        let btnHtml = '';
        if(order.status === 'waiting') {
            btnHtml = `<button class="btn-move btn-start" onclick="updateStatus(${index}, 'prep')">Lancer Préparation</button>`;
        } else if (order.status === 'prep') {
            btnHtml = `<button class="btn-move btn-ready" onclick="updateStatus(${index}, 'ready')">Commande Prête</button>`;
        } else {
            btnHtml = `<div style="text-align:center; color:green; font-weight:bold;">En attente livreur</div>`;
        }

        card.innerHTML = `
                <div class="order-header">
                    <span>#${order.id}</span>
                    <span>${order.time}</span>
                </div>
                <ul class="order-items">
                    ${order.items.map(item => `<li>${item}</li>`).join('')}
                </ul>
                ${btnHtml}
            `;

        document.getElementById(`list-${order.status}`).appendChild(card);
    });
}

function updateStatus(index, newStatus) {
    orders[index].status = newStatus;
    renderOrders();
}
