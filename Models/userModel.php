<?php
    function selectUserByEmail(PDO $pdo, string $email): object|false
    {
        $query = $pdo->prepare('SELECT id, email, password_hash, first_name, last_name, status FROM users WHERE email = :email LIMIT 1');
        $query->execute(['email' => $email]);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    function insertUser(PDO $pdo, string $firstName, string $lastName, string $email, string $password): void
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->beginTransaction();
        try {
            $query = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash) VALUES (:first_name, :last_name, :email, :password_hash)');
            $query->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);
            $userId = (int) $pdo->lastInsertId();
            $query = $pdo->prepare('INSERT INTO athlete_profiles (user_id) VALUES (:user_id)');
            $query->execute(['user_id' => $userId]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    function updateUserLastLogin(PDO $pdo, int $userId): void
    {
        $query = $pdo->prepare('UPDATE users SET last_login_at = UTC_TIMESTAMP() WHERE id = :id');
        $query->execute(['id' => $userId]);
    }

    function selectAthleteProfile(PDO $pdo, int $userId): object|false
    {
        $query = $pdo->prepare('SELECT u.first_name, u.last_name, u.email, p.birth_date, p.height_cm, p.experience_level,
            m.weight_kg, m.ftp_w, m.max_heart_rate_bpm, m.resting_heart_rate_bpm
            FROM users u LEFT JOIN athlete_profiles p ON p.user_id = u.id
            LEFT JOIN athlete_measurements m ON m.id = (
                SELECT id FROM athlete_measurements WHERE user_id = u.id ORDER BY measured_on DESC, id DESC LIMIT 1
            ) WHERE u.id = :user_id AND u.status = :status');
        $query->execute(['user_id' => $userId, 'status' => 'active']);
        return $query->fetch(PDO::FETCH_OBJ);
    }

    function selectAthleteSports(PDO $pdo, int $userId): array
    {
        $query = $pdo->prepare('SELECT sport_code, weekly_sessions, practice_type FROM athlete_sports WHERE user_id = :user_id ORDER BY sport_code');
        $query->execute(['user_id' => $userId]);
        return $query->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    function selectAthleteMuscleGroups(PDO $pdo, int $userId): array
    {
        $query = $pdo->prepare('SELECT muscle_group FROM athlete_muscle_groups WHERE user_id = :user_id ORDER BY muscle_group');
        $query->execute(['user_id' => $userId]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    function hasCompletedAthleteProfile(PDO $pdo, int $userId): bool
    {
        $profile = selectAthleteProfile($pdo, $userId);
        return $profile && $profile->birth_date !== null && $profile->height_cm !== null
            && $profile->weight_kg !== null && count(selectAthleteSports($pdo, $userId)) > 0;
    }

    function saveAthleteProfile(PDO $pdo, int $userId, array $profile, array $sports, array $muscleGroups, array $account = []): void
    {
        $pdo->beginTransaction();
        try {
            // Verrouiller le proprietaire evite deux modifications simultanees du profil.
            $query = $pdo->prepare('SELECT id FROM users WHERE id = :user_id AND status = :status FOR UPDATE');
            $query->execute(['user_id' => $userId, 'status' => 'active']);
            if (!$query->fetchColumn()) {
                throw new RuntimeException('Compte indisponible.');
            }
            if ($account !== []) {
                $query = $pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name,
                    email = :email, password_hash = COALESCE(:password_hash, password_hash) WHERE id = :user_id');
                $query->execute([
                    'first_name' => $account['first_name'], 'last_name' => $account['last_name'],
                    'email' => $account['email'], 'user_id' => $userId,
                    'password_hash' => $account['new_password'] === '' ? null : password_hash($account['new_password'], PASSWORD_DEFAULT)
                ]);
            }
            $query = $pdo->prepare('INSERT INTO athlete_profiles (user_id) VALUES (:user_id) ON DUPLICATE KEY UPDATE user_id = athlete_profiles.user_id');
            $query->execute(['user_id' => $userId]);
            $query = $pdo->prepare('UPDATE athlete_profiles SET birth_date = :birth_date, height_cm = :height_cm, experience_level = :experience_level WHERE user_id = :user_id');
            $query->execute([
                'birth_date' => $profile['birth_date'], 'height_cm' => $profile['height_cm'],
                'experience_level' => $profile['experience_level'], 'user_id' => $userId
            ]);
            // Historique par jour : une correction le meme jour actualise sa mesure.
            $query = $pdo->prepare('INSERT INTO athlete_measurements
                (user_id, measured_on, weight_kg, ftp_w, max_heart_rate_bpm, resting_heart_rate_bpm)
                VALUES (:user_id, :measured_on, :weight_kg, :ftp_w, :max_hr, :rest_hr) AS new
                ON DUPLICATE KEY UPDATE weight_kg = new.weight_kg, ftp_w = new.ftp_w,
                max_heart_rate_bpm = new.max_heart_rate_bpm, resting_heart_rate_bpm = new.resting_heart_rate_bpm');
            $query->execute([
                'user_id' => $userId, 'measured_on' => (new DateTimeImmutable('now', new DateTimeZone('Europe/Brussels')))->format('Y-m-d'),
                'weight_kg' => $profile['weight_kg'], 'ftp_w' => $profile['ftp_w'] === '' ? null : $profile['ftp_w'],
                'max_hr' => $profile['max_heart_rate_bpm'] === '' ? null : $profile['max_heart_rate_bpm'],
                'rest_hr' => $profile['resting_heart_rate_bpm'] === '' ? null : $profile['resting_heart_rate_bpm']
            ]);
            $query = $pdo->prepare('DELETE FROM athlete_sports WHERE user_id = :user_id');
            $query->execute(['user_id' => $userId]);
            $query = $pdo->prepare('INSERT INTO athlete_sports (user_id, sport_code, weekly_sessions, practice_type) VALUES (:user_id, :sport_code, :weekly_sessions, :practice_type)');
            foreach ($sports as $sportCode => $sport) {
                $query->execute([
                    'user_id' => $userId, 'sport_code' => $sportCode, 'weekly_sessions' => $sport['weekly_sessions'],
                    'practice_type' => $sport['practice_type'] ?: null
                ]);
            }
            $query = $pdo->prepare('DELETE FROM athlete_muscle_groups WHERE user_id = :user_id');
            $query->execute(['user_id' => $userId]);
            $query = $pdo->prepare('INSERT INTO athlete_muscle_groups (user_id, muscle_group) VALUES (:user_id, :muscle_group)');
            foreach ($muscleGroups as $muscleGroup) {
                $query->execute(['user_id' => $userId, 'muscle_group' => $muscleGroup]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
