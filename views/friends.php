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
                    <input type="text" id="friendSearchInput" class="search-input" placeholder="Buscar amigos..."
                        oninput="searchFriends(this.value)">
                    <button class="search-clear" id="clearSearchBtn" onclick="clearSearch()"
                        style="display: none;">✕</button>
                </div>

                <div class="friends-tabs">
                    <div class="tab active" data-filter="friend" onclick="filterFriends(event, 'friend')">Todos los
                        amigos</div>
                    <div class="tab" data-filter="request" onclick="filterFriends(event, 'request')">Solicitudes</div>
                    <div class="tab" data-filter="send" onclick="filterFriends(event, 'send')">Enviar solicitud</div>
                </div>
            </div>

            <div id="searchResults" class="search-results-info" style="display: none;"></div>

            <div class="friends-grid" id="friendsGrid">
                <?php
                $friends = [
                    new FriendCard('Pedrito Navajas', '18/03/2025', 'friend', 'https://i.pravatar.cc/150?img=13'),
                    new FriendCard('Juanito Alimaña', '23/06/2025', 'friend', 'https://i.pravatar.cc/150?img=11'),
                    new FriendCard('Carlos Ruiz', '10/01/2025', 'request', 'https://i.pravatar.cc/150?img=12'),
                    new FriendCard('Ana Martínez', '05/04/2025', 'send', 'https://i.pravatar.cc/150?img=10'),
                    new FriendCard('María González', '15/02/2025', 'friend', 'https://i.pravatar.cc/150?img=9'),
                    new FriendCard('Pedro Sánchez', '20/05/2025', 'request', 'https://i.pravatar.cc/150?img=8'),
                ];

                foreach ($friends as $friend) {
                    echo $friend->render();
                }
                ?>

            </div>
            <div id="noResultsMessage" class="no-results" style="display: none;">
                <p>No se encontraron resultados para tu búsqueda.</p>
            </div>
        </div>
    </div>
    <script src="../js/main.js"></script>
    <script src="../js/friends.js"></script>
</body>

</html>