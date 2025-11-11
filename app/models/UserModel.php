<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
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
            return $e->getMessage();
        }
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function loginUser($email, $password)
    {
        try {

            $stmt = $this->pdo->prepare("CALL sp_login_user(:email)");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("User found: " . ($user ? "YES" : "NO"));

            if ($user) {
                error_log("Stored hash: " . $user['password']);
                $passwordValid = password_verify($password, $user['password']);
                error_log("Password valid: " . ($passwordValid ? "YES" : "NO"));

                if ($passwordValid) {
                    return $user;
                }
            }

            return false;

        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            if (strpos($e->getMessage(), 'Correo no encontrado') !== false) {
                return false;
            }
            return $e->getMessage();
        }
    }


}
