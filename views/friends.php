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
        
        <a href="/posts" class="menu-item">
            <span class="menu-icon">📄</span>
            <span>Publicaciones</span>
        </a>
        
        <div class="menu-item active">
            <span class="menu-icon">👥</span>
            <span>Amigos</span>
        </div>
        
        <a href="/profile" class="menu-item">
            <span class="menu-icon">👤</span>
            <span>Perfil</span>
        </a>
        
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
                    <div class="tab active">Todos los amigos</div>
                    <a href="/friendReqs">
                        <div class="tab">Solicitudes</div>
                    </a>
                    <a href="/sendReqs">
                        <div class="tab">Enviar solicitud</div>
                    </a>
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
</body>
</html>