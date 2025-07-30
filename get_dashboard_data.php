<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $period = $_GET['period'] ?? 'week'; // week, month, year
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';

    // Set default date range berdasarkan period
    if (empty($startDate) || empty($endDate)) {
        $endDate = date('Y-m-d');
        switch ($period) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'month':
                $startDate = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'year':
                $startDate = date('Y-m-d', strtotime('-365 days'));
                break;
            default:
                $startDate = date('Y-m-d', strtotime('-7 days'));
        }
    }

    // Debug: Check what dates are in database
    $debugStmt = $pdo->prepare("SELECT DATE(created_at) as date, COUNT(*) as count FROM focus_history GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 5");
    $debugStmt->execute();
    $debugDates = $debugStmt->fetchAll();
    error_log("Available dates in database: " . json_encode($debugDates));

    // 1. Total durasi fokus
    $stmt = $pdo->prepare("
        SELECT 
            SUM(duration_seconds) as total_seconds,
            COUNT(*) as total_sessions,
            AVG(duration_seconds) as avg_duration
        FROM focus_history 
        WHERE DATE(created_at) BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $overallStats = $stmt->fetch();

    // 2. Durasi per hari
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            SUM(duration_seconds) as total_seconds,
            COUNT(*) as sessions
        FROM focus_history 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$startDate, $endDate]);
    $dailyStats = $stmt->fetchAll();

    // 3. Durasi per aktivitas
    $stmt = $pdo->prepare("
        SELECT 
            activity_name,
            SUM(duration_seconds) as total_seconds,
            COUNT(*) as sessions,
            AVG(duration_seconds) as avg_duration
        FROM focus_history 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY activity_name
        ORDER BY total_seconds DESC
        LIMIT 10
    ");
    $stmt->execute([$startDate, $endDate]);
    $activityStats = $stmt->fetchAll();

    // 4. Jam produktif (jam dengan fokus terbanyak)
    $stmt = $pdo->prepare("
        SELECT 
            HOUR(created_at) as hour,
            SUM(duration_seconds) as total_seconds,
            COUNT(*) as sessions
        FROM focus_history 
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY HOUR(created_at)
        ORDER BY hour ASC
    ");
    $stmt->execute([$startDate, $endDate]);
    $hourlyStats = $stmt->fetchAll();

    // Debug: Log query parameters
    error_log("Dashboard Query - Start: $startDate, End: $endDate, Period: $period");
    error_log("Overall Stats: " . json_encode($overallStats));
    error_log("Daily Stats Count: " . count($dailyStats));
    error_log("Activity Stats Count: " . count($activityStats));

    // Format data untuk response
    $response = [
        'success' => true,
        'period' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period' => $period
        ],
        'overall_stats' => [
            'total_seconds' => intval($overallStats['total_seconds'] ?? 0),
            'total_sessions' => intval($overallStats['total_sessions'] ?? 0),
            'avg_duration' => round($overallStats['avg_duration'] ?? 0),
            'total_hours' => round(($overallStats['total_seconds'] ?? 0) / 3600, 1),
            'avg_hours' => round(($overallStats['avg_duration'] ?? 0) / 3600, 2)
        ],
        'daily_stats' => array_map(function ($day) {
            return [
                'date' => $day['date'],
                'total_seconds' => intval($day['total_seconds']),
                'sessions' => intval($day['sessions']),
                'total_hours' => round($day['total_seconds'] / 3600, 2)
            ];
        }, $dailyStats),
        'activity_stats' => array_map(function ($activity) {
            return [
                'activity_name' => $activity['activity_name'],
                'total_seconds' => intval($activity['total_seconds']),
                'sessions' => intval($activity['sessions']),
                'avg_duration' => round($activity['avg_duration']),
                'total_hours' => round($activity['total_seconds'] / 3600, 2),
                'avg_hours' => round($activity['avg_duration'] / 3600, 2)
            ];
        }, $activityStats),
        'hourly_stats' => array_map(function ($hour) {
            return [
                'hour' => intval($hour['hour']),
                'total_seconds' => intval($hour['total_seconds']),
                'sessions' => intval($hour['sessions']),
                'total_hours' => round($hour['total_seconds'] / 3600, 2),
                'hour_label' => sprintf('%02d:00', $hour['hour'])
            ];
        }, $hourlyStats)
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
