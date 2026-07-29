<?php
require 'config/database.php';
$password = password_hash('password123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES ('Test SECOM', 'test@secom.com', ?, 'secom')");
$stmt->execute([$password]);
echo "User created: test@secom.com / password123\n";
