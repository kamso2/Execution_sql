<?php
/**
 * Migration script: Add table_name column to audit_logs
 * This script can be run multiple times safely (idempotent)
 */

require 'includes/db_audit.php';

try {
    // Check if column already exists
    $stmt = $pdoAudit->query("SHOW COLUMNS FROM audit_logs LIKE 'table_name'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "✓ Column 'table_name' already exists in audit_logs table.\n";
    } else {
        // Add the table_name column after query_id
        $sql = "ALTER TABLE audit_logs 
                ADD COLUMN table_name VARCHAR(100) NULL AFTER query_id,
                ADD INDEX idx_table_name (table_name)";
        
        $pdoAudit->exec($sql);
        echo "✓ Column 'table_name' added successfully to audit_logs table.\n";
        echo "✓ Index 'idx_table_name' created successfully.\n";
    }
    
    // Optional: Populate existing records with table name from params JSON
    echo "\nMigrating existing data...\n";
    $stmt = $pdoAudit->query("SELECT id, params FROM audit_logs WHERE table_name IS NULL");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $pdoAudit->prepare("UPDATE audit_logs SET table_name = :table_name WHERE id = :id");
    $updated = 0;
    
    foreach ($rows as $row) {
        $params = json_decode($row['params'], true);
        if (isset($params['table'])) {
            $updateStmt->execute([
                ':table_name' => $params['table'],
                ':id' => $row['id']
            ]);
            $updated++;
        }
    }
    
    echo "✓ Updated $updated existing records with table names.\n";
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error during migration: " . $e->getMessage() . "\n";
    exit(1);
}
?>
