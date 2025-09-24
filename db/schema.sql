-- SwitchedHit Database Schema
-- Cricket Management System
-- Generated: 2025-09-24

-- Create users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create teams table  
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `stadium_name` varchar(255) DEFAULT NULL,
  `pitch_type` enum('Hard','Green','Flat','Dusty','Uneven') NOT NULL,
  `batting_order` JSON DEFAULT NULL,
  `bowling_order` JSON DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create players table (Cricket-based)
CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` enum('Batsman','Bowler','All-rounder','Wicket-keeper','Opening-batsman','Middle-order','Finisher','Fast-bowler','Spin-bowler','Medium-pacer','Specialist-fielder') NOT NULL,
  `age` int(11) DEFAULT 18,
  `morale` int(11) DEFAULT 50,
  `batting_average` decimal(5,2) DEFAULT 25.00,
  `bowling_average` decimal(5,2) DEFAULT 35.00,
  `strike_rate` decimal(5,2) DEFAULT 120.00,
  `economy_rate` decimal(4,2) DEFAULT 8.00,
  `fielding_rating` int(11) DEFAULT 50,
  `overall_rating` int(11) DEFAULT 50,
  `team_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_team_id` (`team_id`),
  KEY `idx_position` (`position`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;