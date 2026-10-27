document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('editPostForm');
  const textarea = document.getElementById('postText');
  if (!form || !textarea) return;

  const initialContent = textarea.value.trim();
  let allowNavigation = false;

  form.addEventListener('submit', function (e) {
    if (allowNavigation) {
      return;
    }

    const content = textarea.value.trim();

    if (content.length < 2) {
      e.preventDefault();
      if (typeof showDialogMessage === 'function') {
        showDialogMessage(
          'Atención',
          'El texto de la publicación debe tener al menos 2 caracteres.'
        );
      }
      return;
    }

    allowNavigation = true;
  });

  // Intercept clicks on any link (sidebar, logo, search history, etc.)
  document.addEventListener(
    'click',
    async function (e) {
      if (allowNavigation) return;

      const link = e.target.closest('a[href]');
      if (!link) return;

      if (link.id === 'logout') return;

      const href = link.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

      e.preventDefault();
      e.stopPropagation();

      const hasChanges = textarea.value.trim() !== initialContent;
      const title = hasChanges ? 'Cambios sin guardar' : 'Salir de la edición';
      const message = hasChanges
        ? 'Tienes cambios sin guardar. ¿Estás seguro/a de que deseas salir sin hacer cambios?'
        : '¿Estás seguro/a de que deseas salir sin hacer ningún cambio?';

      if (typeof showConfirmDialog === 'function') {
        const confirmed = await showConfirmDialog(
          title,
          message,
          'Salir sin cambios',
          'Seguir editando'
        );
        if (confirmed) {
          allowNavigation = true;
          window.location.href = link.href;
        }
      } else {
        allowNavigation = true;
        window.location.href = link.href;
      }
    },
    true
  );

  // Intercept global search form submission from header
  const searchForm = document.getElementById('globalSearchForm');
  if (searchForm) {
    searchForm.addEventListener('submit', async function (e) {
      if (allowNavigation) return;
      e.preventDefault();

      const hasChanges = textarea.value.trim() !== initialContent;
      const title = hasChanges ? 'Cambios sin guardar' : 'Salir de la edición';
      const message = hasChanges
        ? 'Tienes cambios sin guardar. ¿Estás seguro/a de que deseas salir sin hacer cambios?'
        : '¿Estás seguro/a de que deseas salir sin hacer ningún cambio?';

      if (typeof showConfirmDialog === 'function') {
        const confirmed = await showConfirmDialog(
          title,
          message,
          'Salir sin cambios',
          'Seguir editando'
        );
        if (confirmed) {
          allowNavigation = true;
          searchForm.submit();
        }
      } else {
        allowNavigation = true;
        searchForm.submit();
      }
    });
  }
});
