-- Buat database
CREATE DATABASE IF NOT EXISTS focus_timer;
USE focus_timer;

-- Buat tabel focus_history
CREATE TABLE IF NOT EXISTS focus_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_name VARCHAR(255) NOT NULL,
    duration_seconds INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk optimasi query
CREATE INDEX idx_created_at ON focus_history(created_at);
CREATE INDEX idx_activity_name ON focus_history(activity_name); 