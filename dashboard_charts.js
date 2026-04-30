document.addEventListener('DOMContentLoaded', function () {
    // 1. Borrowing Trends Line Chart
    const ctxLine = document.getElementById('borrowingChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: ['Apr 06', 'Apr 07', 'Apr 08', 'Apr 09', 'Apr 10', 'Apr 11', 'Apr 12'],
            datasets: [{
                label: 'Borrows',
                data: [50, 25, 38, 20, 55, 28, 15],
                borderColor: '#3182ce',
                backgroundColor: '#3182ce',
                tension: 0.4,
                fill: false
            }, {
                label: 'Returns',
                data: [12, 32, 28, 5, 45, 43, 20],
                borderColor: '#38a169',
                backgroundColor: '#38a169',
                tension: 0.4,
                fill: false
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } } 
        }
    });

    // 2. Genre Popularity Doughnut
    const ctxDoughnut = document.getElementById('genreChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Biology', 'History', 'Political Science'],
            datasets: [{
                data: [30, 20, 50],
                backgroundColor: ['#1d456d', '#2b6cb0', '#4299e1'],
                borderWidth: 2
            }]
        },
        options: { 
            cutout: '70%', 
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } } 
        }
    });

    // 3. Materials Added (Horizontal Bar)
    const ctxBar = document.getElementById('materialsChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Math', 'Psychology', 'Chemistry', 'Economics', 'Sociology'],
            datasets: [{
                label: 'Items',
                data: [6, 5, 4, 3, 3],
                backgroundColor: '#4299e1',
                borderRadius: 5
            }]
        },
        options: { 
            indexAxis: 'y', 
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // 4. Fine Summary (Vertical Bar)
    const ctxFines = document.getElementById('finesChart').getContext('2d');
    new Chart(ctxFines, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{
                label: 'Fines (₱)',
                data: [120, 320, 0, 0, 0],
                backgroundColor: '#3182ce',
                borderRadius: 8
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false 
        }
    });
});

function openModal() {
    document.getElementById('addBookModal').classList.add('active');
}

function closeModal() {
    document.getElementById('addBookModal').classList.remove('active');
}

// Close if user clicks the dark background
window.onclick = function(event) {
    let modal = document.getElementById('addBookModal');
    if (event.target == modal) {
        closeModal();
    }
}

function toggleActiveBorrows() {
    const section = document.getElementById('activeBorrowsSection');
    const arrow = document.getElementById('borrowArrow');
    
    // Toggle the hidden class
    section.classList.toggle('hidden');
    
    // Rotate the arrow icon
    arrow.classList.toggle('rotate-arrow');
}

function toggleDetails(sectionId, arrowId) {
    const section = document.getElementById(sectionId);
    const arrow = document.getElementById(arrowId);
    
    // Check if it's already open
    const isOpen = !section.classList.contains('hidden');
    
    // Close all other details-sections first (Optional, for a cleaner UI)
    document.querySelectorAll('.details-section').forEach(s => s.classList.add('hidden'));
    document.querySelectorAll('.dropdown-arrow').forEach(a => a.classList.remove('rotate-arrow'));

    // Toggle the selected one
    if (!isOpen) {
        section.classList.remove('hidden');
        arrow.classList.add('rotate-arrow');
    }
}