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
    <header class="admin-header">
        <a href="/dashboard" class="admin-logo-link">
            <img class="admin-logo" src="../assets/images/logoUnired.png" alt="UNIRED Logo">
        </a>
        <button class="btn-logout-header" id="admin-logout">Cerrar sesión</button>
    </header>

    <div class="admin-container">
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
                <div class="summary-card card-orange">
                    <div class="summary-info">
                        <h4 class="summary-title">Hashtags</h4>
                        <p class="summary-value" id="summary-hashtags">0</p>
                    </div>
                </div>
            </div>

            <!-- Stats Nav and Tables (Moved to Top) -->
            <div class="stat-nav">
                <button class="stat-btn active" onclick="fetchUsersWithMostPosts(event)">
                    Usuarios con más publicaciones
                </button>
                <button class="stat-btn" onclick="fetchUsersWithMostFriends(event)">
                    Usuarios con más amigos
                </button>
                <button class="stat-btn" onclick="fetchPostsWithMostComments(event)">
                    Posts con más comentarios
                </button>
                <button class="stat-btn" onclick="fetchPostsWithMostLikes(event)">
                    Posts con más likes
                </button>
                <button class="stat-btn" onclick="fetchTopHashtags(event)">
                    Hashtags más usados
                </button>
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

            <!-- Reports Table -->
            <div class="table-panel" style="margin-top: 20px; margin-bottom: 20px;">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 id="reportsTableTitle">Reportes Pendientes</h3>
                </div>
                <div class="users-table-container">
                    <table class="users-table">
                        <thead id="reportsTableHeader">
                            <tr>
                                <th>Id</th>
                                <th>Reportado Por</th>
                                <th>Razón</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="reportsTableBody">
                            <tr>
                                <td colspan="6" style="text-align:center;">Cargando reportes...</td>
                            </tr>
                        </tbody>
                    </table>
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

            <!-- Peak Usage Heatmap -->
            <div class="chart-panel full-width">
                <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <h3>Pico de uso de la plataforma</h3>
                    <div class="heatmap-toggles">
                        <button class="heatmap-toggle active" onclick="switchHeatmapRange('30', this)">Últimos 30 días</button>
                        <button class="heatmap-toggle" onclick="switchHeatmapRange('all', this)">Todo el tiempo</button>
                    </div>
                </div>
                <div class="heatmap-wrapper">
                    <div class="heatmap-y-labels" id="heatmapYLabels"></div>
                    <div>
                        <div class="heatmap-grid" id="heatmapGrid"></div>
                        <div class="heatmap-x-labels" id="heatmapXLabels"></div>
                    </div>
                </div>
                <div class="heatmap-legend">
                    <span>Bajo</span>
                    <span class="heatmap-legend-swatch" style="background:#e8f4f7;"></span>
                    <span class="heatmap-legend-swatch" style="background:#b3dfe5;"></span>
                    <span class="heatmap-legend-swatch" style="background:#6bc5d2;"></span>
                    <span class="heatmap-legend-swatch" style="background:#2d9dad;"></span>
                    <span class="heatmap-legend-swatch" style="background:#0d737d;"></span>
                    <span>Alto</span>
                </div>
            </div>

            <!-- Chart Section Tabs -->
            <div class="chart-tabs">
                <button class="chart-tab active" onclick="switchChartTab('actividad')">Actividad</button>
                <button class="chart-tab" onclick="switchChartTab('usuarios')">Usuarios</button>
                <button class="chart-tab" onclick="switchChartTab('interacciones')">Interacciones</button>
                <button class="chart-tab" onclick="switchChartTab('hashtags')">Hashtags</button>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <div class="chart-panel">
                    <div class="panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <h3 id="chartATitle">Publicaciones por día</h3>
                        <div class="hashtag-toggles" id="hashtagToggles" style="display:none;">
                            <button class="heatmap-toggle" onclick="switchHashtagRange(7, this)">7 días</button>
                            <button class="heatmap-toggle active" onclick="switchHashtagRange(30, this)">30 días</button>
                            <button class="heatmap-toggle" onclick="switchHashtagRange(90, this)">90 días</button>
                        </div>
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
        </div>
    </dialog>

    <dialog id="resolve-report-modal" class="confirm-dialog">
        <div class="confirm-box" style="max-width: 400px; padding: 20px;">
            <div class="confirm-head">
                <h3>Resolver Reporte</h3>
                <p class="confirm-subtitle">Selecciona la acción de moderación a tomar.</p>
            </div>
            <div class="confirm-sep"></div>
            <form id="resolve-report-form">
                <input type="hidden" id="resolve-report-id" name="report_id">
                
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="resolve-action" style="display:block; margin-bottom: 5px; font-weight: 500;">Acción</label>
                    <select id="resolve-action" name="action" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        <option value="dismiss">Solo desestimar (falsa alarma)</option>
                        <option value="delete">Eliminar contenido reportado</option>
                        <option value="suspend">Suspender usuario y eliminar contenido</option>
                    </select>
                </div>

                <div class="confirm-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-primary" style="flex: 1; padding: 10px; border-radius: 8px; background: #e74c3c; color: white; border: none; cursor: pointer; font-weight: 600;">Aplicar Acción</button>
                    <button type="button" class="btn-secondary" onclick="document.getElementById('resolve-report-modal').close()" style="flex: 1; padding: 10px; border-radius: 8px; background: #eee; border: none; cursor: pointer; font-weight: 600;">Cancelar</button>
                </div>
            </form>
        </div>
    </dialog>

</body>

</html>