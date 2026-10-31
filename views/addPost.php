<?php
namespace App\views;
use App\Models\UserModel;

$userId = getCurrentUserId();
$userModel = new UserModel();
$user = $userModel->getUserById($userId);
$profilePicture = getProfilePicture($user['profile_picture']);
$userRole = $user['role'] ?? 'user';

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

<body class="posts-page add-post-page">
  <?php
  $currentPage = 'addPost';
  require_once 'assets/sidebar.php';
  ?>

  <div class="main-content">
    <?php require_once 'assets/search_header.php'; ?>
    <?= \App\Components\Alert::render(); ?>

    <div class="edit-container add-post-container">
      <div class="single-post-navigation add-post-navigation">
        <a href="/posts" class="btn-back">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          <span>Volver a publicaciones</span>
        </a>
      </div>

      <form action="/posts" method="POST" enctype="multipart/form-data">
        <div class="post-preview add-post-card">
          <div class="add-post-top-header">
            <div class="post-header-section">
              <img src="<?= $profilePicture ?>" alt="User Avatar" class="post-avatar">
              <div class="post-user-info">
                <div class="add-post-author-row">
                  <h3><?= safe_output($_SESSION['user_name'] ?? 'Usuario') ?></h3>
                  <?php if ($userRole === 'teacher'): ?>
                    <span class="role-badge role-teacher">Profesor</span>
                  <?php elseif ($userRole === 'student'): ?>
                    <span class="role-badge role-student">Estudiante</span>
                  <?php endif; ?>
                </div>
                <div class="post-date-info">Compartiendo con la comunidad UNIRED</div>
              </div>
            </div>
            <span class="add-post-badge">Nueva publicación</span>
          </div>

          <div class="post-prompt">
            <div class="post-prompt-title">¿Qué estás pensando hoy?</div>
            <div class="post-prompt-subtitle">Comparte ideas, dudas, noticias o eventos con tus compañeros</div>
          </div>

          <div class="post-text-section add-post-textarea-box">
            <textarea class="post-textarea" id="postText" name="content" maxlength="500" oninput="updateCounter()"
              placeholder="Escribe aquí tu publicación... Puedes usar #hashtags para clasificarla" minlength="1" required></textarea>
            <div class="char-counter"><span id="charCount">0</span> / 500 caracteres</div>
          </div>

          <div class="add-post-image-section" id="drop-zone" onclick="document.getElementById('post_image').click()">
            <div class="upload-icon-circle">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="4" ry="4"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
            </div>
            <div class="image-upload-text">Haz clic para subir o arrastra una imagen aquí</div>
            <div class="image-upload-subtext">PNG, JPG o GIF (máx. 5 MB) — Opcional</div>
          </div>

          <input type="file" id="post_image" name="post_image" accept="image/png, image/jpeg, image/jpg, image/gif"
            style="display: none;" onchange="handleImageSelect(event)">

          <div id="imagePreview" class="post-image-preview" style="display: none;">
            <img id="previewImage" src="" alt="Vista previa">
            <button type="button" class="btn-remove-preview" onclick="removeImage()">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
              Quitar imagen
            </button>
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

          <div class="action-buttons add-post-actions">
            <button type="submit" class="btn btn-primary btn-post">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
              </svg>
              Publicar ahora
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

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

        selectedHashtags.add(tag);
        pill.classList.add('selected');

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

        selectedHashtags.delete(tag);

        const pill = predefinedList.querySelector('.predefined-hashtag-pill[data-tag="' + tag + '"]');
        if (pill) pill.classList.remove('selected');

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

  <?php require __DIR__ . '/assets/modals.php'; ?>
</body>

</html>
