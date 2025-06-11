<?php
$logPath = __DIR__ . '/../private/logs/php_error.log';
error_log("Test from log_test.php\n", 3, $logPath);
echo "Log written.";