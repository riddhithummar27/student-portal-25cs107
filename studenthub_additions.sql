USE studenthub;

CREATE TABLE IF NOT EXISTS audit_logs (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created_at (created_at),
    INDEX idx_audit_user_id (user_id)
);

-- Optional: only run this if you want to add a poster path to events.
-- First check Structure to ensure a poster_path column does not already exist.
-- ALTER TABLE events ADD COLUMN poster_path VARCHAR(255) NULL;
