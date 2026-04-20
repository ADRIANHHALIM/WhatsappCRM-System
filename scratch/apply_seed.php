<?php
define('BASEPATH', true);
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';

$db_config = $db['default'];
$seed_file = 'db_seed.sql';

try {
    $dsn = "pgsql:host={$db_config['hostname']};port={$db_config['port']};dbname={$db_config['database']}";
    $pdo = new PDO($dsn, $db_config['username'], $db_config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n";

    // 1. Truncate table
    echo "Truncating 'employees' table...\n";
    $pdo->exec("TRUNCATE TABLE employees CASCADE");

    // 2. Read and apply seed
    if (file_exists($seed_file)) {
        echo "Applying seed from {$seed_file}...\n";
        $sql = file_get_contents($seed_file);
        $pdo->exec($sql);
        echo "SUCCESS: Seed applied successfully.\n";
    } else {
        echo "ERROR: Seed file {$seed_file} not found.\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
