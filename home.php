<?php
require_once 'config/database.php';
include 'includes/header.php';
?>

<style>
    body, html {
        overflow: hidden;
        height: 100%;
    }

@media (max-width: 768px) {
    body, html {
        overflow: auto;
        height: 100%;
    }   
}
</style>

<div class="card">
    <div class="timer-container">
        <h1>🎯 Focus Timer</h1>
        <p>Kelola waktu fokus Anda untuk produktivitas maksimal</p>

        <div class="timer-display" id="timer">00:00:00</div>

        <model-viewer 
            id="roverViewer"
            src="assets/img/Palta.glb"
            alt="Rover 3D"
            camera-controls
            auto-rotate
            auto-rotate-speed="2"
            background-color="transparent"
            shadow-intensity="1"
            ar
        ></model-viewer>
        <audio id="bgMusic" loop preload="metadata">
            <source src="assets/audio/focus-lofi.mp3" type="audio/mpeg">
            Your browser does not support the audio element.
        </audio>

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
            
            <button id="musicToggle"
                class="music-btn"
                title="Latar belakang musik ♪"
                aria-pressed="false">♪</button>
        </div>

        <div id="message"></div>
    </div>
</div>

<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

<script>
    let timer;
    let isRunning = false;
    let startTime = localStorage.getItem('startTime') ? parseInt(localStorage.getItem('startTime')) : null;
    let pausedTime = localStorage.getItem('pausedTime') ? parseInt(localStorage.getItem('pausedTime')) : null;

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

    function getElapsedSeconds() {
        if (!startTime) return 0;
        if (pausedTime) {
            return Math.floor((pausedTime - startTime) / 1000);
        }
        return Math.floor((Date.now() - startTime) / 1000);
    }

    function updateTimer() {
        const elapsed = getElapsedSeconds();
        timerDisplay.textContent = formatTime(elapsed);
    }

    function startTimer() {
        if (!activityInput.value.trim()) {
            showMessage('Silakan masukkan jenis aktivitas terlebih dahulu!', 'error');
            return;
        }
        if (!startTime) {
            startTime = Date.now();
            localStorage.setItem('startTime', startTime);
        }
        pausedTime = null;
        localStorage.removeItem('pausedTime');
        isRunning = true;
        timer = setInterval(updateTimer, 1000);

        startBtn.disabled = true;
        pauseBtn.disabled = false;
        stopBtn.disabled = false;
        resetBtn.disabled = true;
        activityInput.disabled = true;

        showMessage('Timer dimulai! Fokus pada aktivitas Anda.', 'success');
        updateTimer();
    }

    function pauseTimer() {
        if (isRunning) {
            clearInterval(timer);
            isRunning = false;
            pausedTime = Date.now();
            localStorage.setItem('pausedTime', pausedTime);

            startBtn.disabled = false;
            pauseBtn.disabled = true;
            resetBtn.disabled = false;

            showMessage('Timer dijeda. Klik "Mulai" untuk melanjutkan.', 'success');
            updateTimer();
        }
    }

    function stopTimer() {
        if (startTime) {
            clearInterval(timer);
            isRunning = false;

            const endTime = pausedTime ? new Date(pausedTime) : new Date();
            const activityName = activityInput.value.trim();
            const durationSeconds = getElapsedSeconds();

            // Simpan ke database
            saveFocusSession(activityName, durationSeconds, new Date(startTime), endTime);

            // Reset UI dan localStorage
            resetTimer();
        }
    }

    // Simpan input ke localStorage setiap kali berubah
activityInput.addEventListener('input', function() {
    localStorage.setItem('activityInputValue', activityInput.value);
});

// Saat halaman dimuat, isi input dari localStorage jika ada
window.addEventListener('DOMContentLoaded', () => {
    const savedValue = localStorage.getItem('activityInputValue');
    if (savedValue) {
        activityInput.value = savedValue;
    }
    // Saat halaman dimuat, lanjutkan timer jika masih ada startTime di localStorage
    if (startTime) {
        if (!pausedTime) {
            isRunning = true;
            timer = setInterval(updateTimer, 1000);
            startBtn.disabled = true;
            pauseBtn.disabled = false;
            stopBtn.disabled = false;
            resetBtn.disabled = true;
            activityInput.disabled = true;
        } else {
            updateTimer();
            startBtn.disabled = false;
            pauseBtn.disabled = true;
            stopBtn.disabled = false;
            resetBtn.disabled = false;
            activityInput.disabled = true;
        }
        updateTimer();
    }
});

    function resetTimer() {
        clearInterval(timer);
        isRunning = false;
        startTime = null;
        pausedTime = null;
        localStorage.removeItem('startTime');
        localStorage.removeItem('pausedTime');
        localStorage.removeItem('activityInputValue'); // Tambahkan baris ini

        timerDisplay.textContent = formatTime(0);

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

    function saveFocusSession(activityName, durationSeconds, startTimeObj, endTimeObj) {
        const formData = new FormData();
        formData.append('activity_name', activityName);
        formData.append('duration_seconds', durationSeconds);
        formData.append('start_time', startTimeObj.toISOString());
        formData.append('end_time', endTimeObj.toISOString());

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
            } else if (!startTime) {
                startTimer();
            } else {
                startTimer();
            }
        }
    });
</script>

<script>
(function () {
  const music   = document.getElementById('bgMusic');
  const toggle  = document.getElementById('musicToggle');
  const key     = 'musicEnabled';

  // Restore previous state
  if (localStorage.getItem(key) === 'true') {
    music.volume = 0.25;            // gentle level
    music.play().catch(() => {});   // autoplay might be blocked
    toggle.classList.add('playing');
    toggle.setAttribute('aria-pressed', 'true');
  }

  toggle.addEventListener('click', () => {
    if (music.paused) {
      music.play();
      toggle.classList.add('playing');
      toggle.setAttribute('aria-pressed', 'true');
      localStorage.setItem(key, 'true');
    } else {
      music.pause();
      toggle.classList.remove('playing');
      toggle.setAttribute('aria-pressed', 'false');
      localStorage.setItem(key, 'false');
    }
  });
})();
</script>

<?php include 'includes/footer.php'; ?>