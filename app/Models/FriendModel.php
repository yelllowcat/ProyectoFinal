<?php
namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

class FriendModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    private function getUserIdByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user['user_id'] ?? null;
    }

    private function alreadyFriends($u1, $u2)
    {
        $sql = "SELECT 1 FROM friends 
                WHERE (user_id1=? AND user_id2=?) 
                OR (user_id1=? AND user_id2=?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$u1, $u2, $u2, $u1]);
        return $stmt->fetch();
    }

    private function existingRequest($sender, $receiver)
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM friend_requests WHERE sender_id=? AND receiver_id=? AND status='pending'");
        $stmt->execute([$sender, $receiver]);
        return $stmt->fetch();
    }

    public function getFriends($userId)
    {
        $sql = "
        SELECT 
            u.user_id,
            u.full_name,
            u.email,
            u.profile_picture,
            NULL AS friendship_date
        FROM friends
        JOIN users u 
            ON (u.user_id = friends.user_id1 AND friends.user_id2 = ?)
            OR (u.user_id = friends.user_id2 AND friends.user_id1 = ?)
    ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPendingRequests($userId)
    {
        $sql = "
            SELECT 
                fr.request_id, fr.sender_id, fr.receiver_id, fr.request_date,
                u.full_name, u.email, u.profile_picture
            FROM friend_requests fr
            JOIN users u ON u.user_id = fr.sender_id
            WHERE fr.receiver_id = ? AND fr.status = 'pending'
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSuggestions($userId)
    {
        $sql = "
            SELECT u.user_id, u.full_name, u.email, u.profile_picture, u.registration_date
            FROM users u
            WHERE u.user_id != ?
            AND u.user_id NOT IN (
                SELECT user_id1 FROM friends WHERE user_id2 = ?
                UNION
                SELECT user_id2 FROM friends WHERE user_id1 = ?
            )
            LIMIT 10
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function sendRequestByEmail($senderEmail, $receiverEmail)
    {
        $senderId = $this->getUserIdByEmail($senderEmail);
        $receiverId = $this->getUserIdByEmail($receiverEmail);

        if (!$senderId || !$receiverId || $senderId == $receiverId)
            return false;
        if ($this->alreadyFriends($senderId, $receiverId))
            return false;
        if ($this->existingRequest($senderId, $receiverId))
            return false;

        $stmt = $this->pdo->prepare(
            "INSERT INTO friend_requests (sender_id, receiver_id, status)
             VALUES (?, ?, 'pending')"
        );
        return $stmt->execute([$senderId, $receiverId]);
    }

    public function acceptRequest($requestId, $receiverId)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("SELECT * FROM friend_requests WHERE request_id=?");
            $stmt->execute([$requestId]);
            $req = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$req || $req['receiver_id'] != $receiverId)
                return false;

            $this->pdo->prepare(
                "UPDATE friend_requests SET status='accepted', response_date=NOW()
                 WHERE request_id=?"
            )->execute([$requestId]);

            $this->pdo->prepare(
                "INSERT INTO friends (user_id1,user_id2,created_at) VALUES (?,?,NOW())"
            )->execute([$req['sender_id'], $req['receiver_id']]);

            $this->pdo->commit();
            return true;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function rejectRequest($requestId, $receiverId)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE friend_requests SET status='rejected', response_date=NOW()
             WHERE request_id=? AND receiver_id=?"
        );
        return $stmt->execute([$requestId, $receiverId]);
    }

    public function removeFriend($emailA, $emailB)
    {
        $u1 = $this->getUserIdByEmail($emailA);
        $u2 = $this->getUserIdByEmail($emailB);

        if (!$u1 || !$u2)
            return false;

        $sql = "DELETE FROM friends 
                WHERE (user_id1=? AND user_id2=?) OR (user_id1=? AND user_id2=?)";
        return $this->pdo->prepare($sql)->execute([$u1, $u2, $u2, $u1]);
    }
}
