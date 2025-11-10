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
    $authController = __DIR__ . '/app/controllers/authController.php';
    
    if (file_exists($authController)) {
        require_once $authController;
        
        $controller = new AuthController();
        $controller->login();
    } else {
        die("Error: app/controllers/authController.php not found at $authController");
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
     $authController = __DIR__ . '/app/controllers/authController.php';

     if (file_exists($authController)) {
         require_once $authController;

         $controller = new AuthController();
         $controller->register();
     } else {
         die("Error: app/controllers/authController.php not found at $authController");
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

$router->add('GET', '/sendReqs', function() {
    requireAuth();
    if (file_exists('views/sendReqs.php')) {
        require 'views/sendReqs.php';
    } else {
        die("Error: views/sendReqs.php not found");
    }
});


$router->add('GET', '/friendReqs', function() {
    requireAuth();
    if (file_exists('views/friendReqs.php')) {
        require 'views/friendReqs.php';
    } else {
        die("Error: views/friendReqs.php not found");
    }
});

$router->add('GET', '/addPost', function() {
    requireAuth();
    if (file_exists('views/addPost.php')) {
        require 'views/addPost.php';
    } else {
        die("Error: views/addPost.php not found");
    }
});

$router->add('GET', '/dashboard', function() {
    requireAuth();
    if (file_exists('views/admin/dashboard.php')) {
        require 'views/admin/dashboard.php';
    } else {
        die("Error: views/admin/dashboard.php not found");
    }
});

$router->add('GET', '/editPost/:id', function($id) {
    requireAuth();
    $_GET['post_id'] = $id;
    if (file_exists('views/editPost.php')) {
        require 'views/editPost.php';
    } else {
        die("Error: views/editPost.php not found");
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