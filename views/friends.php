<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Amigos</title>
    <link rel="stylesheet" href="../assets/styles/styles.css">
</head>
<body>
    <?php
    $currentPage = 'friends';
    require_once 'assets/sidebar.php' ?>
    <div class="main-content">
        <div class="friends-container">
            <div class="friends-header">
                <h1 class="friends-title">Manuel Orozco</h1>
                
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Buscador">
                </div>

                <div class="friends-tabs">
                    <div class="tab active" onclick="fetchFriends(event)">Todos los amigos</div>
                        <div class="tab" onclick="fetchFriendRequests(event)">Solicitudes</div>
                        <div class="tab" onclick="fetchSendRequests(event)">Enviar solicitud</div>
                </div>
            </div>

            <div class="friends-grid">

                <div class="friend-card">
                    <div class="friend-avatar"></div>
                    <h3 class="friend-name">Pedrito Navajas</h3>
                    <p class="friend-date">Se unió el: 18/03/2025</p>
                    <button class="btn-view-profile">Ver perfil</button>
                </div>

                <div class="friend-card">
                    <div class="friend-avatar"></div>
                    <h3 class="friend-name">Juanito Alimaña</h3>
                    <p class="friend-date">Se unió el: 23/06/2025</p>
                    <button class="btn-view-profile">Ver perfil</button>
                </div>

                <div class="friend-card">
                    <div class="friend-avatar"></div>
                    <h3 class="friend-name">María González</h3>
                    <p class="friend-date">Se unió el: 15/02/2025</p>
                    <button class="btn-view-profile">Ver perfil</button>
                </div>

                <div class="friend-card">
                    <div class="friend-avatar"></div>
                    <h3 class="friend-name">Carlos Ruiz</h3>
                    <p class="friend-date">Se unió el: 10/01/2025</p>
                    <button class="btn-view-profile">Ver perfil</button>
                </div>

                <div class="friend-card">
                    <div class="friend-avatar"></div>
                    <h3 class="friend-name">Ana Martínez</h3>
                    <p class="friend-date">Se unió el: 05/04/2025</p>
                    <button class="btn-view-profile">Ver perfil</button>
                </div>

                <div class="friend-card">
                    <div class="friend-avatar"></div>
                    <h3 class="friend-name">Luis Fernández</h3>
                    <p class="friend-date">Se unió el: 28/03/2025</p>
                    <button class="btn-view-profile">Ver perfil</button>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/main.js"></script>
</body>
</html>