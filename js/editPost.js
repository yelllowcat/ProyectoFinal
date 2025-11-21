
document.getElementById('editPostForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const content = document.getElementById('postText').value.trim();

  if (content.length < 1) {
    alert('El texto debe tener al menos 2 caracteres');
    return;
  }

  try {
    const response = await fetch('/posts/' + POST_ID, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ content })
    });

    const result = await response.json();

    if (result.success) {
      alert('Publicación actualizada correctamente');
      window.location.href = '/posts';
    } else {
      alert('Error: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error de conexión');
  }
});

console.log("EditPost.js loaded");