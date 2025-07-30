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
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';

    if (empty($activityName)) {
        throw new Exception('Nama aktivitas tidak boleh kosong');
    }

    if ($durationSeconds <= 0) {
        throw new Exception('Durasi harus lebih dari 0 detik');
    }

    if (empty($startTime) || empty($endTime)) {
        throw new Exception('Waktu mulai dan selesai harus diisi');
    }

    // Validasi format waktu
    $startDateTime = new DateTime($startTime);
    $endDateTime = new DateTime($endTime);

    if ($endDateTime <= $startDateTime) {
        throw new Exception('Waktu selesai harus setelah waktu mulai');
    }

    // Simpan ke database
    $stmt = $pdo->prepare("
        INSERT INTO focus_history (activity_name, duration_seconds, start_time, end_time) 
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $activityName,
        $durationSeconds,
        $startDateTime->format('Y-m-d H:i:s'),
        $endDateTime->format('Y-m-d H:i:s')
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Sesi fokus berhasil disimpan',
        'data' => [
            'id' => $pdo->lastInsertId(),
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
