<?php
namespace App\views;
use App\Components\FriendCard;
use App\Controllers\FriendController;
use App\Models\FriendModel;
use App\Models\UserModel;


$userId = $_GET['id'] ?? getCurrentUserId();

$userModel = new UserModel();
$friendModel = new FriendModel();
$friendController = new FriendController();

$friends = $friendModel->getFriends($userId);

$requests = $friendModel->getPendingRequests($userId);
$suggestions = $friendController->getSuggestions()['data'];
?>
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
                <h1 class="friends-title"><?= $userModel->getUserById($userId)['full_name'] ?></h1>

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
                    <div class="tab" data-filter="suggestion" onclick="filterFriends(event, 'suggestion')">Sugerencias
                    </div>
                </div>
            </div>

            <div id="searchResults" class="search-results-info" style="display: none;"></div>

            <div class="friends-grid" id="friendsGrid">
                <?php
                foreach ($friends as $friend) {
                    $friendCard = new FriendCard(
                        $friend['user_id'],
                        $friend['full_name'],
                        date('d/m/Y', strtotime($userModel->getUserById($friend['user_id'])['registration_date'])),
                        'friends',
                        $userModel->getUserById($friend['user_id'])['profile_picture'],
                        null
                    );
                    echo $friendCard->render();
                }
                foreach ($requests as $request) {
                    $friendCard = new FriendCard(
                        $request['sender_id'],
                        $userModel->getUserById($request['sender_id'])['full_name'],
                        date('d/m/Y', strtotime($userModel->getUserById($request['sender_id'])['registration_date'])),
                        'request',
                        $userModel->getUserById($request['sender_id'])['profile_picture'],
                        $request['request_id'],
                        null
                    );
                    echo $friendCard->render();
                }
                foreach ($suggestions as $suggestion) {
                    $friendCard = new FriendCard(
                        $suggestion['user_id'],
                        $suggestion['full_name'],
                        date('d/m/Y', strtotime($userModel->getUserById($suggestion['user_id'])['registration_date'])),
                        'suggestion',
                        $suggestion['profile_picture'],
                        null,
                        $suggestion['email'],
                    );

                    echo $friendCard->render();
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