<?php
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="card">
    <div class="timer-container">
        <h1>🎯 Focus Timer</h1>
        <p>Kelola waktu fokus Anda untuk produktivitas maksimal</p>

        <div class="timer-display" id="timer">00:00:00</div>

        <input type="text"
            class="activity-input"
            id="activityInput"
            placeholder="Masukkan jenis aktivitas (contoh: Membaca, Belajar, Olahraga)"
            maxlength="255">

        <div class="timer-controls">
            <button class="btn btn-primary" id="startBtn">▶️ Mulai</button>
            <button class="btn btn-secondary" id="pauseBtn" disabled>⏸️ Jeda</button>
            <button class="btn btn-success" id="stopBtn" disabled>⏹️ Selesai</button>
            <button class="btn btn-secondary" id="resetBtn">🔄 Reset</button>
        </div>

        <div id="message"></div>
    </div>
</div>

<script>
    let timer;
    let seconds = 0;
    let isRunning = false;
    let startTime = null;

    const timerDisplay = document.getElementById('timer');
    const startBtn = document.getElementById('startBtn');
    const pauseBtn = document.getElementById('pauseBtn');
    const stopBtn = document.getElementById('stopBtn');
    const resetBtn = document.getElementById('resetBtn');
    const activityInput = document.getElementById('activityInput');
    const messageDiv = document.getElementById('message');

    function formatTime(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }

    function updateTimer() {
        seconds++;
        timerDisplay.textContent = formatTime(seconds);
    }

    function startTimer() {
        if (!isRunning) {
            if (!activityInput.value.trim()) {
                showMessage('Silakan masukkan jenis aktivitas terlebih dahulu!', 'error');
                return;
            }

            isRunning = true;
            startTime = new Date();
            timer = setInterval(updateTimer, 1000);

            startBtn.disabled = true;
            pauseBtn.disabled = false;
            stopBtn.disabled = false;
            resetBtn.disabled = true;
            activityInput.disabled = true;

            showMessage('Timer dimulai! Fokus pada aktivitas Anda.', 'success');
        }
    }

    function pauseTimer() {
        if (isRunning) {
            clearInterval(timer);
            isRunning = false;

            startBtn.disabled = false;
            pauseBtn.disabled = true;
            resetBtn.disabled = false;

            showMessage('Timer dijeda. Klik "Mulai" untuk melanjutkan.', 'success');
        }
    }

    function stopTimer() {
        if (isRunning || seconds > 0) {
            clearInterval(timer);
            isRunning = false;

            const endTime = new Date();
            const activityName = activityInput.value.trim();

            // Simpan ke database
            saveFocusSession(activityName, seconds, startTime, endTime);

            // Reset UI
            resetTimer();
        }
    }

    function resetTimer() {
        clearInterval(timer);
        seconds = 0;
        isRunning = false;
        startTime = null;

        timerDisplay.textContent = formatTime(seconds);

        startBtn.disabled = false;
        pauseBtn.disabled = true;
        stopBtn.disabled = true;
        resetBtn.disabled = false;
        activityInput.disabled = false;
        activityInput.value = '';

        showMessage('Timer direset.', 'success');
    }

    function showMessage(message, type) {
        messageDiv.innerHTML = `<div class="${type}">${message}</div>`;
        setTimeout(() => {
            messageDiv.innerHTML = '';
        }, 3000);
    }

    function saveFocusSession(activityName, durationSeconds) {
        const formData = new FormData();
        formData.append('activity_name', activityName);
        formData.append('duration_seconds', durationSeconds);

        fetch('save_history.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Sesi fokus berhasil disimpan!', 'success');
                } else {
                    showMessage('Gagal menyimpan sesi fokus: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Terjadi kesalahan: ' + error.message, 'error');
            });
    }

    // Event listeners
    startBtn.addEventListener('click', startTimer);
    pauseBtn.addEventListener('click', pauseTimer);
    stopBtn.addEventListener('click', stopTimer);
    resetBtn.addEventListener('click', resetTimer);

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && !activityInput.matches(':focus')) {
            e.preventDefault();
            if (isRunning) {
                pauseTimer();
            } else if (seconds === 0) {
                startTimer();
            } else {
                startTimer();
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>