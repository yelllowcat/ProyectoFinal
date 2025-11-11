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

                <?php

                $friends = [
                    new FriendCard('Pedrito Navajas', '18/03/2025', 'friend', 'https://i.pravatar.cc/150?img=13'),
                    new FriendCard('Juanito Alimaña', '23/06/2025', 'friend', 'https://i.pravatar.cc/150?img=11'),
                    new FriendCard('Carlos Ruiz', '10/01/2025', 'request', 'https://i.pravatar.cc/150?img=12'),
                    new FriendCard('Ana Martínez', '05/04/2025', 'send', 'https://i.pravatar.cc/150?img=10'),
                ];

                foreach ($friends as $friend) {
                    echo $friend->render();
                }
                ?>

            </div>
        </div>
    </div>
    <script src="../js/main.js"></script>
    <script src="../js/friends.js"></script>
</body>

</html>