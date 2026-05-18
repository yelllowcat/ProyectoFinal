<?php
namespace App\views;
use App\Models\UserModel;

$userId = getCurrentUserId();
$userModel = new UserModel();
$user = $userModel->getUserById($userId);
$profilePicture = getProfilePicture($user['profile_picture']);

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Nueva Publicación</title>
  <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
  <script src="<?php echo asset('js/main.js'); ?>"></script>
  <script src="<?php echo asset('js/hashtagAutocomplete.js'); ?>"></script>
</head>

<body>
  <?php
  $currentPage = 'addPost';
  require_once 'assets/sidebar.php' ?>

  <div class="main-content">
    <?php require_once 'assets/search_header.php'; ?>
    <div class="edit-container">
      <form action="/posts" method="POST" enctype="multipart/form-data">
        <div class="post-preview">
          <div class="post-header-section">
            <img src="<?= $profilePicture ?>" alt="User Avatar" class="post-avatar">
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

          <div class="predefined-hashtags-section">
            <div class="predefined-hashtags-title">Etiquetas sugeridas</div>
            <div class="predefined-hashtags-list" id="predefinedHashtagsList">
              <button type="button" class="predefined-hashtag-pill" data-tag="noticia">#noticia</button>
              <button type="button" class="predefined-hashtag-pill" data-tag="pregunta">#pregunta</button>
              <button type="button" class="predefined-hashtag-pill" data-tag="evento">#evento</button>
              <button type="button" class="predefined-hashtag-pill" data-tag="opinion">#opinion</button>
              <button type="button" class="predefined-hashtag-pill" data-tag="tutorial">#tutorial</button>
              <button type="button" class="predefined-hashtag-pill" data-tag="general">#general</button>
            </div>
            <div class="selected-hashtags-area" id="selectedHashtagsArea" style="display: none;">
              <div class="selected-hashtags-title">Seleccionadas</div>
              <div class="selected-hashtags-list" id="selectedHashtagsList"></div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const textarea = document.getElementById('postText');
      const predefinedList = document.getElementById('predefinedHashtagsList');
      const selectedArea = document.getElementById('selectedHashtagsArea');
      const selectedList = document.getElementById('selectedHashtagsList');
      const selectedHashtags = new Set();

      if (!predefinedList || !textarea) return;

      predefinedList.addEventListener('click', function(e) {
        const pill = e.target.closest('.predefined-hashtag-pill');
        if (!pill) return;

        const tag = pill.dataset.tag;
        if (selectedHashtags.has(tag)) return;

        // Add to selected set
        selectedHashtags.add(tag);
        pill.classList.add('selected');

        // Append to textarea if not already present
        const tagText = '#' + tag;
        const regex = new RegExp('(^|\\s)' + tagText + '(\\s|$)', 'i');
        if (!regex.test(textarea.value)) {
          const separator = textarea.value.length > 0 && !textarea.value.endsWith(' ') ? ' ' : '';
          textarea.value += separator + tagText + ' ';
        }

        updateCounter();
        renderSelectedHashtags();
      });

      selectedList.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-hashtag-btn');
        if (!removeBtn) return;

        const tag = removeBtn.dataset.tag;
        if (!tag) return;

        // Remove from selected set
        selectedHashtags.delete(tag);

        // Unmark predefined pill
        const pill = predefinedList.querySelector('.predefined-hashtag-pill[data-tag="' + tag + '"]');
        if (pill) pill.classList.remove('selected');

        // Remove from textarea
        const tagText = '#' + tag;
        const regex = new RegExp('(^|\\s)' + tagText + '(\\s|$)', 'g');
        textarea.value = textarea.value.replace(regex, ' ').replace(/\s+/g, ' ').trim();
        if (textarea.value.length > 0) textarea.value += ' ';

        updateCounter();
        renderSelectedHashtags();
      });

      function renderSelectedHashtags() {
        selectedList.innerHTML = '';

        if (selectedHashtags.size === 0) {
          selectedArea.style.display = 'none';
          return;
        }

        selectedArea.style.display = 'block';
        selectedHashtags.forEach(function(tag) {
          const tagEl = document.createElement('div');
          tagEl.className = 'selected-hashtag-tag';
          tagEl.innerHTML = '<span>#' + tag + '</span>' +
            '<button type="button" class="remove-hashtag-btn" data-tag="' + tag + '" title="Quitar etiqueta">&times;</button>';
          selectedList.appendChild(tagEl);
        });
      }
    });
  </script>
</body>

</html>