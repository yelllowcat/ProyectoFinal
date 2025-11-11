/* document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const friendsGrid = document.getElementById('friendsGrid');
    const searchInput = document.getElementById('searchInput');
    let currentType = 'friend';
    let searchTimeout;

    loadFriends('friend');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            currentType = this.dataset.type;
            loadFriends(currentType);
        });
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadFriends(currentType, this.value);
        }, 300);
    });

    function loadFriends(type, search = '') {
        friendsGrid.innerHTML = '<p>Cargando...</p>';
        
        fetch(`api/get_friends.php?type=${type}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    friendsGrid.innerHTML = data.html || '<p>No se encontraron resultados.</p>';
                } else {
                    friendsGrid.innerHTML = '<p>Error al cargar amigos.</p>';
                    console.error(data.error);
                }
            })
            .catch(error => {
                friendsGrid.innerHTML = '<p>Error de conexión.</p>';
                console.error('Error:', error);
            });
    }

    friendsGrid.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-accept')) {
            acceptFriendRequest(e.target);
        } else if (e.target.classList.contains('btn-deny')) {
            denyFriendRequest(e.target);
        } else if (e.target.classList.contains('btn-add')) {
            sendFriendRequest(e.target);
        } else if (e.target.classList.contains('btn-view-profile')) {
            viewProfile(e.target);
        }
    });

    function acceptFriendRequest(button) {
        console.log('Accept friend request');
    }

    function denyFriendRequest(button) {
        console.log('Deny friend request');
    }

    function sendFriendRequest(button) {
        console.log('Send friend request');
    }

    function viewProfile(button) {
        console.log('View profile');
    }
});
*/
console.log("Friends.js loaded");