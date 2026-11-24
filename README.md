# PeackLog 🚴‍♂️💪

**Application web de suivi des sorties à vélo et des entraînements fitness.**

PeackLog est un projet scolaire développé par **Abdirahman et Noé** dans le cadre de l'**UAA13**. Il vise à réunir les activités sportives, leur historique et les indicateurs de progression dans une interface accessible sur ordinateur et mobile.

> Projet en développement. Les fonctionnalités décrites comme prévues constituent les objectifs du projet et ne sont pas toutes utilisables actuellement.

## Sommaire

- [Présentation](#présentation)
- [Fonctionnalités et avancement](#fonctionnalités-et-avancement)
- [Technologies](#technologies)
- [Organisation du projet](#organisation-du-projet)
- [Installation et lancement](#installation-et-lancement)
- [Base de données](#base-de-données)
- [Sécurité](#sécurité)
- [Travail en équipe](#travail-en-équipe)
- [Vérifications](#vérifications)
- [Auteurs et cadre scolaire](#auteurs-et-cadre-scolaire)

## Présentation

L'objectif de PeackLog est de permettre à un sportif de retrouver ses entraînements et de suivre son évolution au même endroit. Le projet associe le suivi des sorties à vélo à celui des séances de fitness et prévoit une présentation des statistiques utiles à la progression.

Les priorités sont une organisation du code compréhensible, une séparation claire des responsabilités et un développement progressif des fonctionnalités.

## Fonctionnalités et avancement

| Domaine | Objectif | État de la proposition de code initiale |
| --- | --- | --- |
| Accueil | Présenter l'application et son univers sportif | Page initiale présente |
| Architecture | Séparer configuration, contrôleurs, modèles et vues | Structure MVC présente |
| Base de données | Préparer le stockage des données sportives | Scripts SQL présents ; intégration à vérifier |
| Comptes | Inscription, connexion et gestion du profil | Vues préliminaires ; authentification à développer |
| Vélo | Enregistrer et consulter les trajets et performances | Prévu |
| Fitness | Suivre les séances et l'historique d'entraînement | Prévu |
| Statistiques | Visualiser l'évolution des performances | Prévu |
| Affichage mobile | Adapter l'interface aux différentes tailles d'écran | Premiers styles présents ; validation à poursuivre |
| Partage | Partager les performances entre utilisateurs | Prévu |

La proposition initiale du dossier local a été publiée dans la [pull request #15](https://github.com/abdirahman220923/UAA13_Abdi_Noe_PeackLog/pull/15), depuis la branche `noe/peacklog-update`. Consulter son état sur GitHub pour savoir si ce code a été intégré à `main`. Les sections techniques ci-dessous concernent cette proposition ; un clone de `main` ne contient ces fichiers qu'après leur intégration.

## Technologies

| Technologie | Rôle |
| --- | --- |
| PHP | Traitement des requêtes et génération des pages |
| PDO MySQL | Connexion à la base de données |
| MySQL | Stockage relationnel des données |
| HTML | Structure des pages |
| CSS | Mise en forme et adaptation de l'interface |
| JavaScript | Interactions côté navigateur |
| Git et GitHub | Historique, branches et revue du travail |

Le schéma SQL fourni cible **MySQL 8.4**, avec InnoDB et l'encodage `utf8mb4`. La version minimale de PHP reste à formaliser et à valider pour le projet.

## Organisation du projet

```text
PeackLog/
├── Assets/
│   ├── CSS/index.css
│   └── JS/animation.js
├── Config/
│   ├── dataBaseconnection.php
│   └── Database/
│       ├── 001_schema.sql
│       └── 002_reference_data.sql
├── Controllers/
│   └── pageController.php
├── Models/
│   └── pageModel.php
├── Views/
│   ├── Admin/
│   ├── Components/
│   ├── Errors/
│   ├── Pages/
│   ├── User/
│   └── base.php
├── images/
├── .gitignore
├── AGENTS.md
├── index.php
└── README.md
```

- **Config/** contient la connexion et les scripts de création de la base.
- **Controllers/** traite la requête et choisit la page à afficher.
- **Models/** accueille les accès aux données et les traitements liés aux entités.
- **Views/** contient les pages et les composants communs, notamment le header et le footer.
- **Assets/** regroupe les styles et les scripts du navigateur.
- **images/** accueille les ressources visuelles du projet.
- **index.php** constitue le point d'entrée de l'application.

Dans la base actuelle, `index.php` démarre la session, charge la connexion puis le contrôleur. Le contrôleur choisit une vue, et `Views/base.php` assemble le contenu avec les composants partagés. Les dossiers vides peuvent contenir un fichier `.gitkeep` pour être conservés dans Git.

## Installation et lancement

### 1. Préparer l'environnement

Disposer de Git, de PHP avec les extensions `PDO` et `pdo_mysql`, ainsi que d'un serveur MySQL compatible avec le schéma fourni. MySQL Workbench peut servir à exécuter les scripts et à inspecter les tables.

### 2. Récupérer le dépôt

```bash
git clone https://github.com/abdirahman220923/UAA13_Abdi_Noe_PeackLog.git
cd UAA13_Abdi_Noe_PeackLog
```

Pour examiner la proposition initiale avant son intégration, utiliser la branche correspondante :

```bash
git switch noe/peacklog-update
```

### 3. Préparer la base

Exécuter les scripts décrits dans la section [Base de données](#base-de-données), sur une instance de développement appropriée.

### 4. Configurer la connexion

La version préparée pour GitHub utilise les variables d'environnement du serveur PHP :

| Variable | Usage | Valeur par défaut |
| --- | --- | --- |
| `DB_HOST` | Adresse du serveur MySQL | `127.0.0.1` |
| `DB_PORT` | Port MySQL | `3306` |
| `DB_NAME` | Nom de la base | `PeackLog` |
| `DB_USER` | Utilisateur MySQL | Vide, à renseigner |
| `DB_PASSWORD` | Mot de passe MySQL | Vide, à renseigner si nécessaire |

Définir ces variables dans l'environnement qui lance PHP, puis redémarrer le serveur après modification. Le code actuel ne charge pas automatiquement un fichier `.env` ou `Config/database.local.php` : leur exclusion par Git ne constitue pas un mécanisme de chargement.

### 5. Lancer en développement

Depuis la racine du projet :

```bash
php -S localhost:8000
```

Ouvrir ensuite [http://localhost:8000/index.php](http://localhost:8000/index.php). Cette commande sert au développement local. Pour les adresses comme `/home` ou `/accueil`, le comportement de routage doit être vérifié selon le serveur choisi ; aucun routeur personnalisé n'est fourni.

Avec Visual Studio Code et l'extension PHP Server, il est également possible d'ouvrir `index.php`, puis de choisir **PHP Server: Serve project**. La connexion à MySQL est chargée dès l'entrée dans l'application et doit donc être configurée pour afficher la page.

## Base de données

Les scripts sont à exécuter dans cet ordre :

1. `Config/Database/001_schema.sql` : création de la base, des tables, contraintes et vues.
2. `Config/Database/002_reference_data.sql` : ajout des données de référence pour les badges.

Le schéma prépare notamment les comptes et profils, les activités, les mesures sportives, le matériel et sa maintenance, les objectifs, les plans d'entraînement, le calendrier, les interactions sociales et les badges.

La présence d'une table ne signifie pas que la fonctionnalité correspondante est déjà développée dans l'interface.

Le script initial contient un `CREATE DATABASE PeackLog` et vise une installation neuve. Il n'est pas conçu comme une migration à relancer sur une base existante. Vérifier la destination et sauvegarder les données existantes avant toute intervention. Les scripts ne fournissent aucun compte de démonstration et n'ont pas été exécutés pendant la publication initiale du code.

## Sécurité

- Conserver les mots de passe et identifiants dans l'environnement local du serveur.
- Vérifier les fichiers ajoutés avant chaque commit, y compris ceux qui seraient déjà suivis par Git.
- Exclure les configurations privées, les clés et les journaux du dépôt.
- Utiliser des requêtes préparées PDO pour les futurs accès aux données.
- Échapper les données affichées dans les vues avec `htmlspecialchars`.
- Prévoir la validation des entrées, le hachage des mots de passe et la protection des formulaires lors du développement de l'authentification.

Ces points constituent des règles de développement ; ils ne représentent pas un audit de sécurité complet de l'application.

## Travail en équipe

Les développements passent par une branche et une pull request vers `main`, afin que le partenaire puisse les relire avant intégration.

```bash
git switch main
git pull --ff-only origin main
git switch -c prenom/description-du-changement
```

Après les modifications :

```bash
git status
git diff
git add chemin/du/fichier
git commit -m "Décrit le changement réalisé"
git push -u origin prenom/description-du-changement
```

Créer ensuite une pull request sur GitHub, décrire les changements et les vérifications effectuées, puis attendre la validation du partenaire. Éviter les pushs forcés et conserver les modifications des autres contributeurs.

## Vérifications

Avant de proposer une modification :

- contrôler la syntaxe des fichiers PHP modifiés avec `php -l chemin/du/fichier.php` ;
- vérifier les pages concernées et leur affichage sur plusieurs tailles d'écran ;
- vérifier les chemins des ressources et les réponses aux URL inconnues ;
- tester la connexion à une base de développement lorsque le changement la concerne ;
- contrôler le contenu du commit et l'absence d'identifiants sensibles.

Lors de la préparation initiale, les **11 fichiers PHP** ont passé le contrôle de syntaxe. Le fonctionnement complet de l'application et l'import MySQL restent à vérifier dans l'environnement de développement. Aucun dispositif de tests automatisés n'est fourni dans cette première proposition.

## Auteurs et cadre scolaire

**Développeurs : Abdirahman et Noé**  
**Projet : UAA13 — PeackLog**  
**Échéance indiquée dans la présentation initiale : 5 juin 2026**

Le périmètre et l'avancement seront mis à jour au fil des développements et des validations communes.
