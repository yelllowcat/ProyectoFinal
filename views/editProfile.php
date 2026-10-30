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
    <?php require_once 'assets/search_header.php'; ?>
    <?= \App\Components\Alert::render(); ?>
    <div class="content-wrapper">
      <form action="/updateProfile" method="POST" enctype="multipart/form-data">
        <?= function_exists('csrfField') ? csrfField() : '' ?>
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
        <div class="form-group">
          <label for="department">Departamento Académico</label>
          <select id="department" name="department" class="form-select">
            <option value="">Selecciona tu departamento...</option>
            <?php
            $departments = [
                'Ciencias de la Computación',
                'Ciencias Marinas y Costeras',
                'Ciencias Sociales y Jurídicas',
                'Humanidades',
                'Economía y Administración',
                'Ingeniería Pesquera',
                'Agronomía y Zootecnia',
                'Ciencias de la Tierra'
            ];
            $currentDept = $user['department'] ?? '';
            foreach ($departments as $dept): ?>
              <option value="<?= $dept ?>" <?= $currentDept === $dept ? 'selected' : '' ?>><?= $dept ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="career">Carrera</label>
          <input type="text" id="career" name="career" value="<?= safe_output($user['career'] ?? '') ?>"
            placeholder="Ej. Lic. en Desarrollo de Software" maxlength="100">
        </div>

        <?php if (($user['role'] ?? 'user') !== 'teacher'): ?>
        <div class="form-group">
          <label for="semester">Semestre</label>
          <select id="semester" name="semester" class="form-select">
            <option value="">Selecciona tu semestre...</option>
            <?php
            $currentSemester = isset($user['semester']) ? (int) $user['semester'] : 0;
            for ($s = 1; $s <= 12; $s++): ?>
              <option value="<?= $s ?>" <?= $currentSemester === $s ? 'selected' : '' ?>><?= $s ?>° Semestre</option>
            <?php endfor; ?>
          </select>
        </div>
        <?php endif; ?>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary btn-save">Guardar perfil</button>
        </div>
      </form>

      <div class="profile-section-edit" style="margin-top: 35px; text-align: left; padding: 35px 40px;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 20px; font-weight: 700; color: inherit;">Cambiar contraseña</h3>
        <form action="/profile/change-password" method="POST">
          <?= function_exists('csrfField') ? csrfField() : '' ?>

          <div class="form-group">
            <label for="current_password">Contraseña actual</label>
            <input type="password" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual" required minlength="6" maxlength="64" autocomplete="current-password">
          </div>

          <div class="form-group">
            <label for="new_password">Nueva contraseña</label>
            <input type="password" id="new_password" name="new_password" placeholder="Mínimo 8 caracteres, al menos una mayúscula y un número" required minlength="8" maxlength="64" autocomplete="new-password">
            <small style="color: #64748b; font-size: 13px; display: block; margin-top: 6px;">
              Debe contener al menos 8 caracteres, una letra mayúscula y un número.
            </small>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirmar nueva contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña" required minlength="8" maxlength="64" autocomplete="new-password">
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-save">Actualizar contraseña</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <dialog id="confirm-delete-account-modal" class="confirm-dialog">
    <div class="confirm-box">
      <div class="confirm-head">
        <h3>Eliminar cuenta</h3>
        <p class="confirm-subtitle">Esta acción es permanente. Para confirmar, por favor ingresa tu contraseña actual:</p>
      </div>
      <div style="padding: 0 26px 18px; text-align: left;">
        <label for="delete-account-password" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: inherit;">Contraseña actual:</label>
        <input type="password" id="delete-account-password" placeholder="Tu contraseña" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; box-sizing: border-box; background: inherit; color: inherit;" autocomplete="current-password" onkeydown="if(event.key==='Enter') deleteAccount()">
        <div id="delete-account-error" style="color: #ef4444; font-size: 13px; margin-top: 8px; display: none;"></div>
      </div>
      <div class="confirm-sep"></div>
      <div class="confirm-actions">
        <button type="button" class="confirm-delete" id="btn-confirm-delete-account" onclick="deleteAccount()">Eliminar cuenta</button>
        <button type="button" class="confirm-cancel" onclick="closeDeleteAccountModal()">Cancelar</button>
      </div>
    </div>
  </dialog>
</body>

</html>