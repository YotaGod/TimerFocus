<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    // Ambil parameter filter (opsional)
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    $activity = trim($_GET['activity'] ?? '');
    $date = trim($_GET['date'] ?? '');

    // Validasi parameter
    $limit = max(1, min(100, $limit)); // Batasi 1-100
    $offset = max(0, $offset);

    // Query dasar
    $sql = "SELECT id, activity_name, duration_seconds, start_time, end_time, created_at FROM focus_history";
    $params = [];
    $conditions = [];

    // Filter berdasarkan aktivitas
    if (!empty($activity)) {
        $conditions[] = "activity_name LIKE ?";
        $params[] = "%$activity%";
    }

    // Filter berdasarkan tanggal
    if (!empty($date)) {
        $conditions[] = "DATE(created_at) = ?";
        $params[] = $date;
    }

    // Gabungkan kondisi
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    // Urutkan berdasarkan waktu terbaru
    $sql .= " ORDER BY created_at DESC";

    // Tambahkan limit dan offset (tidak bisa menggunakan prepared statement untuk LIMIT/OFFSET)
    $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $history = $stmt->fetchAll();

    // Format data untuk response
    $formattedHistory = [];
    foreach ($history as $record) {
        $formattedHistory[] = [
            'id' => $record['id'],
            'activity_name' => $record['activity_name'],
            'duration_seconds' => intval($record['duration_seconds']),
            'duration_formatted' => formatDuration($record['duration_seconds']),
            'start_time' => $record['start_time'],
            'end_time' => $record['end_time'],
            'created_at' => $record['created_at'],
            'date' => date('Y-m-d', strtotime($record['created_at'])),
            'time' => date('H:i', strtotime($record['created_at']))
        ];
    }

    // Hitung total records untuk pagination
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
