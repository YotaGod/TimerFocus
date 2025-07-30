<?php
require_once 'config/database.php';

echo "<h1>Dashboard Debug Test</h1>";

// Check database connection
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM focus_history");
    $result = $stmt->fetch();
    echo "<p>Total records in database: " . $result['total'] . "</p>";
} catch (Exception $e) {
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}

// Check recent data
try {
    $stmt = $pdo->query("SELECT * FROM focus_history ORDER BY created_at DESC LIMIT 5");
    $recent = $stmt->fetchAll();
    echo "<h2>Recent Data:</h2>";
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Activity</th><th>Duration</th><th>Created At</th></tr>";
    foreach ($recent as $row) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['activity_name'] . "</td>";
        echo "<td>" . $row['duration_seconds'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>Error fetching recent data: " . $e->getMessage() . "</p>";
}

// Test dashboard query
$startDate = date('Y-m-d', strtotime('-7 days'));
$endDate = date('Y-m-d');

echo "<h2>Testing Dashboard Query:</h2>";
echo "<p>Date range: $startDate to $endDate</p>";

try {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(duration_seconds) as total_seconds,
            COUNT(*) as total_sessions,
            AVG(duration_seconds) as avg_duration
        FROM focus_history 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $stats = $stmt->fetch();

    echo "<p>Total seconds: " . ($stats['total_seconds'] ?? 0) . "</p>";
    echo "<p>Total sessions: " . ($stats['total_sessions'] ?? 0) . "</p>";
    echo "<p>Avg duration: " . ($stats['avg_duration'] ?? 0) . "</p>";
} catch (Exception $e) {
    echo "<p>Error in dashboard query: " . $e->getMessage() . "</p>";
}

// Test API endpoint
echo "<h2>Testing API Endpoint:</h2>";
$url = "get_dashboard_data.php?period=week";
echo "<p>URL: $url</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: " . htmlspecialchars($response) . "</p>";
