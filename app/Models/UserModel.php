<?php

namespace App\Models;

use PDO;
use PDOException;
require_once __DIR__ . '/../../config/database.php';

class UserModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    private function handleDatabaseError(PDOException $e, $defaultMessage = 'Ha ocurrido un error.')
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();

        error_log("Database error [{$errorCode}]: {$errorMessage}");

        if ($errorCode == '45000') {
            if (preg_match('/: (.+)$/', $errorMessage, $matches)) {
                $customMessage = trim($matches[1]);

                $messageMap = [
                    'El correo ya está registrado' => 'Este correo electrónico ya está en uso.',
                    'Correo no encontrado' => 'Usuario no encontrado.',
                    'Usuario no encontrado' => 'Usuario no encontrado.'
                ];

                foreach ($messageMap as $dbMsg => $userMsg) {
                    if (strpos($customMessage, $dbMsg) !== false) {
                        return $userMsg;
                    }
                }

                return $customMessage;
            }
        }

        if ($errorCode == '23000' && strpos($errorMessage, 'Duplicate entry') !== false) {
            return 'Este correo electrónico ya está registrado.';
        }

        return $defaultMessage;
    }

    public function registerUser($full_name, $email, $password, $role = 'user')
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->pdo->prepare("CALL sp_register_user(:full_name, :email, :password, :role)");
            $stmt->bindParam(':full_name', $full_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            return $this->handleDatabaseError($e, 'Error al registrar usuario. Por favor intenta de nuevo.');
        }
    }

    public function getUserByEmail($email)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getUserByEmail error: " . $e->getMessage());
            return false;
        }
    }

    public function loginUser($email, $password)
    {
        try {
            $stmt = $this->pdo->prepare("CALL sp_login_user(:email)");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $passwordValid = password_verify($password, $user['password']);

                if ($passwordValid) {
                    return $user;
                }
            }

            return false;

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());

            if (
                strpos($e->getMessage(), 'Correo no encontrado') !== false ||
                strpos($e->getMessage(), 'Usuario no encontrado') !== false
            ) {
                return false;
            }

            return $this->handleDatabaseError($e, 'Error al iniciar sesión. Por favor intenta de nuevo.');
        }
    }

    public function getUserById($id)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT user_id, full_name, email, profile_picture, biography, role, registration_date
            FROM users 
            WHERE user_id = ?
        ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getUserById error: " . $e->getMessage());
            return false;
        }
    }

    public function updateUser($userId, $data)
{
    try {
        
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET full_name = ?, biography = ?, updated_at = NOW()
            WHERE user_id = ?
        ");
        
        $result = $stmt->execute([
            $data['full_name'],
            $data['biography'],
            $userId
        ]);
        
        
        return $result;
        
    } catch (PDOException $e) {
        error_log("updateUser error: " . $e->getMessage());
        return false;
    }
}
}