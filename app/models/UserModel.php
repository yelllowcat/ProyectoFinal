cambio el UserModel este
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

            if (
                strpos($e->getMessage(), 'Correo no encontrado') !== false ||
                strpos($e->getMessage(), 'Usuario no encontrado') !== false
            ) {
                return false;
            }

            return $this->handleDatabaseError($e, 'Error al iniciar sesión. Por favor intenta de nuevo.');
        }
    }
}