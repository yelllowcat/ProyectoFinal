<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Editar Perfil de <?= safe_output($user['full_name'] ?? 'Usuario') ?></title>
  <link rel="stylesheet" href="../assets/styles/styles.css" />
</head>

<body>
  <?php
  $currentPage = 'editProfile';
  require_once 'assets/sidebar.php' ?>

  <div class="main-content">
    <div class="content-wrapper">
      <form action="/updateProfile" method="POST" enctype="multipart/form-data">
        <div class="profile-section-edit">
          <div class="profile-photo-edit">
            <div id="profilePreview" class="profile-preview">
              <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default_avatar.png'): ?>
                <img src="/assets/imagesProfile/<?= safe_output($user['profile_picture']) ?>" alt="Foto de perfil" id="currentProfileImage">
              <?php else: ?>
                <img src="/assets/imagesProfile/default_avatar.png" alt="Foto de perfil por defecto" id="currentProfileImage">
              <?php endif; ?>
            </div>
            
            <input 
              type="file" 
              id="profile_picture" 
              name="profile_picture" 
              accept="image/png, image/jpeg, image/jpg" 
              style="display: none;"
              onchange="handleProfileImageSelect(event)"
            >
            
            <div id="profileImagePreview" class="image-preview" style="display: none;">
              <img id="previewProfileImage" src="" alt="Vista previa">
              <button type="button" class="btn-remove-image" onclick="removeProfileImage()">Quitar imagen</button>
            </div>
          </div>
          
          <button type="button" class="btn-change-photo" onclick="document.getElementById('profile_picture').click()">
            Cambiar foto de perfil
          </button>

          <h2 class="profile-name"><?= safe_output($user['full_name'] ?? 'Usuario') ?></h2>
          <p class="profile-date">
            Miembro desde: <?= date('d-m-Y', strtotime($user['registration_date'] ?? 'now')) ?>
          </p>

          <button type="button" class="btn btn-delete" onclick="showDeleteConfirmation()">Eliminar cuenta</button>
        </div>

        <div class="form-group">
          <label for="full_name">Nombre completo</label>
          <input type="text" id="full_name" name="full_name" value="<?= safe_output($user['full_name'] ?? '') ?>"
            placeholder="Ingresa tu nombre completo" required minlength="2" maxlength="50" onpaste="return false;"
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s\.\-']{2,50}$">
        </div>

        <div class="form-group">
          <label for="biography">Biografía</label>
          <textarea id="postText" name="biography" placeholder="Describe algo sobre ti..." rows="6" maxlength="500"
            oninput="updateCounter()"><?= safe_output($user['biography'] ?? '') ?></textarea>
          <div class="char-counter"><span id="charCount"><?= strlen($user['biography'] ?? '') ?></span>/500</div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-save">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>


  <script src="../js/main.js"></script>

 
</body>

</html>