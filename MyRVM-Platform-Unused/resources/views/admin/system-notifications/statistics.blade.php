@extends('components.admin-layout')

@section('title', 'System Notifications Statistics')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Notification Statistics</h1>
            <p class="text-muted">Analytics and insights for system notifications</p>
        </div>
        <div>
            <a href="{{ route('admin.system-notifications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Notifications
            </a>
            <button type="button" class="btn btn-primary" onclick="refreshAllData()">
                <i class="fas fa-sync-alt me-2"></i>Refresh Data
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-notifications">
                                {{ $overview['total_notifications'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Notifications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-notifications">
                                {{ $overview['active_notifications'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-broadcast-tower fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Recipients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-recipients">
                                {{ $overview['total_recipients'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avg. Read Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avg-read-rate">
                                {{ $overview['avg_read_rate'] ?? 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Charts Column -->
        <div class="col-lg-8">
            <!-- Notifications Over Time Chart -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notifications Over Time</h6>
                </div>
                <div class="card-body">
                    <canvas id="notificationsChart" width="100%" height="40"></canvas>
                </div>
            </div>

            <!-- Notification Types Distribution -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notification Types Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="typesChart" width="100%" height="40"></canvas>
                </div>
            </div>

            <!-- Delivery Performance -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Delivery Performance</h6>
                </div>
                <div class="card-body">
                    <canvas id="deliveryChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-lg-4">
            <!-- Priority Distribution -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Priority Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="priorityChart" width="100%" height="200"></canvas>
                </div>
            </div>

            <!-- Target Audience Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Target Audience</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">All Users</span>
                            <span class="text-muted small">{{ $targetStats['all'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $targetStats['all_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Tenants Only</span>
                            <span class="text-muted small">{{ $targetStats['tenants'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $targetStats['tenants_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Users Only</span>
                            <span class="text-muted small">{{ $targetStats['users'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $targetStats['users_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Specific Users</span>
                            <span class="text-muted small">{{ $targetStats['specific'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $targetStats['specific_percentage'] ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($recentActivity as $activity)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $activity['type'] === 'created' ? 'primary' : ($activity['type'] === 'delivered' ? 'success' : 'info') }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $activity['title'] }}</h6>
                                <p class="timeline-text">{{ $activity['description'] }}</p>
                                <small class="text-muted">{{ $activity['time'] }}</small>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted text-center">No recent activity</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top Performing Notifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing</h6>
                </div>
                <div class="card-body">
                    @forelse($topPerforming as $notification)
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="icon-circle bg-{{ $notification->type === 'error' ? 'danger' : ($notification->type === 'warning' ? 'warning' : ($notification->type === 'success' ? 'success' : 'info')) }}">
                                <i class="fas {{ $notification->type === 'error' ? 'fa-times' : ($notification->type === 'warning' ? 'fa-exclamation' : ($notification->type === 'success' ? 'fa-check' : 'fa-info')) }} text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="small font-weight-bold">{{ Str::limit($notification->title, 30) }}</div>
                            <div class="small text-muted">{{ $notification->read_rate }}% read rate</div>
                        </div>
                        <div class="text-end">
                            <div class="small font-weight-bold text-success">{{ $notification->recipients_count }}</div>
                            <div class="small text-muted">recipients</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No data available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart configurations
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top'
        }
    }
};

// Notifications Over Time Chart
const notificationsCtx = document.getElementById('notificationsChart').getContext('2d');
const notificationsChart = new Chart(notificationsCtx, {
    type: 'line',
    data: {
        labels: @json($timelineData['labels'] ?? []),
        datasets: [{
            label: 'Notifications Created',
            data: @json($timelineData['created'] ?? []),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3
        }, {
            label: 'Notifications Delivered',
            data: @json($timelineData['delivered'] ?? []),
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.3
        }]
    },
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Notification Types Chart
const typesCtx = document.getElementById('typesChart').getContext('2d');
const typesChart = new Chart(typesCtx, {
    type: 'doughnut',
    data: {
        labels: @json($typeDistribution['labels'] ?? []),
        datasets: [{
            data: @json($typeDistribution['data'] ?? []),
            backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b']
        }]
    },
    options: chartOptions
});

// Priority Distribution Chart
const priorityCtx = document.getElementById('priorityChart').getContext('2d');
const priorityChart = new Chart(priorityCtx, {
    type: 'pie',
    data: {
        labels: @json($priorityDistribution['labels'] ?? []),
        datasets: [{
            data: @json($priorityDistribution['data'] ?? []),
            backgroundColor: ['#6c757d', '#17a2b8', '#f6c23e', '#e74a3b']
        }]
    },
    options: chartOptions
});

// Delivery Performance Chart
const deliveryCtx = document.getElementById('deliveryChart').getContext('2d');
const deliveryChart = new Chart(deliveryCtx, {
    type: 'bar',
    data: {
        labels: @json($deliveryData['labels'] ?? []),
        datasets: [{
            label: 'Delivered',
            data: @json($deliveryData['delivered'] ?? []),
            backgroundColor: '#1cc88a'
        }, {
            label: 'Read',
            data: @json($deliveryData['read'] ?? []),
            backgroundColor: '#36b9cc'
        }, {
            label: 'Pending',
            data: @json($deliveryData['pending'] ?? []),
            backgroundColor: '#f6c23e'
        }]
    },
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Refresh all data
function refreshAllData() {
    fetch('/admin/system-notifications/statistics/data')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateOverviewCards(data.overview);
                updateCharts(data);
            }
        })
        .catch(error => console.error('Error refreshing data:', error));
}

function updateOverviewCards(overview) {
    document.getElementById('total-notifications').textContent = overview.total_notifications || 0;
    document.getElementById('active-notifications').textContent = overview.active_notifications || 0;
    document.getElementById('total-recipients').textContent = overview.total_recipients || 0;
    document.getElementById('avg-read-rate').textContent = (overview.avg_read_rate || 0) + '%';
}

function updateCharts(data) {
    // Update timeline chart
    notificationsChart.data.labels = data.timelineData.labels;
    notificationsChart.data.datasets[0].data = data.timelineData.created;
    notificationsChart.data.datasets[1].data = data.timelineData.delivered;
    notificationsChart.update();

    // Update types chart
    typesChart.data.labels = data.typeDistribution.labels;
    typesChart.data.datasets[0].data = data.typeDistribution.data;
    typesChart.update();

    // Update priority chart
    priorityChart.data.labels = data.priorityDistribution.labels;
    priorityChart.data.datasets[0].data = data.priorityDistribution.data;
    priorityChart.update();

    // Update delivery chart
    deliveryChart.data.labels = data.deliveryData.labels;
    deliveryChart.data.datasets[0].data = data.deliveryData.delivered;
    deliveryChart.data.datasets[1].data = data.deliveryData.read;
    deliveryChart.data.datasets[2].data = data.deliveryData.pending;
    deliveryChart.update();
}

// Auto-refresh every 60 seconds
setInterval(refreshAllData, 60000);
</script>
@endpush

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -31px;
    top: 15px;
    width: 2px;
    height: calc(100% + 5px);
    background-color: #e3e6f0;
}

.timeline-title {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0;
}

.icon-circle {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush