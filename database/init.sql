-- =====================================================
-- Schéma de base pour Gestion Associations Sportives
-- MySQL 8.0+ / MariaDB 10.6+
-- =====================================================

-- Tables utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'secrétaire', 'membre') DEFAULT 'membre',
    is_active BOOLEAN DEFAULT 1,
    is_deleted BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table membres
CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    dob DATE,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    postal_code VARCHAR(10),
    license_number VARCHAR(50),
    is_deleted BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_name (last_name, first_name),
    INDEX idx_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table équipes
CREATE TABLE IF NOT EXISTS teams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL UNIQUE,
    sport_type VARCHAR(50),
    is_deleted BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relation membres-équipes
CREATE TABLE IF NOT EXISTS team_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    member_id INT NOT NULL,
    position VARCHAR(50) DEFAULT 'Joueur',
    joined_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_member (team_id, member_id),
    INDEX idx_team (team_id),
    INDEX idx_member (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table matchs/compétitions
CREATE TABLE IF NOT EXISTS matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    team_id INT NOT NULL,
    opponent_name VARCHAR(150) NOT NULL,
    match_date DATETIME NOT NULL,
    location VARCHAR(255),
    match_type ENUM('friendly', 'league', 'cup', 'tournament') DEFAULT 'friendly',
    status ENUM('programmé', 'en cours', 'terminé', 'annulé') DEFAULT 'programmé',
    score_team INT,
    score_opponent INT,
    notes TEXT,
    is_deleted BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    INDEX idx_date (match_date),
    INDEX idx_team (team_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table paiements
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    year INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('payé', 'en attente', 'retard') DEFAULT 'en attente',
    payment_date DATETIME,
    due_date DATE,
    payment_method VARCHAR(50),
    notes TEXT,
    is_deleted BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    UNIQUE KEY unique_member_year (member_id, year),
    INDEX idx_status (status),
    INDEX idx_year (year),
    INDEX idx_member (member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table logs d'audit
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table queue emails
CREATE TABLE IF NOT EXISTS email_queue (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body LONGTEXT,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Données de test - Compte administrateur par défaut
-- =====================================================

-- Admin: admin@association.local / password
INSERT INTO users (email, password_hash, role, is_active)
VALUES ('admin@association.local', '$2y$10$8L8O.bZN.3Jkf1J5xBwMDOvz1Ks3c8Z8qJXf9R5jVkOlJcR1Lx7R6', 'admin', 1);

-- Secrétaire test
INSERT INTO users (email, password_hash, role, is_active)
VALUES ('secretaire@association.local', '$2y$10$8L8O.bZN.3Jkf1J5xBwMDOvz1Ks3c8Z8qJXf9R5jVkOlJcR1Lx7R6', 'secrétaire', 1);

-- Membre test
INSERT INTO users (email, password_hash, role, is_active)
VALUES ('membre@association.local', '$2y$10$8L8O.bZN.3Jkf1J5xBwMDOvz1Ks3c8Z8qJXf9R5jVkOlJcR1Lx7R6', 'membre', 1);

-- Membres test
INSERT INTO members (first_name, last_name, phone, city, postal_code)
VALUES ('Jean', 'Dupont', '0612345678', 'Paris', '75001');

INSERT INTO members (first_name, last_name, phone, city, postal_code)
VALUES ('Marie', 'Martin', '0712345678', 'Lyon', '69001');

-- Équipes test
INSERT INTO teams (name, sport_type)
VALUES ('Équipe 1', 'football');

INSERT INTO teams (name, sport_type)
VALUES ('Équipe 2', 'basketball');

-- Matchs test
INSERT INTO matches (team_id, opponent_name, match_date, location, match_type, status)
VALUES (1, 'FC Rivaux', '2025-06-15 20:00:00', 'Stade Central', 'league', 'programmé');

INSERT INTO matches (team_id, opponent_name, match_date, location, match_type, status)
VALUES (2, 'Basketball Club', '2025-06-10 19:00:00', 'Halle Sports', 'friendly', 'programmé');

-- Paiements test
INSERT INTO payments (member_id, year, amount, status, due_date)
VALUES (1, 2025, 50.00, 'en attente', '2025-06-30');

INSERT INTO payments (member_id, year, amount, status, payment_date, due_date)
VALUES (2, 2025, 50.00, 'payé', '2025-05-15', '2025-06-30');

-- Affectations équipes
INSERT INTO team_members (team_id, member_id, position, joined_date)
VALUES (1, 1, 'Attaquant', '2025-01-01');

INSERT INTO team_members (team_id, member_id, position, joined_date)
VALUES (2, 2, 'Pivot', '2025-01-01');
