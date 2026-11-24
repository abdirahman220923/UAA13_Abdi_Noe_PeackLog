-- Executer apres 001_schema.sql. Referentiel uniquement, aucun utilisateur fictif.
USE PeackLog;
SET NAMES utf8mb4;
INSERT INTO badges (code, name, description, metric, threshold_value) VALUES
('first_activity', 'Premiere activite', 'Enregistrer une premiere activite.', 'activity_count', 1),
('distance_1000_km', '1000 kilometres', 'Cumuler 1000 kilometres.', 'total_distance_m', 1000000),
('elevation_10000_m', '10000 metres de denivele', 'Cumuler 10000 metres de denivele positif.', 'total_elevation_m', 10000),
('single_100_km', 'Centurion', 'Realiser une activite de 100 kilometres.', 'single_distance_m', 100000);
