<?php
namespace App\Controllers;

use App\Models\FriendModel;
use App\Models\UserModel;

class FriendController
{
    private $friendModel;
    private $userModel;

    public function __construct()
    {
        $this->friendModel = new FriendModel();
        $this->userModel = new UserModel();
    }

    public function sendRequest($params)
    {
        requireAuth();

        $senderEmail = $_SESSION['email'];
        $receiverEmail = clean_input($params['email'] ?? '');

        if (!$receiverEmail)
            return ['success' => false, 'message' => 'Email no especificado'];
        if ($senderEmail === $receiverEmail)
            return ['success' => false, 'message' => 'No puedes enviarte solicitud a ti mismo'];

        if (!$this->userModel->getUserByEmail($receiverEmail))
            return ['success' => false, 'message' => 'Usuario no encontrado'];

        $ok = $this->friendModel->sendRequestByEmail($senderEmail, $receiverEmail);

        return [
            'success' => $ok,
            'message' => $ok ? 'Solicitud enviada' : 'No se pudo enviar'
        ];
    }

    public function sendRequestById($id)
    {
        requireAuth();

        $senderId = $_SESSION['user_id'];
        $receiverId = intval(clean_input($id));

        error_log("--------------------------------------");
        error_log("Sender ID: " . $senderId);
        error_log("Receiver ID: " . $receiverId);

        if (!$receiverId)
            return ['success' => false, 'message' => 'ID no especificado'];
        if ($senderId === $receiverId)
            return ['success' => false, 'message' => 'No puedes enviarte solicitud a ti mismo'];

        if (!$this->userModel->getUserById($receiverId))
            return ['success' => false, 'message' => 'Usuario no encontrado'];

        $ok = $this->friendModel->sendRequest($senderId, $receiverId);

        return [
            'success' => $ok,
            'message' => $ok ? 'Solicitud enviada' : 'No se pudo enviar'
        ];
    }

    public function acceptRequest($id)
    {
        requireAuth();
        $requestId = intval(clean_input($id));
        $receiverId = $_SESSION['user_id'];

        if (!$requestId) {
            return ['success' => false, 'message' => 'ID requerido'];
        }
        $ok = $this->friendModel->acceptRequest($requestId, $receiverId);

        return ['success' => $ok];
    }

    public function acceptRequestByUserId($id)
    {
        requireAuth();
        $senderId = $id;
        $receiverId = $_SESSION['user_id'];

        if (!$senderId) {
            return ['success' => false, 'message' => 'ID requerido'];
        }

        $ok = $this->friendModel->acceptRequestByUserId($senderId, $receiverId);

        return ['success' => $ok];
    }

    public function rejectRequest($id)
    {
        requireAuth();
        $requestId = $id;
        $receiverId = $_SESSION['user_id'];

        if (!$requestId) {
            return ['success' => false, 'message' => 'ID requerido'];
        }
        return ['success' => $this->friendModel->rejectRequest($requestId, $receiverId)];
    }

    public function cancelRequestByUserId($id)
    {
        requireAuth();
        $senderId = $_SESSION['user_id'];
        $receiverId = $id;

        if (!$receiverId) {
            return ['success' => false, 'message' => 'ID requerido'];
        }

        $ok = $this->friendModel->cancelRequestByUserId($senderId, $receiverId);

        return ['success' => $ok];
    }

    public function removeFriend($id)
    {
        requireAuth();

        $currentEmail = $_SESSION['email'];
        $otherEmail = $id;

        if (!$otherEmail)
            return ['success' => false, 'message' => 'Email requerido'];

        $ok = $this->friendModel->removeFriend($currentEmail, $otherEmail);

        return ['success' => $ok];
    }

    public function removeFriendById($id)
    {
        requireAuth();
        $currentUserId = $_SESSION['user_id'];
        $friendId = $id;

        if (!$friendId)
            return ['success' => false, 'message' => 'ID requerido'];

        $ok = $this->friendModel->removeFriendById($currentUserId, $friendId);

        return ['success' => $ok];
    }

    public function getPendingRequests()
    {
        requireAuth();
        $userId = $_SESSION['user_id'];

        $requests = $this->friendModel->getPendingRequests($userId);

        return ['success' => true, 'data' => $requests];
    }

    public function getSentRequests()
    {
        requireAuth();
        $userId = $_SESSION['user_id'];

        $requests = $this->friendModel->getSentRequests($userId);

        return ['success' => true, 'data' => $requests];
    }

    public function getFriends()
    {
        requireAuth();
        $userId = $_SESSION['user_id'];

        $friends = $this->friendModel->getFriends($userId);

        return ['success' => true, 'data' => $friends];
    }

    public function getSuggestions()
    {
        requireAuth();
        $userId = $_SESSION['user_id'];

        $suggestions = $this->friendModel->getSuggestions($userId);

        return ['success' => true, 'data' => $suggestions];
    }

    public function getStatus($params)
    {
        requireAuth();
        $userId = $_SESSION['user_id'];
        $otherUserId = intval(clean_input($params['id'] ?? ''));

        if (!$otherUserId) {
            return ['success' => false, 'message' => 'ID requerido'];
        }

        $status = $this->friendModel->getFriendshipStatus($userId, $otherUserId);

        return ['success' => true, 'status' => $status];
    }
}
