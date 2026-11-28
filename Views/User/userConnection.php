<section class="auth-page">
    <h1>Connexion</h1>
    <p>Retrouve ton espace PeackLog.</p>
    <?php if (!empty($_SESSION['registration_success'])): ?>
        <p class="form-success" role="status">Ton compte est créé. Tu peux maintenant te connecter.</p>
        <?php unset($_SESSION['registration_success']); ?>
    <?php endif; ?>
    <?php require('Views/User/formErrors.php'); ?>
    <form action="/connexion" method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="espace">
            <label for="adressMail">Adresse e-mail</label>
            <input type="email" name="adressMail" id="adressMail" autocomplete="username" maxlength="254" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="espace">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>
        </div>
        <button type="submit" name="envoyer">Se connecter</button>
    </form>
    <p>Pas encore de compte ? <a href="/inscription">Créer un compte</a></p>
</section>
