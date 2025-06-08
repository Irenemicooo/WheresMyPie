-- System announcements table
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- User activity logs
CREATE TABLE user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- System statistics
CREATE TABLE system_stats (
    stat_date DATE PRIMARY KEY,
    total_items INT DEFAULT 0,
    new_items INT DEFAULT 0,
    total_claims INT DEFAULT 0,
    successful_claims INT DEFAULT 0,
    active_users INT DEFAULT 0
);

-- Create indexes
CREATE INDEX idx_announcements_dates ON announcements(start_date, end_date);
CREATE INDEX idx_user_activities_user ON user_activities(user_id, created_at);
