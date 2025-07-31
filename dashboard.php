<?php
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="card">
    <h1>📊 Dashboard Statistik Fokus</h1>
    <p>Analisis produktivitas dan pola fokus Anda</p>

    <div class="period-selector">
        <div class="period-card">
            <!-- 📅 Period Dropdown -->
            <div class="input-group">
                <label for="periodSelect">Periode</label>
                <select id="periodSelect" class="select-input" onchange="changePeriod(this.value)">
                    <option value="week" selected>Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>

            <!-- 🗓️ Date Range Picker -->
            <div class="date-range">
                <div class="date-group">
                    <label for="startDate">Dari</label>
                    <input type="date" id="startDate" class="date-input">
                </div>

                <div class="date-group">
                    <label for="endDate">Sampai</label>
                    <input type="date" id="endDate" class="date-input">
                </div>

                <button class="btn apply-btn" onclick="loadCustomPeriod()">
                    Terapkan
                </button>
            </div>
        </div>
    </div>

    <div id="loading" class="loading">Memuat statistik...</div>
    <div id="error" class="error" style="display: none;"></div>

    <!-- Statistik Umum -->
    <div class="stats-grid" id="overallStats" style="display: none;">
        <div class="stat-card">
            <div class="stat-number" id="totalMinutes">0</div>
            <div class="stat-label">Total Fokus (menit)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalSessions">0</div>
            <div class="stat-label">Total Sesi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="avgDuration">0</div>
            <div class="stat-label">Rata-rata Durasi (menit)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="periodLabel">Minggu Ini</div>
            <div class="stat-label">Periode</div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="card" id="chartsContainer" style="display: none;">
        <h2>📈 Grafik Aktivitas</h2>

        <div class="chart-container">
            <canvas id="dailyChart"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="activityChart"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>

    <!-- Tabel Aktivitas Teratas -->
    <div class="card" id="topActivitiesContainer" style="display: none;">
        <h2>🏆 Aktivitas Teratas</h2>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Peringkat</th>
                    <th>Aktivitas</th>
                    <th>Total Fokus (menit)</th>
                    <th>Jumlah Sesi</th>
                    <th>Rata-rata Durasi (menit)</th>
                    <th>Jam</th> <!-- Kolom Jam baru -->
                </tr>
            </thead>
            <tbody id="topActivitiesTable">
            </tbody>
        </table>
    </div>

    <div id="noData" style="display: none; text-align: center; padding: 40px; color: #666;">
        <h3>Belum ada data untuk ditampilkan</h3>
        <p>Mulai timer di halaman utama untuk melihat statistik fokus Anda!</p>
    </div>
</div>

<script>
    let currentPeriod = 'week';
    let charts = {};

    // -------------------------------------------------
    // 🔁 Auto full-year range when "year" is selected
    // -------------------------------------------------
    const periodSelect = document.getElementById('periodSelect');
    const startDateEl = document.getElementById('startDate');
    const endDateEl = document.getElementById('endDate');

    periodSelect.addEventListener('change', () => {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1; // Bulan saat ini (1-12)
        const todayStr = now.toISOString().split('T')[0];

        if (periodSelect.value === 'year') {
            startDateEl.value = `${year}-01-01`;
            endDateEl.value = `${year}-12-31`;
            loadDashboard('year');
        } else if (periodSelect.value === 'month') {
            // Awal bulan saat ini
            const firstDay = `${year}-${month.toString().padStart(2, '0')}-01`;
            // Akhir bulan saat ini
            const lastDayDate = new Date(year, month, 0); // 0 = hari terakhir bulan ini
            const lastDay = `${year}-${month.toString().padStart(2, '0')}-${lastDayDate.getDate().toString().padStart(2, '0')}`;
            startDateEl.value = firstDay;
            endDateEl.value = lastDay;
            loadDashboard('month');
        } else if (periodSelect.value === 'week') {
            // Cari hari Senin dan Minggu minggu ini
            const dayOfWeek = now.getDay(); // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu
            const monday = new Date(now);
            monday.setDate(now.getDate() - ((dayOfWeek + 6) % 7));
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            startDateEl.value = monday.toISOString().split('T')[0];
            endDateEl.value = sunday.toISOString().split('T')[0];
            loadDashboard('week');
        }
    });

    /* opsional: panggil sekali saat halaman dimuat jika default = year */
    if (periodSelect.value === 'year') {
        const y = new Date().getFullYear();
        startDateEl.value = `${y}-01-01`;
        endDateEl.value = `${y}-12-31`;
    }

    let startTime = localStorage.getItem('startTime') ? parseInt(localStorage.getItem('startTime')) : null;

    function startTimer() {
        if (!startTime) {
            startTime = Date.now();
            localStorage.setItem('startTime', startTime);
        }
        timer = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        timerDisplay.textContent = formatTime(elapsed);
    }

    function stopTimer() {
        clearInterval(timer);
        localStorage.removeItem('startTime');
    }

    // Load dashboard saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
        setDefaultDates();
        // Set dropdown ke periode saat ini
        document.getElementById('periodSelect').value = currentPeriod;
    });

    // 🎯 Set tanggal default berdasarkan periode
    function setDefaultDates() {
        const now = new Date();
        const todayStr = now.toISOString().split('T')[0];

        if (currentPeriod === 'week') {
            const dayOfWeek = now.getDay();
            const monday = new Date(now);
            monday.setDate(now.getDate() - ((dayOfWeek + 6) % 7));
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            startDateEl.value = monday.toISOString().split('T')[0];
            endDateEl.value = sunday.toISOString().split('T')[0];
        } else if (currentPeriod === 'month') {
            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            const firstDay = `${year}-${month.toString().padStart(2, '0')}-01`;
            const lastDayDate = new Date(year, month, 0);
            const lastDay = `${year}-${month.toString().padStart(2, '0')}-${lastDayDate.getDate().toString().padStart(2, '0')}`;
            startDateEl.value = firstDay;
            endDateEl.value = lastDay;
        } else if (currentPeriod === 'year') {
            const year = now.getFullYear();
            startDateEl.value = `${year}-01-01`;
            endDateEl.value = `${year}-12-31`;
        } else {
            // Default: endDate adalah hari ini
            startDateEl.value = todayStr;
            endDateEl.value = todayStr;
        }
    }

    // 🔄 Ganti periode melalui dropdown
    function changePeriod(period) {
        currentPeriod = period;
        setDefaultDates();
        loadDashboard();
    }

    // 📅 Load periode kustom
    function loadCustomPeriod() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (!startDate || !endDate) {
            showError('Silakan pilih tanggal mulai dan selesai');
            return;
        }

        if (startDate > endDate) {
            showError('Tanggal mulai harus sebelum tanggal selesai');
            return;
        }

        loadDashboard(startDate, endDate);
    }

    // 📊 Load data dashboard
    function loadDashboard(startDate = '', endDate = '') {
        showLoading();

        const params = new URLSearchParams({
            period: currentPeriod
        });

        if (startDate && endDate) {
            params.set('start_date', startDate);
            params.set('end_date', endDate);
        }

        fetch(`get_dashboard_data.php?${params}`)
            .then(response => response.json())
            .then(data => {
                hideLoading();
                console.log('Dashboard data:', data);

                if (data.success) {
                    displayDashboard(data);
                } else {
                    showError(data.message || 'Terjadi kesalahan saat memuat data');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Dashboard error:', error);
                showError('Terjadi kesalahan: ' + error.message);
            });
    }

    // 🖼️ Tampilkan dashboard
    function displayDashboard(data) {
        if (data.overall_stats.total_sessions === 0) {
            showNoData();
            return;
        }

        hideNoData();

        // Update statistik umum
        document.getElementById('totalMinutes').textContent = data.overall_stats.total_minutes;
        document.getElementById('totalSessions').textContent = data.overall_stats.total_sessions;
        document.getElementById('avgDuration').textContent = data.overall_stats.avg_minutes;
        document.getElementById('periodLabel').textContent = getPeriodLabel(data.period);

        document.getElementById('overallStats').style.display = 'grid';
        document.getElementById('chartsContainer').style.display = 'block';
        document.getElementById('topActivitiesContainer').style.display = 'block';

        // Buat grafik
        createDailyChart(data.daily_stats);
        createActivityChart(data.activity_stats);
        createHourlyChart(data.hourly_stats);

        // Update tabel aktivitas teratas
        updateTopActivitiesTable(data.activity_stats);
    }

    // 📈 Grafik tren harian
    function createDailyChart(dailyStats) {
        const ctx = document.getElementById('dailyChart').getContext('2d');
        if (charts.daily) charts.daily.destroy();

        const labels = dailyStats.map(day => formatDate(day.date));
        const data = dailyStats.map(day => day.total_minutes);

        charts.daily = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Fokus (menit) per Hari',
                    data: data,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tren Fokus Harian'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Menit'
                        }
                    }
                }
            }
        });
    }

    // 🍩 Grafik distribusi aktivitas
    function createActivityChart(activityStats) {
        const ctx = document.getElementById('activityChart').getContext('2d');
        if (charts.activity) charts.activity.destroy();

        const labels = activityStats.map(a => a.activity_name);
        const data = activityStats.map(a => a.total_minutes);
        const colors = generateColors(labels.length);

        charts.activity = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribusi Fokus per Aktivitas'
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // 📊 Grafik jam produktif
    function createHourlyChart(hourlyStats) {
        const ctx = document.getElementById('hourlyChart').getContext('2d');
        if (charts.hourly) charts.hourly.destroy();

        const labels = hourlyStats.map(h => h.hour_label);
        const data = hourlyStats.map(h => h.total_minutes);

        charts.hourly = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Fokus (menit) per Jam',
                    data: data,
                    backgroundColor: '#764ba2',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Jam Produktif'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Menit'
                        }
                    }
                }
            }
        });
    }

    // 🏆 Tabel aktivitas teratas
    function updateTopActivitiesTable(activityStats) {
        const tableBody = document.getElementById('topActivitiesTable');
        tableBody.innerHTML = '';

        activityStats.forEach((activity, index) => {
            const jam = activity.total_hours !== undefined
                ? `${activity.total_hours} jam`
                : '-';
            const row = document.createElement('tr');
            row.innerHTML = `
            <td><strong>#${index + 1}</strong></td>
            <td><strong>${escapeHtml(activity.activity_name)}</strong></td>
            <td><span style="font-family: monospace;">${activity.total_minutes} menit</span></td>
            <td>${activity.sessions} sesi</td>
            <td>${activity.avg_minutes} menit</td>
            <td>${jam}</td> <!-- Data jam total fokus -->
        `;
            tableBody.appendChild(row);
        });
    }

    // 🎨 Generate warna grafik
    function generateColors(count) {
        const colors = [
            '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe',
            '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140'
        ];
        const result = [];
        for (let i = 0; i < count; i++) result.push(colors[i % colors.length]);
        return result;
    }

    // 📝 Format label periode
    function getPeriodLabel(period) {
        const start = new Date(period.start_date);
        const end = new Date(period.end_date);

        if (period.period === 'custom') {
            return `${formatDate(start)} - ${formatDate(end)}`;
        }

        const labels = {
            week: 'Minggu Ini',
            month: 'Bulan Ini',
            year: 'Tahun Ini'
        };
        return labels[period.period] || 'Periode Kustom';
    }

    // 📅 Format tanggal
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short'
        });
    }

    // 🔒 Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // 🔄 Loading states
    function showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('error').style.display = 'none';
        document.getElementById('overallStats').style.display = 'none';
        document.getElementById('chartsContainer').style.display = 'none';
        document.getElementById('topActivitiesContainer').style.display = 'none';
        document.getElementById('noData').style.display = 'none';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    function showError(message) {
        const errorDiv = document.getElementById('error');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        document.getElementById('overallStats').style.display = 'none';
        document.getElementById('chartsContainer').style.display = 'none';
        document.getElementById('topActivitiesContainer').style.display = 'none';
        document.getElementById('noData').style.display = 'none';
    }

    function showNoData() {
        document.getElementById('overallStats').style.display = 'none';
        document.getElementById('chartsContainer').style.display = 'none';
        document.getElementById('topActivitiesContainer').style.display = 'none';
        document.getElementById('noData').style.display = 'block';
    }

    function hideNoData() {
        document.getElementById('noData').style.display = 'none';
    }
</script>

<?php include 'includes/footer.php'; ?>