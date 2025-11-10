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

      <form action="/register" method="POST">
        <div class="form-group">
          <input type="text" placeholder="Nombre de usuario" name="full_name" required />
        </div>

        <div class="form-group">
          <input type="email" placeholder="Correo electrónico" name="email" required />
        </div>

        <div class="form-group password-field">
          <input
            type="password"
            id="password"
            placeholder="Contraseña"
            name="password"
            required
          />
          <span class="eye-icon" onclick="togglePassword('password')">👁️</span>
        </div>

        <div class="form-group password-field">
          <input
            type="password"
            id="confirm-password"
            placeholder="Confirmar Contraseña"
            name="confirm_password"
            required
          />
          <span class="eye-icon" onclick="togglePassword('confirm-password')">👁️</span>
        </div>

        <div class="login-link">
          ¿Ya tienes una cuenta? <a href="/login">Iniciar Sesión</a>
        </div>

        <button type="submit">Registrarte</button>
      </form>
    </div>
  </body>
</html>
