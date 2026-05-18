<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
$model = new App\Models\AdminModel();
echo "Summary Stats:\n";
print_r($model->getSummaryStats());
echo "Activity Timeline:\n";
print_r($model->getActivityTimeline());
echo "Engagement Breakdown:\n";
print_r($model->getEngagementBreakdown());
echo "User Growth:\n";
print_r($model->getUserGrowth());
