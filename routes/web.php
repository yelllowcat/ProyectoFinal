<?php

use App\controllers\AuthController;



$router->get('/', function () {
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    header('Location: /login');
    exit();
});

$router->get('/login', function () {
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    require __DIR__ . '/../views/login.php';
});

$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', function () {
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    require __DIR__ . '/../views/register.php';
});

$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/posts', function () {
    requireAuth();
    require __DIR__ . '/../views/posts.php';
});

$router->get('/profile', function () {
    requireAuth();
    require __DIR__ . '/../views/profile.php';
});

$router->get('/profile/:id', function ($id) {
    requireAuth();
    $_GET['user_id'] = $id;
    require __DIR__ . '/../views/profile.php';
});

$router->get('/editProfile', function () {
    requireAuth();
    require __DIR__ . '/../views/editProfile.php';
});

//$router->post('/profile/update', [ProfileController::class, 'update']);

$router->get('/friends', function () {
    requireAuth();
    require __DIR__ . '/../views/friends.php';
});

$router->get('/sendReqs', function () {
    requireAuth();
    require __DIR__ . '/../views/sendReqs.php';
});

$router->get('/friendReqs', function () {
    requireAuth();
    require __DIR__ . '/../views/friendReqs.php';
});

//$router->post('/friend/request/:id', [FriendController::class, 'sendRequest']);
//$router->post('/friend/accept/:id', [FriendController::class, 'acceptRequest']);
//$router->post('/friend/reject/:id', [FriendController::class, 'rejectRequest']);
//$router->delete('/friend/remove/:id', [FriendController::class, 'removeFriend']);

$router->get('/addPost', function () {
    requireAuth();
    require __DIR__ . '/../views/addPost.php';
});

//$router->post('/posts', [PostController::class, 'store']);

$router->get('/editPost/:id', function ($id) {
    requireAuth();
    $_GET['post_id'] = $id;
    require __DIR__ . '/../views/editPost.php';
});

//$router->put('/posts/:id', [PostController::class, 'update']);
//$router->delete('/posts/:id', [PostController::class, 'destroy']);

//$router->post('/posts/:id/like', [PostController::class, 'like']);
//$router->delete('/posts/:id/like', [PostController::class, 'unlike']);

//$router->post('/posts/:id/comments', [PostController::class, 'addComment']);
//$router->delete('/comments/:id', [PostController::class, 'deleteComment']);


$router->get('/dashboard', function () {
    requireAuth();
    requireAdmin();
    require __DIR__ . '/../views/admin/dashboard.php';
});