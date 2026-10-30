<?php
namespace App\views;

use App\Components\Post;
use App\Components\Alert;

$postAuthorName = htmlspecialchars($postData['author_name'] ?? 'Usuario');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Publicación de <?= $postAuthorName ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body class="posts-page single-post-page">
    <?php
    $currentPage = 'posts';
    require_once 'assets/sidebar.php';
    ?>

    <div class="main-content">
        <?php require_once 'assets/search_header.php'; ?>
        <?= Alert::render(); ?>

        <div class="content-wrapper" style="max-width: 680px; margin: 0 auto;">
            <div class="single-post-navigation" style="margin-bottom: 20px;">
                <a href="/posts" class="btn-back" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #4db8c4; font-weight: 600; font-size: 14px;">
                    &larr; Volver a publicaciones
                </a>
            </div>

            <?php
            $authorPicture = getProfilePicture($postData['author_picture'] ?? '');
            $postComponent = new Post([
                'id' => $postData['post_id'],
                'author' => $postData['author_name'] ?? 'Usuario',
                'author_role' => $postData['author_role'] ?? 'user',
                'date' => date('d/m/Y', strtotime($postData['created_at'])),
                'created_at' => $postData['created_at'],
                'image' => $postData['image'] ? "/assets/imagesPosts/{$postData['image']}" : '',
                'image_alt' => 'Imagen del post',
                'text' => $postData['content'],
                'likes' => $postData['likes_count'] ?? 0,
                'comments_count' => $postData['comments_count'] ?? 0,
                'comments' => $comments,
                'replies_data' => $repliesData,
                'user_id' => $postData['user_id'],
                'current_user_id' => $currentUserId,
                'has_liked' => $hasLiked,
                'user_avatar' => $authorPicture,
                'single_mode' => true,
                'interactive' => true,
                'expand_all_comments' => true
            ]);

            echo $postComponent->render();
            ?>
        </div>
    </div>

    <dialog id="image-modal" class="image-modal">
        <div class="image-modal-content">
            <button class="image-modal-close" id="closeImageModal">
                <img src="/assets/images/x.png" alt="Cerrar">
            </button>
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

    <dialog id="confirm-delete-comment-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar este comentario?</p>
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

                <textarea id="report-reason-details" name="reason_details" maxlength="200" placeholder="Detalles opcionales..." style="margin-top: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit; resize: vertical; min-height: 60px;"></textarea>

                <div class="confirm-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="document.getElementById('report-modal').close()" class="confirm-cancel" style="padding: 8px 16px;">Cancelar</button>
                    <button type="submit" class="confirm-delete" style="background-color: #f44336; border: none; color: white; padding: 8px 16px; cursor: pointer; border-radius: 4px; font-weight: bold;">Enviar Reporte</button>
                </div>
            </form>
        </div>
    </dialog>
</body>

</html>
