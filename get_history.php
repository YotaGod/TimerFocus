<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    $activity = trim($_GET['activity'] ?? '');
    $date = trim($_GET['date'] ?? '');

    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);

    $sql = "SELECT id, activity_name, duration_seconds, created_at FROM focus_history";
    $params = [];
    $conditions = [];

    if (!empty($activity)) {
        $conditions[] = "activity_name LIKE ?";
        $params[] = "%$activity%";
    }

    if (!empty($date)) {
        $conditions[] = "DATE(created_at) = ?";
        $params[] = $date;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY created_at DESC";
    $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $history = $stmt->fetchAll();

    $formattedHistory = [];
    foreach ($history as $record) {
        $formattedHistory[] = [
            'id' => $record['id'],
            'activity_name' => $record['activity_name'],
            'duration_seconds' => intval($record['duration_seconds']),
            'duration_formatted' => formatDuration($record['duration_seconds']),
            'date' => date('Y-m-d', strtotime($record['created_at'])),
            'time' => date('H:i', strtotime($record['created_at']))
        ];
    }

    $countSql = "SELECT COUNT(*) as total FROM focus_history";
    if (!empty($conditions)) {
        $countSql .= " WHERE " . implode(" AND ", $conditions);
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetch()['total'];

    echo json_encode([
        'success' => true,
        'data' => $formattedHistory,
        'pagination' => [
            'total' => intval($totalRecords),
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $totalRecords
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}

function formatDuration($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}
