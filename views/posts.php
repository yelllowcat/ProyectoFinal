<?php
namespace App\views;
use App\Components\Post;
use App\Models\PostModel;
use App\Models\LikeModel;
use App\Models\CommentModel;
use App\Models\UserModel;

$postModel = new PostModel();
$likeModel = new LikeModel();
$commentModel = new CommentModel();
$userModel = new UserModel();

$postsData = $postModel->getPostsWithCounts();
$currentUserId = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Publicaciones</title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body>
    <?php
    $currentPage = 'posts';
    require_once 'assets/sidebar.php';
    ?>

    <div class="main-content">
        <div class="content-wrapper">
            <?php
            if (empty($postsData)) {
                echo '<div class="no-posts">No hay publicaciones aún. <a href="/addPost">Sé el primero en publicar</a></div>';
            } else {
                foreach ($postsData as $postData) {
                    $hasLiked = $likeModel->hasLiked($postData['post_id'], $currentUserId);
                    $likesCount = $likeModel->getLikeCount($postData['post_id']);
                    $comments = $commentModel->getCommentsByPost($postData['post_id']);
                    $commentsCount = $commentModel->getCommentCount($postData['post_id']);

                    $author = $userModel->getUserById($postData['user_id']);

                    $authorPicture = getProfilePicture($author['profile_picture']);

                    $postComponent = new Post([
                        'id' => $postData['post_id'],
                        'author' => $postData['full_name'],
                        'date' => date('d/m/Y', strtotime($postData['created_at'])),
                        'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                        'image_alt' => 'Imagen del post',
                        'text' => $postData['content'],
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

    <dialog id="image-modal" class="image-modal">
        <div class="image-modal-content">
            <button class="image-modal-close" id="closeImageModal"><img src="../assets/images/plus.png"
                    alt="Cerrar"></button>
            <img id="modalImage" src="" alt="Post image">
        </div>
    </dialog>

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