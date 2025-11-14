<?php

namespace App\Controllers;

class FriendController
{

    public function sendRequest($friendId)
    {
        requireAuth();

        $userId = $_SESSION['user_id'];


        return ['success' => true, 'message' => 'Friend request sent'];
    }

    public function acceptRequest($requestId)
    {
        requireAuth();


        return ['success' => true, 'message' => 'Friend request accepted'];
    }

    public function rejectRequest($requestId)
    {
        requireAuth();


        return ['success' => true, 'message' => 'Friend request rejected'];
    }

    public function removeFriend($friendId)
    {
        requireAuth();


        return ['success' => true, 'message' => 'Friend removed'];
    }
}