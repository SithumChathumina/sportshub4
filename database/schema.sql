-- ============================================================
-- NSBM SportsHub - Database Schema
-- Run this in phpMyAdmin to create all required tables.
-- Database: nsbm_sportshub
-- ============================================================

-- Create the database (run this first if it doesn't exist)
CREATE DATABASE IF NOT EXISTS sportshub4_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportshub4_db;

-- ============================================================
-- TABLE: users
-- Stores all registered users (students and admins)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,          -- Hashed with password_hash()
    student_id  VARCHAR(50)   NOT NULL UNIQUE,
    phone       VARCHAR(20),
    role        ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin account (password: admin123)
INSERT INTO users (name, email, password, student_id, phone, role) VALUES
('Admin User', 'admin@nsbm.ac.lk', '$2y$10$C3fxfpa69TCoTixogxwNE.18lqBSyVrHwtz28sdXbUztv6Oq3Xx7i', 'ADMIN001', '0700000000', 'admin');

-- ============================================================
-- TABLE: clubs
-- Stores all sports clubs
-- ============================================================
CREATE TABLE IF NOT EXISTS clubs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    sport_type   VARCHAR(100) NOT NULL,
    description  TEXT,
    max_members  INT DEFAULT 30,
    status       ENUM('active', 'inactive') DEFAULT 'active',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample clubs
INSERT INTO clubs (name, sport_type, description, max_members) VALUES
('NSBM Cricket Club',    'Cricket',    'University cricket club open to all students.', 25),
('NSBM Football Club',   'Football',   'Competitive football team representing NSBM.',  22),
('NSBM Badminton Club',  'Badminton',  'Singles and doubles badminton training.',       20),
('NSBM Basketball Club', 'Basketball', 'Basketball training and inter-uni competitions.',15);

-- ============================================================
-- TABLE: club_memberships
-- Tracks which students have applied to / joined which clubs
-- ============================================================
CREATE TABLE IF NOT EXISTS club_memberships (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    club_id      INT NOT NULL,
    status       ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    applied_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: facilities
-- Stores sports facilities available for booking
-- ============================================================
CREATE TABLE IF NOT EXISTS facilities (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    location    VARCHAR(150),
    capacity    INT,
    status      ENUM('available', 'maintenance') DEFAULT 'available',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample facilities
INSERT INTO facilities (name, location, capacity) VALUES
('Main Cricket Ground', 'Block A, Outdoor', 500),
('Indoor Badminton Courts', 'Block B, Level 2', 50),
('Football Field', 'Block A, Outdoor', 300),
('Basketball Court', 'Block C, Outdoor', 100),
('Multi-purpose Gym', 'Block D, Level 1', 60);

-- ============================================================
-- TABLE: facility_bookings
-- Tracks facility booking requests from students
-- ============================================================
CREATE TABLE IF NOT EXISTS facility_bookings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    facility_id  INT NOT NULL,
    booking_date DATE NOT NULL,
    time_slot    VARCHAR(50) NOT NULL,        -- e.g. "8:00 AM - 10:00 AM"
    purpose      VARCHAR(255),
    status       ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: schedules
-- Training sessions set by admin for each club
-- ============================================================
CREATE TABLE IF NOT EXISTS schedules (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    club_id       INT NOT NULL,
    title         VARCHAR(150) NOT NULL,
    session_date  DATE NOT NULL,
    session_time  TIME NOT NULL,
    location      VARCHAR(150),
    notes         TEXT,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE: tournaments
-- Sports tournaments organised by the admin
-- ============================================================
CREATE TABLE IF NOT EXISTS tournaments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    sport_type   VARCHAR(100) NOT NULL,
    start_date   DATE NOT NULL,
    end_date     DATE NOT NULL,
    venue        VARCHAR(150),
    max_teams    INT DEFAULT 8,
    description  TEXT,
    status       ENUM('upcoming', 'ongoing', 'completed') DEFAULT 'upcoming',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE: match_results
-- Records the results of tournament matches
-- ============================================================
CREATE TABLE IF NOT EXISTS match_results (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id   INT NOT NULL,
    team1           VARCHAR(100) NOT NULL,
    team2           VARCHAR(100) NOT NULL,
    score_team1     INT DEFAULT 0,
    score_team2     INT DEFAULT 0,
    match_date      DATE NOT NULL,
    notes           VARCHAR(255),             -- e.g. "Semi-Final", "Quarter Final"
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- ============================================================
-- END OF SCHEMA
-- ============================================================
