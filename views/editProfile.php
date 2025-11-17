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
      <form action="/updateProfile" method="POST">
        <div class="profile-section-edit">
          <div class="profile-photo-edit">
            <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default_avatar.png'): ?>
              <img src="/assets/images/<?= safe_output($user['profile_picture']) ?>" alt="Foto de perfil"
                onerror="this.src='/assets/images/default_avatar.png'">
            <?php else: ?>
              <div class="default-avatar"></div>
            <?php endif; ?>
          </div>
          <button type="button" class="btn-change-photo" disabled>Cambiar foto (próximamente)</button>

          <h2 class="profile-name"><?= safe_output($user['full_name'] ?? 'Usuario') ?></h2>
          <p class="profile-date">
            Miembro desde: <?= date('d-m-Y', strtotime($user['registration_date'] ?? 'now')) ?>
          </p>

          <button type="button" class="btn btn-delete">Eliminar cuenta</button>
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
          <div class="char-counter"><span id="charCount">350</span>/500</div>
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