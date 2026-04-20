<?php
$hash = '$2y$10$bOOWx7i4iB5B/X0J0sI51e4Nq/hA48xW2wL57QpZ/FOf9X4x45m5G';
$password = 'admin123';
if (password_verify($password, $hash)) {
    echo "Password is valid\n";
} else {
    echo "Password is invalid\n";
    echo "Correct hash for 'admin123' is: " . password_hash($password, PASSWORD_BCRYPT) . "\n";
}
