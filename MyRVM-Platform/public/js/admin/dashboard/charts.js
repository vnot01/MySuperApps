// Charting Functions

function initializeStatusChart() {
    console.log('Initializing status chart...');
    
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    const canvas = document.getElementById('statusChart');
    if (!canvas) {
        console.error('Status chart canvas not found!');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Could not get 2D context for chart!');
        return;
    }
    
    console.log('Chart canvas found, creating chart...');
    
    try {
        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'Maintenance', 'Full/Error'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#28a745', '#6c757d', '#ffc107', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { 
                    legend: { 
                        position: 'bottom', 
                        labels: { padding: 20 } 
                    } 
                }
            }
        });
        
        console.log('Status chart initialized successfully:', statusChart);
    } catch (error) {
        console.error('Error initializing status chart:', error);
    }
}

function updateStatusChart() {
    if (!statusChart || !monitoringData) return;
    
    console.log('Updating status chart with data:', monitoringData.rvms);
    
    const counts = monitoringData.rvms.reduce((acc, rvm) => {
        const status = rvm.calculated_status || rvm.status;
        acc[status] = (acc[status] || 0) + 1;
        return acc;
    }, {});
    
    console.log('Status counts:', counts);
    
    statusChart.data.datasets[0].data = [
        counts.active || 0,
        counts.inactive || 0,
        counts.maintenance || 0,
        (counts.full || 0) + (counts.error || 0)
    ];
    
    console.log('Chart data updated:', statusChart.data.datasets[0].data);
    statusChart.update();
}
