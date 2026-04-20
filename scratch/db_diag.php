<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];

try {
    $dsn = "pgsql:host={$db_config['hostname']};port={$db_config['port']};dbname={$db_config['database']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS: Connected to database '{$db_config['database']}'\n";

    $stmt = $pdo->query("SELECT count(*) FROM information_schema.tables WHERE table_name = 'employees'");
    if ($stmt->fetchColumn() == 0) {
        echo "ERROR: Table 'employees' does NOT exist.\n";
    } else {
        echo "SUCCESS: Table 'employees' exists.\n";
        $stmt = $pdo->query("SELECT id, username, password, role FROM employees");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($users) . " users in table:\n";
        foreach ($users as $u) {
            echo "- Username: {$u['username']} (Role: {$u['role']})\n";
            $verify = password_verify('admin123', $u['password']);
            echo "  Password 'admin123' is " . ($verify ? "VALID" : "INVALID") . " for this user.\n";
        }
    }

} catch (PDOException $e) {
    echo "ERROR: Could not connect to database: " . $e->getMessage() . "\n";
}
