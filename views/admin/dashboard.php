<?php
namespace App\views\admin;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Administrador</title>
    <link rel="stylesheet" href="../assets/styles/dashboard.css">
</head>

<body>
    <div class="admin-header">
        <div class="logo">
            <img src="../assets/images/logoUnired.png" alt="UNIRED Logo">
        </div>
        <h1 class="admin-title">Administrador</h1>
        <a href="/logout">
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
        <button class="btn-download">Estadísticas en PDF</button>

        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>correo electronico</th>
                        <th>Nº publicaciones</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>John Doe</td>
                        <td>John.Doe@gmail.com</td>
                        <td>120</td>
                        <td><a href="/profile/1"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Jane Smith</td>
                        <td>Jane.Smith@gmail.com</td>
                        <td>100</td>
                        <td><a href="/profile/2"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Carlos Pérez</td>
                        <td>Carlos.Pérez@gmail.com</td>
                        <td>80</td>
                        <td><a href="/profile/3"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>María López</td>
                        <td>Maria.López@gmail.com</td>
                        <td>57</td>
                        <td><a href="/profile/4"></a><button class="btn-view-profile-table">Ver perfil</button></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Ana Torres</td>
                        <td>Ana.Torres@gmail.com</td>
                        <td>41</td>
                        <td><a href="/profile/5"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Pedro Martínez</td>
                        <td>Pedro.Martinez@gmail.com</td>
                        <td>35</td>
                        <td><a href="/profile/6"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Laura García</td>
                        <td>Laura.Garcia@gmail.com</td>
                        <td>28</td>
                        <td><a href="/profile/7"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Miguel Rodríguez</td>
                        <td>Miguel.Rodriguez@gmail.com</td>
                        <td>22</td>
                        <td><a href="/profile/8"><button class="btn-view-profile-table">Ver perfil</button></a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../../js/main.js"></script>
    <script src="../../js/dashboard.js"></script>
</body>

</html>