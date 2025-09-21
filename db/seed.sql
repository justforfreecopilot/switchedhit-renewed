-- SwitchedHit Database Seed Data

-- Insert admin user
-- Password hash for 'admin123' (bcrypt)
INSERT INTO users (email, password_hash, role) VALUES
('admin@switchedhit.com', '$2y$10$LCLJKIgPfTo6tdsbrUhTO.7U.bj2/nepKW0JDULSjV2gJrItISDLu', 'admin');