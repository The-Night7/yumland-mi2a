# 🥩 Le Grand Miam - Steakhouse & Grillades Premium

## 📖 Principe du Projet
**Le Grand Miam** est une application web de restauration conçue pour une chaîne spécialisée dans les grillades. Le projet vise à offrir une solution numérique complète couvrant tout le cycle de vie d'une commande, de la prise de décision du client à la validation finale par le livreur. 

L'application repose sur une architecture multi-utilisateurs où chaque interface est optimisée pour son terminal de destination (Desktop, Tablette ou Mobile).

## 👥 L'Équipe (Groupe MI2A)
* **Myriam BENSAÏD** : The-Night7
* **Sheryne OUARGHI-MHIRI** : Souarghi
* **Kylian VANDEL** : Kylian-19

## 🛠️ Fonctionnalités Clés (Features)

### 👤 Profil Client
* **Menu Dynamique** : Consultation de la carte avec distinction des plats (Bœuf, Porc, Végétarien, Halal).
* **Système de Fidélité "Le Grand Miam Club"** : Accumulation de points (**Miams**) convertibles en produits offerts (1 € dépensé = 10 Miams).
* **Espace Membre** : Gestion des informations personnelles, historique de commandes et suivi du solde de points.

### 🍱 Profil Restaurateur (Tablette)
* **Flux de Commandes** : Réception et mise à jour du statut des commandes en cuisine (En préparation / Prête).
* **Inventaire** : Gestion simplifiée de la disponibilité des plats en temps réel.

### 🚴 Profil Livreur (Mobile)
* **Interface Haute Visibilité** : Design conçu pour la lecture en extérieur (forts contrastes sur fond crème).
* **Ergonomie "Gants"** : Zones de toucher élargies (minimum 60px) pour une manipulation sans retirer d'équipement.
* **Suivi Logistique** : Gestion des étapes de livraison de la récupération au client final.

### 🔑 Profil Administrateur (Desktop)
* **Gestion Globale** : Supervision des comptes utilisateurs et maintenance de la plateforme.

## 📁 Organisation du Projet

### 📅 Calendrier et Phases de Développement

* **Phase 1 : Conception Graphique et Intégration Statique (Front-End)**
  * **Début :** 27/01/2026 - 9f9547b
  * **Fin / Soutenance :** 22/02/2026 
  

* **Phase 2 : Serveur et Base de Données (Back-End)**

* **Phase 3 : Interactivité et Requêtes Asynchrones**

* **Phase 4 : Standardisation, Sécurité et Soutenance Finale**


### Le dépôt est organisé de manière modulaire :

```text
📦 yumland-mi2a
├── 📂 api/                        # (Préparation Phase 2) Scripts serveur PHP
│   └── 📄 index.php             # Point d'entrée de notre future API
├── 📂 consigne/                   # Documents et cahiers des charges officiels
├── 📂 data/                       # Données statiques de test (Mock)
│   └── 📄 user.json             # Simulation de la base de données (Phase 1)
├── 📂 docs/                       # Livrables et documents de conception de l'équipe
│   ├── 📄 Charte_graphique.pdf  # UI/UX, choix des couleurs (Rouge Grill, Noir Charbon...)
│   ├── 📄 Compte Rendu MI2.pdf  # Répartition des tâches au sein de l'équipe
│   └── 📄 Programme de fidélité.pdf # Concept d'innovation "Le Grand Miam Club"
├── 📂 public/                     # Ressources Front-End accessibles au client
│   ├── 📂 css/
│   │   ├── 📄 style.css         # Feuille de style principale commune
│   │   └── 📄 dark-mode.css     # Gestion du thème sombre (Innovation ergonomique)
│   ├── 📂 html/
│   │   ├── 📄 admin.html        # Interface Administrateur (Optimisée Desktop)
│   │   ├── 📄 carte.html        # Consultation du menu avec filtres
│   │   ├── 📄 connexion.html    # Authentification
│   │   ├── 📄 inscription.html  # Création de compte client
│   │   ├── 📄 livreur.html      # Interface Livreur (Mobile, gros boutons pour gants, fort contraste)
│   │   ├── 📄 mentions.html     # Mentions légales
│   │   ├── 📄 notation.html     # Retour d'expérience client
│   │   ├── 📄 profil.html       # Gestion du compte et des adresses
│   │   └── 📄 restaurateur.html # Interface Cuisine/Restaurateur (Optimisée Tablette)
│   ├── 📂 images/
│   │   ├── 📂 logo/             # Identité visuelle du restaurant
│   │   └── 📂 nourriture/       # Assets visuels des plats (Burger, Entrecôte...)
│   └── 📂 js/
│       └── 📄 auth-client.js    # (Préparation Phase 3) Scripts d'interactivité dynamique
├── 📄 index.html                  # Page d'accueil racine (Vitrine principale)
├── 📄 vercel.json                 # Configuration pour le déploiement continu
└── 📄 README.md                   # Présentation du projet et guide de démarrage
```

## 🚀 Utilisation
1. **Installation** :
   ```bash
   git clone [https://github.com/the-night7/yumland-mi2a.git](https://github.com/the-night7/yumland-mi2a.git)

Projet réalisé dans le cadre de l'UE Sciences - Module Informatique 4 - CY Tech - 2025/2026.
