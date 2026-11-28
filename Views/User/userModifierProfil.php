<section class="profile-page">
    <h1><?= $showAccountFields ? 'Modifier mon profil' : 'Compléter mon profil sportif' ?></h1>
    <p><?= $showAccountFields ? 'Modifie ici les informations de ton compte et ton profil sportif.' : 'Renseigne tes informations et les sports que tu pratiques. Tu pourras les modifier ensuite dans ton profil.' ?></p>
    <?php require('Views/User/formErrors.php'); ?>
    <form action="<?= $showAccountFields ? '/profil/modifier' : '/profil' ?>" method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($showAccountFields): ?>
            <fieldset>
                <legend>Informations du compte</legend>
                <div class="profile-grid">
                    <div class="espace">
                        <label for="first_name">Prénom</label>
                        <input type="text" name="first_name" id="first_name" autocomplete="given-name" maxlength="100" value="<?= htmlspecialchars($account['first_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="espace">
                        <label for="last_name">Nom</label>
                        <input type="text" name="last_name" id="last_name" autocomplete="family-name" maxlength="100" value="<?= htmlspecialchars($account['last_name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="espace">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" name="email" id="email" autocomplete="username" maxlength="254" value="<?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Changer de mot de passe (facultatif)</legend>
                <p>Laisse ces champs vides pour conserver ton mot de passe.</p>
                <div class="profile-grid">
                    <div class="espace">
                        <label for="current_password">Mot de passe actuel</label>
                        <input type="password" name="current_password" id="current_password" autocomplete="current-password">
                    </div>
                    <div class="espace">
                        <label for="new_password">Nouveau mot de passe</label>
                        <input type="password" name="new_password" id="new_password" minlength="4" autocomplete="new-password">
                    </div>
                    <div class="espace">
                        <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" minlength="4" autocomplete="new-password">
                    </div>
                </div>
            </fieldset>
        <?php endif; ?>
        <fieldset>
            <legend>Informations sportives générales</legend>
            <div class="profile-grid">
                <div class="espace">
                    <label for="birth_date">Date de naissance</label>
                    <input type="date" name="birth_date" id="birth_date" min="1900-01-01" max="<?= htmlspecialchars((new DateTimeImmutable($today))->modify('-1 day')->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>" autocomplete="bday" value="<?= htmlspecialchars($profile['birth_date'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="espace">
                    <label for="height_cm">Taille (cm)</label>
                    <input type="number" name="height_cm" id="height_cm" min="50" max="300" step="1" value="<?= htmlspecialchars($profile['height_cm'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="espace">
                    <label for="weight_kg">Poids (kg)</label>
                    <input type="number" name="weight_kg" id="weight_kg" min="0.01" max="999.99" step="0.01" value="<?= htmlspecialchars($profile['weight_kg'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="espace">
                    <label for="experience_level">Niveau de pratique</label>
                    <select name="experience_level" id="experience_level" required>
                        <?php foreach ($levels as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $profile['experience_level'] === $code ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Mes sports</legend>
            <p>Sélectionne au moins un sport et sa fréquence habituelle.</p>
            <?php foreach ($sportLabels as $code => $label): ?>
                <label class="checkbox-label" for="sport_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="checkbox" name="sports[]" id="sport_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" data-sport-toggle="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= isset($sports[$code]) ? 'checked' : '' ?>>
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <fieldset data-sport-fields="cycling">
            <legend>Cyclisme</legend>
            <div class="profile-grid">
                <div class="espace">
                    <label for="cycling_frequency">Séances par semaine</label>
                    <input type="number" name="cycling_frequency" id="cycling_frequency" min="1" max="21" step="1" value="<?= htmlspecialchars((string) ($sports['cycling']['weekly_sessions'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="espace">
                    <label for="practice_type">Type de pratique (facultatif)</label>
                    <select name="practice_type" id="practice_type">
                        <option value="">Non renseigné</option>
                        <?php foreach ($practiceTypes as $code => $label): ?>
                            <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= ($sports['cycling']['practice_type'] ?? '') === $code ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="espace">
                    <label for="ftp_w">Puissance de référence / FTP (watts, facultatif)</label>
                    <input type="number" name="ftp_w" id="ftp_w" min="1" max="2000" value="<?= htmlspecialchars($profile['ftp_w'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="espace">
                    <label for="max_heart_rate_bpm">Fréquence cardiaque maximale (bpm, facultatif)</label>
                    <input type="number" name="max_heart_rate_bpm" id="max_heart_rate_bpm" min="1" max="300" value="<?= htmlspecialchars($profile['max_heart_rate_bpm'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="espace">
                    <label for="resting_heart_rate_bpm">Fréquence au repos (bpm, facultatif)</label>
                    <input type="number" name="resting_heart_rate_bpm" id="resting_heart_rate_bpm" min="1" max="300" value="<?= htmlspecialchars($profile['resting_heart_rate_bpm'], ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <p class="muted">Laisse les mesures vides si tu ne les connais pas.</p>
        </fieldset>

        <fieldset data-sport-fields="strength">
            <legend>Musculation</legend>
            <div class="espace">
                <label for="strength_frequency">Séances par semaine</label>
                <input type="number" name="strength_frequency" id="strength_frequency" min="1" max="21" step="1" value="<?= htmlspecialchars((string) ($sports['strength']['weekly_sessions'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <p>Groupes principalement travaillés (facultatif)</p>
            <div class="profile-grid">
                <?php foreach ($muscleLabels as $code => $label): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="muscle_groups[]" value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($code, $muscleGroups, true) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <div class="profile-actions">
            <button type="submit">Enregistrer mon profil</button>
            <a href="<?= $profileComplete ? '/profil' : '/home' ?>">Annuler</a>
        </div>
    </form>
</section>
