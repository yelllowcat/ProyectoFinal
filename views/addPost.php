<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UNIRED - Nueva Publicación</title>
    <link rel="stylesheet" href="../assets/styles/styles.css" />
  </head>
  <body>
    <div class="sidebar">
      <div class="logo-container">
        <div class="logo">
          <img src="../assets/images/logoUnired.png" alt="Logo" />
        </div>
      </div>

      <div class="menu-item active">
        <span class="menu-icon">➕</span>
        <span>Nueva publicación</span>
      </div>

      <a href="/posts" class="menu-item">
        <span class="menu-icon">📄</span>
        <span>Publicaciones</span>
      </a>

      <a href="/friends" class="menu-item">
        <span class="menu-icon">👥</span>
        <span>Amigos</span>
      </a>

      <a href="/profile" class="menu-item">
        <span class="menu-icon">👤</span>
        <span>Perfil</span>
      </a>

      <a href="/logout" class="menu-item">
        <span class="menu-icon">🚪</span>
        <span>Cerrar sesión</span>
      </a>
    </div>

    <div class="main-content">
      <div class="edit-container">
        <div class="post-preview">
          <div class="post-header-section">
            <div class="post-avatar"></div>
            <div class="post-user-info">
              <h3>Manuel Orozco</h3>
              <div class="post-date-info">Publicado el: 18/03/2025</div>
            </div>
          </div>

          <div class="post-prompt">
            <div class="post-prompt-title">¿Que estas pensando?</div>
            <div class="post-prompt-subtitle">Comparte con nosotros</div>
          </div>

          <div class="add-post-image-section">
            <img
              class="add-post-image"
              src="../assets/images/addImage.png"
              alt="add Image"
            />
          </div>

          <div class="post-text-section">
            <textarea
              class="post-textarea"
              id="postText"
              maxlength="500"
              oninput="updateCounter()"
            >
Blandit habitasse eleifend himenaeos maecenas risus dui congue torquent, felis curae eros cubilia justo iaculis ornare, inceptos est arcu odio mus diam rhoncus. Orci tortor semper parturient nascetur venenatis porta cum i</textarea
            >
            <div class="char-counter"><span id="charCount">350</span>/500</div>
          </div>

          <div class="action-buttons">
            <button class="btn btn-primary btn-post">Publicar</button>
          </div>
        </div>
      </div>
    </div>
  <script src="/main.js"></script>
  </body>
</html>
