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

$currentUser = $userModel->getUserById($userId);
$currentUserFullName = safe_output($currentUser['full_name'] ?? 'Usuario');

$friends = $friendModel->getFriends($userId);
$requests = $friendModel->getPendingRequests($userId);
$sentRequests = $friendModel->getSentRequests($userId);
$suggestions = $friendController->getSuggestions()['data'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Amigos</title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/styles.css'); ?>">
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    <script src="<?php echo asset('js/friends.js'); ?>"></script>
</head>

<body>
    <?php
    $currentPage = 'friends';
    require_once 'assets/sidebar.php';
    ?>
    <div class="main-content">
        <div class="friends-container">
            <div class="friends-header">
                <div class="friends-header-top">
                    <div class="friends-header-info">
                        <span class="friends-badge">Comunidad UNIRED</span>
                        <h1 class="friends-title"><?= $currentUserFullName ?></h1>
                        <p class="friends-subtitle">Gestiona tus amigos, solicitudes y descubre nuevas conexiones</p>
                    </div>

                    <div class="search-bar">
                        <span class="search-bar-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="text" id="friendSearchInput" class="search-input" placeholder="Buscar amigos por nombre..."
                            oninput="searchFriends(this.value)" maxlength="50" autocomplete="off">
                        <button type="button" class="search-clear" id="clearSearchBtn" onclick="clearSearch()"
                            style="display: none;" aria-label="Limpiar búsqueda">✕</button>
                    </div>
                </div>

                <div class="friends-tabs">
                    <div class="tab active" data-filter="friend" onclick="filterFriends(event, 'friend')">
                        <span>Todos los amigos</span>
                        <span class="tab-badge" id="badge-friend"><?= count($friends) ?></span>
                    </div>
                    <div class="tab" data-filter="request" onclick="filterFriends(event, 'request')">
                        <span>Solicitudes</span>
                        <span class="tab-badge" id="badge-request"><?= count($requests) ?></span>
                    </div>
                    <div class="tab" data-filter="pending" onclick="filterFriends(event, 'pending')">
                        <span>Pendientes</span>
                        <span class="tab-badge" id="badge-pending"><?= count($sentRequests) ?></span>
                    </div>
                    <div class="tab" data-filter="suggestion" onclick="filterFriends(event, 'suggestion')">
                        <span>Sugerencias</span>
                        <span class="tab-badge" id="badge-suggestion"><?= count($suggestions) ?></span>
                    </div>
                </div>
            </div>

            <div id="searchResults" class="search-results-info" style="display: none;"></div>

            <div class="friends-grid" id="friendsGrid">
                <?php
                foreach ($friends as $friend) {
                    $friendCard = new FriendCard(
                        (string) $friend['user_id'],
                        $friend['full_name'] ?? 'Usuario',
                        date('d/m/Y', strtotime($friend['registration_date'] ?? 'now')),
                        'friends',
                        getProfilePicture($friend['profile_picture'] ?? ''),
                        null
                    );
                    echo $friendCard->render();
                }
                foreach ($requests as $request) {
                    $friendCard = new FriendCard(
                        (string) $request['sender_id'],
                        $request['full_name'] ?? 'Usuario',
                        date('d/m/Y', strtotime($request['registration_date'] ?? 'now')),
                        'request',
                        getProfilePicture($request['profile_picture'] ?? ''),
                        (string) $request['request_id'],
                        null
                    );
                    echo $friendCard->render();
                }
                foreach ($sentRequests as $sentRequest) {
                    $friendCard = new FriendCard(
                        (string) $sentRequest['receiver_id'],
                        $sentRequest['full_name'] ?? 'Usuario',
                        date('d/m/Y', strtotime($sentRequest['registration_date'] ?? 'now')),
                        'pending',
                        getProfilePicture($sentRequest['profile_picture'] ?? ''),
                        (string) $sentRequest['receiver_id'],
                        null
                    );
                    echo $friendCard->render();
                }
                foreach ($suggestions as $suggestion) {
                    $friendCard = new FriendCard(
                        (string) $suggestion['user_id'],
                        $suggestion['full_name'] ?? 'Usuario',
                        date('d/m/Y', strtotime($suggestion['registration_date'] ?? 'now')),
                        'suggestion',
                        getProfilePicture($suggestion['profile_picture'] ?? ''),
                        null,
                        $suggestion['email'] ?? null,
                    );

                    echo $friendCard->render();
                }
                ?>

            </div>
            <div id="noResultsMessage" class="no-results" style="display: none;">
                <div class="no-results-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 id="noResultsTitle">Sin resultados</h3>
                <p id="noResultsSubtitle">No se encontraron personas en esta sección.</p>
            </div>
        </div>
    </div>
</body>

</html>
