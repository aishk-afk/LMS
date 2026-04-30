// Chart instances - stored globally for updates
let charts = {
    borrowing: null,
    genre: null,
    materials: null,
    fines: null
};

// Function to fetch dashboard data from server
async function fetchDashboardData(type, startDate, endDate, period = 'week') {
    try {
        const url = `getDashboardData.php?type=${type}&startDate=${startDate}&endDate=${endDate}&period=${period}`;
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error fetching data:', error);
        return [];
    }
}

// Update statistics cards
async function updateStatistics(startDate, endDate) {
    try {
        // Active Borrows
        const activeBorrows = await fetchDashboardData('activeBorrows', startDate, endDate);
        document.querySelector('.stat-card:nth-child(1) h3').textContent = activeBorrows.count || 0;

        // Overdue Items
        const overdueItems = await fetchDashboardData('overdueItems', startDate, endDate);
        document.querySelector('.stat-card:nth-child(2) h3').textContent = overdueItems.count || 0;

        // Total Books
        const totalBooks = await fetchDashboardData('totalBooks', startDate, endDate);
        document.querySelector('.stat-card:nth-child(3) h3').textContent = totalBooks.count || 0;

        // Fines Collected
        const finesCollected = await fetchDashboardData('finesCollected', startDate, endDate);
        document.querySelector('.stat-card:nth-child(4) h3').textContent = '₱' + finesCollected.total || 0;
    } catch (error) {
        console.error('Error updating statistics:', error);
    }
}

// Update fine summary display
async function updateFineSummary() {
    try {
        const fineSummary = await fetchDashboardData('fineSummary', '', '');
        const summaryElement = document.querySelector('.dashboard-row.single .card small');
        if (summaryElement) {
            summaryElement.textContent = `Collected: ₱${fineSummary.collected || 0} · Pending: ₱${fineSummary.pending || 0} · Total: ₱${fineSummary.total || 0}`;
        }
    } catch (error) {
        console.error('Error updating fine summary:', error);
    }
}

// Update active borrow details table
async function updateActiveBorrowsTable(startDate, endDate) {
    try {
        const data = await fetchDashboardData('activeBorrowDetails', startDate, endDate);
        const tbody = document.querySelector('#activeSection .borrow-table tbody');
        
        if (!tbody) return;
        
        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">No active borrows</td></tr>';
            return;
        }

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><div style="display:flex; align-items:center; gap:12px;">
                    <div class="book-img-holder"><img src="book_placeholder.jpg" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2245%22 height=%2260%22%3E%3Crect fill=%22%23e2e8f0%22 width=%2245%22 height=%2260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%2364748b%22 font-size=%2210%22%3EBook%3C/text%3E%3C/svg%3E'"></div>
                    ${row.title}
                </div></td>
                <td>${row.user_id}</td>
                <td>${row.due_date}</td>
                <td><button class="btn-return">Return</button></td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error('Error updating active borrows table:', error);
    }
}

// Update overdue details table
async function updateOverdueTable(startDate, endDate) {
    try {
        const data = await fetchDashboardData('overdueDetails', startDate, endDate);
        const tbody = document.querySelector('#overdueSection .borrow-table tbody');
        
        if (!tbody) return;

        tbody.innerHTML = '';
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #64748b;">No overdue items</td></tr>';
            return;
        }

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><div style="display:flex; align-items:center; gap:12px;">
                    <div class="book-img-holder"><img src="book_placeholder.jpg" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2245%22 height=%2260%22%3E%3Crect fill=%22%23e2e8f0%22 width=%2245%22 height=%2260%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%2364748b%22 font-size=%2210%22%3EBook%3C/text%3E%3C/svg%3E'"></div>
                    ${row.title}
                </div></td>
                <td>${row.user_id}</td>
                <td style="color: #ef4444; font-weight: 600;">${row.days_overdue} days</td>
                <td>₱${row.fine_amount || 0}</td>
                <td><button class="btn-return" style="background:#ef4444">Return</button></td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error('Error updating overdue table:', error);
    }
}

// Update top borrowers
async function updateTopBorrowers(startDate, endDate) {
    try {
        const data = await fetchDashboardData('topBorrowers', startDate, endDate);
        const container = document.getElementById('topBorrowersList');
        
        if (!container) return;
        container.innerHTML = '';

        if (data.length === 0) {
            container.innerHTML = '<div class="borrower-item" style="display:flex; align-items:center; justify-content:space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;"><div style="color:#64748b;">No top borrowers found for this period.</div></div>';
            return;
        }

        data.slice(0, 5).forEach(row => {
            const div = document.createElement('div');
            div.className = 'borrower-item';
            div.style.cssText = 'display:flex; align-items:center; justify-content:space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;';
            
            const initials = row.user_id ? row.user_id.substring(0, 1).toUpperCase() : '?';
            div.innerHTML = `
                <div style="display:flex; align-items:center; gap:12px;">
                    <div class="avatar" style="background:#eff6ff; color:#3b82f6; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:600;">${initials}</div>
                    <div><strong>${row.user_id}</strong><br><small style="color:#64748b;">Borrowed ${row.borrow_count} books</small></div>
                </div>
                <span style="font-weight:600; color:#3b82f6;">${row.borrow_count}x</span>
            `;
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Error updating top borrowers:', error);
    }
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function () {
    initializeBorrowingChart();
    initializeGenreChart();
    initializeMaterialsChart();
    initializeFinesChart();
});

function initializeBorrowingChart() {
    const ctxLine = document.getElementById('borrowingChart').getContext('2d');
    
    charts.borrowing = new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Borrows',
                data: [],
                borderColor: '#3182ce',
                backgroundColor: 'rgba(49, 130, 206, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#3182ce'
            }, {
                label: 'Returns',
                data: [],
                borderColor: '#38a169',
                backgroundColor: 'rgba(56, 161, 105, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#38a169'
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initializeGenreChart() {
    const ctxDoughnut = document.getElementById('genreChart').getContext('2d');
    const defaultLabels = ['Biology', 'History', 'Political Science', 'Mathematics', 'Literature'];
    const defaultColors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8'];

    charts.genre = new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: defaultLabels,
            datasets: [{
                data: [0, 0, 0, 0, 0],
                backgroundColor: defaultColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: { 
            cutout: '70%', 
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } } 
        }
    });
}

function initializeMaterialsChart() {
    const ctxBar = document.getElementById('materialsChart').getContext('2d');
    charts.materials = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Items',
                data: [],
                backgroundColor: '#4299e1',
                borderRadius: 5
            }]
        },
        options: { 
            indexAxis: 'y', 
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
}

function initializeFinesChart() {
    const ctxFines = document.getElementById('finesChart').getContext('2d');
    charts.fines = new Chart(ctxFines, {
        type: 'bar',
        data: {
            labels: [], // Start with empty labels
            datasets: [{
                label: 'Fines (₱)',
                data: [], // Start with empty data
                backgroundColor: '#3182ce',
                borderRadius: 8
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Function called when period changes
async function refreshCharts(period, dateRange) {
    const { start, end } = dateRange;
    
    // Update statistics cards
    await updateStatistics(start, end);
    
    // Update fine summary
    await updateFineSummary();
    
    // Update table details
    await updateActiveBorrowsTable(start, end);
    await updateOverdueTable(start, end);
    await updateTopBorrowers(start, end);
    
    // Update borrowing trends chart
    const borrowingData = await fetchDashboardData('borrowingTrends', start, end, period);
    if (charts.borrowing) {
        const labels = borrowingData.length > 0 ? borrowingData.map(d => d.date || d.label) : [];
        const borrows = borrowingData.length > 0 ? borrowingData.map(d => d.borrows || 0) : [];
        const returns = borrowingData.length > 0 ? borrowingData.map(d => d.returns || 0) : [];
        
        charts.borrowing.data.labels = labels;
        charts.borrowing.data.datasets[0].data = borrows;
        charts.borrowing.data.datasets[1].data = returns;
        charts.borrowing.update();
    }
    
    // Update genre chart
    const genreData = await fetchDashboardData('genrePopularity', start, end, period);
    if (charts.genre) {
        const defaultLabels = ['Biology', 'History', 'Political Science', 'Mathematics', 'Literature'];
        const defaultColors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2', '#F8B88B', '#A8E6CF'];
        const labels = genreData.length > 0 ? genreData.map(d => d.label) : defaultLabels;
        const values = genreData.length > 0 ? genreData.map(d => d.value || 0) : new Array(defaultLabels.length).fill(0);
        const colors = defaultColors.slice(0, labels.length);
        
        charts.genre.data.labels = labels;
        charts.genre.data.datasets[0].data = values;
        charts.genre.data.datasets[0].backgroundColor = colors;
        charts.genre.update();
    }
    
    // Update materials chart
    const materialsData = await fetchDashboardData('materialsAdded', start, end, period);
    if (charts.materials) {
        const labels = materialsData.length > 0 ? materialsData.map(d => d.label) : [];
        const values = materialsData.length > 0 ? materialsData.map(d => d.value || 0) : [];
        
        charts.materials.data.labels = labels;
        charts.materials.data.datasets[0].data = values;
        charts.materials.update();
    }
    
    // Update fines chart
    const finesData = await fetchDashboardData('fineSummary', start, end, period);
    
    if (charts.fines) {
        let labels = [];
        let amounts = [];
        
        if (finesData.length > 0) {
            labels = finesData.map(d => d.label);
            amounts = finesData.map(d => d.amount || 0);
        } else {
            // Generate appropriate empty labels based on period
            if (period === 'week') {
                labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            } else if (period === 'month') {
                labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
            } else {
                labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            }
            amounts = new Array(labels.length).fill(0);
        }
        
        charts.fines.data.labels = labels;
        charts.fines.data.datasets[0].data = amounts;
        charts.fines.update();
    }
}

function openModal() {
    const modal = document.getElementById('addBookModal');
    if (modal) modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('addBookModal');
    if (modal) modal.classList.remove('active');
}

// Close modal if user clicks the dark background
window.onclick = function(event) {
    let modal = document.getElementById('addBookModal');
    if (event.target === modal) {
        closeModal();
    }
}