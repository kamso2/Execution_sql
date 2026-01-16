<?php
require 'includes/audit_log.php';

echo "Testing DB Log Insertion...\n";
logAudit('test_user', 'TEST_QUERY', ['foo' => 'bar'], 42, 'success', '');

echo "Testing DB Log Retrieval...\n";
$logs = getAuditLogs(1);

if (count($logs) > 0 && $logs[0]['user'] === 'test_user') {
    echo "SUCCESS: Log found in DB.\n";
    print_r($logs[0]);
} else {
    echo "FAILURE: Log not found.\n";
    print_r($logs);
}
?>
