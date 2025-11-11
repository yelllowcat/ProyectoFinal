<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Registro</title>
  <link rel="stylesheet" href="../assets/styles/register.css" />
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
    <form action="/register" method="POST">
      <div class="form-group">
        <input type="text" placeholder="Nombre de usuario" name="full_name" required />
      </div>

      <div class="form-group">
        <input type="email" placeholder="Correo electrónico" name="email" required />
      </div>

      <div class="form-group password-field">
        <input type="password" id="password" placeholder="Contraseña" name="password" required />
        <img class="eye-icon password" onclick="togglePassword(event)" src="../assets/images/eye-off.png"
          alt="Ocultar contraseña" />
      </div>

      <div class="form-group password-field">
        <input type="password" id="confirm-password" placeholder="Confirmar Contraseña" name="confirm_password"
          required />
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