# 🥩 Le Grand Miam - Steakhouse & Grillades Premium

## 📖 Principe du Projet
"Le Grand Miam" est une application web complète pour une chaîne de restaurants de type "Steakhouse". Le projet offre une solution numérique multi-rôles qui couvre l'ensemble du parcours client et du flux opérationnel, de la consultation de la carte jusqu'à la livraison.

L'application repose sur une architecture dynamique PHP/MySQL où chaque interface (Client, Restaurateur, Livreur, Admin) est optimisée pour son terminal de destination (Desktop, Tablette ou Mobile).

---

## 👥 L'Équipe (Groupe MI-2A)
* **Myriam BENSAÏD** : The-Night7
* **Sheryne OUARGHI-MHIRI** : Souarghi
* **Kylian VANDEL** : Kylian-19

---

## 🛠️ Fonctionnalités Clés (Features)

### 👤 Profil Client
* **Menu Dynamique** : Consultation de la carte avec distinction des plats (Bœuf, Porc, Végétarien, Halal).
* **Panier d'Achat Interactif** : Ajout de produits avec gestion des options (cuisson, suppléments).
* **Paiement Sécurisé** : Intégration de la passerelle de paiement externe **CYBank** fournie par l'école.
* **Club de Fidélité "Le Grand Miam"** : Accumulation de points ("Miams") à chaque commande, avec une boutique dédiée pour les échanger contre des produits gratuits.
* **Espace Personnel** : Gestion du profil, consultation de l'historique des commandes avec possibilité de "Recommander" en un clic.

### 👨‍🍳 Profil Restaurateur (Optimisé Tablette)
* **Tableau de Bord "Kanban"** : Visualisation des commandes en temps réel sur 3 colonnes (En attente, En préparation, Prête).
* **Gestion des Statuts** : Passage simple et rapide d'une commande à l'étape suivante.
* **Détail des Commandes** : Affichage clair des plats et de leurs options (ex: cuisson de la viande).

### 🚴 Profil Livreur (Optimisé Mobile)
* **Interface Haute Visibilité** : Design "Mode Nuit" et forts contrastes pour une lecture en extérieur.
* **Ergonomie "Gants"** : Boutons surdimensionnés (hauteur > 60px) pour une manipulation facile.
* **Logistique Simplifiée** : Accès direct à Google Maps pour la navigation et bouton d'appel du client.

### 🛡️ Profil Administrateur (Desktop)
* **Tableau de Bord Complet** : Supervision de tous les utilisateurs, commandes et produits de la plateforme.

## 🏗️ Architecture Technique
*   **Langage Back-End** : PHP 8
*   **Base de Données** : MySQL (hébergée sur Aiven)
*   **Hébergement** : Vercel (avec configuration `vercel.json` pour le routing)
*   **Développement Local** : Serveur PHP interne avec un `router.php` simulant l'environnement Vercel.
*   **Paiement** : Interface externe CYBank (plateforme-smc.fr).

## 📅 Calendrier et Phases de Développement
Le projet est construit de manière modulaire, marquant l'évolution entre la conception initiale et le développement dynamique.

* **Phase 1 : Conception Graphique et Intégration Statique (Front-End)**
  * **Début :** 27/01/2026 - commit `9f9547b`
  * **Fin :** 22/02/2026 - commit `bab7de5`

* **Phase 2 : Serveur et Base de Données (Back-End)**
  * **Début :** 22/02/2026 - commit `0c5ee9ca`
  * **Fin :** 05/04/2026 - commit `c0d3718`
  
* **Phase 3 : Interactivité et Requêtes Asynchrones**
  * 

* **Phase 4 : Standardisation, Sécurité et Soutenance Finale**

## � Organisation du Projet

```text
📦 yumland-mi2a
├── 📂 api/                        # Cœur de l'application PHP
│   ├── 📂 admin/                # Scripts pour le tableau de bord Admin
│   ├── 📂 client/               # Scripts pour l'espace Client (profil, commandes...)
│   ├── 📂 includes/             # Fichiers de configuration et fonctions partagées (config, auth, BDD...)
│   ├── 📂 livreur/              # Interface dynamique du Livreur
│   ├── 📂 obsolete/             # Anciens fichiers de la Phase 1
│   ├── 📂 pages/                # Pages PHP principales (carte, connexion, inscription...)
│   ├── 📂 restaurateur/         # Interface dynamique de la Cuisine
│   ├── 📄 ajouter_panier.php    # Endpoint AJAX pour le panier
│   ├── 📄 commander.php         # Processus de commande et redirection vers CYBank
│   ├── 📄 index.php             # Page d'accueil dynamique
│   ├── 📄 init_db.php           # Script de création des tables SQL
│   ├── 📄 login.php             # Endpoint AJAX pour la connexion
│   ├── 📄 retour_paiement.php    # Endpoint de retour de CYBank
│   └── ...
├── 📂 consigne/                   # Documents du cahier des charges
├── 📂 docs/                       # Livrables (Charte graphique, CR...)
├── 📂 public/                     # Ressources Front-End (CSS, JS, images)
│   ├── 📂 css/
│   ├── 📂 images/
│   └── 📂 js/
├── 📄 .gitignore
├── 📄 README.md                   # Ce fichier
├── 📄 router.php                  # Routeur pour le développement local (simule Vercel)
└── 📄 vercel.json                 # Configuration de déploiement pour Vercel
```

## ⚙️ Prérequis (Phase 2)

Pour faire fonctionner l'application dynamique en local (spécialement sous Windows via WSL/Ubuntu), vous aurez besoin de :
* PHP 8.0 ou supérieur.
* MySQL (Serveur de base de données).
* Extension PHP-MySQL activée (`pdo_mysql`).

---

## 🚀 Installation et Lancement (Guide Complet)

### 1. Installation de l'environnement sous WSL (Ubuntu)
Ouvrez votre terminal WSL (Ubuntu) et installez les paquets nécessaires :
```bash
sudo apt update
sudo apt install php php-mysql mysql-server
```

Démarrez ensuite le service MySQL (indispensable sous WSL à chaque redémarrage) :
```bash
sudo service mysql start
```

### 2. Récupérer le projet
```bash
git clone [https://github.com/the-night7/yumland-mi2a.git](https://github.com/the-night7/yumland-mi2a.git)
cd yumland-mi2a
```

### 3. Configurer la base de données (MySQL)
L'application utilise un utilisateur MySQL dédié. Connectez-vous d'abord en administrateur :
```bash
sudo mysql
```

Puis copiez-collez ce bloc de commandes pour préparer votre base :
```sql
CREATE DATABASE IF NOT EXISTS yumland_mi2a CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER 'yumland'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Miam123!_Yumland';
GRANT ALL PRIVILEGES ON yumland_mi2a.* TO 'yumland'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Démarrer le serveur web
Dans le dossier racine du projet (là où se trouve `router.php`), lancez le serveur local :
```bash
php -S localhost:8000 router.php
```
*(Laissez ce terminal ouvert en arrière-plan pendant la navigation)*

Projet réalisé dans le cadre de l'UE Sciences - Module Informatique 4 - CY Tech - 2025/2026.
