<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';

$pdo = getDB();

echo "<h1>UNIRED Database Diagnostics</h1>";

try {
    // 1. Check connection
    echo "<h2>1. Connection</h2>";
    echo "Connected successfully to " . DB_HOST . "<br>";

    // 2. Query routines count
    echo "<h2>2. Routines (Stored Procedures)</h2>";
    $routines = $pdo->query("SELECT ROUTINE_NAME, ROUTINE_TYPE FROM information_schema.routines WHERE routine_schema = '" . DB_NAME . "'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($routines) . " routines:<br>";
    echo "<ul>";
    foreach ($routines as $r) {
        echo "<li>" . htmlspecialchars($r['ROUTINE_NAME']) . " (" . htmlspecialchars($r['ROUTINE_TYPE']) . ")</li>";
    }
    echo "</ul>";

    // 3. Query tables count
    echo "<h2>3. Tables</h2>";
    $tables = $pdo->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($tables) . " tables:<br>";
    echo "<ul>";
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM " . $t['TABLE_NAME'])->fetchColumn();
        echo "<li>" . htmlspecialchars($t['TABLE_NAME']) . ": <strong>$count</strong> rows</li>";
    }
    echo "</ul>";

    // 4. Query views
    echo "<h2>4. Views</h2>";
    $views = $pdo->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND TABLE_TYPE = 'VIEW'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($views) . " views:<br>";
    echo "<ul>";
    foreach ($views as $v) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM " . $v['TABLE_NAME'])->fetchColumn();
            echo "<li>" . htmlspecialchars($v['TABLE_NAME']) . ": <strong>$count</strong> rows</li>";
        } catch (Exception $e) {
            echo "<li>" . htmlspecialchars($v['TABLE_NAME']) . ": <span style='color:red;'>Error querying view: " . htmlspecialchars($e->getMessage()) . "</span></li>";
        }
    }
    echo "</ul>";

    // 5. Check a sample query from v_posts_stats
    echo "<h2>5. Posts in v_posts_stats</h2>";
    try {
        $posts = $pdo->query("SELECT * FROM v_posts_stats LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "Successfully fetched " . count($posts) . " posts:<br>";
        echo "<pre>" . print_r($posts, true) . "</pre>";
    } catch (Exception $e) {
        echo "<span style='color:red;'>Error querying v_posts_stats: " . htmlspecialchars($e->getMessage()) . "</span>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red;'>Connection/Query Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
