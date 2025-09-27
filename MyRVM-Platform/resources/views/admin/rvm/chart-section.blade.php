<!-- System Performance Chart -->
@if($systemMetrics->count() > 0)
<div class="row mb-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="fas fa-chart-area"></i>
                            </span>
                        </div>
                        <div>
                            <h5 class="mb-0">System Performance</h5>
                            <small>Last {{ $systemMetrics->count() }} system performance readings</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <select class="form-select form-select-sm" id="timeRange" style="width: auto;">
                            <option value="1h">Last Hour</option>
                            <option value="6h">Last 6 Hours</option>
                            <option value="24h" selected>Last 24 Hours</option>
                            <option value="7d">Last 7 Days</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 12px; height: 12px; background-color: #17a2b8; border-radius: 2px;"></div>
                                <small class="text-muted">CPU Usage</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 12px; height: 12px; background-color: #28a745; border-radius: 2px;"></div>
                                <small class="text-muted">Memory Usage</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 12px; height: 12px; background-color: #ffc107; border-radius: 2px;"></div>
                                <small class="text-muted">Disk Usage</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="width: 12px; height: 12px; background-color: #6f42c1; border-radius: 2px;"></div>
                                <small class="text-muted">Uptime (hours)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chart-container" style="position: relative; height: 300px;">
                    <canvas id="systemPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for chart
    const systemMetrics = @json($systemMetrics);
    
    // Sort data by timestamp (oldest first for proper chart display)
    const sortedMetrics = systemMetrics.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    
    const labels = sortedMetrics.map(metric => {
        const date = new Date(metric.created_at);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    });
    
    const cpuData = sortedMetrics.map(metric => parseFloat(metric.cpu_usage) || 0);
    const memoryData = sortedMetrics.map(metric => parseFloat(metric.memory_usage) || 0);
    const diskData = sortedMetrics.map(metric => parseFloat(metric.disk_usage) || 0);
    const uptimeData = sortedMetrics.map(metric => {
        const uptime = parseFloat(metric.uptime) || 0;
        return uptime / 3600; // Convert seconds to hours
    });
    
    // Create the chart
    const ctx = document.getElementById('systemPerformanceChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'CPU Usage (%)',
                    data: cpuData,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Memory Usage (%)',
                    data: memoryData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Disk Usage (%)',
                    data: diskData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5
                },
                {
                    label: 'Uptime (hours)',
                    data: uptimeData,
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false // We're using custom legend above
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#dee2e6',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label === 'Uptime (hours)') {
                                label += context.parsed.y.toFixed(2) + ' hours';
                            } else {
                                label += context.parsed.y.toFixed(2) + '%';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#6c757d',
                        maxTicksLimit: 8
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    min: 0,
                    max: 100,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        color: '#6c757d',
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    min: 0,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        color: '#6c757d',
                        callback: function(value) {
                            return value.toFixed(1) + 'h';
                        }
                    }
                }
            }
        }
    });
    
    // Time range selector functionality
    document.getElementById('timeRange').addEventListener('change', function() {
        // This would typically make an AJAX call to get filtered data
        // For now, we'll just show a message
        console.log('Time range changed to:', this.value);
        // You can implement AJAX filtering here
    });
});
</script>
@endif
