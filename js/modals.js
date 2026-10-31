/**
 * UNIRED - Modals Module (js/modals.js)
 * Manages image viewer, confirmation dialogs, delete confirmations, and report modals.
 */

// ---------------------------------------------------
// Image Viewer Modal
// ---------------------------------------------------
function openImageModal(imageUrl) {
  const modal = document.getElementById("image-modal");
  const modalImage = document.getElementById("modalImage");

  if (modal && modalImage) {
    modalImage.src = imageUrl;
    modal.showModal();
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const imageModal = document.getElementById("image-modal");
  const closeImageModalBtn = document.getElementById("closeImageModal");

  if (imageModal && closeImageModalBtn) {
    closeImageModalBtn.addEventListener("click", function () {
      imageModal.close();
    });

    imageModal.addEventListener("click", function (event) {
      if (event.target === imageModal) {
        imageModal.close();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && imageModal.open) {
        imageModal.close();
      }
    });
  }
});

// ---------------------------------------------------
// Account Deletion Modal
// ---------------------------------------------------
function showDeleteConfirmation() {
  const modal = document.getElementById('confirm-delete-account-modal');
  if (modal) {
    const pwdInput = document.getElementById('delete-account-password');
    if (pwdInput) {
      pwdInput.value = '';
    }
    const errDiv = document.getElementById('delete-account-error');
    if (errDiv) {
      errDiv.style.display = 'none';
      errDiv.textContent = '';
    }
    modal.showModal();
    if (pwdInput) {
      setTimeout(() => pwdInput.focus(), 100);
    }
  }
}

function closeDeleteAccountModal() {
  const modal = document.getElementById('confirm-delete-account-modal');
  if (modal) {
    modal.close();
  }
}

async function deleteAccount() {
  const modal = document.getElementById('confirm-delete-account-modal');
  const pwdInput = document.getElementById('delete-account-password');
  const errDiv = document.getElementById('delete-account-error');
  const btn = document.getElementById('btn-confirm-delete-account');

  const password = pwdInput ? pwdInput.value : '';
  if (!password) {
    if (errDiv) {
      errDiv.textContent = 'Por favor ingresa tu contraseña para confirmar.';
      errDiv.style.display = 'block';
    } else {
      alert('Por favor ingresa tu contraseña para confirmar.');
    }
    if (pwdInput) pwdInput.focus();
    return;
  }

  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Eliminando...';
  }

  try {
    const response = await fetch('/profile/delete', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ password: password })
    });

    const data = await response.json().catch(() => ({}));

    if (response.ok && data.success) {
      const redirectUrl = (data.data && data.data.redirect) ? data.data.redirect : (data.redirect || '/login');
      window.location.href = redirectUrl;
    } else {
      const errMsg = data.message || 'Error al eliminar la cuenta.';
      if (errDiv) {
        errDiv.textContent = errMsg;
        errDiv.style.display = 'block';
      } else {
        alert(errMsg);
      }
      if (pwdInput) pwdInput.focus();
    }
  } catch (error) {
    console.error('Error al eliminar la cuenta:', error);
    if (errDiv) {
      errDiv.textContent = 'Error de conexión al eliminar la cuenta.';
      errDiv.style.display = 'block';
    } else {
      alert('Error de conexión al eliminar la cuenta.');
    }
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Eliminar cuenta';
    }
  }
}

// ---------------------------------------------------
// Report Content Modal
// ---------------------------------------------------
function openReportModal(entityType, entityId) {
  const modal = document.getElementById("report-modal");
  if (!modal) return;
  
  document.getElementById("report-entity-type").value = entityType;
  document.getElementById("report-entity-id").value = entityId;
  
  document.getElementById("report-form").reset();
  modal.showModal();
}

document.addEventListener("DOMContentLoaded", () => {
  const reportForm = document.getElementById("report-form");
  if (reportForm) {
    reportForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      
      const entityType = document.getElementById("report-entity-type").value;
      const entityId = document.getElementById("report-entity-id").value;
      
      const reasonRadio = document.querySelector('input[name="reason"]:checked');
      if (!reasonRadio) {
        showDialogMessage("Atención", "Por favor selecciona una razón.");
        return;
      }
      const reason = reasonRadio.value;
      const details = document.getElementById("report-reason-details").value;
      const fullReason = details ? `${reason}: ${details}` : reason;
      
      const formData = new FormData();
      formData.append("reason", fullReason);
      
      if (entityType === "user") formData.append("reported_user_id", entityId);
      if (entityType === "post") formData.append("post_id", entityId);
      if (entityType === "comment") formData.append("comment_id", entityId);
      if (entityType === "reply") formData.append("reply_id", entityId);
      
      try {
        const response = await fetch("/report", {
          method: "POST",
          body: formData
        });
        const result = await response.json();
        
        if (result.success) {
          document.getElementById("report-modal").close();
          showDialogMessage("Éxito", result.data.message || "Reporte enviado correctamente.");
        } else {
          showDialogMessage("Error", result.message);
        }
      } catch (error) {
        console.error("Error al enviar reporte:", error);
        showDialogMessage("Error", "Ocurrió un error al enviar el reporte.");
      }
    });
  }
});

// ---------------------------------------------------
// Dynamic Generic Confirmation Dialogs
// ---------------------------------------------------
function showConfirmDialog(title, message, confirmText = 'Eliminar', cancelText = 'Cancelar') {
    return new Promise((resolve) => {
        const dialog = document.createElement('dialog');
        dialog.className = 'confirm-dialog';
        dialog.innerHTML = `
            <div class="confirm-box">
                <div class="confirm-head">
                    <h3>${title}</h3>
                    <p class="confirm-subtitle">${message}</p>
                </div>
                <div class="confirm-sep"></div>
                <form method="dialog" class="confirm-actions">
                    <button value="confirm" class="confirm-delete">${confirmText}</button>
                    <button value="cancel" class="confirm-cancel">${cancelText}</button>
                </form>
            </div>
        `;
        document.body.appendChild(dialog);
        dialog.showModal();
        dialog.addEventListener('close', () => {
            const confirmed = dialog.returnValue === 'confirm';
            dialog.remove();
            resolve(confirmed);
        });
    });
}

function showDialogMessage(title, message, onClose) {
    const dialog = document.createElement('dialog');
    dialog.className = 'confirm-dialog';
    dialog.innerHTML = `
        <div class="confirm-box">
            <div class="confirm-head">
                <h3>${title}</h3>
                <p class="confirm-subtitle">${message}</p>
            </div>
            <div class="confirm-sep"></div>
            <div class="confirm-actions">
                <button type="button" class="confirm-primary" onclick="this.closest('dialog').close()">Aceptar</button>
            </div>
        </div>
    `;
    document.body.appendChild(dialog);
    dialog.showModal();
    dialog.addEventListener('close', () => {
        dialog.remove();
        if (typeof onClose === 'function') {
            onClose();
        }
    });
}
