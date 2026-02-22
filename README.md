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

Le dépôt est organisé de manière modulaire :

```text
/
├── index.html              # Page d'accueil (Vitrine)
├── public/
│   ├── html/               # Pages de l'application (carte, profil, livreur, etc.)
│   ├── css/                # Feuille de style unique (Charte Oswald & Lato)
│   ├── js/                 # Logique d'interactivité (Auth & API Fetch)
│   └── images/             # Assets graphiques (Logo, Plats HD)
├── data/
│   └── user.json           # Structure de données simulée (Phase 1)
└── docs/
    └── charte_graphique.pdf # Identité visuelle officielle

```

## 🚀 Utilisation
1. **Installation** :
   ```bash
   git clone [https://github.com/the-night7/yumland-mi2a.git](https://github.com/the-night7/yumland-mi2a.git)

Projet réalisé dans le cadre de l'UE Informatique 4 - CY Tech - 2025/2026.
