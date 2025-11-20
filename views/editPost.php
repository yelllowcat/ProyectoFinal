<?php
namespace App\views;

use App\Models\PostModel;

requireAuth();

$postId = $_GET['post_id'] ?? null;
$currentUserId = $_SESSION['user_id'];

if (!$postId) {
  flash('error', 'Publicación no encontrada');
  redirect('/posts');
}

$postModel = new PostModel();
$post = $postModel->getPostById($postId);

if (!$post || $post['user_id'] != $currentUserId) {
  flash('error', 'No tienes permisos para editar esta publicación');
  redirect('/posts');
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Editar Publicación</title>
  <link rel="stylesheet" href="../assets/styles/styles.css" />
</head>

<body>
  <?php
  $currentPage = 'editPost';
  require_once 'assets/sidebar.php';
  ?>

  <div class="main-content">
    <div class="edit-container">
      <form id="editPostForm" method="POST">
        <div class="post-preview">
          <div class="post-header-section">
            <div class="post-avatar"></div>
            <div class="post-user-info">
              <h3><?= safe_output($_SESSION['user_name'] ?? 'Usuario') ?></h3>
              <div class="post-date-info">Publicado el: <?= date('d/m/Y', strtotime($post['created_at'])) ?></div>
            </div>
          </div>

          <div class="post-prompt">
            <div class="post-prompt-title">¿Qué estás pensando?</div>
            <div class="post-prompt-subtitle">Edita tu publicación</div>
          </div>

          <?php if ($post['image']): ?>
            <div class="post-image-section">
              <img src="/assets/imagesPosts/<?= $post['image'] ?>" alt="Imagen del post" class="post-image" />
              <div class="image-note">* La imagen no se puede editar</div>
            </div>
          <?php endif; ?>

          <div class="post-text-section">
            <textarea class="post-textarea" id="postText" name="content" maxlength="500" oninput="updateCounter()"
              placeholder="Escribe lo que quieres compartir..." minlength="2"
              required><?= safe_output($post['content']) ?></textarea>
            <div class="char-counter"><span id="charCount"><?= strlen($post['content']) ?></span>/500</div>
          </div>

          <div class="action-buttons">
            <button type="button" class="btn btn-delete-post" onclick="deletePost(<?= $postId ?>)">Eliminar
              publicación</button>
            <button type="submit" class="btn btn-save">Guardar Cambios</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    const POST_ID = <?= $postId ?>;
  </script>
  <script src="../js/main.js"></script>
</body>

</html>