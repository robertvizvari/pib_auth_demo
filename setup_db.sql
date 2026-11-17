-- database setup for my SQLi testing project
CREATE DATABASE IF NOT EXISTS pib_portal_db;
USE pib_portal_db;

DROP TABLE IF EXISTS accounts;

CREATE TABLE accounts (
  acc_id INT AUTO_INCREMENT PRIMARY KEY,
  login_name VARCHAR(50) NOT NULL,
  pass_code VARCHAR(100) NOT NULL,
  contact_mail VARCHAR(100) NOT NULL,
  access_level VARCHAR(30) NOT NULL,
  private_token VARCHAR(255) NOT NULL
);

-- test data
INSERT INTO accounts (login_name, pass_code, contact_mail, access_level, private_token) VALUES
('admin_root', 'Adm1nPass2026!', 'admin@portal-test.local', 'admin', 'SECRET_KEY_9941_ADMIN'),
('matus_k', 'matus1234', 'matus.k@portal-test.local', 'student', 'Basic student account'),
('elena_v', 'elenaPass99', 'elena.v@portal-test.local', 'editor', 'Editor internal code: 7731');