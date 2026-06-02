<?php
use App\Components\Alert;
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Iniciar Sesión</title>
  <link rel="stylesheet" href="<?php echo asset('assets/styles/login.css'); ?>">
  <script src="<?php echo asset('js/login.js'); ?>"></script>
</head>

<body>
  <div class="container">
    <div class="logo-container">
      <div class="logo">
        <img src="../assets/images/logoUnired.png" alt="UNIRED Logo" />
      </div>
    </div>
    <?php echo Alert::render(); ?>
    <h1>INICIAR SESIÓN</h1>

    <form method="post" action="/login">
      <div class="form-group">
        <input type="email" id="email" placeholder="Correo electrónico" name="email"
          value="<?= safe_output($_POST['email'] ?? '') ?>" required onpaste="return false;" maxlength="200" />
        <label for="email" class="form-label" data-help="Formato: correo@ejemplo.com">Correo electrónico</label>
      </div>

      <div class="form-group password-field">
        <input type="password" id="password" name="password" placeholder="Contraseña" required
          minlength="8"
          onpaste="return false;" maxlength="64" />
        <label for="password" class="form-label"
          data-help="Mínimo 8 caracteres">Contraseña</label>
        <img class="eye-icon" onclick="togglePassword()" src="../assets/images/eye-off.png" alt="Ocultar contraseña" />
      </div>

      <div class="register-link">
        ¿Eres un nuevo usuario? <a href="/register">Registrarse</a>
      </div>

      <button type="submit">Iniciar sesión</button>
    </form>
</body>

</html>