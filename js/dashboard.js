function fetchUsersWithMostPosts(event) {
  console.log("Fetching users with most posts...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchUsersWithMostFriends(event) {
  console.log("Fetching users with most friends...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchPostsWithMostComments(event) {
  console.log("Fetching posts with most comments...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}

function fetchPostsWithMostLikes(event) {
  console.log("Fetching posts with most likes...");
  const tabs = document.querySelectorAll(".stat-tab");
  tabs.forEach((tab) => tab.classList.remove("active"));
  event.currentTarget.classList.add("active");
}
