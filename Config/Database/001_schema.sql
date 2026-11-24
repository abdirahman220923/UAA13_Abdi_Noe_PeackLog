-- RoadCoach : nouvelle application, installation initiale uniquement.
-- Cible : MySQL 8.4 / InnoDB. Encodage UTF-8 complet. Dates/heures en UTC.
-- Ne pas importer dans la base du brouillon. Le nom distinct évite les collisions.
-- Aucun DROP, aucune désactivation des contraintes, aucun compte de démonstration.
-- Le DDL MySQL n'est pas transactionnel : une erreur nécessite une analyse avant reprise.
CREATE DATABASE PeackLog CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE PeackLog;
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'athlete',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT ck_users_role CHECK (role IN ('athlete', 'admin')),
    CONSTRAINT ck_users_status CHECK (status IN ('active', 'suspended')),
    CONSTRAINT ck_users_names CHECK (CHAR_LENGTH(TRIM(first_name)) > 0 AND CHAR_LENGTH(TRIM(last_name)) > 0)
) ENGINE=InnoDB;

CREATE TABLE athlete_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    birth_date DATE NULL,
    height_cm SMALLINT UNSIGNED NULL,
    experience_level VARCHAR(20) NOT NULL DEFAULT 'beginner',
    timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/Brussels',
    bio TEXT NULL,
    avatar_path VARCHAR(512) NULL,
    default_visibility VARCHAR(20) NOT NULL DEFAULT 'private',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_profiles_height CHECK (height_cm IS NULL OR height_cm BETWEEN 50 AND 300),
    CONSTRAINT ck_profiles_level CHECK (experience_level IN ('beginner', 'intermediate', 'advanced', 'expert')),
    CONSTRAINT ck_profiles_visibility CHECK (default_visibility IN ('private', 'followers', 'public'))
) ENGINE=InnoDB;

-- Historique des mesures : les nouvelles valeurs ne remplacent pas les anciennes.
CREATE TABLE athlete_measurements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    measured_on DATE NOT NULL,
    weight_kg DECIMAL(5,2) NULL,
    ftp_w SMALLINT UNSIGNED NULL,
    max_heart_rate_bpm SMALLINT UNSIGNED NULL,
    resting_heart_rate_bpm SMALLINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_measurements_user_date UNIQUE (user_id, measured_on),
    CONSTRAINT fk_measurements_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_measurements_weight CHECK (weight_kg IS NULL OR weight_kg > 0),
    CONSTRAINT ck_measurements_ftp CHECK (ftp_w IS NULL OR ftp_w > 0),
    CONSTRAINT ck_measurements_hr CHECK ((max_heart_rate_bpm IS NULL OR max_heart_rate_bpm BETWEEN 1 AND 300) AND (resting_heart_rate_bpm IS NULL OR resting_heart_rate_bpm BETWEEN 1 AND 300)),
    CONSTRAINT ck_measurements_hr_order CHECK (resting_heart_rate_bpm IS NULL OR max_heart_rate_bpm IS NULL OR resting_heart_rate_bpm < max_heart_rate_bpm),
    CONSTRAINT ck_measurements_value CHECK (weight_kg IS NOT NULL OR ftp_w IS NOT NULL OR max_heart_rate_bpm IS NOT NULL OR resting_heart_rate_bpm IS NOT NULL)
) ENGINE=InnoDB;

CREATE TABLE auth_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    purpose VARCHAR(30) NOT NULL,
    token_hash BINARY(32) NOT NULL COMMENT 'SHA-256 binaire du jeton aleatoire ; jamais le jeton brut',
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_auth_tokens_hash UNIQUE (token_hash),
    INDEX idx_auth_tokens_user_purpose (user_id, purpose),
    INDEX idx_auth_tokens_expiry (expires_at),
    CONSTRAINT fk_auth_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_auth_tokens_purpose CHECK (purpose IN ('email_verification', 'password_reset')),
    CONSTRAINT ck_auth_tokens_expiry CHECK (expires_at > created_at)
) ENGINE=InnoDB;

CREATE TABLE weekly_availabilities (
    user_id BIGINT UNSIGNED NOT NULL,
    weekday TINYINT UNSIGNED NOT NULL COMMENT 'ISO : lundi=1, dimanche=7',
    available_minutes SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, weekday),
    CONSTRAINT fk_availabilities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_availabilities_day CHECK (weekday BETWEEN 1 AND 7),
    CONSTRAINT ck_availabilities_duration CHECK (available_minutes BETWEEN 1 AND 1440)
) ENGINE=InnoDB;

CREATE TABLE wellness_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    recorded_on DATE NOT NULL,
    sleep_minutes SMALLINT UNSIGNED NULL,
    sleep_quality TINYINT UNSIGNED NULL,
    fatigue TINYINT UNSIGNED NULL,
    muscle_soreness TINYINT UNSIGNED NULL,
    stress TINYINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_wellness_user_date UNIQUE (user_id, recorded_on),
    CONSTRAINT fk_wellness_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_wellness_sleep CHECK (sleep_minutes IS NULL OR sleep_minutes <= 1440),
    CONSTRAINT ck_wellness_scores CHECK ((sleep_quality IS NULL OR sleep_quality BETWEEN 1 AND 10) AND (fatigue IS NULL OR fatigue BETWEEN 1 AND 10) AND (muscle_soreness IS NULL OR muscle_soreness BETWEEN 1 AND 10) AND (stress IS NULL OR stress BETWEEN 1 AND 10))
) ENGINE=InnoDB;

CREATE TABLE external_connections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    provider VARCHAR(30) NOT NULL,
    external_user_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    access_token_encrypted BLOB NOT NULL,
    refresh_token_encrypted BLOB NULL,
    token_expires_at DATETIME NULL,
    scopes TEXT NULL,
    last_synced_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_connections_user_provider UNIQUE (user_id, provider),
    CONSTRAINT uq_connections_external UNIQUE (provider, external_user_id),
    CONSTRAINT fk_connections_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_connections_provider CHECK (provider IN ('strava'))
) ENGINE=InnoDB;

CREATE TABLE activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    sport VARCHAR(20) NOT NULL DEFAULT 'cycling',
    activity_type VARCHAR(20) NOT NULL DEFAULT 'endurance',
    source VARCHAR(20) NOT NULL DEFAULT 'manual',
    external_activity_id VARCHAR(100) CHARACTER SET ascii COLLATE ascii_bin NULL,
    started_at DATETIME NOT NULL COMMENT 'UTC',
    timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/Brussels',
    duration_s INT UNSIGNED NOT NULL,
    moving_duration_s INT UNSIGNED NULL,
    distance_m DECIMAL(12,2) NOT NULL DEFAULT 0,
    elevation_gain_m DECIMAL(9,2) NULL,
    average_heart_rate_bpm SMALLINT UNSIGNED NULL,
    max_heart_rate_bpm SMALLINT UNSIGNED NULL,
    average_power_w SMALLINT UNSIGNED NULL,
    max_power_w SMALLINT UNSIGNED NULL,
    average_cadence_rpm SMALLINT UNSIGNED NULL,
    max_speed_mps DECIMAL(7,3) NULL,
    energy_kcal INT UNSIGNED NULL,
    perceived_exertion TINYINT UNSIGNED NULL COMMENT 'RPE : 1 facile, 10 maximal',
    feeling TINYINT UNSIGNED NULL COMMENT '1 tres mauvais, 10 excellent',
    temperature_c DECIMAL(5,2) NULL,
    weather VARCHAR(80) NULL,
    notes TEXT NULL,
    visibility VARCHAR(20) NOT NULL DEFAULT 'private',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_activities_owner UNIQUE (id, user_id),
    CONSTRAINT uq_activities_external UNIQUE (source, external_activity_id),
    INDEX idx_activities_user_date (user_id, started_at),
    INDEX idx_activities_feed (visibility, started_at),
    CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_activities_title CHECK (CHAR_LENGTH(TRIM(title)) > 0),
    CONSTRAINT ck_activities_sport CHECK (sport IN ('cycling', 'running', 'swimming', 'strength', 'other')),
    CONSTRAINT ck_activities_type CHECK (activity_type IN ('endurance', 'intervals', 'race', 'group', 'recovery', 'other')),
    CONSTRAINT ck_activities_source CHECK ((source = 'manual' AND external_activity_id IS NULL) OR (source = 'strava' AND external_activity_id IS NOT NULL)),
    CONSTRAINT ck_activities_visibility CHECK (visibility IN ('private', 'followers', 'public')),
    CONSTRAINT ck_activities_duration CHECK (duration_s > 0 AND (moving_duration_s IS NULL OR moving_duration_s <= duration_s)),
    CONSTRAINT ck_activities_distance CHECK (distance_m >= 0 AND (elevation_gain_m IS NULL OR elevation_gain_m >= 0)),
    CONSTRAINT ck_activities_hr CHECK ((average_heart_rate_bpm IS NULL OR average_heart_rate_bpm BETWEEN 1 AND 300) AND (max_heart_rate_bpm IS NULL OR max_heart_rate_bpm BETWEEN 1 AND 300) AND (average_heart_rate_bpm IS NULL OR max_heart_rate_bpm IS NULL OR average_heart_rate_bpm <= max_heart_rate_bpm)),
    CONSTRAINT ck_activities_power CHECK (average_power_w IS NULL OR max_power_w IS NULL OR average_power_w <= max_power_w),
    CONSTRAINT ck_activities_speed CHECK (max_speed_mps IS NULL OR max_speed_mps >= 0),
    CONSTRAINT ck_activities_scores CHECK ((perceived_exertion IS NULL OR perceived_exertion BETWEEN 1 AND 10) AND (feeling IS NULL OR feeling BETWEEN 1 AND 10))
) ENGINE=InnoDB;

-- Flux GPS et capteurs facultatifs ; un point peut exister sans coordonnees GPS.
CREATE TABLE activity_samples (
    activity_id BIGINT UNSIGNED NOT NULL,
    elapsed_s INT UNSIGNED NOT NULL,
    latitude DECIMAL(9,6) NULL,
    longitude DECIMAL(9,6) NULL,
    altitude_m DECIMAL(8,2) NULL,
    distance_m DECIMAL(12,2) NULL,
    heart_rate_bpm SMALLINT UNSIGNED NULL,
    power_w SMALLINT UNSIGNED NULL,
    cadence_rpm SMALLINT UNSIGNED NULL,
    PRIMARY KEY (activity_id, elapsed_s),
    CONSTRAINT fk_samples_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    CONSTRAINT ck_samples_coordinates CHECK ((latitude IS NULL AND longitude IS NULL) OR (latitude IS NOT NULL AND longitude IS NOT NULL AND latitude BETWEEN -90 AND 90 AND longitude BETWEEN -180 AND 180)),
    CONSTRAINT ck_samples_distance CHECK (distance_m IS NULL OR distance_m >= 0),
    CONSTRAINT ck_samples_hr CHECK (heart_rate_bpm IS NULL OR heart_rate_bpm BETWEEN 1 AND 300)
) ENGINE=InnoDB;

CREATE TABLE activity_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    storage_path VARCHAR(512) NOT NULL,
    caption VARCHAR(255) NULL,
    sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_photos_activity_order (activity_id, sort_order),
    CONSTRAINT fk_photos_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE equipment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    equipment_type VARCHAR(20) NOT NULL,
    brand VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    purchased_on DATE NULL,
    initial_distance_m DECIMAL(12,2) NOT NULL DEFAULT 0,
    maintenance_interval_m DECIMAL(12,2) NULL,
    retired_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_equipment_owner UNIQUE (id, user_id),
    INDEX idx_equipment_user_retired (user_id, retired_at),
    CONSTRAINT fk_equipment_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_equipment_type CHECK (equipment_type IN ('bike', 'wheels', 'tires', 'chain', 'cassette', 'shoes', 'other')),
    CONSTRAINT ck_equipment_distance CHECK (initial_distance_m >= 0 AND (maintenance_interval_m IS NULL OR maintenance_interval_m > 0))
) ENGINE=InnoDB;

-- Les cles composites interdisent de lier le materiel d'un autre utilisateur.
CREATE TABLE activity_equipment (
    activity_id BIGINT UNSIGNED NOT NULL,
    equipment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (activity_id, equipment_id),
    CONSTRAINT fk_activity_equipment_activity FOREIGN KEY (activity_id, user_id) REFERENCES activities(id, user_id) ON DELETE CASCADE,
    CONSTRAINT fk_activity_equipment_equipment FOREIGN KEY (equipment_id, user_id) REFERENCES equipment(id, user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE equipment_maintenance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    performed_on DATE NOT NULL,
    description TEXT NOT NULL,
    odometer_m DECIMAL(12,2) NOT NULL,
    cost_eur DECIMAL(10,2) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_maintenance_equipment_date (equipment_id, performed_on),
    CONSTRAINT fk_maintenance_equipment FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    CONSTRAINT ck_maintenance_values CHECK (odometer_m >= 0 AND (cost_eur IS NULL OR cost_eur >= 0))
) ENGINE=InnoDB;

CREATE TABLE goals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    metric VARCHAR(30) NOT NULL,
    target_value DECIMAL(14,2) NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    sport VARCHAR(20) NULL COMMENT 'NULL : tous les sports',
    archived_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_goals_user_dates (user_id, starts_on, ends_on),
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_goals_metric CHECK (metric IN ('distance_m', 'duration_s', 'elevation_gain_m', 'activity_count')),
    CONSTRAINT ck_goals_sport CHECK (sport IS NULL OR sport IN ('cycling', 'running', 'swimming', 'strength', 'other')),
    CONSTRAINT ck_goals_target CHECK (target_value > 0 AND (metric <> 'activity_count' OR target_value = FLOOR(target_value))),
    CONSTRAINT ck_goals_dates CHECK (ends_on >= starts_on)
) ENGINE=InnoDB;

CREATE TABLE training_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    objective VARCHAR(30) NOT NULL,
    event_name VARCHAR(150) NULL,
    event_on DATE NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    generation_method VARCHAR(20) NOT NULL DEFAULT 'manual',
    generator_version VARCHAR(100) NULL,
    generation_context JSON NULL COMMENT 'Parametres et mesures utilises au moment de la generation',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_plans_owner UNIQUE (id, user_id),
    INDEX idx_plans_user_status (user_id, status),
    CONSTRAINT fk_plans_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_plans_objective CHECK (objective IN ('general_fitness', 'race', 'sportive', 'other')),
    CONSTRAINT ck_plans_status CHECK (status IN ('draft', 'active', 'completed', 'abandoned')),
    CONSTRAINT ck_plans_generation CHECK (generation_method IN ('manual', 'rules', 'ai')),
    CONSTRAINT ck_plans_dates CHECK (ends_on >= starts_on AND (event_on IS NULL OR event_on BETWEEN starts_on AND ends_on))
) ENGINE=InnoDB;

-- Seances du calendrier ; plan_id NULL autorise une seance independante.
CREATE TABLE scheduled_workouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    scheduled_on DATE NOT NULL COMMENT 'Date locale du calendrier de l athlete',
    start_time TIME NULL,
    sport VARCHAR(20) NOT NULL DEFAULT 'cycling',
    workout_type VARCHAR(20) NOT NULL,
    planned_duration_s INT UNSIGNED NULL,
    planned_distance_m DECIMAL(12,2) NULL,
    description TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_workouts_owner UNIQUE (id, user_id),
    INDEX idx_workouts_user_date (user_id, scheduled_on),
    CONSTRAINT fk_workouts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_workouts_plan FOREIGN KEY (plan_id, user_id) REFERENCES training_plans(id, user_id) ON DELETE CASCADE,
    CONSTRAINT ck_workouts_sport CHECK (sport IN ('cycling', 'running', 'swimming', 'strength', 'other')),
    CONSTRAINT ck_workouts_type CHECK (workout_type IN ('endurance', 'intervals', 'race', 'group', 'recovery', 'rest', 'other')),
    CONSTRAINT ck_workouts_status CHECK (status IN ('planned', 'skipped', 'cancelled')),
    CONSTRAINT ck_workouts_values CHECK ((planned_duration_s IS NULL OR planned_duration_s > 0) AND (planned_distance_m IS NULL OR planned_distance_m >= 0)),
    CONSTRAINT ck_workouts_time CHECK (start_time IS NULL OR (start_time >= '00:00:00' AND start_time < '24:00:00'))
) ENGINE=InnoDB;

-- Une repetition simple porte sur ce bloc effort/recuperation (pas de JSON opaque).
CREATE TABLE workout_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workout_id BIGINT UNSIGNED NOT NULL,
    position SMALLINT UNSIGNED NOT NULL,
    step_type VARCHAR(20) NOT NULL,
    repetitions SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    duration_s INT UNSIGNED NULL,
    distance_m DECIMAL(10,2) NULL,
    recovery_duration_s INT UNSIGNED NULL,
    target_metric VARCHAR(20) NULL,
    target_min DECIMAL(8,2) NULL,
    target_max DECIMAL(8,2) NULL,
    instructions TEXT NULL,
    CONSTRAINT uq_steps_position UNIQUE (workout_id, position),
    CONSTRAINT fk_steps_workout FOREIGN KEY (workout_id) REFERENCES scheduled_workouts(id) ON DELETE CASCADE,
    CONSTRAINT ck_steps_type CHECK (step_type IN ('warmup', 'effort', 'recovery', 'cooldown')),
    CONSTRAINT ck_steps_order CHECK (position > 0 AND repetitions > 0),
    CONSTRAINT ck_steps_duration CHECK ((duration_s IS NOT NULL AND duration_s > 0 AND distance_m IS NULL) OR (distance_m IS NOT NULL AND distance_m > 0 AND duration_s IS NULL)),
    CONSTRAINT ck_steps_target CHECK ((target_metric IS NULL AND target_min IS NULL AND target_max IS NULL) OR (target_metric IS NOT NULL AND target_metric IN ('power_w', 'ftp_percent', 'heart_rate_bpm', 'cadence_rpm', 'rpe') AND target_min IS NOT NULL AND target_max IS NOT NULL AND target_min >= 0 AND target_max >= target_min)),
    CONSTRAINT ck_steps_recovery CHECK (recovery_duration_s IS NULL OR recovery_duration_s > 0)
) ENGINE=InnoDB;

-- Le statut realise est deduit de cette relation : pas de double source de verite.
CREATE TABLE workout_completions (
    workout_id BIGINT UNSIGNED PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_completions_activity UNIQUE (activity_id),
    CONSTRAINT fk_completions_workout FOREIGN KEY (workout_id, user_id) REFERENCES scheduled_workouts(id, user_id) ON DELETE CASCADE,
    CONSTRAINT fk_completions_activity FOREIGN KEY (activity_id, user_id) REFERENCES activities(id, user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    deduplication_key VARCHAR(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    action_path VARCHAR(512) NULL COMMENT 'Chemin interne valide par application',
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_notifications_dedup UNIQUE (user_id, deduplication_key),
    INDEX idx_notifications_user_read_date (user_id, read_at, created_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_follows (
    follower_id BIGINT UNSIGNED NOT NULL,
    followed_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, followed_id),
    INDEX idx_follows_followed_status (followed_id, status),
    CONSTRAINT fk_follows_follower FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_follows_followed FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_follows_self CHECK (follower_id <> followed_id),
    CONSTRAINT ck_follows_status CHECK (status IN ('pending', 'accepted'))
) ENGINE=InnoDB;

CREATE TABLE activity_likes (
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (activity_id, user_id),
    INDEX idx_likes_user (user_id),
    CONSTRAINT fk_likes_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_comments_activity_date (activity_id, created_at),
    CONSTRAINT fk_comments_activity FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT ck_comments_body CHECK (CHAR_LENGTH(TRIM(body)) BETWEEN 1 AND 5000)
) ENGINE=InnoDB;

CREATE TABLE badges (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    metric VARCHAR(30) NOT NULL,
    threshold_value DECIMAL(14,2) NOT NULL,
    icon_path VARCHAR(512) NULL,
    CONSTRAINT uq_badges_code UNIQUE (code),
    CONSTRAINT ck_badges_metric CHECK (metric IN ('total_distance_m', 'total_elevation_m', 'activity_count', 'single_distance_m')),
    CONSTRAINT ck_badges_threshold CHECK (threshold_value > 0)
) ENGINE=InnoDB;

CREATE TABLE user_badges (
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id BIGINT UNSIGNED NOT NULL,
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Les statistiques, progressions et kilometrages sont calcules, pas dupliques.
CREATE VIEW equipment_totals AS
SELECT e.id AS equipment_id, e.user_id,
       e.initial_distance_m + COALESCE(SUM(a.distance_m), 0) AS total_distance_m
FROM equipment e
LEFT JOIN activity_equipment ae ON ae.equipment_id = e.id AND ae.user_id = e.user_id
LEFT JOIN activities a ON a.id = ae.activity_id AND a.user_id = ae.user_id
GROUP BY e.id, e.user_id, e.initial_distance_m;

CREATE VIEW workout_calendar AS
SELECT w.id, w.user_id, w.plan_id, w.title, w.scheduled_on, w.start_time,
       w.sport, w.workout_type, w.planned_duration_s, w.planned_distance_m,
       c.activity_id,
       CASE WHEN c.activity_id IS NOT NULL THEN 'completed' ELSE w.status END AS effective_status
FROM scheduled_workouts w
LEFT JOIN workout_completions c ON c.workout_id = w.id;
