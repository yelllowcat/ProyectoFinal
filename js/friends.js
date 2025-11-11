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
      card.dataset.status = "send";
    } else {
      card.dataset.status = "friend";
    }
  });

  filterAndSearch();
});
console.log("Friends.js loaded");
