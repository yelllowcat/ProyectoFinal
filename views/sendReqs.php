<?php
if (!isLoggedIn()) {
    header('Location: /login');
    exit();
}
$currentUser = getCurrentUser();
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
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo">
                <img src="../assets/images/logoUnired.png" alt="UNIRED Logo">
            </div>
        </div>
        
        <div class="menu-item">
            <span class="menu-icon">➕</span>
            <span>Nueva publicación</span>
        </div>
        
        <div class="menu-item">
            <span class="menu-icon">📄</span>
            <span>Publicaciones</span>
        </div>
        
        <div class="menu-item active">
            <span class="menu-icon">👥</span>
            <span>Amigos</span>
        </div>
        
        <div class="menu-item">
            <span class="menu-icon">👤</span>
            <span>Perfil</span>
        </div>
        
        <div class="menu-item">
            <span class="menu-icon">🚪</span>
            <span>Cerrar sesión</span>
        </div>
    </div>

    <div class="main-content">
        <div class="friends-container">
            <div class="friends-header">
                <h1 class="friends-title">Manuel Orozco</h1>
                
                <div class="search-bar">
                    <input type="text" class="search-input" placeholder="Buscador">
                </div>

                <div class="friends-tabs">
                    <a href="/friends">
                        <div class="tab">Todos los amigos</div>
                    </a>
                    <a href="/friendReqs">
                        <div class="tab">Solicitudes</div>
                    </a>
                    <div class="tab active">Enviar solicitud</div>
                </div>
            </div>

            <div class="friends-list-container">
                <div class="friend-request-card">
                    <div class="friend-request-avatar">
                        <img src="https://placehold.co/140x140/e0e0e0/333?text=Foto" alt="Foto de perfil">
                    </div>
                    <div class="friend-request-info">
                        <h3 class="friend-request-name">Casimiro Paredes</h3>
                        <p class="friend-request-date">Se unió el: 18/03/2025</p>
                        <p class="friend-request-bio">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam."</p>
                        <button class="btn btn-send">Enviar solicitud</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

