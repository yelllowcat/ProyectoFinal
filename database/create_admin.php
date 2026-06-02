<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

$pdo = getDB();

try {
    $email = 'admin@gmail.com';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $exists = $stmt->fetchColumn();

    if ($exists == 0) {
        $fullName = 'Administrador';
        $password = password_hash('Admin123?', PASSWORD_DEFAULT);
        $role = 'admin';
        $biography = 'Administrador del sistema.';
        
        $stmtInsert = $pdo->prepare("INSERT INTO users (full_name, email, password, role, biography) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute([$fullName, $email, $password, $role, $biography]);
        echo "Admin user created successfully.\n";
    } else {
        echo "Admin user already exists.\n";
    }
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}
