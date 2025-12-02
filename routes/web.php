<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\PostController;
use App\Controllers\FriendController;
use App\Controllers\ProfileController;
use App\Controllers\AdminController;
use App\Controllers\PdfController;


$router->get('/', function () {
    if (isAdmin()) {
        header('Location: /dashboard');
        exit();
    }
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }

    header('Location: /login');
    exit();
});

$router->get('/login', function () {
    if (isAdmin()) {
        header('Location: /dashboard');
        exit();
    }
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }

    require __DIR__ . '/../views/login.php';
});

$router->post('/login', [AuthController::class, 'login']);

$router->get('/register', function () {
    if (isAdmin()) {
        header('Location: /dashboard');
        exit();
    }
    if (isLoggedIn()) {
        header('Location: /posts');
        exit();
    }
    require __DIR__ . '/../views/register.php';
});

$router->post('/register', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/posts', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/posts.php';
});

$router->get('/profile', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/profile.php';
});

$router->get('/profile/:id', [UserController::class, 'show']);
$router->post('/updateProfile', [UserController::class, 'update']);
$router->get('/editProfile', [UserController::class, 'edit']);
$router->delete('/profile/delete', [UserController::class, 'deleteAccount']);


$router->get('/friends', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/friends.php';
});

$router->get('/sendReqs', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/sendReqs.php';
});

$router->get('/friendReqs', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/friendReqs.php';
});

$router->post('/friend/request/:id', [FriendController::class, 'sendRequestById']);
$router->post('/friend/accept/:id', [FriendController::class, 'acceptRequest']);
$router->post('/friend/acceptUser/:id', [FriendController::class, 'acceptRequestByUserId']);
$router->post('/friend/reject/:id', [FriendController::class, 'rejectRequest']);
$router->post('/friend/cancel/:id', [FriendController::class, 'cancelRequest']);
$router->post('/friend/cancelUser/:id', [FriendController::class, 'cancelRequestByUserId']);
$router->delete('/friend/remove/:id', [FriendController::class, 'removeFriend']);
$router->post('/friend/remove/:id', [FriendController::class, 'removeFriendById']);

$router->get('/friend/requests', [FriendController::class, 'getPendingRequests']);
$router->get('/friend/status', [FriendController::class, 'getFriendshipStatus']);
$router->get('/friend/counts', [FriendController::class, 'getFriendsCounts']);
$router->get('/friends/list', [FriendController::class, 'getFriends']);

$router->get('/addPost', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/addPost.php';
});

$router->post('/posts', [PostController::class, 'store']);
$router->get('/posts', function () {
    requireUser();
    requireAuth();
    require __DIR__ . '/../views/posts.php';
});

$router->get('/editPost/:id', function ($id) {
    requireUser();
    requireAuth();
    $_GET['post_id'] = $id;
    require __DIR__ . '/../views/editPost.php';
});

$router->post('/editPost/:id', [PostController::class, 'update']);

$router->put('/posts/:id', [PostController::class, 'update']);
$router->delete('/posts/:id', [PostController::class, 'destroy']);

$router->post('/posts/:id/like', [PostController::class, 'like']);
$router->delete('/posts/:id/like', [PostController::class, 'unlike']);

$router->post('/posts/:id/comments', [PostController::class, 'addComment']);
$router->get('/posts/:id/comments', [PostController::class, 'getComments']);
$router->delete('/comments/:id', [PostController::class, 'deleteComment']);

$router->get('/admin/stats/users-posts', [AdminController::class, 'getUsersWithMostPosts']);
$router->get('/admin/stats/users-friends', [AdminController::class, 'getUsersWithMostFriends']);
$router->get('/admin/stats/posts-comments', [AdminController::class, 'getPostsWithMostComments']);
$router->get('/admin/stats/posts-likes', [AdminController::class, 'getPostsWithMostLikes']);

$router->get('/dashboard', function () {
    requireAuth();
    requireAdmin();
    require __DIR__ . '/../views/admin/dashboard.php';
});

$router->get('/admin/stats/pdf', [PdfController::class, 'downloadStatsPdf']);