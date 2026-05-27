let currentChart = null;
let timelineChartInstance = null;
let chartAInstance = null;
let chartBInstance = null;
const colors = {
    teal: '#4db8c4',
    green: '#7cb342',
    pink: '#FF6384',
    yellow: '#FFCE56',
    purple: '#9966FF',
    orange: '#FF9F40',
    gray: '#e0e0e0'
};

document.addEventListener('DOMContentLoaded', () => {
    // Modal logic for logout
    const logoutBtn = document.getElementById('admin-logout');
    const confirmLogoutModal = document.getElementById('confirm-logout-modal');
    
    if (logoutBtn && confirmLogoutModal) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            confirmLogoutModal.showModal();
        });

        const confirmActions = confirmLogoutModal.querySelector('.confirm-actions');
        if (confirmActions) {
            confirmActions.addEventListener('click', (e) => {
                const button = e.target.closest('button');
                if (!button) return;

                if (button.value === 'confirm') {
                    window.location.href = '/logout';
                } else if (button.value === 'cancel') {
                    confirmLogoutModal.close();
                }
            });
        }
    }

    // Fetch new endpoints
    fetchSummaryStats();
    fetchHashtagSummary();
    fetchActivityTimeline();
    fetchPeakUsageHeatmap('30');
    switchChartTab('actividad');

    // Load initial table data
    fetchUsersWithMostPosts({ target: document.querySelector('.stat-btn.active') });
    fetchReports();

    const resolveForm = document.getElementById('resolve-report-form');
    if (resolveForm) {
        resolveForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('resolve-report-id').value;
            const action = document.getElementById('resolve-action').value;
            
            try {
                const fd = new FormData();
                fd.append('action', action);

                const res = await fetch(`/admin/reports/${id}/resolve`, { 
                    method: 'POST',
                    body: fd
                });
                const json = await res.json();
                if (json.success) {
                    document.getElementById('resolve-report-modal').close();
                    fetchReports();
                } else {
                    alert(json.message);
                }
            } catch (error) {
                console.error(error);
            }
        });
    }
});

// Animate numbers counting up
function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Fetch Summary Stats for top cards
function fetchSummaryStats() {
    fetch('/admin/stats/summary')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const d = data.data;
                const elUsers = document.getElementById('summary-users');
                if (elUsers) animateValue(elUsers, 0, d.total_users || 0, 1500);
            }
        })
        .catch(error => console.error('Error fetching summary stats:', error));
}

// Fetch Activity Timeline for Main Chart
function fetchActivityTimeline() {
    fetch('/admin/stats/activity-timeline')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderTimelineChart(data.data);
            }
        })
        .catch(error => console.error('Error fetching timeline:', error));
}

function renderTimelineChart(data) {
    const ctx = document.getElementById('timelineChart');
    if (!ctx) return;

    if (timelineChartInstance) {
        timelineChartInstance.destroy();
    }

    timelineChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Publicaciones',
                    data: data.posts,
                    borderColor: colors.teal,
                    backgroundColor: 'rgba(77, 184, 196, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Comentarios',
                    data: data.comments,
                    borderColor: colors.pink,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.4
                },
                {
                    label: 'Me gusta',
                    data: data.likes,
                    borderColor: colors.yellow,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: colors.gray } }
            }
        }
    });
}

// Chart Tab Switching
let currentTab = 'actividad';

function switchChartTab(tab) {
    currentTab = tab;
    document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.chart-tab').forEach(t => {
        const tabText = t.textContent.trim().toLowerCase();
        if (tabText === tab || (tab === 'hashtags' && tabText === 'hashtags')) t.classList.add('active');
    });

    const hashtagToggles = document.getElementById('hashtagToggles');
    if (hashtagToggles) {
        hashtagToggles.style.display = tab === 'hashtags' ? 'flex' : 'none';
    }

    if (tab === 'actividad') {
        fetchPostsByDayOfWeek();
        fetchPostImageRatio();
    } else if (tab === 'usuarios') {
        fetchUserGrowth();
        fetchUserActivitySplit();
    } else if (tab === 'interacciones') {
        fetchEngagementBreakdown();
        fetchTopEngagedUsers();
    } else if (tab === 'hashtags') {
        fetchHashtagTrend(30);
        fetchTopHashtagsForChart();
    }
}

// User Growth Chart
function fetchUserGrowth() {
    fetch('/admin/stats/user-growth')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderChartA('bar', data.data.labels, data.data.counts, 'Crecimiento de Usuarios (semanas)', colors.teal);
            }
        })
        .catch(error => console.error('Error fetching user growth:', error));
}

// Activity Split Chart
function fetchUserActivitySplit() {
    fetch('/admin/stats/user-activity-split')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderChartB('doughnut', ['Activos', 'Inactivos'], [data.data.active, data.data.inactive], 'Usuarios Activos vs Inactivos', [colors.teal, colors.gray]);
            }
        })
        .catch(error => console.error('Error fetching activity split:', error));
}

// Posts by Day of Week
function fetchPostsByDayOfWeek() {
    fetch('/admin/stats/posts-by-day')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.labels && data.data.counts) {
                renderChartA('bar', data.data.labels, data.data.counts, 'Publicaciones por Día de la Semana', colors.teal);
            } else {
                console.error('Invalid postsby-day response:', data);
                renderChartA('bar', ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'], [0,0,0,0,0,0,0], 'Publicaciones por Día de la Semana', colors.teal);
            }
        })
        .catch(error => {
            console.error('Error fetching posts by day:', error);
            renderChartA('bar', ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'], [0,0,0,0,0,0,0], 'Publicaciones por Día de la Semana', colors.teal);
        });
}

// Post Image Ratio
function fetchPostImageRatio() {
    fetch('/admin/stats/post-image-ratio')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderChartB('doughnut', ['Con imagen', 'Solo texto'], [data.data.with_image, data.data.text_only], 'Tipo de Contenido', [colors.teal, colors.green]);
            }
        })
        .catch(error => console.error('Error fetching image ratio:', error));
}

// Engagement Breakdown (doughnut)
function fetchEngagementBreakdown() {
    fetch('/admin/stats/engagement-breakdown')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const labels = ['Likes en posts', 'Comentarios', 'Respuestas', 'Likes en comments', 'Likes en replies'];
                const values = Object.values(data.data);
                renderChartA('doughnut', labels, values, 'Distribución de Interacciones', [colors.teal, colors.green, colors.pink, colors.yellow, colors.purple]);
            }
        })
        .catch(error => console.error('Error fetching engagement:', error));
}

// Top Engaged Users (horizontal bar)
function fetchTopEngagedUsers() {
    fetch('/admin/stats/top-engaged-users')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const top5 = data.data.slice(0, 5);
                const labels = top5.map(u => u.full_name.split(' ')[0]);
                const values = top5.map(u => parseInt(u.total_engagement));
                renderChartB('bar', labels, values, 'Usuarios con Mayor Participación', colors.teal, true);
            }
        })
        .catch(error => console.error('Error fetching top engaged users:', error));
}

// Render Chart A (left panel)
function renderChartA(type, labels, dataArray, titleText, bgColors, horizontal) {
    const ctx = document.getElementById('chartA');
    if (!ctx) return;
    document.getElementById('chartATitle').innerText = titleText;
    if (chartAInstance) chartAInstance.destroy();

    const datasets = [{
        label: '',
        data: dataArray,
        backgroundColor: Array.isArray(bgColors) ? bgColors.slice(0, dataArray.length) : (type === 'doughnut' || type === 'pie' ? [colors.teal, colors.green, colors.pink, colors.yellow, colors.purple, colors.orange].slice(0, dataArray.length) : bgColors),
        borderWidth: type === 'doughnut' || type === 'pie' ? 1 : 0,
        borderRadius: type === 'bar' ? 6 : 0
    }];

    chartAInstance = new Chart(ctx, {
        type: type,
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: horizontal ? 'y' : 'x',
            plugins: {
                legend: { display: type === 'doughnut' || type === 'pie', position: 'right' }
            },
            scales: type === 'bar' && !horizontal ? {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: colors.gray } }
            } : (type === 'bar' && horizontal ? {
                x: { beginAtZero: true, grid: { color: colors.gray } },
                y: { grid: { display: false } }
            } : {})
        }
    });
}

// Render Chart B (right panel)
function renderChartB(type, labels, dataArray, titleText, bgColors, horizontal) {
    const ctx = document.getElementById('chartB');
    if (!ctx) return;
    document.getElementById('chartBTitle').innerText = titleText;
    if (chartBInstance) chartBInstance.destroy();

    const datasets = [{
        label: '',
        data: dataArray,
        backgroundColor: Array.isArray(bgColors) ? bgColors.slice(0, dataArray.length) : (type === 'doughnut' || type === 'pie' ? [colors.teal, colors.green, colors.pink, colors.yellow, colors.purple, colors.orange].slice(0, dataArray.length) : bgColors),
        borderWidth: type === 'doughnut' || type === 'pie' ? 1 : 0,
        borderRadius: type === 'bar' ? 6 : 0
    }];

    chartBInstance = new Chart(ctx, {
        type: type,
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: horizontal ? 'y' : 'x',
            plugins: {
                legend: { display: type === 'doughnut' || type === 'pie', position: 'right' }
            },
            scales: type === 'bar' && !horizontal ? {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: colors.gray },
                    ticks: { precision: 0 }
                }
            } : (type === 'bar' && horizontal ? {
                x: {
                    beginAtZero: true,
                    grid: { color: colors.gray },
                    ticks: { precision: 0 }
                },
                y: { grid: { display: false } }
            } : {})
        }
    });
}

function updateActiveTab(event) {
    if (event && event.target) {
        document.querySelectorAll('.stat-btn').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
    }
}

// Fetch existing stats for table
window.fetchUsersWithMostPosts = function (event) {
    updateActiveTab(event);
    document.getElementById('tableTitle').innerText = 'Usuarios con más publicaciones';
    fetchDataAndRenderTable('/admin/stats/users-posts', 'user', 'post_count', 'Nº publicaciones');
};

window.fetchUsersWithMostFriends = function (event) {
    updateActiveTab(event);
    document.getElementById('tableTitle').innerText = 'Usuarios con más amigos';
    fetchDataAndRenderTable('/admin/stats/users-friends', 'user', 'friend_count', 'Nº amigos');
};

window.fetchPostsWithMostComments = function (event) {
    updateActiveTab(event);
    document.getElementById('tableTitle').innerText = 'Posts con más comentarios';
    fetchDataAndRenderTable('/admin/stats/posts-comments', 'post', 'comment_count', 'Nº comentarios');
};

window.fetchPostsWithMostLikes = function (event) {
    updateActiveTab(event);
    document.getElementById('tableTitle').innerText = 'Posts con más likes';
    fetchDataAndRenderTable('/admin/stats/posts-likes', 'post', 'like_count', 'Nº likes');
};

function fetchDataAndRenderTable(url, type, countField, countLabel) {
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const normalized = data.data.map(item => {
                    item.count = item[countField];
                    return item;
                });
                if (type === 'user') {
                    renderUserTable(normalized, countLabel);
                } else if (type === 'post') {
                    renderPostTable(normalized, countLabel);
                }
            }
        })
        .catch(error => console.error('Error fetching data:', error));
}

function renderUserTable(data, countLabel) {
    const thead = document.getElementById('statsTableHeader');
    thead.innerHTML = `
        <tr>
            <th>Id</th>
            <th>Nombre</th>
            <th>Correo electrónico</th>
            <th>${countLabel}</th>
            <th>Acción</th>
        </tr>
    `;

    const tbody = document.getElementById('statsTableBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay datos disponibles.</td></tr>';
        return;
    }

    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.user_id}</td>
            <td>${item.full_name}</td>
            <td>${item.email}</td>
            <td style="font-weight:bold; color:${colors.teal}">${item.count}</td>
            <td><button class="btn-view-profile-table" onclick="window.location.href='/profile?id=${item.user_id}'">Ver perfil</button></td>
        `;
        tbody.appendChild(tr);
    });
}

function renderPostTable(data, countLabel) {
    const thead = document.getElementById('statsTableHeader');
    thead.innerHTML = `
        <tr>
            <th>Id Post</th>
            <th>Autor</th>
            <th>Contenido</th>
            <th>${countLabel}</th>
            <th>Acción</th>
        </tr>
    `;

    const tbody = document.getElementById('statsTableBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay datos disponibles.</td></tr>';
        return;
    }

    data.forEach(item => {
        const tr = document.createElement('tr');
        const contentSnippet = item.content.length > 50 ? item.content.substring(0, 50) + '...' : item.content;
        
        tr.innerHTML = `
            <td>${item.post_id}</td>
            <td>${item.author_name}</td>
            <td>${contentSnippet}</td>
            <td style="font-weight:bold; color:${colors.teal}">${item.count}</td>
            <td><button class="btn-view-profile-table" onclick="window.location.href='/profile?id=${item.user_id}'">Ver autor</button></td>
        `;
        tbody.appendChild(tr);
    });
}

window.fetchReports = function () {
    fetch('/admin/reports')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderReportTable(data.data);
            }
        })
        .catch(error => console.error('Error fetching reports:', error));
};

function renderReportTable(data) {
    const tbody = document.getElementById('reportsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay reportes pendientes.</td></tr>';
        return;
    }

    data.forEach(item => {
        const tr = document.createElement('tr');

        let targetText = '';
        let targetUrl = '/report/view/' + item.report_id;
        let targetLabel = 'Ver contenido reportado';

        if (item.reported_user_id) {
            targetText = 'Usuario: ' + item.reported_user_name;
        } else if (item.post_id) {
            const snippet = item.post_content ? item.post_content.substring(0, 20) + '...' : '';
            targetText = 'Post: ' + snippet;
        } else if (item.comment_id) {
            targetText = 'Comentario';
        } else if (item.reply_id) {
            targetText = 'Respuesta';
        }

        const externalLinkIcon = targetUrl ? `
            <a href="${targetUrl}" target="_blank" title="${targetLabel}" style="text-decoration: none; display: inline-flex; align-items: center; margin-left: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #4db8c4; vertical-align: middle;">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </a>
        ` : '';

        let statusBadge = item.status === 'pending'
            ? '<span style="background: #ff9800; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px;">Pendiente</span>'
            : '<span style="background: #4caf50; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px;">Resuelto</span>';

        tr.innerHTML = `
            <td>${item.report_id}</td>
            <td>${item.reporter_name}</td>
            <td>
                <strong>${item.reason}</strong><br>
                <small style="color: #666;">${targetText}${externalLinkIcon}</small>
            </td>
            <td>${new Date(item.created_at).toLocaleDateString()}</td>
            <td>${statusBadge}</td>
            <td>
                <div style="display:flex; gap: 5px;">
                    ${item.status === 'pending' ? `<button class="btn-view-profile-table" style="background:#4caf50;" onclick="resolveReport(${item.report_id})">Resolver</button>` : ''}
                    <button class="btn-view-profile-table" style="background:#f44336;" onclick="deleteReport(${item.report_id})">Eliminar</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

window.resolveReport = function(id) {
    const modal = document.getElementById('resolve-report-modal');
    document.getElementById('resolve-report-id').value = id;
    modal.showModal();
};

window.deleteReport = async function(id) {
    if (!confirm('¿Eliminar este reporte permanentemente?')) return;
    try {
        const res = await fetch(`/admin/reports/${id}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.success) {
            fetchReports();
        } else {
            alert(json.message);
        }
    } catch (e) {
        console.error(e);
    }
};

// Peak Usage Heatmap
function fetchPeakUsageHeatmap(range) {
    fetch('/admin/stats/peak-usage?range=' + range)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                buildHeatmap(data.data);
            }
        })
        .catch(error => console.error('Error fetching peak usage:', error));
}

function switchHeatmapRange(range, btn) {
    document.querySelectorAll('.heatmap-toggle').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    fetchPeakUsageHeatmap(range);
}

function buildHeatmap(data) {
    const grid = document.getElementById('heatmapGrid');
    const yLabels = document.getElementById('heatmapYLabels');
    const xLabels = document.getElementById('heatmapXLabels');
    if (!grid || !yLabels || !xLabels) return;

    yLabels.innerHTML = '';
    xLabels.innerHTML = '';
    grid.innerHTML = '';

    var dayNames = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    var max = data.max || 1;
    var thresholds = [0, max * 0.2, max * 0.4, max * 0.6, max * 0.8];
    var palette = ['#e8f4f7', '#b3dfe5', '#6bc5d2', '#2d9dad', '#0d737d'];

    function getColor(val) {
        if (val <= 0) return '#fafafa';
        for (var i = palette.length - 1; i >= 0; i--) {
            if (val >= thresholds[i]) return palette[i];
        }
        return palette[0];
    }

    for (var r = 0; r < 7; r++) {
        var yLabel = document.createElement('div');
        yLabel.className = 'heatmap-y-label';
        yLabel.textContent = dayNames[r];
        yLabels.appendChild(yLabel);

        for (var c = 0; c < 24; c++) {
            var val = data.data[r] ? (data.data[r][c] || 0) : 0;
            var cell = document.createElement('div');
            cell.className = 'heatmap-cell';
            if (val === 0) cell.classList.add('heatmap-cell-zero');
            cell.style.backgroundColor = getColor(val);
            cell.setAttribute('title', dayNames[r] + ' ' + data.hours[c] + ' — ' + val + ' actividades');
            grid.appendChild(cell);
        }
    }

    for (var h = 0; h < 24; h++) {
        var xLabel = document.createElement('div');
        xLabel.className = 'heatmap-x-label';
        xLabel.textContent = h % 3 === 0 ? h + 'h' : '';
        xLabels.appendChild(xLabel);
    }
}

window.renderPostTable = renderPostTable;

// Hashtag Analytics
function fetchHashtagSummary() {
    fetch('/admin/stats/hashtag-summary')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const el = document.getElementById('summary-hashtags');
                if (el) animateValue(el, 0, data.data.total_hashtags || 0, 1500);
            }
        })
        .catch(error => console.error('Error fetching hashtag summary:', error));
}

window.fetchTopHashtags = function (event) {
    updateActiveTab(event);
    document.getElementById('tableTitle').innerText = 'Hashtags más usados';
    fetch('/admin/stats/top-hashtags')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderHashtagTable(data.data);
            }
        })
        .catch(error => console.error('Error fetching top hashtags:', error));
};

function renderHashtagTable(data) {
    const thead = document.getElementById('statsTableHeader');
    thead.innerHTML = `
        <tr>
            <th>Hashtag</th>
            <th>Publicaciones</th>
            <th>Likes</th>
            <th>Comentarios</th>
            <th>Acción</th>
        </tr>
    `;

    const tbody = document.getElementById('statsTableBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay datos disponibles.</td></tr>';
        return;
    }

    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>#${item.name}</strong></td>
            <td style="font-weight:bold; color:${colors.teal}">${item.post_count}</td>
            <td>${item.total_likes}</td>
            <td>${item.total_comments}</td>
            <td><button class="btn-view-profile-table" onclick="window.location.href='/hashtag/${encodeURIComponent(item.name)}'">Ver posts</button></td>
        `;
        tbody.appendChild(tr);
    });
}

function fetchHashtagTrend(range = 30) {
    fetch('/admin/stats/hashtag-trend?range=' + range)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                renderHashtagTrendChart(data.data);
            }
        })
        .catch(error => console.error('Error fetching hashtag trend:', error));
}

function renderHashtagTrendChart(data) {
    const ctx = document.getElementById('chartA');
    if (!ctx) return;
    document.getElementById('chartATitle').innerText = 'Tendencia de Hashtags';
    if (chartAInstance) chartAInstance.destroy();

    chartAInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: data.datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    grid: { color: colors.gray },
                    ticks: { precision: 0 }
                }
            }
        }
    });
}

function fetchTopHashtagsForChart() {
    fetch('/admin/stats/top-hashtags?sort=posts')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const top5 = data.data.slice(0, 5);
                const labels = top5.map(h => '#' + h.name);
                const values = top5.map(h => parseInt(h.post_count));
                renderChartB('bar', labels, values, 'Top Hashtags por Publicaciones', colors.teal, true);
            }
        })
        .catch(error => console.error('Error fetching top hashtags for chart:', error));
}

window.switchHashtagRange = function(range, btn) {
    document.querySelectorAll('.hashtag-toggles .heatmap-toggle').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    fetchHashtagTrend(range);
};
