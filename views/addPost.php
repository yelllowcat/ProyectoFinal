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
      <form action="/posts" method="POST" enctype="multipart/form-data">
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

          <div class="add-post-image-section" id="drop-zone" onclick="document.getElementById('post_image').click()">
            <img class="add-post-image" src="../assets/images/addImage.png" alt="add Image" />
            <div class="image-upload-text">Haz clic o arrastra una imagen aquí</div>
          </div>

          <input type="file" id="post_image" name="post_image" accept="image/png, image/jpeg, image/jpg, image/gif"
            style="display: none;" onchange="handleImageSelect(event)">

          <div id="imagePreview" class="image-preview" style="display: none;">
            <img id="previewImage" src="" alt="Vista previa">
            <button type="button" class="btn-remove-image" onclick="removeImage()">Quitar imagen</button>
          </div>

          <div class="post-text-section">
            <textarea class="post-textarea" id="postText" name="content" maxlength="500" oninput="updateCounter()"
              placeholder="Escribe lo que quieres compartir..." minlength="1" required></textarea>
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


  <style>
    .add-post-image-section {
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      border: 2px dashed #ddd;
      border-radius: 10px;
      margin: 15px 0;
      transition: all 0.3s ease;
    }

    .add-post-image-section.dragover {
      background-color: rgba(0, 0, 0, 0.05);
      border-color: #007bff;
      transform: scale(1.02);
    }

    .image-preview {
      position: relative;
      text-align: center;
    }

    .image-preview img {
      max-width: 100%;
      max-height: 300px;
      border-radius: 10px;
    }

    .btn-remove-image {
      background: rgba(220, 53, 69, 0.9);
    }
  </style>
</body>

</html>