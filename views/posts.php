<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Publicaciones</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <?php
    $currentPage = 'posts';
    require_once 'assets/sidebar.php';
    require_once 'assets/postCard.php'; 
    ?>   

    <div class="main-content">
        <div class="feed-container">
            <?php 
            foreach ($posts as $post) {
                renderPost($post);
            }
            ?>
        </div>
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