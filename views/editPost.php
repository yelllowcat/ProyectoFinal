<?php
namespace App\views;

$post = $_GET['post_id'];

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
  require_once 'assets/sidebar.php' ?>

  <div class="main-content">
    <div class="edit-container">
      <div class="post-preview">
        <div class="post-header-section">
          <div class="post-avatar"></div>
          <div class="post-user-info">
            <h3>Manuel Orozco</h3>
            <div class="post-date-info">Publicado el: 18/03/2025</div>
          </div>
        </div>

        <div class="post-prompt">
          <div class="post-prompt-title">¿Que estas pensando?</div>
          <div class="post-prompt-subtitle">Comparte con nosotros</div>
        </div>

        <div class="post-image-section">
          <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=350&fit=crop"
            alt="Paisaje montañoso" class="post-image" />
        </div>

        <div class="post-text-section">
          <textarea class="post-textarea" id="postText" maxlength="500" oninput="updateCounter()">
Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum i</textarea>
          <div class="char-counter"><span id="charCount">350</span>/500</div>
        </div>

        <div class="action-buttons">
          <button class="btn btn-delete-post">Eliminar publicación</button>
          <button class="btn btn-save">Guardar Cambios</button>
        </div>
      </div>
    </div>
  </div>

  <script src="../js/main.js"></script>
</body>

</html>