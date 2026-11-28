<section class="auth-page">
    <h1>Créer un compte</h1>
    <p>Commence ton suivi avec PeackLog.</p>
    <?php require('Views/User/formErrors.php'); ?>
    <form action="/inscription" method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="espace">
            <label for="prenom">Prénom</label>
            <input type="text" name="prenom" id="prenom" autocomplete="given-name" maxlength="100" value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="espace">
            <label for="nom">Nom</label>
            <input type="text" name="nom" id="nom" autocomplete="family-name" maxlength="100" value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="espace">
            <label for="adressMail">Adresse e-mail</label>
            <input type="email" name="adressMail" id="adressMail" autocomplete="username" maxlength="254" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="espace">
            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" autocomplete="new-password" minlength="4" aria-describedby="password-help" required>
            <small id="password-help">4 caractères minimum. Maximum : 72 octets, selon les caractères utilisés.</small>
        </div>
        <div class="espace">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" minlength="4" required>
        </div>
        <button type="submit" name="envoyer">Créer mon compte</button>
    </form>
    <p>Déjà un compte ? <a href="/connexion">Se connecter</a></p>
</section>
