<a class="skip-link" href="#main-content">Aller au contenu</a>
<nav class="container" aria-label="Navigation principale">
    <a class="brand" href="/home">PeackLog</a>
    <div class="nav-user">
        <?php if (isset($_SESSION['user'])): ?>
            <span>Bonjour <?= htmlspecialchars($_SESSION['user']['prenom'], ENT_QUOTES, 'UTF-8') ?></span>
            <a href="/profil">Mon profil</a>
            <form action="/deconnexion" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Se déconnecter</button>
            </form>
        <?php else: ?>
            <a href="/connexion">Connexion</a>
            <a href="/inscription">Inscription</a>
        <?php endif; ?>
    </div>
</nav>
