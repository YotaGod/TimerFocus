<?php
require_once 'config/database.php';
include 'includes/header.php';
?>

<div class="card">
    <h1>📚 Riwayat Aktivitas Fokus</h1>
    <p>Lihat semua sesi fokus yang telah Anda lakukan</p>

    <div class="filters">
        <div class="filter-row">
            <input type="text" id="activityFilter" placeholder="Filter berdasarkan aktivitas..." class="activity-input">
            <input type="date" id="dateFilter" class="activity-input">
            <button class="btn btn-primary" onclick="loadHistory()">🔍 Filter</button>
        </div>
        <div class="button-row">
            <button class="btn btn-secondary" onclick="clearFilters()">🔄 Reset</button>
        </div>
    </div>

    <div id="loading" class="loading">Memuat riwayat...</div>
    <div id="error" class="error" style="display: none;"></div>

    <div class="table-wrapper" id="tableWrapper" style="display: none;">
        <table class="history-table" id="historyTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Aktivitas</th>
                    <th>Durasi</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                </tr>
            </thead>
            <tbody id="historyTableBody">
            </tbody>
        </table>
    </div>

    <div id="pagination" style="display: none;">
        <div class="pagination-controls">
            <button class="btn btn-secondary" id="prevBtn" onclick="loadPrevious()">←</button>
            <span id="pageInfo"></span>
            <button class="btn btn-secondary" id="nextBtn" onclick="loadNext()">→</button>
        </div>
    </div>

    <div id="noData" style="display: none; text-align: center; padding: 40px; color: #666;">
        <h3>Belum ada data aktivitas fokus</h3>
        <p>Mulai timer di halaman utama untuk mencatat aktivitas fokus Anda!</p>
    </div>
</div>

<script>
    let currentOffset = 0;
    let currentLimit = 20;
    let totalRecords = 0;

    // Load history saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        loadHistory();
    });

    function loadHistory(resetOffset = true) {
        if (resetOffset) {
            currentOffset = 0;
        }

        const activityFilter = document.getElementById('activityFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;

        showLoading();

        const params = new URLSearchParams({
            limit: currentLimit,
            offset: currentOffset
        });

        if (activityFilter) {
            params.append('activity', activityFilter);
        }

        if (dateFilter) {
            params.append('date', dateFilter);
        }

        fetch(`get_history.php?${params}`)
            .then(response => response.json())
            .then(data => {
                hideLoading();

                if (data.success) {
                    displayHistory(data.data);
                    updatePagination(data.pagination);
                } else {
                    showError(data.message || 'Terjadi kesalahan saat memuat data');
                }
            })
            .catch(error => {
                hideLoading();
                showError('Terjadi kesalahan: ' + error.message);
            });
    }

    function displayHistory(history) {
        const tableWrapper = document.getElementById('tableWrapper');
        const table = document.getElementById('historyTable');
        const tableBody = document.getElementById('historyTableBody');
        const noData = document.getElementById('noData');

        if (history.length === 0) {
            tableWrapper.style.display = 'none';
            document.getElementById('pagination').style.display = 'none';
            noData.style.display = 'block';
            return;
        }

        tableWrapper.style.display = 'block';
        document.getElementById('pagination').style.display = 'block';
        noData.style.display = 'none';

        tableBody.innerHTML = '';

        history.forEach(record => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${formatDate(record.date)}</td>
            <td>${record.time}</td>
            <td><strong>${escapeHtml(record.activity_name)}</strong></td>
            <td><span style="font-family: monospace; font-weight: bold;">${record.duration_formatted}</span></td>
            <td>${formatDateTime(record.start_time)}</td>
            <td>${formatDateTime(record.end_time)}</td>
        `;
            tableBody.appendChild(row);
        });
    }

    function updatePagination(pagination) {
        totalRecords = pagination.total;
        const pageInfo = document.getElementById('pageInfo');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        const currentPage = Math.floor(currentOffset / currentLimit) + 1;
        const totalPages = Math.ceil(totalRecords / currentLimit);

        pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages} (Total: ${totalRecords} aktivitas)`;

        prevBtn.disabled = currentOffset === 0;
        nextBtn.disabled = !pagination.has_more;
    }

    function loadPrevious() {
        if (currentOffset > 0) {
            currentOffset = Math.max(0, currentOffset - currentLimit);
            loadHistory(false);
        }
    }

    function loadNext() {
        if (currentOffset + currentLimit < totalRecords) {
            currentOffset += currentLimit;
            loadHistory(false);
        }
    }

    function clearFilters() {
        document.getElementById('activityFilter').value = '';
        document.getElementById('dateFilter').value = '';
        loadHistory();
    }

    function showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('error').style.display = 'none';
        document.getElementById('tableWrapper').style.display = 'none';
        document.getElementById('pagination').style.display = 'none';
        document.getElementById('noData').style.display = 'none';
    }

    function hideLoading() {
        document.getElementById('loading').style.display = 'none';
    }

    function showError(message) {
        const errorDiv = document.getElementById('error');
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        document.getElementById('tableWrapper').style.display = 'none';
        document.getElementById('pagination').style.display = 'none';
        document.getElementById('noData').style.display = 'none';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');

        return `${day}/${month}/${year}, ${hours}.${minutes}`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Keyboard shortcuts untuk filter
    document.getElementById('activityFilter').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadHistory();
        }
    });

    document.getElementById('dateFilter').addEventListener('change', function() {
        loadHistory();
    });
</script>

<?php include 'includes/footer.php'; ?>