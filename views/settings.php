<?php
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Configuración de la cuenta</title>
  <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
  <script src="<?php echo asset('js/main.js'); ?>"></script>
</head>

<body>
  <?php
  $currentPage = 'settings';
  require_once 'assets/sidebar.php';
  ?>

  <div class="main-content">
    <?php require_once 'assets/search_header.php'; ?>
    <?= \App\Components\Alert::render(); ?>

    <div class="content-wrapper settings-container">
      <div class="settings-page-header">
        <h1 class="settings-page-title">Configuración de la cuenta</h1>
        <p class="settings-page-subtitle">Gestiona la seguridad y los accesos de tu cuenta.</p>
      </div>

      <!-- Change Password Section -->
      <div class="settings-card">
        <div class="settings-card-header">
          <svg class="settings-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
          <div>
            <h2 class="settings-card-title">Seguridad y Contraseña</h2>
            <p class="settings-card-desc">Actualiza tu contraseña periódicamente para mantener tu cuenta protegida.</p>
          </div>
        </div>

        <form action="/profile/change-password" method="POST" class="settings-form">
          <?= function_exists('csrfField') ? csrfField() : '' ?>

          <div class="form-group">
            <label for="current_password">Contraseña actual</label>
            <input type="password" id="current_password" name="current_password" placeholder="Ingresa tu contraseña actual" required minlength="6" maxlength="64" autocomplete="current-password">
          </div>

          <div class="form-group">
            <label for="new_password">Nueva contraseña</label>
            <input type="password" id="new_password" name="new_password" placeholder="Mínimo 8 caracteres, al menos una mayúscula y un número" required minlength="8" maxlength="64" autocomplete="new-password">
            <small class="form-hint">
              Debe contener al menos 8 caracteres, una letra mayúscula y un número.
            </small>
          </div>

          <div class="form-group">
            <label for="confirm_password">Confirmar nueva contraseña</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña" required minlength="8" maxlength="64" autocomplete="new-password">
          </div>

          <div class="form-actions" style="margin-top: 24px;">
            <button type="submit" class="btn btn-primary btn-save">Actualizar contraseña</button>
          </div>
        </form>
      </div>

      <!-- Danger Zone / Delete Account Section -->
      <div class="settings-card settings-danger-card">
        <div class="settings-card-header">
          <svg class="settings-card-icon danger-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <div>
            <h2 class="settings-card-title danger-title">Zona de peligro</h2>
            <p class="settings-card-desc">Acciones irreversibles que afectan permanentemente tu cuenta.</p>
          </div>
        </div>

        <div class="danger-box">
          <div class="danger-info">
            <h3 class="danger-action-title">Eliminar cuenta</h3>
            <p class="danger-action-desc">
              Al eliminar tu cuenta, se borrarán de forma definitiva tu perfil, tus publicaciones, comentarios, fotos y amistades. Esta acción no se puede deshacer.
            </p>
          </div>
          <button type="button" class="btn-delete-account" onclick="showDeleteConfirmation()">
            Eliminar cuenta
          </button>
        </div>
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
