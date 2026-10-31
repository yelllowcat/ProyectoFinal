<?php

namespace App\Controllers;

use App\Models\AdminModel;

class AdminController
{
    private $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function getUsersWithMostPosts()
    {
        requireAdmin();

        $users = $this->adminModel->getUsersWithMostPosts();
        jsonSuccess($users);
    }

    public function getUsersWithMostFriends()
    {
        requireAdmin();

        $users = $this->adminModel->getUsersWithMostFriends();
        jsonSuccess($users);
    }

    public function getPostsWithMostComments()
    {
        requireAdmin();

        $posts = $this->adminModel->getPostsWithMostComments();
        jsonSuccess($posts);
    }

    public function getPostsWithMostLikes()
    {
        requireAdmin();

        $posts = $this->adminModel->getPostsWithMostLikes();
        jsonSuccess($posts);
    }

    public function getSummaryStats()
    {
        requireAdmin();
        $stats = $this->adminModel->getSummaryStats();
        jsonSuccess($stats);
    }

    public function getActivityTimeline()
    {
        requireAdmin();
        $data = $this->adminModel->getActivityTimeline();
        jsonSuccess($data);
    }

    public function getEngagementBreakdown()
    {
        requireAdmin();
        $data = $this->adminModel->getEngagementBreakdown();
        jsonSuccess($data);
    }

    public function getUserGrowth()
    {
        requireAdmin();
        $data = $this->adminModel->getUserGrowth();
        jsonSuccess($data);
    }

    public function getUserActivitySplit()
    {
        requireAdmin();
        $data = $this->adminModel->getUserActivitySplit();
        jsonSuccess($data);
    }

    public function getPostsByDayOfWeek()
    {
        requireAdmin();
        $data = $this->adminModel->getPostsByDayOfWeek();
        jsonSuccess($data);
    }

    public function getPostImageRatio()
    {
        requireAdmin();
        $data = $this->adminModel->getPostImageRatio();
        jsonSuccess($data);
    }

    public function getPeakUsageHeatmap()
    {
        requireAdmin();
        $range = $_GET['range'] ?? '30';
        if (!in_array($range, ['30', '90', 'all'])) {
            $range = '30';
        }
        $data = $this->adminModel->getPeakUsageHeatmap($range);
        jsonSuccess($data);
    }

    public function getTopEngagedUsers()
    {
        requireAdmin();
        $data = $this->adminModel->getTopEngagedUsers();
        jsonSuccess($data);
    }

    public function getHashtagSummary()
    {
        requireAdmin();
        $total = $this->adminModel->getTotalHashtags();
        jsonSuccess(['total_hashtags' => $total]);
    }

    public function getTopHashtags()
    {
        requireAdmin();
        $sort = clean_input($_GET['sort'] ?? 'posts');
        if (!in_array($sort, ['posts', 'likes', 'comments'])) {
            $sort = 'posts';
        }
        $data = $this->adminModel->getTopHashtags(20, $sort);
        jsonSuccess($data);
    }

    public function getHashtagTrend()
    {
        requireAdmin();
        $range = (int) ($_GET['range'] ?? 30);
        if (!in_array($range, [7, 30, 90])) {
            $range = 30;
        }
        $data = $this->adminModel->getHashtagTrend(10, $range);
        jsonSuccess($data);
    }

    public function getUsersList()
    {
        requireAdmin();
        $search = clean_input($_GET['search'] ?? ($_GET['q'] ?? ''));
        $status = clean_input($_GET['status'] ?? 'all');
        $role = clean_input($_GET['role'] ?? 'all');
        $limit = min(200, max(1, (int) ($_GET['limit'] ?? 100)));

        $users = $this->adminModel->getAllUsersForManagement($search, $status, $role, $limit);
        jsonSuccess($users);
    }

    public function toggleUserStatus($id)
    {
        requireAdmin();
        $userId = (int) $id;

        if ($userId <= 0) {
            jsonError('ID de usuario inválido.');
            return;
        }

        if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
            jsonError('No puedes suspender tu propia cuenta de administrador.');
            return;
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->getUserById($userId);
        if (!$user) {
            jsonError('Usuario no encontrado.');
            return;
        }

        if (($user['role'] ?? '') === 'admin') {
            jsonError('No se pueden modificar cuentas con rol de administrador.');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['active'])) {
            $newActive = $input['active'] ? 1 : 0;
        } elseif (isset($_POST['active'])) {
            $newActive = $_POST['active'] ? 1 : 0;
        } else {
            // Check active from DB directly
            $stmt = getDB()->prepare("SELECT active FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $currentActive = (int)$stmt->fetchColumn();
            $newActive = $currentActive === 1 ? 0 : 1;
        }

        $success = $this->adminModel->setUserActive($userId, $newActive);

        if ($success) {
            $actionMsg = $newActive === 1 ? 'Usuario reactivado exitosamente.' : 'Usuario suspendido exitosamente.';
            jsonSuccess(['active' => $newActive, 'user_id' => $userId], $actionMsg);
        } else {
            jsonError('No se pudo actualizar el estado del usuario.');
        }
    }
}
