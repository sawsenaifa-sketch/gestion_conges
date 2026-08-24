-- ================================
-- DATABASE: gestion_conges
-- ================================

CREATE TABLE groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  description TEXT
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  group_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
);

CREATE TABLE leave_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  motif VARCHAR(255),
  statut ENUM('en_attente','accepte','refuse') DEFAULT 'en_attente',
  admin_comment VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE holidays (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(100) NOT NULL,
  date_debut DATE NOT NULL,
  date_fin DATE NOT NULL,
  couleur VARCHAR(7) DEFAULT '#e74c3c',
  type ENUM('ferie_national','evenement') DEFAULT 'ferie_national'
);

-- ================================
-- DATA INITIALE
-- ================================

-- Groupes
INSERT INTO groups (nom, description) VALUES 
('Développement', 'Equipe dev web'),
('Marketing', 'Equipe marketing'),
('RH', 'Ressources humaines');

-- Admin par défaut (email: admin@test.com / password: admin123)
INSERT INTO users (nom, prenom, email, password, role, group_id) VALUES
('Admin', 'Super', 'admin@test.com', '$2y$10$92XIGaZKiu9EMDoCfbXTL.Qy5f9SGpi/BhoSPB/UcnnT2y4TgOMz6', 'admin', NULL);

-- Quelques users de test (password pour tous: test123)
INSERT INTO users (nom, prenom, email, password, role, group_id) VALUES
('Ben Ali', 'Ahmed', 'ahmed@test.com', '$2y$10$XjHz5tK8W5x1E5kZ8vY0hOqZ9pLd3jRzC7bF1mN6sT2wA4rY8uI9K', 'user', 1),
('Trabelsi', 'Sana', 'sana@test.com', '$2y$10$XjHz5tK8W5x1E5kZ8vY0hOqZ9pLd3jRzC7bF1mN6sT2wA4rY8uI9K', 'user', 1),
('Gharbi', 'Mohamed', 'mohamed@test.com', '$2y$10$XjHz5tK8W5x1E5kZ8vY0hOqZ9pLd3jRzC7bF1mN6sT2wA4rY8uI9K', 'user', 2);

-- Jours fériés Tunisie 2026
INSERT INTO holidays (titre, date_debut, date_fin, couleur, type) VALUES
('Ras l3am', '2026-01-01', '2026-01-01', '#3498db', 'ferie_national'),
('Aid El Fitr', '2026-03-20', '2026-03-21', '#2ecc71', 'ferie_national'),
('Fête du Travail', '2026-05-01', '2026-05-01', '#f39c12', 'ferie_national'),
('Aid El Adha', '2026-05-27', '2026-05-28', '#2ecc71', 'ferie_national'),
('Fête Républic', '2026-07-25', '2026-07-25', '#9b59b6', 'ferie_national');