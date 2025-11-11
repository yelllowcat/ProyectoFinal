<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>UNIRED - Iniciar Sesión</title>
</head>
<link rel="stylesheet" href="../assets/styles/login.css" />

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
        <input type="email" placeholder="Correo electronico" name="email" required />
      </div>

      <div class="form-group password-field">
        <input type="password" id="password" name="password" placeholder="Contraseña" required />
        <img class="eye-icon" onclick="togglePassword()" src="../assets/images/eye-off.png" alt="Ocultar contraseña" />
      </div>

      <div class="register-link">
        ¿Eres un nuevo usuario? <a href="/register">Registrarse</a>
      </div>

      <button type="submit">Iniciar sesion</button>
    </form>
  </div>

  <script>
    let hidePasswordTimeout = null;

    function togglePassword() {
      const passwordField = document.getElementById("password");
      const eyeIcon = document.querySelector(".eye-icon");

      if (hidePasswordTimeout) {
        clearTimeout(hidePasswordTimeout);
        hidePasswordTimeout = null;
      }

      if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.src = "../assets/images/eye.png";

        hidePasswordTimeout = setTimeout(() => {
          passwordField.type = "password";
          eyeIcon.src = "../assets/images/eye-off.png";
          hidePasswordTimeout = null;
        }, 3000);

      } else {
        passwordField.type = "password";
        eyeIcon.src = "../assets/images/eye-off.png";
      }
    }
  </script>
</body>

</html>