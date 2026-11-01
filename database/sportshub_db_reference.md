# 🗄️ NSBM SportsHub — Database Schema (`sportshub_db.sql`)

Save this file as `database/sportshub_db.sql` and import it into phpMyAdmin.

---

```sql
-- ============================================================
-- NSBM SportsHub Database
-- Module: Web and Mobile Application Development
-- Batch: 26.1 IFSL | NSBM Green University
-- ============================================================

-- Create and select the database
CREATE DATABASE IF NOT EXISTS sportshub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportshub_db;

-- ============================================================
-- TABLE 1: users
-- Stores both admin and student accounts
-- ============================================================
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,           -- bcrypt hashed
    student_id  VARCHAR(20)   DEFAULT NULL,       -- e.g., "26.1-CS-001"
    phone       VARCHAR(15)   DEFAULT NULL,
    role        ENUM('admin','student') NOT NULL DEFAULT 'student',
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 2: clubs
-- Sports clubs managed by admin
-- ============================================================
CREATE TABLE clubs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)  NOT NULL,
    sport_type   VARCHAR(50)   NOT NULL,           -- e.g., "Cricket", "Football"
    description  TEXT          DEFAULT NULL,
    max_members  INT           DEFAULT 30,
    status       ENUM('active','inactive') DEFAULT 'active',
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 3: club_memberships
-- Tracks student applications to join clubs
-- ============================================================
CREATE TABLE club_memberships (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    club_id     INT           NOT NULL,
    status      ENUM('pending','approved','rejected') DEFAULT 'pending',
    applied_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (club_id)  REFERENCES clubs(id)  ON DELETE CASCADE,
    UNIQUE KEY unique_membership (user_id, club_id)  -- one application per club
);

-- ============================================================
-- TABLE 4: facilities
-- Training grounds and equipment available for booking
-- ============================================================
CREATE TABLE facilities (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100)  NOT NULL,           -- e.g., "Main Cricket Ground"
    type         ENUM('ground','court','equipment','pool') NOT NULL,
    location     VARCHAR(150)  DEFAULT NULL,
    capacity     INT           DEFAULT NULL,
    description  TEXT          DEFAULT NULL,
    status       ENUM('available','maintenance') DEFAULT 'available'
);

-- ============================================================
-- TABLE 5: schedules
-- Training session schedule (linked to a club)
-- ============================================================
CREATE TABLE schedules (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    club_id      INT           NOT NULL,
    title        VARCHAR(150)  NOT NULL,
    session_date DATE          NOT NULL,
    session_time TIME          NOT NULL,
    location     VARCHAR(150)  DEFAULT NULL,
    notes        TEXT          DEFAULT NULL,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE 6: tournaments
-- Tournaments created and managed by admin
-- ============================================================
CREATE TABLE tournaments (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150)  NOT NULL,
    sport_type   VARCHAR(50)   NOT NULL,
    start_date   DATE          NOT NULL,
    end_date     DATE          NOT NULL,
    venue        VARCHAR(150)  DEFAULT NULL,
    max_teams    INT           DEFAULT 8,
    description  TEXT          DEFAULT NULL,
    status       ENUM('upcoming','ongoing','completed') DEFAULT 'upcoming',
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- TABLE 7: tournament_registrations
-- Students registering for tournaments
-- ============================================================
CREATE TABLE tournament_registrations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id   INT           NOT NULL,
    user_id         INT           NOT NULL,
    team_name       VARCHAR(100)  DEFAULT NULL,
    registered_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('confirmed','cancelled') DEFAULT 'confirmed',
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE,
    UNIQUE KEY unique_reg (tournament_id, user_id)
);

-- ============================================================
-- TABLE 8: match_results
-- Results for tournament matches recorded by admin
-- ============================================================
CREATE TABLE match_results (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    tournament_id INT           NOT NULL,
    team1         VARCHAR(100)  NOT NULL,
    team2         VARCHAR(100)  NOT NULL,
    score_team1   INT           DEFAULT 0,
    score_team2   INT           DEFAULT 0,
    match_date    DATE          NOT NULL,
    venue         VARCHAR(150)  DEFAULT NULL,
    notes         TEXT          DEFAULT NULL,
    recorded_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- ============================================================
-- TABLE 9: facility_bookings
-- Students booking facilities or equipment
-- ============================================================
CREATE TABLE facility_bookings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT           NOT NULL,
    facility_id   INT           NOT NULL,
    booking_date  DATE          NOT NULL,
    time_slot     VARCHAR(50)   NOT NULL,          -- e.g., "08:00 - 10:00"
    purpose       VARCHAR(200)  DEFAULT NULL,
    status        ENUM('pending','approved','rejected') DEFAULT 'pending',
    booked_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)      REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (facility_id)  REFERENCES facilities(id)  ON DELETE CASCADE
);


-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin account (password: Admin@1234)
INSERT INTO users (name, email, password, student_id, phone, role) VALUES
('Admin User',      'admin@nsbm.ac.lk',    '$2y$10$eImiTXuWVxfM37uY4JANjuwRh0ygDWqVmOiEIJ2e6ikAplhSmADHi', NULL,          '0771234567', 'admin'),
('Kasun Perera',    'kasun@student.nsbm.lk', '$2y$10$eImiTXuWVxfM37uY4JANjuwRh0ygDWqVmOiEIJ2e6ikAplhSmADHi', '26.1-CS-001', '0712345678', 'student'),
('Nimasha Silva',   'nimasha@student.nsbm.lk','$2y$10$eImiTXuWVxfM37uY4JANjuwRh0ygDWqVmOiEIJ2e6ikAplhSmADHi','26.1-CS-002', '0723456789', 'student'),
('Ravindu Jayawardena','ravindu@student.nsbm.lk','$2y$10$eImiTXuWVxfM37uY4JANjuwRh0ygDWqVmOiEIJ2e6ikAplhSmADHi','26.1-CS-003', '0734567890', 'student'),
('Sachini Fernando', 'sachini@student.nsbm.lk','$2y$10$eImiTXuWVxfM37uY4JANjuwRh0ygDWqVmOiEIJ2e6ikAplhSmADHi','26.1-CS-004', '0745678901', 'student'),
('Thisara Bandara',  'thisara@student.nsbm.lk','$2y$10$eImiTXuWVxfM37uY4JANjuwRh0ygDWqVmOiEIJ2e6ikAplhSmADHi','26.1-CS-005', '0756789012', 'student');
-- Note: All sample passwords above hash to "Admin@1234" for testing purposes

-- Sports Clubs
INSERT INTO clubs (name, sport_type, description, max_members, status) VALUES
('NSBM Cricket Club',    'Cricket',    'Official cricket club of NSBM. Participate in inter-university tournaments.',   30, 'active'),
('NSBM Football Club',   'Football',   'For all football enthusiasts at NSBM. Weekly practice sessions on the main ground.', 25, 'active'),
('NSBM Badminton Club',  'Badminton',  'Casual and competitive badminton. Access to indoor courts.',                    20, 'active'),
('NSBM Swimming Club',   'Swimming',   'Competitive swimming team. Training held at the NSBM pool.',                   15, 'active'),
('NSBM Chess Club',      'Chess',      'Strategy and fun. Join our chess community for inter-faculty competitions.',    40, 'active'),
('NSBM Athletics Club',  'Athletics',  'Track and field events. Train with certified coaches.',                        35, 'active');

-- Facilities
INSERT INTO facilities (name, type, location, capacity, description, status) VALUES
('Main Cricket Ground',    'ground',    'Block A - Outdoor', 100, 'Full-size cricket pitch with practice nets.',    'available'),
('Football Ground',        'ground',    'Block B - Outdoor',  80, 'Standard football field, floodlit.',             'available'),
('Indoor Badminton Courts','court',     'Sports Complex - 1F', 40, '4 badminton courts with proper flooring.',      'available'),
('Swimming Pool',          'pool',      'Aquatic Centre',     50, '25m swimming pool, 6 lanes.',                    'available'),
('Cricket Batting Nets',   'equipment', 'Block A - Nets Area', 10, '3 batting nets available for hire.',            'available'),
('Multipurpose Hall',      'court',     'Sports Complex - GF', 200,'Large hall for indoor sports and events.',      'available');

-- Membership Applications
INSERT INTO club_memberships (user_id, club_id, status) VALUES
(2, 1, 'approved'),
(2, 3, 'pending'),
(3, 2, 'approved'),
(4, 1, 'pending'),
(4, 4, 'approved'),
(5, 5, 'approved'),
(6, 6, 'rejected');

-- Training Schedules
INSERT INTO schedules (club_id, title, session_date, session_time, location, notes) VALUES
(1, 'Batting Practice',        '2026-11-03', '07:00:00', 'Main Cricket Ground',     'Bring your own gloves and pads.'),
(1, 'Bowling & Fielding',      '2026-11-05', '07:00:00', 'Main Cricket Ground',     'Focus on spin bowling this session.'),
(2, 'Fitness & Drills',        '2026-11-04', '16:00:00', 'Football Ground',         'Warm-up at 15:45.'),
(3, 'Doubles Practice',        '2026-11-06', '17:00:00', 'Indoor Badminton Courts', 'Pair up on arrival.'),
(4, 'Endurance Swim',          '2026-11-07', '06:00:00', 'Swimming Pool',           'Target: 20 laps.'),
(6, 'Sprint & Long Jump Drills','2026-11-08','06:30:00', 'Athletics Track',         'Coaches: Mr. Ranjith & Ms. Dilni.');

-- Tournaments
INSERT INTO tournaments (name, sport_type, start_date, end_date, venue, max_teams, description, status) VALUES
('NSBM Inter-Faculty Cricket Cup 2026', 'Cricket',   '2026-11-15', '2026-11-30', 'Main Cricket Ground',     8, 'Annual cricket championship between faculties.', 'upcoming'),
('Green University Football League',    'Football',  '2026-12-01', '2026-12-20', 'Football Ground',         6, 'Round-robin league format, top 2 go to finals.', 'upcoming'),
('Badminton Singles Open',             'Badminton', '2026-11-20', '2026-11-22', 'Indoor Badminton Courts', 16,'Open singles tournament, all students welcome.', 'upcoming');

-- Tournament Registrations
INSERT INTO tournament_registrations (tournament_id, user_id, team_name, status) VALUES
(1, 2, 'CS Hawks',    'confirmed'),
(1, 3, 'CS Hawks',    'confirmed'),
(2, 4, 'Green Wolves','confirmed'),
(3, 5, NULL,          'confirmed');

-- Match Results (for a completed tournament example)
INSERT INTO match_results (tournament_id, team1, team2, score_team1, score_team2, match_date, venue, notes) VALUES
(1, 'CS Hawks',    'BIT Tigers',    145, 130, '2026-11-15', 'Main Cricket Ground', 'CS Hawks won by 15 runs.'),
(1, 'MIT Sharks',  'Engineering XI', 98,  102, '2026-11-16', 'Main Cricket Ground', 'Engineering XI won by 4 runs.'),
(2, 'Green Wolves','Blue Bolts',     3,    1,  '2026-12-01', 'Football Ground',     'Green Wolves win 3-1.');

-- Facility Bookings
INSERT INTO facility_bookings (user_id, facility_id, booking_date, time_slot, purpose, status) VALUES
(2, 5, '2026-11-10', '08:00 - 10:00', 'Batting practice',       'approved'),
(3, 3, '2026-11-11', '17:00 - 19:00', 'Club practice session',  'pending'),
(4, 1, '2026-11-12', '07:00 - 09:00', 'Cricket team warmup',    'pending'),
(5, 6, '2026-11-13', '14:00 - 16:00', 'Chess club event setup', 'approved'),
(6, 2, '2026-11-14', '16:00 - 18:00', 'Athletics sprint drills','rejected');
```

---

## Entity Relationship Overview

```
users ──< club_memberships >── clubs
users ──< facility_bookings >── facilities
users ──< tournament_registrations >── tournaments
clubs ──< schedules
tournaments ──< match_results
```

> [!NOTE]
> The password hash in sample data corresponds to **`Admin@1234`** for all users.
> Students should change these after import for any production/demo use.

> [!TIP]
> In **phpMyAdmin**: Go to your database → **Import** tab → Choose File → select `sportshub_db.sql` → Click **Go**.
