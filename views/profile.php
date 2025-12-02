<?php

use App\Components\Post;
use App\Components\Profile;
use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\UserModel;
use App\Models\FriendModel;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));
$userId = $segments[1] ?? getCurrentUserId();

$userModel = new UserModel();
$user = $userModel->getUserById($userId);

$postModel = new PostModel();
$likeModel = new LikeModel();
$commentModel = new CommentModel();
$friendModel = new FriendModel();

$userPosts = $postModel->getPostsByUserId($userId);
$currentUserId = getCurrentUserId();

$totalLikes = 0;
$postCount = count($userPosts);

foreach ($userPosts as $post) {
    $totalLikes += $likeModel->getLikeCount($post['post_id']);
}

$isOwnProfile = ($userId == $currentUserId);
$friendsCount = count($friendModel->getFriends($userId));

if ($isOwnProfile) {
    $state = 'own';
} else {
    $friendshipStatus = $friendModel->getFriendshipStatus($currentUserId, $userId);

    switch ($friendshipStatus) {
        case 'friends':
            $state = 'friend';
            break;
        case 'pending_received':
            $state = 'request';
            break;
        case 'pending_sent':
            $state = 'pending';
            break;
        default:
            $state = 'stranger';
            break;
    }
}

$profilePicture = getProfilePicture($user['profile_picture']);

if (!$user) {
    flash('error', 'Usuario no encontrado');
    redirect('/posts');
}

$safe_full_name = safe_output($user['full_name'] ?? '');
$safe_biography = safe_output($user['biography'] ?? '');
$safe_email = safe_output($user['email'] ?? '');

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Perfil de <?php echo $safe_full_name; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body>
    <?php
    $currentPage = 'profile';
    require_once 'assets/sidebar.php';
    ?>
    <div class="main-content">
        <div class="content-wrapper">
            <?php
            $profile = new Profile(
                $state,
                $safe_full_name,
                $safe_biography,
                $postCount,
                $totalLikes,
                $friendsCount,
                $userId,
                $profilePicture
            );
            echo $profile->render();

            if (empty($userPosts)) {
                if ($isOwnProfile) {
                    echo '<div class="no-posts">Aún no hay publicaciones... <a href="/addPost">¡Haz tu primera publicación!</a></div>';
                } else {
                    echo '<div class="no-posts">Este usuario aún no tiene publicaciones.</div>';
                }
            } else {
                foreach ($userPosts as $postData) {
                    $likesCount = $likeModel->getLikeCount($postData['post_id']);
                    $hasLiked = $likeModel->hasLiked($postData['post_id'], $currentUserId);
                    $comments = $commentModel->getCommentsByPost($postData['post_id']);
                    $commentsCount = $commentModel->getCommentCount($postData['post_id']);

                    $authorName = $postData['author_name'] ?? $safe_full_name;
                    $authorPicture = $profilePicture; 

                    $safe_post_content = safe_output($postData['content'] ?? '');

                    $postComponent = new Post([
                        'id' => $postData['post_id'],
                        'author' => $authorName,
                        'date' => date('d/m/Y', strtotime($postData['created_at'])),
                        'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                        'image_alt' => 'Imagen del post',
                        'text' => $safe_post_content,
                        'likes' => $likesCount,
                        'comments_count' => $commentsCount,
                        'comments' => $comments,
                        'user_id' => $postData['user_id'],
                        'current_user_id' => $currentUserId,
                        'has_liked' => $hasLiked,
                        'user_avatar' => $authorPicture
                    ]);

                    echo $postComponent->render();
                }
            }
            ?>
        </div>
    </div>

    <dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar esta publicación?</p>
            </div>
            <div class="confirm-sep"></div>
            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-delete">Eliminar</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>
</body>

</html>