<?php
namespace App\views\admin;


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Administrador</title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/dashboard.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    <script src="<?php echo asset('js/dashboard.js'); ?>"></script>
</head>

<body>
    <div class="admin-header">
        <div class="logo">
            <img src="../assets/images/logoUnired.png" alt="UNIRED Logo">
        </div>
        <h1 class="admin-title">Administrador</h1>
        <a href="/logout" id="admin-logout">
            <button class="btn-logout">Cerrar sesión</button>
        </a>
    </div>

    <div class="admin-content">
        <div class="stats-tabs">
            <div class="stat-tab active" onclick="fetchUsersWithMostPosts(event)">Usuarios con mas publicaciones</div>
            <div class="stat-tab" onclick="fetchUsersWithMostFriends(event)">Usuarios con mas amigos</div>
            <div class="stat-tab" onclick="fetchPostsWithMostComments(event)">Publicaciones con mas comentarios</div>
            <div class="stat-tab" onclick="fetchPostsWithMostLikes(event)">Publicaciones con mas "Me gusta"</div>
        </div>
        <a href="/admin/stats/pdf" target="_blank" style="text-decoration: none;">
            <button class="btn-download">Descargar Estadísticas en PDF</button>
        </a>

        <div class="users-table-container">
            <table class="users-table">
                <thead id="statsTableHeader">
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Correo electrónico</th>
                        <th>Nº publicaciones</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="statsTableBody">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            Cargando estadísticas...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <dialog id="confirm-logout-modal" class="confirm-dialog" aria-labelledby="confirm-logout-title">
        <div class="confirm-box">
            <div class="confirm-head">
                <h3 id="confirm-logout-title">Confirmar cierre de sesión</h3>
                <p class="confirm-subtitle">¿Estás seguro/a de que deseas cerrar sesión?</p>
            </div>
            <div class="confirm-sep"></div>
            <form method="dialog" class="confirm-actions">
                <button value="confirm" class="confirm-logout">Cerrar sesión</button>
                <button value="cancel" class="confirm-cancel">Cancelar</button>
            </form>
        </div>
    </dialog>
</body>

</html>