<?php
require_once 'helpers/auth.php';
require_once 'config/database.php';

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/login');
    }
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE correo_electronico = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
   
   

    if ($user && password_verify($password, $user['contrasena'])) {
        setUserSession($user);
        redirect('/posts');
    } else {
        $_SESSION['error'] = 'Invalid credentials';
        redirect('/login');
    }
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('/register');
    }
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO usuarios (nombre_completo, correo_electronico, contrasena) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$name, $email, $hashedPassword]);
        redirect('/login');
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
        redirect('/register');
    }
}
