<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Accueil', ENT_QUOTES, 'UTF-8') ?> — PeackLog</title>
    <link rel="stylesheet" href="/Assets/CSS/index.css">
    <script src="/Assets/JS/animation.js" defer></script>
</head>
<body>
    <header class="site-header">
        <?php require_once("Views/Components/header.php"); ?>
    </header>
    <main id="main-content" class="container">
        <?php require_once($template); ?>
    </main>
    <footer class="site-footer container">
        <?php require_once("Views/Components/footer.php"); ?>
    </footer>
</body>
</html>
