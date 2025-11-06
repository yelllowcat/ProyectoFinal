<?php

session_start();

$requiredFiles = [
    'Router.php',
    'config/database.php',
    'helpers/auth.php'
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("Error: Required file '$file' not found. Please create it first.");
    }
}

require_once 'config/database.php';
require_once 'helpers/auth.php';
require_once 'Router.php';

$router = new Router();

$router->add('GET', '/', function() {
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    header('Location: /login');
    exit();
});

$router->add('GET', '/login', function() {
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    if (file_exists('views/login.php')) {
        require 'views/login.php';
    } else {
        die("Error: views/login.php not found");
    }
});

$router->add('POST', '/login', function() {
    if (file_exists('controllers/authController.php')) {
        require_once 'controllers/authController.php';
        if (function_exists('handleLogin')) {
            handleLogin();
        } else {
            die("Error: handleLogin() function not found in authController.php");
        }
    } else {
        die("Error: controllers/authController.php not found");
    }
});

$router->add('GET', '/register', function() {
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    if (file_exists('views/register.php')) {
        require 'views/register.php';
    } else {
        die("Error: views/register.php not found");
    }
});

$router->add('POST', '/register', function() {
    if (file_exists('controllers/authController.php')) {
        require_once 'controllers/authController.php';
        if (function_exists('handleRegister')) {
            handleRegister();
        } else {
            die("Error: handleRegister() function not found in authController.php");
        }
    } else {
        die("Error: controllers/authController.php not found");
    }
});

$router->add('GET', '/logout', function() {
    session_destroy();
    header('Location: /login');
    exit();
});

$router->add('GET', '/posts', function() {
    requireAuth();
    if (file_exists('views/posts.php')) {
        require 'views/posts.php';
    } else {
        die("Error: views/posts.php not found. Rename posts.html to posts.php");
    }
});

$router->add('GET', '/profile', function() {
    requireAuth();
    if (file_exists('views/profile.php')) {
        require 'views/profile.php';
    } else {
        die("Error: views/profile.php not found");
    }
});

$router->add('GET', '/profile/:id', function($id) {
    requireAuth();
    $_GET['user_id'] = $id;
    if (file_exists('views/profile.php')) {
        require 'views/profile.php';
    } else {
        die("Error: views/profile.php not found");
    }
});

$router->add('GET', '/editProfile', function() {
    requireAuth();
    if (file_exists('views/editProfile.php')) {
        require 'views/editProfile.php';
    } else {
        die("Error: views/editProfile.php not found");
    }
});

$router->add('GET', '/friends', function() {
    requireAuth();
    if (file_exists('views/friends.php')) {
        require 'views/friends.php';
    } else {
        die("Error: views/friends.php not found");
    }
});

try {
    $router->dispatch();
} catch (Exception $e) {
    die("Router Error: " . $e->getMessage());
}

function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit();
    }
}