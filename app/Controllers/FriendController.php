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
        $receiverEmail = $params['email'] ?? null;

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

    public function acceptRequest($params)
    {
        requireAuth();

        $requestId = $params['id'] ?? null;
        $receiverId = $_SESSION['user_id'];

        if (!$requestId)
            return ['success' => false, 'message' => 'ID requerido'];

        $ok = $this->friendModel->acceptRequest($requestId, $receiverId);

        return ['success' => $ok];
    }

    public function rejectRequest($params)
    {
        requireAuth();

        $requestId = $params['id'] ?? null;
        $receiverId = $_SESSION['user_id'];

        if (!$requestId)
            return ['success' => false, 'message' => 'ID requerido'];

        return ['success' => $this->friendModel->rejectRequest($requestId, $receiverId)];
    }

    public function removeFriend($params)
    {
        requireAuth();

        $currentEmail = $_SESSION['email'];
        $otherEmail = $params['email'] ?? null;

        if (!$otherEmail)
            return ['success' => false, 'message' => 'Email requerido'];

        $ok = $this->friendModel->removeFriend($currentEmail, $otherEmail);

        return ['success' => $ok];
    }
}
