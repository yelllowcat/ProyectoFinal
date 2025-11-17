<?php
namespace App\views;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Nueva Publicación</title>
  <link rel="stylesheet" href="../assets/styles/styles.css" />
</head>

<body>
  <?php
  $currentPage = 'addPost';
  require_once 'assets/sidebar.php' ?>

  <div class="main-content">
    <div class="edit-container">
      <form action="/posts" method="POST">
        <div class="post-preview">
          <div class="post-header-section">
            <div class="post-avatar"></div>
            <div class="post-user-info">
              <h3><?= safe_output($_SESSION['user_name'] ?? 'Usuario') ?></h3>
              <div class="post-date-info">Publicando ahora</div>
            </div>
          </div>

          <div class="post-prompt">
            <div class="post-prompt-title">¿Qué estás pensando?</div>
            <div class="post-prompt-subtitle">Comparte con nosotros</div>
          </div>

          <div class="add-post-image-section">
            <img class="add-post-image" src="../assets/images/addImage.png" alt="add Image" />
          </div>

          <div class="post-text-section">
            <textarea class="post-textarea" id="postText" name="content" maxlength="500" oninput="updateCounter()"
              placeholder="Escribe lo que quieres compartir..."></textarea>
            <div class="char-counter"><span id="charCount">0</span>/500</div>
          </div>

          <div class="action-buttons">
            <button type="submit" class="btn btn-primary btn-post">Publicar</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="../js/main.js"></script>
</body>

</html>