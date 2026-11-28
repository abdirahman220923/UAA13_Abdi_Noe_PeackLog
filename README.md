# UAA13_Abdi_Noe_PeackLog

## Inscription et connexion

- `/inscription` : creation du compte et de son profil sportif, puis redirection vers la connexion.
- `/connexion` : ouverture de session, puis retour a l'accueil. `/connection` reste un alias.
- `/deconnexion` : fermeture de session via le bouton du menu (POST uniquement).
- `/profil` : premiere saisie du profil sportif, puis consultation du profil complet une fois renseigne.
- `/profil/modifier` : formulaire commun pour modifier les informations du compte et les informations sportives.

A la premiere connexion, un profil incomplet dirige vers `/profil`. Une fois la date de naissance, la taille, le poids, le niveau et au moins un sport enregistres, la page affiche le recapitulatif du compte et du profil sportif. La prochaine connexion dirige vers l'accueil. Le bouton « Modifier mon profil » ouvre ensuite un seul formulaire pour le prenom, le nom, l'e-mail et les donnees sportives. Le changement de mot de passe est facultatif et demande le mot de passe actuel ; le minimum reste de 4 caracteres pour la beta.

Les modifications du compte et du profil sont enregistrees dans la meme transaction. Une adresse e-mail deja utilisee refuse toute la modification. Les acces et mises a jour utilisent l'identifiant de session, jamais un identifiant recu du formulaire. Le prenom du menu est actualise apres sauvegarde. Aucune modification du schema SQL n'est necessaire pour cette evolution.

Le profil prend en charge cyclisme et musculation, avec une frequence hebdomadaire pour chacun. La pratique cycliste, la FTP, les frequences cardiaques et les groupes musculaires sont facultatifs. Les mesures sont historisees par date ; une correction le meme jour actualise la mesure du jour. Les anciens jours restent conserves. Les informations cyclistes precedentes sont conservees si le cyclisme est decoche.

Les tables `athlete_sports` et `athlete_muscle_groups` ont ete ajoutees a la base de developpement. `Config/Database/005_athlete_sports.sql` sert uniquement pour une autre installation anterieure a cette evolution ; ne pas le relancer sur la base deja mise a jour. `001_schema.sql` contient deja ces tables pour une installation neuve. La validation reste dans `userController.php`, les requetes dans `userModel.php`, le recapitulatif dans `Views/User/userProfil.php` et le formulaire dans `Views/User/userModifierProfil.php`.

Le code reste dans `Controllers/userController.php`, `Models/userModel.php` et `Views/User/`. `index.php` inclut le controleur utilisateur avant celui des pages generales. Aucune modification du SQL n'est necessaire avec le schema fourni.

Les mots de passe sont haches ; les formulaires utilisent un jeton CSRF et les requetes SQL sont preparees. Les erreurs conservent les champs saisis sauf les mots de passe. Une temporisation de connexion existe par session ; avant une mise en ligne publique, une limitation partagee entre sessions sera necessaire. La beta ne prevoit ni confirmation par e-mail ni mot de passe oublie. Aucune table de jetons de recuperation n'est necessaire.

Historique de la beta : la colonne `email_verified_at` a ete retiree directement de la base. Le script `004_remove_auth_tokens.sql` correspond au retrait de la table de jetons inutilisee. Ne pas relancer le script historique `003_remove_email_verification.sql` sur la base actuelle. Le schema `001_schema.sql` est deja a jour pour les nouvelles installations.

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

Les identifiants MySQL doivent etre renseignes dans `Config/dataBaseconnection.php` avant de lancer le site : utiliser les parametres de la connexion Workbench. Ne pas publier de vrais mots de passe dans Git. La connexion reelle reste a verifier avec ces identifiants.

## Lancement avec PHP Server

Ouvrir le dossier PeackLog dans VS Code puis `index.php`. Clic droit > **PHP Server: Serve project**. Si le serveur tourne deja : **PHP Server: Reload server**.

Le projet ne contient pas de `router.php` personnalise. PHP Server utilise son fonctionnement par defaut. Le controleur choisit la page et `Views/base.php` assemble son affichage. `.htaccess` sert uniquement si le site est heberge sous Apache ; PHP Server ne le lit pas.

`Views/Pages/` regroupe l'accueil et les futures pages principales et annexes. `Views/Components/` contient les elements partages, `Views/Admin/` les pages d'administration et `Views/Errors/` les pages d'erreur.

Les dossiers encore vides contiennent un fichier `.gitkeep` pour etre conserves par Git. Le retirer lorsqu'un vrai fichier est ajoute.

Les requetes utiliseront PDO prepare/execute et les vues echapperont les donnees avec `htmlspecialchars`. Les fonctionnalites metier seront developpees progressivement.
