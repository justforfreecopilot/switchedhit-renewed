-- SwitchedHit Database Seed Data

-- Insert admin user
-- Password hash for 'admin123' (bcrypt)
INSERT INTO users (email, password_hash, role) VALUES
('admin@switchedhit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');