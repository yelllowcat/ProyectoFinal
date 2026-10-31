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

<body class="edit-profile-page">
  <?php
  $currentPage = 'editProfile';
  require_once 'assets/sidebar.php';
  ?>

  <div class="main-content">
    <?php require_once 'assets/search_header.php'; ?>
    <?= \App\Components\Alert::render(); ?>

    <div class="content-wrapper edit-profile-container">
      <!-- Top Navigation & Header -->
      <div class="edit-profile-header">
        <a href="/profile" class="edit-profile-back" title="Volver al perfil">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          <span>Volver al perfil</span>
        </a>
        <div class="edit-profile-title-block">
          <h1 class="edit-profile-title">Editar Perfil</h1>
          <p class="edit-profile-desc">Personaliza tu foto, biografía y detalles académicos</p>
        </div>
      </div>

      <form action="/updateProfile" method="POST" enctype="multipart/form-data" class="edit-profile-form">
        <?= function_exists('csrfField') ? csrfField() : '' ?>

        <!-- Hero Card: Avatar & User Highlights -->
        <div class="edit-card edit-card-hero">
          <div class="edit-avatar-section">
            <div class="edit-avatar-box">
              <div id="profilePreview" class="edit-avatar-preview">
                <img src="<?= getProfilePicture($user['profile_picture']) ?>" alt="Foto de perfil" id="currentProfileImage" class="edit-avatar-img">
                <img id="previewProfileImage" alt="Vista previa de foto" class="edit-avatar-img" style="display:none;">
              </div>
              <button type="button" class="edit-avatar-overlay-btn" onclick="document.getElementById('profile_picture').click()" aria-label="Cambiar foto de perfil" title="Cambiar foto de perfil">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                  <circle cx="12" cy="13" r="4"></circle>
                </svg>
              </button>
              <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/jpg" style="display: none;" onchange="handleProfileImageSelect(event)">
            </div>

            <div class="edit-avatar-info">
              <div class="edit-user-name-row">
                <h2 class="edit-user-name"><?= safe_output($user['full_name'] ?? 'Usuario') ?></h2>
                <?php if (($user['role'] ?? '') === 'teacher'): ?>
                  <span class="role-badge role-teacher">Profesor</span>
                <?php elseif (($user['role'] ?? '') === 'student'): ?>
                  <span class="role-badge role-student">Estudiante</span>
                <?php endif; ?>
              </div>
              <p class="edit-user-meta">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Miembro desde: <?= date('d/m/Y', strtotime($user['registration_date'] ?? 'now')) ?></span>
              </p>
              <div class="edit-avatar-actions">
                <button type="button" class="btn-change-avatar" onclick="document.getElementById('profile_picture').click()">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                  </svg>
                  <span>Subir nueva foto</span>
                </button>
                <span class="edit-avatar-hint">JPG o PNG. Máx. 5 MB</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Información Personal -->
        <div class="edit-card">
          <div class="edit-card-heading">
            <div class="edit-heading-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
            <div>
              <h3 class="edit-card-title">Información Personal</h3>
              <p class="edit-card-subtitle">Tu nombre público y biografía visible en tu perfil.</p>
            </div>
          </div>

          <div class="edit-form-body">
            <div class="edit-field-group">
              <label for="full_name" class="edit-label">Nombre completo <span class="required-star">*</span></label>
              <div class="edit-input-wrapper">
                <input type="text" id="full_name" name="full_name" class="edit-input"
                  value="<?= safe_output($user['full_name'] ?? '') ?>"
                  placeholder="Ingresa tu nombre completo" required minlength="2" maxlength="50" onpaste="return false;"
                  pattern="^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s']{2,50}$">
              </div>
              <small class="edit-field-hint">Solo letras y espacios (2 a 50 caracteres).</small>
            </div>

            <div class="edit-field-group">
              <div class="edit-label-row">
                <label for="postText" class="edit-label">Biografía</label>
                <span class="edit-counter"><span id="charCount"><?= strlen($user['biography'] ?? '') ?></span>/500</span>
              </div>
              <div class="edit-input-wrapper">
                <textarea id="postText" name="biography" class="edit-textarea"
                  placeholder="Cuéntanos un poco sobre ti, tus intereses o proyectos..." rows="4" maxlength="500"
                  oninput="updateCounter()"><?= safe_output($user['biography'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Información Académica -->
        <div class="edit-card">
          <div class="edit-card-heading">
            <div class="edit-heading-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
              </svg>
            </div>
            <div>
              <h3 class="edit-card-title">Información Académica</h3>
              <p class="edit-card-subtitle">Facilita que compañeros de tu misma carrera y departamento te encuentren.</p>
            </div>
          </div>

          <div class="edit-form-body">
            <div class="edit-grid-2col">
              <div class="edit-field-group">
                <label for="department" class="edit-label">Departamento Académico</label>
                <div class="edit-select-wrapper">
                  <select id="department" name="department" class="edit-select">
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
              </div>

              <div class="edit-field-group">
                <label for="career" class="edit-label">Carrera</label>
                <div class="edit-input-wrapper">
                  <input type="text" id="career" name="career" class="edit-input"
                    value="<?= safe_output($user['career'] ?? '') ?>"
                    placeholder="Ej. Lic. en Desarrollo de Software" maxlength="100">
                </div>
              </div>
            </div>

            <?php if (($user['role'] ?? 'user') !== 'teacher'): ?>
            <div class="edit-field-group" style="margin-top: 18px;">
              <label for="semester" class="edit-label">Semestre actual</label>
              <div class="edit-select-wrapper" style="max-width: 320px;">
                <select id="semester" name="semester" class="edit-select">
                  <option value="">Selecciona tu semestre...</option>
                  <?php
                  $currentSemester = isset($user['semester']) ? (int) $user['semester'] : 0;
                  for ($s = 1; $s <= 12; $s++): ?>
                    <option value="<?= $s ?>" <?= $currentSemester === $s ? 'selected' : '' ?>><?= $s ?>° Semestre</option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Sticky Form Footer -->
        <div class="edit-form-footer">
          <a href="/profile" class="btn-cancel-edit">
            Cancelar
          </a>
          <button type="submit" class="btn-submit-profile">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Guardar cambios</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>
