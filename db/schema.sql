-- SwitchedHit Database Schema

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create teams table
CREATE TABLE teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    stadium_name VARCHAR(255),
    pitch_type ENUM('Hard', 'Green', 'Flat', 'Dusty', 'Uneven') NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create players table
CREATE TABLE players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position ENUM('GK', 'LB', 'CB', 'RB', 'CDM', 'CM', 'CAM', 'LW', 'ST', 'RW', 'CF') NOT NULL,
    age INT DEFAULT 18,
    morale INT DEFAULT 50,
    speed INT DEFAULT 50,
    strength INT DEFAULT 50,
    technique INT DEFAULT 50,
    overall_rating INT DEFAULT 50,
    team_id INT NOT NULL,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_team_id (team_id),
    INDEX idx_position (position)
);