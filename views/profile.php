<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Perfil de Usuario</title>
   <link rel="stylesheet" href="/assets/styles/styles.css">
</head>
<body>
    <?php
    $currentPage = 'profile';
    require_once 'assets/sidebar.php';
    require_once 'assets/postCard.php';
    
    ?>
    <div class="main-content">
        <div class="content-wrapper">
            <div class="profile-section">
            <div class="profile-photo"></div>
            <h2 class="profile-name">Manuel Orozco</h2>
            <p class="profile-bio">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.
            </p>
            
            <div class="stats-container">
                <div class="stat">
                    <div class="stat-number">2,100</div>
                    <div class="stat-label">Amigos</div>
                </div>
                <div class="stat">
                    <div class="stat-number">187</div>
                    <div class="stat-label">Me gusta</div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="/editProfile">
                    <button class="btn btn-primary">Editar perfil</button>
                </a>
            </div>
        </div>

        <?php
        foreach ($posts as $post) {
            renderPost($post);
        }
        ?>

    </div>
    <dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-delete-title">Confirmar eliminación</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar a <span class="friend-name">[Nombre del amigo]</span>?</p>
            </div>

            <div class="confirm-sep"></div>

            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-delete">Eliminar</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>
  <script src="../js/main.js"></script>
</body>
</html>