<?php
/**
 * Shared Modals Component for UNIRED
 * Includes: Image Viewer Modal, Delete Post Confirmation Modal,
 * Delete Comment Confirmation Modal, and Report Content Modal.
 */
?>
<!-- Image Viewer Modal -->
<dialog id="image-modal" class="image-modal">
    <div class="image-modal-content">
        <button class="image-modal-close" id="closeImageModal" type="button" aria-label="Cerrar imagen">
            <img src="/assets/images/x.png" alt="Cerrar">
        </button>
        <img id="modalImage" src="" alt="Vista previa de imagen">
    </div>
</dialog>

<!-- Delete Post Confirmation Modal -->
<dialog id="confirm-delete-modal" class="confirm-dialog" aria-labelledby="confirm-delete-title">
    <div class="confirm-box">
        <div class="confirm-head">
            <h3 id="confirm-delete-title">Confirmar eliminación</h3>
            <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar esta publicación?</p>
        </div>
        <div class="confirm-sep"></div>
        <form method="dialog" class="confirm-actions">
            <button value="confirm" class="confirm-delete">Eliminar</button>
            <button value="cancel" class="confirm-cancel">Cancelar</button>
        </form>
    </div>
</dialog>

<!-- Delete Comment Confirmation Modal -->
<dialog id="confirm-delete-comment-modal" class="confirm-dialog" aria-labelledby="confirm-delete-comment-title">
    <div class="confirm-box">
        <div class="confirm-head">
            <h3 id="confirm-delete-comment-title">Confirmar eliminación</h3>
            <p class="confirm-subtitle">¿Estás seguro/a de que deseas eliminar este comentario o respuesta?</p>
        </div>
        <div class="confirm-sep"></div>
        <form method="dialog" class="confirm-actions">
            <button value="confirm" class="confirm-delete">Eliminar</button>
            <button value="cancel" class="confirm-cancel">Cancelar</button>
        </form>
    </div>
</dialog>

<!-- Report Content Modal -->
<dialog id="report-modal" class="confirm-dialog" aria-labelledby="report-modal-title">
    <div class="confirm-box" style="width: 400px; max-width: 90%; text-align: left;">
        <div class="confirm-head">
            <h3 id="report-modal-title" style="margin-bottom: 5px;">Reportar Contenido</h3>
            <p class="confirm-subtitle" style="margin: 0; font-size: 14px; color: #666;">Selecciona una razón para reportar este contenido:</p>
        </div>
        <div class="confirm-sep"></div>
        <form id="report-form" class="report-form" style="padding: 15px; display: flex; flex-direction: column; gap: 10px;">
            <input type="hidden" id="report-entity-type" name="entity_type">
            <input type="hidden" id="report-entity-id" name="entity_id">
            
            <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                <input type="radio" name="reason" value="Spam" required> Spam
            </label>
            <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                <input type="radio" name="reason" value="Contenido inapropiado"> Contenido inapropiado
            </label>
            <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                <input type="radio" name="reason" value="Acoso o bullying"> Acoso o bullying
            </label>
            <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                <input type="radio" name="reason" value="Discurso de odio"> Discurso de odio
            </label>
            <label style="display:flex; align-items:center; gap: 8px; font-size: 14px; cursor: pointer;">
                <input type="radio" name="reason" value="Otro"> Otro
            </label>

            <textarea id="report-reason-details" name="reason_details" maxlength="200" placeholder="Detalles opcionales..." style="margin-top: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-family: inherit; resize: vertical; min-height: 60px;"></textarea>

            <div class="confirm-actions" style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('report-modal').close()" class="confirm-cancel" style="padding: 8px 16px;">Cancelar</button>
                <button type="submit" class="confirm-delete" style="background-color: #f44336; border: none; color: white; padding: 8px 16px; cursor: pointer; border-radius: 4px; font-weight: bold;">Enviar Reporte</button>
            </div>
        </form>
    </div>
</dialog>

<script src="<?php echo asset('js/modals.js'); ?>"></script>
<script src="<?php echo asset('js/comments.js'); ?>"></script>
<script src="<?php echo asset('js/search.js'); ?>"></script>
