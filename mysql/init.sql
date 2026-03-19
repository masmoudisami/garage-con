-- ============================================
-- SCRIPT SQL DÉFINITIF - GESTION FACTURES & DEVIS
-- Garage Auto Service - Mécanicien
-- Version Finale avec Authentification
-- ============================================

-- Supprimer la base si elle existe (pour réinstallation)
DROP DATABASE IF EXISTS `mechanic_db`;

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS `mechanic_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Sélectionner la base de données
USE `mechanic_db`;

-- Désactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================
-- TABLE: users (Utilisateurs)
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin', 'user') DEFAULT 'user',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: clients
-- ============================================
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `car_model` varchar(100) DEFAULT NULL,
  `matricule_fiscal` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name`),
  INDEX `idx_car_model` (`car_model`),
  INDEX `idx_matricule_fiscal` (`matricule_fiscal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: repair_types
-- ============================================
CREATE TABLE IF NOT EXISTS `repair_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `default_price` decimal(10,3) DEFAULT 0.000,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: invoices
-- ============================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `mileage` int(11) DEFAULT 0,
  `comment` text DEFAULT NULL,
  `droit_timbre` decimal(10,3) DEFAULT 0.000,
  `tax_rate` decimal(5,2) DEFAULT 19.00,
  `total_ht` decimal(10,3) DEFAULT 0.000,
  `total_tva` decimal(10,3) DEFAULT 0.000,
  `total_ttc` decimal(10,3) DEFAULT 0.000,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  INDEX `idx_invoice_date` (`invoice_date`),
  INDEX `idx_client_date` (`client_id`, `invoice_date`),
  CONSTRAINT `invoices_ibfk_1` 
    FOREIGN KEY (`client_id`) 
    REFERENCES `clients` (`id`) 
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: invoice_lines
-- ============================================
CREATE TABLE IF NOT EXISTS `invoice_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `repair_type_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price_unit` decimal(10,3) NOT NULL,
  `total_line` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `repair_type_id` (`repair_type_id`),
  CONSTRAINT `invoice_lines_ibfk_1` 
    FOREIGN KEY (`invoice_id`) 
    REFERENCES `invoices` (`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `invoice_lines_ibfk_2` 
    FOREIGN KEY (`repair_type_id`) 
    REFERENCES `repair_types` (`id`) 
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: devis (Quotes/Estimates)
-- ============================================
CREATE TABLE IF NOT EXISTS `devis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `devis_date` date NOT NULL,
  `validity_date` date DEFAULT NULL,
  `mileage` int(11) DEFAULT 0,
  `comment` text DEFAULT NULL,
  `droit_timbre` decimal(10,3) DEFAULT 0.000,
  `tax_rate` decimal(5,2) DEFAULT 19.00,
  `total_ht` decimal(10,3) DEFAULT 0.000,
  `total_tva` decimal(10,3) DEFAULT 0.000,
  `total_ttc` decimal(10,3) DEFAULT 0.000,
  `status` enum('draft', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'draft',
  `converted_to_invoice_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  INDEX `idx_devis_date` (`devis_date`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `devis_ibfk_1` 
    FOREIGN KEY (`client_id`) 
    REFERENCES `clients` (`id`) 
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE: devis_lines (Quote Lines)
-- ============================================
CREATE TABLE IF NOT EXISTS `devis_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `devis_id` int(11) NOT NULL,
  `repair_type_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price_unit` decimal(10,3) NOT NULL,
  `total_line` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `devis_id` (`devis_id`),
  KEY `repair_type_id` (`repair_type_id`),
  CONSTRAINT `devis_lines_ibfk_1` 
    FOREIGN KEY (`devis_id`) 
    REFERENCES `devis` (`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `devis_lines_ibfk_2` 
    FOREIGN KEY (`repair_type_id`) 
    REFERENCES `repair_types` (`id`) 
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES DE DÉMONSTRATION
-- ============================================

-- Types de réparations (30 exemples)
INSERT INTO `repair_types` (`name`, `default_price`) VALUES
('Vidange moteur', 45.000),
('Filtre à huile', 25.000),
('Filtre à air', 30.000),
('Filtre à carburant', 35.000),
('Plaquettes de frein avant', 80.000),
('Plaquettes de frein arrière', 70.000),
('Disques de frein avant', 120.000),
('Disques de frein arrière', 100.000),
('Liquide de frein', 20.000),
('Liquide de refroidissement', 25.000),
('Bougie d''allumage', 15.000),
('Courroie de distribution', 150.000),
('Amortisseur avant', 90.000),
('Amortisseur arrière', 80.000),
('Pneumatique', 120.000),
('Équilibrage roues', 40.000),
('Géométrie / Parallélisme', 60.000),
('Climatisation - Recharge', 80.000),
('Batterie', 150.000),
('Alternateur', 200.000),
('Démarreur', 180.000),
('Embrayage', 250.000),
('Joint de culasse', 300.000),
('Pompe à eau', 80.000),
('Thermostat', 40.000),
('Radiateur', 150.000),
('Silencieux échappement', 100.000),
('Catalyseur', 250.000),
('Capteur / Sonde', 50.000),
('Main d''œuvre', 30.000);

-- Clients (5 exemples avec matricule fiscal)
INSERT INTO `clients` (`name`, `car_model`, `matricule_fiscal`, `phone`, `address`) VALUES
('Mohamed Ali', 'Renault Clio 4', '1234567/A/M/000', '+216 98 123 456', 'Avenue Habib Bourguiba, Tunis'),
('Sami Ben Ahmed', 'Peugeot 308', '2345678/B/M/000', '+216 25 789 012', 'Rue de la République, Sfax'),
('Ahmed Trabelsi', 'BMW Série 3', '3456789/C/M/000', '+216 97 456 789', 'Avenue de la Liberté, Ariana'),
('Fatma Gharbi', 'Volkswagen Golf 7', '4567890/D/M/000', '+216 22 345 678', 'Rue Mongi Slim, Nabeul'),
('Karim Mansouri', 'Citroën C4', '5678901/E/M/000', '+216 99 876 543', 'Avenue Farhat Hached, Sousse');

-- Utilisateur administrateur par défaut (mot de passe: admin123)
-- Le hash est généré avec: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `active`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@garage.com', 'Administrateur', 'admin', 1)
ON DUPLICATE KEY UPDATE username=username;

-- Réactiver les vérifications de clés étrangères
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- FIN DU SCRIPT
-- ============================================