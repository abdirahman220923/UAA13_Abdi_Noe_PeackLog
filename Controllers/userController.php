<?php
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    require_once('Models/userModel.php');

    if (!in_array($uri, ['/inscription', '/connexion', '/connection', '/deconnexion', '/profil', '/profil/modifier'], true)) {
        return;
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $errors = [];
    $firstName = '';
    $lastName = '';
    $email = '';

    if (!in_array($method, ['GET', 'POST', 'HEAD'], true) || ($uri === '/deconnexion' && $method !== 'POST')) {
        http_response_code(405);
        header($uri === '/deconnexion' ? 'Allow: POST' : 'Allow: GET, HEAD, POST');
        $title = 'Methode non autorisee';
        $template = 'Views/Errors/http.php';
        require_once('Views/base.php');
        return;
    }

    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            $errors[] = 'Ce formulaire a expiré. Merci de réessayer.';
        }
    }

    if ($uri === '/profil' || $uri === '/profil/modifier') {
        if (!isset($_SESSION['user'])) {
            header('Location: /connexion', true, 303);
            exit;
        }
        $userId = (int) $_SESSION['user']['id'];
        $levels = ['beginner' => 'Débutant', 'intermediate' => 'Intermédiaire', 'advanced' => 'Avancé', 'expert' => 'Expert'];
        $sportLabels = ['cycling' => 'Cyclisme', 'strength' => 'Musculation'];
        $practiceTypes = ['road' => 'Route', 'mountain' => 'VTT', 'gravel' => 'Gravel', 'indoor' => 'Vélo en intérieur', 'other' => 'Autre'];
        $muscleLabels = ['chest' => 'Pectoraux', 'back' => 'Dos', 'shoulders' => 'Épaules', 'arms' => 'Bras', 'legs' => 'Jambes', 'core' => 'Abdominaux'];
        $today = (new DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')))->format('Y-m-d');
        try {
            $savedProfile = selectAthleteProfile($pdo, $userId);
            if (!$savedProfile) {
                unset($_SESSION['user']);
                header('Location: /connexion', true, 303);
                exit;
            }
            $profile = [];
            foreach (['birth_date', 'height_cm', 'experience_level', 'weight_kg', 'ftp_w', 'max_heart_rate_bpm', 'resting_heart_rate_bpm'] as $field) {
                $value = $savedProfile->$field;
                $profile[$field] = $value === null ? '' : (string) $value;
            }
            $profile['experience_level'] = $profile['experience_level'] ?: 'beginner';
            $sports = selectAthleteSports($pdo, $userId);
            $muscleGroups = selectAthleteMuscleGroups($pdo, $userId);
            $profileComplete = $savedProfile->birth_date !== null && $savedProfile->height_cm !== null
                && $savedProfile->weight_kg !== null && count($sports) > 0;
            $_SESSION['user']['profile_complete'] = $profileComplete;
            $showAccountFields = $profileComplete || $uri === '/profil/modifier';
            $account = ['first_name' => $savedProfile->first_name, 'last_name' => $savedProfile->last_name, 'email' => $savedProfile->email];

            if ($method === 'POST') {
                if ($showAccountFields) {
                    foreach (array_keys($account) as $field) {
                        $account[$field] = is_string($_POST[$field] ?? null) ? trim($_POST[$field]) : '';
                    }
                    $account['email'] = strtolower($account['email']);
                    if (!preg_match('/^.{1,100}$/us', $account['first_name']) || !preg_match('/^.{1,100}$/us', $account['last_name'])) {
                        $errors[] = 'Le prénom et le nom sont obligatoires (100 caractères maximum).';
                    }
                    if (strlen($account['email']) > 254 || !filter_var($account['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Renseigne une adresse e-mail valide.';
                    }
                    $account['new_password'] = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
                    $confirmation = is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : '';
                    if ($account['new_password'] !== '' || $confirmation !== '') {
                        $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
                        $currentUser = selectUserByEmail($pdo, $savedProfile->email);
                        if (!$currentUser || !password_verify($currentPassword, $currentUser->password_hash)) {
                            $errors[] = 'Le mot de passe actuel est incorrect.';
                        }
                        if (!preg_match('/^.{4,}$/us', $account['new_password']) || strlen($account['new_password']) > 72 || str_contains($account['new_password'], "\0")) {
                            $errors[] = 'Le nouveau mot de passe doit contenir au moins 4 caractères et au maximum 72 octets.';
                        }
                        if ($account['new_password'] !== $confirmation) {
                            $errors[] = 'Les deux nouveaux mots de passe doivent être identiques.';
                        }
                    }
                }
                foreach (array_keys($profile) as $field) {
                    $profile[$field] = is_string($_POST[$field] ?? null) ? trim($_POST[$field]) : '';
                }
                $sports = [];
                $selectedSports = $_POST['sports'] ?? [];
                if (!is_array($selectedSports) || $selectedSports === [] || count($selectedSports) > count($sportLabels)) {
                    $errors[] = 'Sélectionne au moins un sport valide.';
                    $selectedSports = [];
                }
                foreach ($selectedSports as $sportCode) {
                    if (!is_string($sportCode) || !isset($sportLabels[$sportCode])) {
                        $errors[] = 'Un des sports sélectionnés est invalide.';
                        continue;
                    }
                    $frequency = $_POST[$sportCode . '_frequency'] ?? '';
                    $practice = $sportCode === 'cycling' ? ($_POST['practice_type'] ?? '') : '';
                    $sports[$sportCode] = [
                        'weekly_sessions' => is_string($frequency) ? $frequency : '',
                        'practice_type' => is_string($practice) ? $practice : ''
                    ];
                    if (!is_string($frequency) || !ctype_digit($frequency) || (int) $frequency < 1 || (int) $frequency > 21) {
                        $errors[] = 'Indique entre 1 et 21 séances par semaine pour chaque sport.';
                    }
                    if ($sportCode === 'cycling' && (!is_string($practice) || ($practice !== '' && !isset($practiceTypes[$practice])))) {
                        $errors[] = 'Le type de pratique cycliste est invalide.';
                    }
                }
                $muscleGroups = [];
                if (isset($sports['strength'])) {
                    $selectedMuscles = $_POST['muscle_groups'] ?? [];
                    if (!is_array($selectedMuscles) || count($selectedMuscles) > count($muscleLabels)) {
                        $errors[] = 'Les groupes musculaires sont invalides.';
                    } else {
                        foreach ($selectedMuscles as $muscle) {
                            if (!is_string($muscle) || !isset($muscleLabels[$muscle])) {
                                $errors[] = 'Un groupe musculaire est invalide.';
                            } else {
                                $muscleGroups[] = $muscle;
                            }
                        }
                        $muscleGroups = array_values(array_unique($muscleGroups));
                    }
                }
                $birthDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $profile['birth_date'])
                    ? DateTimeImmutable::createFromFormat('!Y-m-d', $profile['birth_date']) : false;
                if (!$birthDate || $birthDate->format('Y-m-d') !== $profile['birth_date'] || $profile['birth_date'] >= $today || $profile['birth_date'] < '1900-01-01') {
                    $errors[] = 'Renseigne une date de naissance valide, antérieure à aujourd’hui.';
                }
                if (!ctype_digit($profile['height_cm']) || (int) $profile['height_cm'] < 50 || (int) $profile['height_cm'] > 300) {
                    $errors[] = 'La taille doit être comprise entre 50 et 300 cm.';
                }
                $profile['weight_kg'] = str_replace(',', '.', $profile['weight_kg']);
                if (!preg_match('/^\d{1,3}(\.\d{1,2})?$/', $profile['weight_kg']) || (float) $profile['weight_kg'] <= 0 || (float) $profile['weight_kg'] > 999.99) {
                    $errors[] = 'Indique un poids positif en kg, avec deux décimales maximum.';
                }
                if (!isset($levels[$profile['experience_level']])) {
                    $errors[] = 'Choisis un niveau de pratique valide.';
                }
                foreach (['ftp_w' => 2000, 'max_heart_rate_bpm' => 300, 'resting_heart_rate_bpm' => 300] as $field => $maximum) {
                    if (!isset($sports['cycling'])) {
                        $profile[$field] = $savedProfile->$field === null ? '' : (string) $savedProfile->$field;
                    }
                    if ($profile[$field] !== '' && (!ctype_digit($profile[$field]) || (int) $profile[$field] < 1 || (int) $profile[$field] > $maximum)) {
                        $errors[] = 'La FTP ou les fréquences cardiaques renseignées sont invalides.';
                    }
                }
                if ($profile['max_heart_rate_bpm'] !== '' && $profile['resting_heart_rate_bpm'] !== '' && (int) $profile['resting_heart_rate_bpm'] >= (int) $profile['max_heart_rate_bpm']) {
                    $errors[] = 'La fréquence au repos doit être inférieure à la fréquence maximale.';
                }
                if (empty($errors)) {
                    try {
                        saveAthleteProfile($pdo, $userId, $profile, $sports, $muscleGroups, $showAccountFields ? $account : []);
                        $_SESSION['user']['prenom'] = $account['first_name'];
                        $_SESSION['user']['profile_complete'] = true;
                        $_SESSION['profile_saved'] = true;
                        header('Location: /profil', true, 303);
                        exit;
                    } catch (PDOException $e) {
                        if ($e->getCode() !== '23000') {
                            throw $e;
                        }
                        $errors[] = 'Cette adresse e-mail est déjà utilisée. Aucune modification n’a été enregistrée.';
                    }
                }
            }
            $title = $profileComplete ? 'Modifier mon profil' : 'Compléter mon profil sportif';
            $template = 'Views/User/userModifierProfil.php';
            if ($profileComplete && $uri === '/profil' && $method !== 'POST') {
                $title = 'Mon profil';
                $template = 'Views/User/userProfil.php';
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
            http_response_code(503);
            $title = 'Profil temporairement indisponible';
            $template = 'Views/Errors/http.php';
        }
        require_once('Views/base.php');
        return;
    }

    if ($uri === '/deconnexion') {
        if (empty($errors)) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: /connexion', true, 303);
            exit;
        }
        $title = 'Déconnexion refusée';
        $template = 'Views/User/userConnection.php';
    } elseif (isset($_SESSION['user'])) {
        header('Location: /home', true, 303);
        exit;
    } else {
        if ($method === 'POST') {
            // Les champs recus doivent etre des chaines, jamais des tableaux.
            $firstName = is_string($_POST['prenom'] ?? null) ? trim($_POST['prenom']) : '';
            $lastName = is_string($_POST['nom'] ?? null) ? trim($_POST['nom']) : '';
            $email = is_string($_POST['adressMail'] ?? null) ? strtolower(trim($_POST['adressMail'])) : '';
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

            if ($uri === '/inscription') {
                $confirmation = is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : '';
                if (!preg_match('/^.{1,100}$/us', $firstName) || !preg_match('/^.{1,100}$/us', $lastName)) {
                    $errors[] = 'Le prénom et le nom sont obligatoires (100 caractères maximum).';
                }
                if (!preg_match('/^.{4,}$/us', $password) || strlen($password) > 72 || str_contains($password, "\0")) {
                    $errors[] = 'Choisis un mot de passe de 4 caractères minimum et de 72 octets maximum.';
                }
                if ($password !== $confirmation) {
                    $errors[] = 'Les deux mots de passe doivent être identiques.';
                }
            } elseif ($password === '' || strlen($password) > 72 || str_contains($password, "\0")) {
                $errors[] = 'Renseigne un mot de passe valide.';
            }

            if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Renseigne une adresse e-mail valide.';
            }

            // Temporisation simple par session apres plusieurs echecs de connexion.
            if ($uri !== '/inscription' && ($_SESSION['login_blocked_until'] ?? 0) > time()) {
                $errors[] = 'Trop de tentatives. Réessaie dans une minute.';
            }

            if (empty($errors)) {
                try {
                    $user = selectUserByEmail($pdo, $email);
                    if ($uri === '/inscription') {
                        if ($user) {
                            $errors[] = 'Impossible de créer ce compte avec cette adresse e-mail.';
                        } else {
                            insertUser($pdo, $firstName, $lastName, $email, $password);
                            $_SESSION['registration_success'] = true;
                            header('Location: /connexion', true, 303);
                            exit;
                        }
                    } else {
                        // Hash de remplacement pour verifier aussi un mot de passe si le compte est inconnu.
                        $hash = $user ? $user->password_hash : '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
                        $validPassword = password_verify($password, $hash);
                        if (!$user || !$validPassword || $user->status !== 'active') {
                            $errors[] = 'Adresse e-mail ou mot de passe incorrect.';
                            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                            if ($_SESSION['login_attempts'] >= 5) {
                                $_SESSION['login_blocked_until'] = time() + 60;
                                $_SESSION['login_attempts'] = 0;
                            }
                        } else {
                            $profileComplete = hasCompletedAthleteProfile($pdo, (int) $user->id);
                            updateUserLastLogin($pdo, (int) $user->id);
                            session_regenerate_id(true);
                            $_SESSION['user'] = ['id' => (int) $user->id, 'prenom' => $user->first_name, 'profile_complete' => $profileComplete];
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            unset($_SESSION['login_attempts'], $_SESSION['login_blocked_until']);
                            header('Location: ' . ($profileComplete ? '/home' : '/profil'), true, 303);
                            exit;
                        }
                    }
                } catch (PDOException $e) {
                    error_log($e->getMessage());
                    $errors[] = $e->getCode() === '23000'
                        ? 'Impossible de créer ce compte avec cette adresse e-mail.'
                        : 'Le service est indisponible. Merci de réessayer plus tard.';
                }
            }
        }
        $title = $uri === '/inscription' ? 'Inscription' : 'Connexion';
        $template = $uri === '/inscription' ? 'Views/User/userInscription.php' : 'Views/User/userConnection.php';
    }

    require_once('Views/base.php');
