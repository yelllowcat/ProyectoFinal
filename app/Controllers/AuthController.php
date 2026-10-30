<?php

namespace App\Controllers;
use App\Models\UserModel;


class AuthController
{
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $full_name = clean_input($_POST['full_name'] ?? '');
            $email = clean_input($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');
            $department = clean_input($_POST['department'] ?? '');
            $career = clean_input($_POST['career'] ?? '');
            $semester = isset($_POST['semester']) && $_POST['semester'] !== '' ? intval($_POST['semester']) : null;

            if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['error'] = 'Por favor completa todos los campos requeridos.';
                redirect('/register');
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Las contraseñas no coinciden.';
                redirect('/register');
            }

            $strengthError = validatePasswordStrength($password);
            if ($strengthError !== null) {
                $_SESSION['error'] = $strengthError;
                redirect('/register');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
                $_SESSION['error'] = 'Por favor ingresa un correo electrónico válido.';
                redirect('/register');
            }

            $email_lower = mb_strtolower($email, 'UTF-8');
            if (str_ends_with($email_lower, '@uabcs.mx')) {
                $role = 'teacher';
            } elseif (str_ends_with($email_lower, '@alu.uabcs.mx')) {
                $role = 'student';
            } else {
                $_SESSION['error'] = 'Solo se permiten correos de @uabcs.mx o @alu.uabcs.mx.';
                redirect('/register');
            }

            $userModel = new UserModel();
            $result = $userModel->registerUser(
                $full_name,
                $email,
                $password,
                $role,
                $department ?: null,
                $career ?: null,
                $role === 'teacher' ? null : $semester
            );

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

            $email = clean_input($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Por favor completa todos los campos.';
                redirect('/login');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
                $_SESSION['error'] = 'Por favor ingresa un correo electrónico válido.';
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
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['user_active'] = (int) ($user['active'] ?? 1);

                $_SESSION['success'] = 'Inicio de sesión exitoso.';
                if (isAdmin()) {
                    redirect('/dashboard');
                } else {
                    redirect('/posts');
                }
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
