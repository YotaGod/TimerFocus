<?php
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="card">
    <h1>📊 Dashboard Statistik Fokus</h1>
    <p>Analisis produktivitas dan pola fokus Anda</p>

    <div class="period-selector">
        <button class="btn btn-primary" onclick="changePeriod('week')" id="weekBtn">Minggu Ini</button>
        <button class="btn btn-secondary" onclick="changePeriod('month')" id="monthBtn">Bulan Ini</button>
        <button class="btn btn-secondary" onclick="changePeriod('year')" id="yearBtn">Tahun Ini</button>
        <input type="date" id="startDate" class="activity-input">
        <input type="date" id="endDate" class="activity-input">
        <button class="btn btn-primary" onclick="loadCustomPeriod()">Terapkan</button>
    </div>

    <div id="loading" class="loading">Memuat statistik...</div>
    <div id="error" class="error" style="display: none;"></div>

    <!-- Statistik Umum -->
    <div class="stats-grid" id="overallStats" style="display: none;">
        <div class="stat-card">
            <div class="stat-number" id="totalHours">0</div>
            <div class="stat-label">Total Jam Fokus</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalSessions">0</div>
            <div class="stat-label">Total Sesi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="avgDuration">0</div>
            <div class="stat-label">Rata-rata Durasi (jam)</div>
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
                    <th>Total Jam</th>
                    <th>Jumlah Sesi</th>
                    <th>Rata-rata Durasi</th>
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

    // Load dashboard saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboard();
        setDefaultDates();
    });

    function setDefaultDates() {
        const endDate = new Date();
        const startDate = new Date();

        switch (currentPeriod) {
            case 'week':
                startDate.setDate(endDate.getDate() - 7);
                break;
            case 'month':
                startDate.setDate(endDate.getDate() - 30);
                break;
            case 'year':
                startDate.setDate(endDate.getDate() - 365);
                break;
        }

        document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
        document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
    }

    function changePeriod(period) {
        currentPeriod = period;

        // Update button states
        document.getElementById('weekBtn').className = 'btn btn-secondary';
        document.getElementById('monthBtn').className = 'btn btn-secondary';
        document.getElementById('yearBtn').className = 'btn btn-secondary';
        document.getElementById(period + 'Btn').className = 'btn btn-primary';

        setDefaultDates();
        loadDashboard();
    }

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

    function displayDashboard(data) {
        if (data.overall_stats.total_sessions === 0) {
            showNoData();
            return;
        }

        hideNoData();

        // Update statistik umum
        document.getElementById('totalHours').textContent = data.overall_stats.total_hours;
        document.getElementById('totalSessions').textContent = data.overall_stats.total_sessions;
        document.getElementById('avgDuration').textContent = data.overall_stats.avg_hours;
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

    function createDailyChart(dailyStats) {
        const ctx = document.getElementById('dailyChart').getContext('2d');

        if (charts.daily) {
            charts.daily.destroy();
        }

        const labels = dailyStats.map(day => formatDate(day.date));
        const data = dailyStats.map(day => day.total_hours);

        charts.daily = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jam Fokus per Hari',
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
                            text: 'Jam'
                        }
                    }
                }
            }
        });
    }

    function createActivityChart(activityStats) {
        const ctx = document.getElementById('activityChart').getContext('2d');

        if (charts.activity) {
            charts.activity.destroy();
        }

        const labels = activityStats.map(activity => activity.activity_name);
        const data = activityStats.map(activity => activity.total_hours);
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

    function createHourlyChart(hourlyStats) {
        const ctx = document.getElementById('hourlyChart').getContext('2d');

        if (charts.hourly) {
            charts.hourly.destroy();
        }

        const labels = hourlyStats.map(hour => hour.hour_label);
        const data = hourlyStats.map(hour => hour.total_hours);

        charts.hourly = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jam Fokus per Jam',
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
                            text: 'Jam'
                        }
                    }
                }
            }
        });
    }

    function updateTopActivitiesTable(activityStats) {
        const tableBody = document.getElementById('topActivitiesTable');
        tableBody.innerHTML = '';

        activityStats.forEach((activity, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td><strong>#${index + 1}</strong></td>
            <td><strong>${escapeHtml(activity.activity_name)}</strong></td>
            <td><span style="font-family: monospace;">${activity.total_hours} jam</span></td>
            <td>${activity.sessions} sesi</td>
            <td>${activity.avg_hours} jam</td>
        `;
            tableBody.appendChild(row);
        });
    }

    function generateColors(count) {
        const colors = [
            '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe',
            '#00f2fe', '#43e97b', '#38f9d7', '#fa709a', '#fee140'
        ];

        const result = [];
        for (let i = 0; i < count; i++) {
            result.push(colors[i % colors.length]);
        }
        return result;
    }

    function getPeriodLabel(period) {
        const start = new Date(period.start_date);
        const end = new Date(period.end_date);

        if (period.period === 'custom') {
            return `${formatDate(start)} - ${formatDate(end)}`;
        }

        const labels = {
            'week': 'Minggu Ini',
            'month': 'Bulan Ini',
            'year': 'Tahun Ini'
        };

        return labels[period.period] || 'Periode Kustom';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short'
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

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