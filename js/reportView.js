document.addEventListener('DOMContentLoaded', () => {
    // Auto-scroll to highlighted comment or reply
    const hash = window.location.hash;
    if (hash) {
        const el = document.querySelector(hash);
        if (el) {
            setTimeout(() => {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    }

    // Also try to scroll to highlighted elements without hash
    const highlighted = document.querySelector('.highlighted-comment, .highlighted-reply');
    if (highlighted && !hash) {
        setTimeout(() => {
            highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    // Logout button
    const logoutBtn = document.getElementById('admin-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            window.location.href = '/logout';
        });
    }

    // Moderation buttons
    const moderationButtons = document.querySelectorAll('.moderation-btn');
    const confirmModal = document.getElementById('confirm-action-modal');
    const confirmBtn = document.getElementById('confirm-action-btn');
    const confirmTitle = document.getElementById('confirm-action-title');
    const confirmSubtitle = document.getElementById('confirm-action-subtitle');

    let pendingAction = null;
    let pendingReportId = null;

    const actionLabels = {
        dismiss: {
            title: 'Desestimar reporte',
            subtitle: window.isUserReport
                ? 'Marcar este reporte como falsa alarma. El usuario no será suspendido.'
                : 'Marcar este reporte como falsa alarma. El contenido no será eliminado.',
            btnText: 'Desestimar',
            btnColor: '#1565c0'
        },
        delete: {
            title: 'Eliminar contenido',
            subtitle: 'Eliminar permanentemente el contenido reportado. Esta acción no se puede deshacer.',
            btnText: 'Eliminar',
            btnColor: '#c62828'
        },
        suspend: {
            title: 'Suspender usuario',
            subtitle: window.isUserReport
                ? 'Suspender permanentemente a este usuario. Esta acción no se puede deshacer.'
                : 'Suspender al autor del contenido y eliminar el contenido reportado.',
            btnText: 'Suspender',
            btnColor: '#ad1457'
        }
    };

    moderationButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.dataset.action;
            const reportId = btn.dataset.reportId;
            const config = actionLabels[action];

            if (!config) return;

            pendingAction = action;
            pendingReportId = reportId;

            confirmTitle.textContent = config.title;
            confirmSubtitle.textContent = config.subtitle;
            confirmBtn.textContent = config.btnText;
            confirmBtn.style.backgroundColor = config.btnColor;

            confirmModal.showModal();
        });
    });

    // Modal actions
    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            if (button.value === 'confirm' && pendingAction && pendingReportId) {
                executeModerationAction(pendingReportId, pendingAction);
            } else if (button.value === 'cancel') {
                confirmModal.close();
                pendingAction = null;
                pendingReportId = null;
            }
        });
    }

    async function executeModerationAction(reportId, action) {
        try {
            const fd = new FormData();
            fd.append('action', action);

            const res = await fetch(`/admin/reports/${reportId}/resolve`, {
                method: 'POST',
                body: fd
            });
            const json = await res.json();

            if (json.success) {
                // Reload page to reflect changes
                window.location.reload();
            } else {
                alert(json.message || 'Error al aplicar la acción');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión al aplicar la acción');
        } finally {
            if (confirmModal) confirmModal.close();
            pendingAction = null;
            pendingReportId = null;
        }
    }
});
