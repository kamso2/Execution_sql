<?php
require 'includes/db_config.php';

try {
    // Attempt with JSON first (for modern MySQL/MariaDB)
    // If it fails, we fall back to TEXT
    $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user VARCHAR(50) NOT NULL,
        query_id VARCHAR(100),
        table_name VARCHAR(100),
        params TEXT, /* Changed from JSON to TEXT for compatibility */
        row_count INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'success',
        error_msg TEXT,
        remote_ip VARCHAR(45),
        INDEX idx_timestamp (timestamp),
        INDEX idx_user (user),
        INDEX idx_table_name (table_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'audit_logs' created successfully (with TEXT params).";
    
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
