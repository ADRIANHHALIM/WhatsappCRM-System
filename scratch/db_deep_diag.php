<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];

try {
    $dsn = "pgsql:host={$db_config['hostname']};port={$db_config['port']};dbname={$db_config['database']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "--- DB CONTENT ANALYSIS ---\n";

    $stmt = $pdo->query("SELECT id, fullname, username, LENGTH(username) as len, password, role FROM employees");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $u) {
        echo "User ID: {$u['id']}\n";
        echo "Fullname: {$u['fullname']}\n";
        echo "Username: '{$u['username']}' (Length: {$u['len']})\n";
        echo "Role: {$u['role']}\n";
        
        // Check case sensitivity
        $user_input = $u['username'];
        $pass_check = password_verify('admin123', $u['password']);
        echo "Password 'admin123' Verify: " . ($pass_check ? "OK" : "FAIL") . "\n";
        
        // Manual simulation of get_by_username
        $check_stmt = $pdo->prepare("SELECT username FROM employees WHERE username = ?");
        
        $variations = [$u['username'], strtolower($u['username']), ucfirst($u['username']), strtoupper($u['username'])];
        $variations = array_unique($variations);
        
        foreach ($variations as $v) {
            $check_stmt->execute([$v]);
            $found = $check_stmt->fetch();
            echo "SQL Lookup for '{$v}': " . ($found ? "FOUND" : "NOT FOUND") . "\n";
        }
        echo "---------------------------\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
