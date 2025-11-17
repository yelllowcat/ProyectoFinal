<?php

use App\Components\Post;
use App\Components\Profile;
use App\Models\PostModel;

$userId = $_GET['id'] ?? getCurrentUserId();
$userModel = new App\Models\UserModel();
$user = $userModel->getUserById($userId);

$postModel = new PostModel();
$userPosts = $postModel->getPostsByUserId($userId);
die(json_encode($user));
if (!$user) {
    flash('error', 'Usuario no encontrado');
    redirect('/posts');
} ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Perfil de Usuario</title>
    <link rel="stylesheet" href="/assets/styles/styles.css">
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
                'own',
                $user['full_name'],
                $user['biography'] ?? '',
                2100,
                187
            );
            echo $profile->render();


            if (empty($userPosts)) {
                echo '<div class="no-posts">Aún no tienes publicaciones... <a href="/addPost">¡Haz tu primera publicación!</a></div>';
            } else {

                foreach ($userPosts as $postData) {
                    $postComponent = new Post([
                        'id' => $postData['post_id'],
                        'author' => $postData['full_name'],
                        'date' => date('d/m/Y', strtotime($postData['created_at'])),
                        'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop',
                        'image_alt' => 'Imagen del post',
                        'text' => $postData['content'],
                        'likes' => 0,
                        'comments_count' => 0,
                        'comments' => [],
                        'user_id' => $postData['user_id'],
                        'current_user_id' => $userId
                    ]);
                    echo $postComponent->render();
                }
            }
            ?>

        </div>
        <dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
            <div class="confirm-box">
                <div class="confirm-head">
                    <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                    <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar a <span
                            class="friend-name">[Nombre del amigo]</span>?</p>
                </div>

                <div class="confirm-sep"></div>

                <form method="dialog" class="confirm-actions">
                    <button value="confirm" class="confirm-delete">Eliminar</button>
                    <button value="cancel" class="confirm-cancel">Cancelar</button>
                </form>
            </div>
        </dialog>
        <script src="../js/main.js"></script>
</body>

</html>