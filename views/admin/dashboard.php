<?php
namespace App\views\admin;

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNIRED - Admin Dashboard</title>
    <link rel="stylesheet" href="<?php echo asset('assets/styles/dashboard.css'); ?>">
    <script src="<?php echo asset('js/chart.min.js'); ?>"></script>
    <script src="<?php echo asset('js/main.js'); ?>"></script>
    <script src="<?php echo asset('js/dashboard.js'); ?>"></script>
</head>

<body>
    <!-- Sidebar -->
    <div class="admin-sidebar" id="sidebar">
        <button class="sidebar-toggle-btn" id="sidebarToggle">
            <img src="../../assets/images/menu.png" alt="Toggle Menu">
        </button>

        <a href="/dashboard">
            <div class="logo-container">
                <img class="logo" src="../assets/images/logoUnired.png" alt="UNIRED Logo">
            </div>
        </a>

        <div class="sidebar-menu">
            <h3 class="sidebar-title">ESTADÍSTICAS</h3>
            <div class="stat-tab active" onclick="fetchUsersWithMostPosts(event)">
                Usuarios con más publicaciones
            </div>
            <div class="stat-tab" onclick="fetchUsersWithMostFriends(event)">
                Usuarios con más amigos
            </div>
            <div class="stat-tab" onclick="fetchPostsWithMostComments(event)">
                Posts con más comentarios
            </div>
            <div class="stat-tab" onclick="fetchPostsWithMostLikes(event)">
                Posts con más likes
            </div>
        </div>

        <a href="/logout" id="admin-logout" class="sidebar-logout">
            <button class="btn-logout">Cerrar sesión</button>
        </a>
    </div>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <!-- Top Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card card-teal">
                <div class="summary-info">
                    <h4 class="summary-title">Usuarios Totales</h4>
                    <p class="summary-value" id="summary-users">0</p>
                </div>
            </div>
            <div class="summary-card card-green">
                <div class="summary-info">
                    <h4 class="summary-title">Publicaciones</h4>
                    <p class="summary-value" id="summary-posts">0</p>
                </div>
            </div>
            <div class="summary-card card-pink">
                <div class="summary-info">
                    <h4 class="summary-title">Comentarios</h4>
                    <p class="summary-value" id="summary-comments">0</p>
                </div>
            </div>
            <div class="summary-card card-yellow">
                <div class="summary-info">
                    <h4 class="summary-title">Me gusta</h4>
                    <p class="summary-value" id="summary-likes">0</p>
                </div>
            </div>
            <div class="summary-card card-purple">
                <div class="summary-info">
                    <h4 class="summary-title">Amistades</h4>
                    <p class="summary-value" id="summary-friendships">0</p>
                </div>
            </div>
        </div>

        <!-- Main Chart Area -->
        <div class="chart-panel full-width">
            <div class="panel-header">
                <h3>Actividad en los últimos 30 días</h3>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="timelineChart"></canvas>
            </div>
        </div>

        <!-- Chart Section Tabs -->
        <div class="chart-tabs">
            <button class="chart-tab active" onclick="switchChartTab('actividad')">Actividad</button>
            <button class="chart-tab" onclick="switchChartTab('usuarios')">Usuarios</button>
            <button class="chart-tab" onclick="switchChartTab('interacciones')">Interacciones</button>
        </div>

        <!-- Charts Grid -->
        <div class="charts-grid">
            <div class="chart-panel">
                <div class="panel-header">
                    <h3 id="chartATitle">Publicaciones por día</h3>
                </div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="chartA"></canvas>
                </div>
            </div>
            <div class="chart-panel">
                <div class="panel-header">
                    <h3 id="chartBTitle">Tipo de contenido</h3>
                </div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="chartB"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-panel">
            <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 id="tableTitle">Usuarios con más publicaciones</h3>
                <a href="/admin/stats/pdf" target="_blank" style="text-decoration: none;">
                    <button class="btn-download">Descargar PDF</button>
                </a>
            </div>
            <div class="users-table-container">
                <table class="users-table">
                    <thead id="statsTableHeader">
                        <tr>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Correo electrónico</th>
                            <th>Cantidad</th>
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
    </div>

    <!-- Modals -->
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