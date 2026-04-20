<?php
/**
 * Verification script for Case-Insensitive Login
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'development');

require_once 'application/config/database.php';

$db_config = $db['default'];

try {
    $dsn = "pgsql:host={$db_config['hostname']};port={$db_config['port']};dbname={$db_config['database']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- CASE-INSENSITIVE VERIFICATION ---\n";

    $variations = ['adrian', 'Adrian', 'ADRIAN'];
    $success = true;

    foreach ($variations as $v) {
        $stmt = $pdo->prepare("SELECT id, username FROM employees WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute([$v]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "Variation '{$v}': FOUND (Stored as '{$user['username']}')\n";
        } else {
            echo "Variation '{$v}': NOT FOUND\n";
            $success = false;
        }
    }

    if ($success) {
        echo "RESULT: SUCCESS - Login is now case-insensitive.\n";
    } else {
        echo "RESULT: FAILED - Some variations were not found.\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
