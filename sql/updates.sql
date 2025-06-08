CREATE TABLE item_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (item_id) REFERENCES items(item_id),
    FOREIGN KEY (changed_by) REFERENCES users(user_id)
);

CREATE INDEX idx_item_status ON items(status);
CREATE INDEX idx_item_category ON items(category);
CREATE INDEX idx_claim_status ON claims(status);
