let currentFilter = "friend";
let currentSearchTerm = "";

function searchFriends(searchTerm) {
  currentSearchTerm = searchTerm.toLowerCase().trim();
  const clearBtn = document.getElementById("clearSearchBtn");
  const searchResults = document.getElementById("searchResults");

  if (currentSearchTerm.length > 0) {
    clearBtn.style.display = "block";
  } else {
    clearBtn.style.display = "none";
    searchResults.style.display = "none";
  }

  filterAndSearch();
}

function clearSearch() {
  const searchInput = document.getElementById("friendSearchInput");
  searchInput.value = "";
  currentSearchTerm = "";
  document.getElementById("clearSearchBtn").style.display = "none";
  document.getElementById("searchResults").style.display = "none";
  filterAndSearch();
}

function filterFriends(event, filter) {
  const tabs = document.querySelectorAll(".tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.target.classList.add("active");

  currentFilter = filter;
  filterAndSearch();
}

function filterAndSearch() {
  const friendCards = document.querySelectorAll(".friend-card");
  const noResultsMessage = document.getElementById("noResultsMessage");
  const searchResults = document.getElementById("searchResults");
  let visibleCount = 0;

  friendCards.forEach((card) => {
    const friendName = card
      .querySelector(".friend-name")
      .textContent.toLowerCase();
    const cardStatus = card.dataset.status || "friend";

    const matchesFilter = cardStatus === currentFilter;

    const matchesSearch =
      currentSearchTerm === "" || friendName.includes(currentSearchTerm);

    if (matchesFilter && matchesSearch) {
      card.style.display = "block";
      visibleCount++;
    } else {
      card.style.display = "none";
    }
  });

  if (visibleCount === 0) {
    noResultsMessage.style.display = "block";
  } else {
    noResultsMessage.style.display = "none";
  }

  if (currentSearchTerm.length > 0) {
    searchResults.style.display = "block";
    searchResults.textContent = `Se encontraron ${visibleCount} resultado${visibleCount !== 1 ? "s" : ""
      } para "${currentSearchTerm}"`;
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const friendCards = document.querySelectorAll(".friend-card");
  friendCards.forEach((card) => {
    if (card.querySelector(".btn-accept")) {
      card.dataset.status = "request";
    } else if (card.querySelector(".btn-cancel")) {
      card.dataset.status = "pending";
    } else if (card.querySelector(".btn-add")) {
      card.dataset.status = "suggestion";
    } else {
      card.dataset.status = "friend";
    }
  });

  filterAndSearch();

  document.addEventListener('click', function (event) {
    if (event.target && event.target.classList.contains('btn-action')) {
      const button = event.target;
      const action = button.dataset.action;
      const id = button.dataset.requestId || button.dataset.suggestionId || button.dataset.userId;
      if (!id) return;
      console.log("ID: ", id);
      console.log("Action: ", action);

      if (action === 'accept') {
        handleFriendAction(id, button, '/friend/accept/');
      } else if (action === 'deny') {
        handleFriendAction(id, button, '/friend/reject/');
      } else if (action === 'add') {
        handleFriendAction(id, button, '/friend/request/');
      } else if (action === 'cancel') {
        handleFriendAction(id, button, '/friend/cancelUser/');
      }
    }
  });
});

function handleFriendAction(id, button, endpoint) {
  console.log("ID: ", id);
  console.log("Endpoint: ", endpoint);
  console.log("Button: ", button);
  button.disabled = true;
  const originalText = button.textContent;
  button.textContent = 'Procesando...';
  const action = button.dataset.action;

  fetch(`${endpoint}${id}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({})
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const card = button.closest('.friend-card');
        if (card) {
          updateCardAfterAction(card, action);
        }
      } else {
        alert('Error: ' + (data.message || 'Acción fallida'));
        button.disabled = false;
        button.textContent = originalText;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Ocurrió un error');
      button.disabled = false;
      button.textContent = originalText;
    });
}

function updateCardAfterAction(card, action) {
  const actionsContainer = card.querySelector('.friend-actions');

  card.style.transition = 'opacity 0.3s';
  card.style.opacity = '0';

  setTimeout(() => {
    switch (action) {
      case 'accept':
        card.dataset.status = 'friend';
        const userId = card.querySelector('[data-request-id]').dataset.requestId;
        actionsContainer.innerHTML = `<a href='/profile/${userId}'><button data-user-id='${userId}' data-action='view' class='btn btn-view-profile btn-action'>Ver perfil</button></a>`;
        break;

      case 'deny':
        card.remove();
        filterAndSearch();
        return;

      case 'add':
        card.dataset.status = 'pending';
        const suggestionId = card.querySelector('[data-suggestion-id]').dataset.suggestionId;
        actionsContainer.innerHTML = `<button data-user-id='${suggestionId}' data-action='cancel' class='btn btn-deny btn-cancel btn-action'>Cancelar solicitud</button>`;
        break;

      case 'cancel':
        card.dataset.status = 'suggestion';
        const cancelUserId = card.querySelector('[data-user-id]').dataset.userId;
        actionsContainer.innerHTML = `
          <button data-suggestion-id='${cancelUserId}' data-action='add' class='btn btn-primary btn-add btn-action'>Agregar</button>
          <button data-suggestion-id='${cancelUserId}' data-action='deny' class='btn btn-deny btn-action'>Eliminar</button>
        `;
        break;
    }

    card.style.opacity = '1';
    filterAndSearch();
  }, 300);
}

console.log("Friends.js loaded");
