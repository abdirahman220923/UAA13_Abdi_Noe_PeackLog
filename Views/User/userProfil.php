<section class="profile-page">
    <h1>Mon profil</h1>
    <p>Les informations de ton compte et ton profil sportif sont réunis ici.</p>
    <?php if (!empty($_SESSION['profile_saved'])): ?>
        <p class="form-success" role="status">Ton profil a bien été enregistré.</p>
        <?php unset($_SESSION['profile_saved']); ?>
    <?php endif; ?>
    <p><a class="button-link" href="/profil/modifier">Modifier mon profil</a></p>

    <h2>Informations du compte</h2>
    <dl class="profile-details">
        <dt>Prénom</dt><dd><?= htmlspecialchars($account['first_name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Nom</dt><dd><?= htmlspecialchars($account['last_name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Adresse e-mail</dt><dd><?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?></dd>
    </dl>

    <h2>Profil sportif</h2>
    <dl class="profile-details">
        <dt>Date de naissance</dt><dd><?= htmlspecialchars((new DateTimeImmutable($profile['birth_date']))->format('d/m/Y'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt>Taille</dt><dd><?= htmlspecialchars($profile['height_cm'], ENT_QUOTES, 'UTF-8') ?> cm</dd>
        <dt>Poids</dt><dd><?= htmlspecialchars($profile['weight_kg'], ENT_QUOTES, 'UTF-8') ?> kg</dd>
        <dt>Niveau</dt><dd><?= htmlspecialchars($levels[$profile['experience_level']] ?? $profile['experience_level'], ENT_QUOTES, 'UTF-8') ?></dd>
    </dl>

    <h2>Sports pratiqués</h2>
    <?php foreach ($sports as $code => $sport): ?>
        <h3><?= htmlspecialchars($sportLabels[$code], ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= (int) $sport['weekly_sessions'] ?> séance(s) par semaine</p>
        <?php if ($code === 'cycling'): ?>
            <dl class="profile-details">
                <dt>Pratique</dt><dd><?= htmlspecialchars($practiceTypes[$sport['practice_type']] ?? 'Non renseignée', ENT_QUOTES, 'UTF-8') ?></dd>
                <dt>FTP</dt><dd><?= $profile['ftp_w'] !== '' ? htmlspecialchars($profile['ftp_w'], ENT_QUOTES, 'UTF-8') . ' W' : 'Non renseignée' ?></dd>
                <dt>Fréquence cardiaque maximale</dt><dd><?= $profile['max_heart_rate_bpm'] !== '' ? htmlspecialchars($profile['max_heart_rate_bpm'], ENT_QUOTES, 'UTF-8') . ' bpm' : 'Non renseignée' ?></dd>
                <dt>Fréquence au repos</dt><dd><?= $profile['resting_heart_rate_bpm'] !== '' ? htmlspecialchars($profile['resting_heart_rate_bpm'], ENT_QUOTES, 'UTF-8') . ' bpm' : 'Non renseignée' ?></dd>
            </dl>
        <?php elseif ($code === 'strength'): ?>
            <p>Groupes principalement travaillés :</p>
            <?php if ($muscleGroups === []): ?>
                <p class="muted">Non renseignés</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($muscleGroups as $muscle): ?>
                        <li><?= htmlspecialchars($muscleLabels[$muscle], ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <p><a href="/home">Retour à l'accueil</a></p>
</section>
