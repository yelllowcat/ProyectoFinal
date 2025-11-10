<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UNIRED - Editar Perfil</title>
    <link rel="stylesheet" href="../assets/styles/styles.css" />
  </head>
  <body>
    <?php
    $currentPage = 'editProfile';
    require_once 'assets/sidebar.php' ?>

    <div class="main-content">
      <div class="content-wrapper">
        <div class="profile-section-edit">
          <div class="profile-photo-edit"></div>

          <button class="btn-change-photo">Cambiar foto</button>

          <h2 class="profile-name">Manuel Orozco</h2>
          <p class="profile-date">25-octubre-2025</p>

          <button class="btn btn-delete">Eliminar cuenta</button>
        </div>
          <div class="form-group">
            <label>Nombre completo</label>
            <textarea placeholder="Manuel Orozco"></textarea>
          </div>

          <div class="form-group">
            <label>Bibliografía</label>
            <textarea placeholder="Descripción"></textarea>
          </div>

          <button class="btn btn-save" style="float: right; padding: 10px 35px">
            Guardar
          </button>
      </div>
    </div>
  </body>
</html>
