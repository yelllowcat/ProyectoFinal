<?php
use App\Components\Alert;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Registro</title>
  <link rel="stylesheet" href="<?php echo asset('assets/styles/register.css'); ?>">
</head>

<body>
  <div class="container">
    <div class="logo-container">
      <div class="logo">
        <img src="../assets/images/logoUnired.png" alt="UNIRED Logo" />
      </div>
    </div>

    <h1>REGISTRO</h1>
    <?php echo Alert::render(); ?>
    <form action="/register" method="POST" novalidate>
      <?= function_exists('csrfField') ? csrfField() : '' ?>
      <div class="form-group">
        <input type="text" id="full_name" placeholder="Nombre de usuario" name="full_name" required
          pattern="^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ'\s]{3,50}$" minlength="3" maxlength="50"
          value="<?= safe_output($_POST['full_name'] ?? '') ?>" onpaste="return false;" />
        <label for="full_name" class="form-label" data-help="Solo letras, mínimo 3 caracteres">Nombre de usuario</label>
      </div>

      <div class="form-group">
        <input type="text" inputmode="email" autocomplete="email" id="email" placeholder="Correo electrónico" name="email" required
          pattern="^.*@(uabcs\.mx|alu\.uabcs\.mx)$" title="Debe ser un correo @uabcs.mx o @alu.uabcs.mx"
          value="<?= safe_output($_POST['email'] ?? '') ?>" onpaste="return false;" maxlength="200" />
        <label for="email" class="form-label" data-help="Formato: usuario@uabcs.mx o @alu.uabcs.mx">Correo electrónico</label>
      </div>
      <div class="form-group">
        <select id="department" name="department" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; background: inherit; color: inherit;">
          <option value="">Departamento Académico (opcional)...</option>
          <option value="Ciencias de la Computación" <?= (['department'] ?? '') === 'Ciencias de la Computación' ? 'selected' : '' ?>>Ciencias de la Computación</option>
          <option value="Ciencias Marinas y Costeras" <?= (['department'] ?? '') === 'Ciencias Marinas y Costeras' ? 'selected' : '' ?>>Ciencias Marinas y Costeras</option>
          <option value="Ciencias Sociales y Jurídicas" <?= (['department'] ?? '') === 'Ciencias Sociales y Jurídicas' ? 'selected' : '' ?>>Ciencias Sociales y Jurídicas</option>
          <option value="Humanidades" <?= (['department'] ?? '') === 'Humanidades' ? 'selected' : '' ?>>Humanidades</option>
          <option value="Economía y Administración" <?= (['department'] ?? '') === 'Economía y Administración' ? 'selected' : '' ?>>Economía y Administración</option>
          <option value="Ingeniería Pesquera" <?= (['department'] ?? '') === 'Ingeniería Pesquera' ? 'selected' : '' ?>>Ingeniería Pesquera</option>
          <option value="Agronomía y Zootecnia" <?= (['department'] ?? '') === 'Agronomía y Zootecnia' ? 'selected' : '' ?>>Agronomía y Zootecnia</option>
          <option value="Ciencias de la Tierra" <?= (['department'] ?? '') === 'Ciencias de la Tierra' ? 'selected' : '' ?>>Ciencias de la Tierra</option>
        </select>
      </div>

      <div class="form-group">
        <input type="text" id="career" placeholder="Carrera (ej. Lic. en Desarrollo de Software)" name="career"
          value="<?= safe_output(['career'] ?? '') ?>" maxlength="100" />
        <label for="career" class="form-label">Carrera (opcional)</label>
      </div>

      <div class="form-group">
        <select id="semester" name="semester" class="form-select" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; background: inherit; color: inherit;">
          <option value="">Semestre (opcional)...</option>
          <?php for ($s = 1; $s <= 12; $s++): ?>
            <option value="<?= $s ?>" <?= (int)($_POST['semester'] ?? 0) === $s ? 'selected' : '' ?>><?= $s ?>° Semestre</option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group password-field">
        <input type="password" id="password" placeholder="Contraseña" name="password" required
          minlength="8"
          maxlength="64" />
        <label for="password" class="form-label"
          data-help="Mínimo 8 caracteres">Contraseña</label>
        <img class="eye-icon password" onclick="togglePassword(event)" src="../assets/images/eye-off.png"
          alt="Ocultar contraseña" />
      </div>

      <div class="form-group password-field">
        <input type="password" id="confirm-password" placeholder="Confirmar Contraseña" name="confirm_password" required
          minlength="8"
          maxlength="64" />
        <label for="confirm-password" class="form-label" data-help="Debe coincidir con la contraseña">Confirmar
          Contraseña</label>
        <img class="eye-icon confirm-password" onclick="togglePassword(event)" src="../assets/images/eye-off.png"
          alt="Ocultar contraseña" />
      </div>

      <div class="login-link">
        ¿Ya tienes una cuenta? <a href="/login">Iniciar Sesión</a>
      </div>

      <button type="submit">Registrarte</button>
    </form>
  </div>
  <script>
    function togglePassword(event) {
      const eyeIcon = event.target;
      const container = eyeIcon.closest('.password-field');
      const passwordField = container.querySelector('input[type="password"], input[type="text"]');

      if (container.hideTimeout) {
        clearTimeout(container.hideTimeout);
        container.hideTimeout = null;
      }

      if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.src = "../assets/images/eye.png";
        eyeIcon.alt = "hide password";

        container.hideTimeout = setTimeout(() => {
          passwordField.type = "password";
          eyeIcon.src = "../assets/images/eye-off.png";
          eyeIcon.alt = "show password";
          container.hideTimeout = null;
        }, 3000);

      } else {
        passwordField.type = "password";
        eyeIcon.src = "../assets/images/eye-off.png";
        eyeIcon.alt = "show password";
      }
    }
  </script>
</body>

</html>