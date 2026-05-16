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
                $profilePicture,
                $user['role'] ?? 'user'
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
                        'author_role' => $user['role'] ?? 'user',
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

    <!-- Report Modal -->
    <dialog id="report-modal" class="confirm-dialog" aria-labelledby="report-modal-title">
        <div class="confirm-box" style="width: 400px; max-width: 90%; text-align: left;">
            <div class="confirm-head">
                <h3 id="report-modal-title" style="margin-bottom: 5px;">Reportar Contenido</h3>
                <p class="confirm-subtitle" style="margin: 0; font-size: 14px; color: #666;">Selecciona una razón para reportar este contenido:</p>
            </div>
            <div class="confirm-sep"></div>
            <form id="report-form" class="report-form" style="padding: 15px; display: flex; flex-direction: column; gap: 10px;">
                <input type="hidden" id="report-entity-type" name="entity_type">
                <input type="hidden" id="report-entity-id" name="entity_id">
                
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Spam" required> Spam
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Contenido inapropiado"> Contenido inapropiado
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Acoso o bullying"> Acoso o bullying
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Discurso de odio"> Discurso de odio
                </label>
                <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                    <input type="radio" name="reason" value="Otro"> Otro
                </label>

                <textarea id="report-reason-details" name="reason_details" placeholder="Detalles opcionales..." style="margin-top: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit; resize: vertical; min-height: 60px;"></textarea>

                <div class="confirm-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('report-modal').close()" class="confirm-cancel" style="padding: 8px 16px;">Cancelar</button>
                    <button type="submit" class="confirm-delete" style="background-color: #f44336; border: none; color: white; padding: 8px 16px; cursor: pointer; border-radius: 4px; font-weight: bold;">Enviar Reporte</button>
                </div>
            </form>
        </div>
    </dialog>
</body>

</html>