<section class="welcome" aria-labelledby="welcome-title">
    <p class="eyebrow">Entraînement · Suivi · Progression</p>
    <h1 id="welcome-title">Bienvenue sur PeackLog</h1>
    <p>Ton espace pour organiser tes entraînements et suivre ta progression.</p>
    <?php if (isset($_SESSION['user'])): ?>
        <?php if (!empty($_SESSION['profile_saved'])): ?>
            <p class="form-success" role="status">Ton profil a bien été enregistré.</p>
            <?php unset($_SESSION['profile_saved']); ?>
        <?php endif; ?>
        <p><a href="/profil">Consulter mon profil</a></p>
    <?php else: ?>
        <p><a href="/inscription">Créer un compte</a> ou <a href="/connexion">se connecter</a> pour commencer.</p>
    <?php endif; ?>
</section>
