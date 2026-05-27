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
}
