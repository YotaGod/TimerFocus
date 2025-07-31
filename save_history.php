<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

try {
    // Validasi input
    $activityName = trim($_POST['activity_name'] ?? '');
    $durationSeconds = intval($_POST['duration_seconds'] ?? 0);

    if (empty($activityName)) {
        throw new Exception('Nama aktivitas tidak boleh kosong');
    }

    if ($durationSeconds <= 0) {
        throw new Exception('Durasi harus lebih dari 0 detik');
    }

    // Ambil id berikutnya
    $nextId = getNextId($pdo, 'focus_history', 'id');

    // Insert dengan id manual
    $stmt = $pdo->prepare("
        INSERT INTO focus_history (id, activity_name, duration_seconds) 
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $nextId,
        $activityName,
        $durationSeconds
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Sesi fokus berhasil disimpan',
        'data' => [
            'id' => $nextId,
            'activity_name' => $activityName,
            'duration_seconds' => $durationSeconds,
            'duration_formatted' => formatDuration($durationSeconds)
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function formatDuration($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

function getNextId($pdo, $table, $idColumn = 'id') {
    $stmt = $pdo->prepare("SELECT MAX($idColumn) AS max_id FROM $table");
    $stmt->execute();
    $row = $stmt->fetch();
    return isset($row['max_id']) ? intval($row['max_id']) + 1 : 1;
}
