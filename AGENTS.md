# Consignes permanentes du projet RoadCoach

## Architecture prioritaire

La structure des fichiers est un élément essentiel du TFE. Avant de développer les fonctionnalités, maintenir une architecture MVC claire, cohérente, détaillée et facile à expliquer lors de la défense.

## Nouveau départ obligatoire

RoadCoach doit être recommencé entièrement de zéro. Le brouillon actuellement présent dans le dossier ne constitue pas une base de code à modifier, nettoyer, migrer ou compléter.

Le brouillon peut uniquement servir de référence fonctionnelle pour comprendre les idées déjà explorées. Ne pas copier son code dans la nouvelle application. Concevoir chaque partie proprement dès le départ, avec une architecture validée, des responsabilités bien séparées, des conventions cohérentes et du code maintenable.

Le projet de référence de l'utilisateur se trouve dans :

`C:\Programmation\5TTI\Projet web\Project`

RoadCoach doit reprendre explicitement l'organisation générale de cette référence :

- `Config/` pour la configuration et la connexion à la base de données ;
- `Controllers/` pour le traitement des requêtes et la coordination ;
- `Models/` pour l'accès aux données et la logique liée aux entités ;
- `Views/` pour l'affichage, organisé par fonctionnalité ;
- `Views/Components/` pour les composants partagés comme le header et le footer ;
- `Assets/CSS/` pour les feuilles de style ;
- `Assets/JS/` pour le JavaScript ;
- un emplacement clair pour les images et autres ressources publiques ;
- un point d'entrée central `index.php`.

Cette référence concerne surtout la structure de fichiers et la lisibilité du MVC. Le nouveau projet doit conserver cette organisation reconnaissable tout en améliorant la qualité du code, la sécurité, le routage, les responsabilités de chaque couche et les conventions de nommage.

Ne pas commencer une réorganisation importante ou choisir une autre architecture sans en discuter avec l'utilisateur.
