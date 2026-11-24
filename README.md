# PeackLog 🚴‍♂️💪

Application web de suivi de trajets à vélo et d'entraînements fitness.

**Projet :** UAA13  
**Développeurs :** Abdirahman & Noé  
**Technologie :** PHP/MVC, HTML, CSS, JavaScript  
**Date limite :** 5 juin 2026  

## Fonctionnalités
- 🚴 Suivi des trajets à vélo
- 💪 Suivi des entraînements fitness/gym
- 📊 Statistiques et analytiques
- 👤 Authentification utilisateur
- 📱 Design responsive
- 🔄 Partage de performances

## Architecture et lancement

Architecture et technique PHP reprises du projet scolaire `Project`.

```text
PeackLog/
|-- Config/
|   |-- dataBaseconnection.php
|   `-- Database/                 Scripts SQL deja importes
|-- Controllers/
|   `-- pageController.php
|-- Models/
|   `-- pageModel.php
|-- Views/
|   |-- Components/
|   |   |-- header.php
|   |   `-- footer.php
|   |-- Pages/                   Accueil et futures pages principales
|   |   `-- pageAccueil.php
|   |-- Admin/                   Reserve aux futures pages admin
|   |-- Errors/
|   |   `-- http.php
|   `-- base.php
|-- User/                        Reserve aux futures pages utilisateur
|-- Assets/
|   |-- CSS/index.css
|   `-- JS/animation.js
|-- images/
`-- index.php
```

## Fonctionnement

1. `index.php` demarre la session, inclut la connexion et le controleur.
2. `Config/dataBaseconnection.php` cree directement `$pdo`.
3. `pageController.php` lit l'URL et inclut son modele.
4. Le modele contiendra des fonctions SQL recevant `$pdo`, comme `bikeModel.php` dans Project. Il reste vide tant qu'aucune requete n'est necessaire.
5. Le controleur choisit `$template` avec des `if` / `elseif`, puis inclut `Views/base.php`.
6. `base.php` inclut le header, `$template`, puis le footer.

Avant de lancer le site, definir les variables d'environnement DB_HOST, DB_PORT, DB_NAME, DB_USER et DB_PASSWORD dans le serveur PHP. Les valeurs par defaut sont 127.0.0.1, 3306 et PeackLog ; utilisateur et mot de passe restent vides. Aucun identifiant local n'est publie. La connexion reelle reste a verifier.

## Lancement avec PHP Server

Ouvrir le dossier PeackLog dans VS Code puis `index.php`. Clic droit > **PHP Server: Serve project**. Si le serveur tourne deja : **PHP Server: Reload server**.

Le projet ne contient pas de `router.php` personnalise. PHP Server utilise son fonctionnement par defaut. Le controleur choisit la page et `Views/base.php` assemble son affichage. `.htaccess` sert uniquement si le site est heberge sous Apache ; PHP Server ne le lit pas.

`Views/Pages/` regroupe l'accueil et les futures pages principales et annexes. `Views/Components/` contient les elements partages, `Views/Admin/` les pages d'administration et `Views/Errors/` les pages d'erreur.

Les dossiers encore vides contiennent un fichier `.gitkeep` pour etre conserves par Git. Le retirer lorsqu'un vrai fichier est ajoute.

Les requetes utiliseront PDO prepare/execute et les vues echapperont les donnees avec `htmlspecialchars`. Les fonctionnalites metier seront developpees progressivement.
