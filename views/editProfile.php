<?php
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Editar Perfil de <?= safe_output($user['full_name'] ?? 'Usuario') ?></title>
  <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
  <script src="<?php echo asset('js/main.js'); ?>"></script>
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
              <img src="<?= getProfilePicture($user['profile_picture']) ?>" alt="Foto de perfil"
                id="currentProfileImage">

              <img id="previewProfileImage" alt="Vista previa" style="display:none;">
            </div>


            <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/jpg"
              style="display: none;" onchange="handleProfileImageSelect(event)">
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
            pattern="^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s']{2,50}$">
        </div>

        <div class="form-group">
          <label for="biography">Biografía</label>
          <textarea id="postText" name="biography" placeholder="Describe algo sobre ti..." rows="6" maxlength="500"
            oninput="updateCounter()"><?= safe_output($user['biography'] ?? '') ?></textarea>
          <div class="char-counter"><span id="charCount"><?= strlen($user['biography'] ?? '') ?></span>/500</div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary btn-save">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <dialog id="confirm-delete-account-modal" class="confirm-dialog">
    <div class="confirm-box">
      <div class="confirm-head">
        <h3>Eliminar cuenta</h3>
        <p>¿Estás seguro de que deseas eliminar tu cuenta?</p>
      </div>
      <div class="confirm-sep"></div>
      <div class="confirm-actions">
        <button type="button" class="confirm-delete" onclick="deleteAccount()">Eliminar</button>
        <button type="button" class="confirm-cancel" onclick="document.getElementById('confirm-delete-account-modal').close()">Cancelar</button>
      </div>
    </div>
  </dialog>
</body>

</html>