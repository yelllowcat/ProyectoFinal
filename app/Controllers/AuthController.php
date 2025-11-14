<?php

namespace App\Controllers;
use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Helpers\Auth;


class AuthController
{
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');

            if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['error'] = 'Por favor completa todos los campos.';
                redirect('/register');
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Las contraseñas no coinciden.';
                redirect('/register');
            }

            $userModel = new UserModel();
            $result = $userModel->registerUser($full_name, $email, $password);

            if ($result === true) {
                $_SESSION['success'] = 'Registro exitoso. Inicia sesión.';
                redirect('/login');
            } else {
                $_SESSION['error'] = $result;
                redirect('/register');
            }
        } else {
            redirect('/register');
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Por favor completa todos los campos.';
                redirect('/login');
            }

            $userModel = new UserModel();
            $user = $userModel->loginUser($email, $password);

            if ($user === false) {
                $_SESSION['error'] = 'Credenciales incorrectas.';
                redirect('/login');
            } else if (is_string($user)) {
                $_SESSION['error'] = $user;
                redirect('/login');
            } else {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];

                $_SESSION['success'] = 'Inicio de sesión exitoso.';
                redirect('/posts');
            }
        } else {
            redirect('/login');
        }
    }

    public function logout()
    {
        clearUserSession();
        flash('success', 'Logged out successfully');
        redirect('/login');
    }
}
