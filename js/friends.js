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
    searchResults.textContent = `Se encontraron ${visibleCount} resultado${
      visibleCount !== 1 ? "s" : ""
    } para "${currentSearchTerm}"`;
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const friendCards = document.querySelectorAll(".friend-card");
  friendCards.forEach((card) => {
    if (card.querySelector(".btn-accept")) {
      card.dataset.status = "request";
    } else if (card.querySelector(".btn-add")) {
      card.dataset.status = "suggestion";
    } else {
      card.dataset.status = "friend";
    }
  });

  filterAndSearch();

  document.addEventListener('click', function(event) {
    if (event.target && event.target.classList.contains('btn-action')) {
      const button = event.target;
      const action = button.dataset.action;
      const id = button.dataset.requestId || button.dataset.suggestionId;
      if (!id) return;
      console.log("ID: ", id);
      console.log("Action: ", action);

      if (action === 'accept') {
        handleFriendAction(id, button, '/friend/accept/');
      } else if (action === 'deny') {
        handleFriendAction(id, button, '/friend/reject/');
      } else if (action === 'add') {
        handleFriendAction(id, button, '/friend/request/');
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
      window.location.reload();
      const card = button.closest('.friend-card');
      if (card) {
        card.style.transition = 'opacity 0.5s';
        card.style.opacity = '0';
        setTimeout(() => {
            card.remove();
            filterAndSearch();
        }, 500);
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

console.log("Friends.js loaded");
