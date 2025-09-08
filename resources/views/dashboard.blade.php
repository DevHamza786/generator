@extends('layouts.app')

@section('title', 'Enterprise Dashboard')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Page Header -->
    <div class="page-header-modern animate-fadeInUp">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-modern">
                <li class="breadcrumb-item active">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Enterprise Dashboard
                </li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-2 text-white">
                    <i class="fas fa-bolt me-3"></i>
                    GeneratorPro Enterprise
                </h1>
                <p class="lead text-white-50 mb-0">Real-time monitoring and analytics for industrial power systems</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex justify-content-end gap-3 align-items-center">
                    <div class="text-center">
                        <div class="h3 mb-0 text-white fw-bold" id="currentTime">{{ now()->format('H:i') }}</div>
                        <small class="text-white-50">{{ now()->format('M d, Y') }}</small>
                    </div>
                    <div class="vr text-white-50"></div>
                    <div class="text-center">
                        <div class="h3 mb-0 text-white fw-bold" id="uptime">99.9%</div>
                        <small class="text-white-50">System Uptime</small>
                    </div>
                    <div class="vr text-white-50"></div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm" onclick="refreshData()" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
        <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.1s;">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--success-gradient);">
                            <i class="fas fa-power-off fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold" id="totalClients">{{ $totalClients }}</h3>
                            <small class="text-white-50">Total Clients</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Client Management</h6>
                    <div class="mt-2">
                        <span class="badge badge-success-modern badge-modern">ACTIVE CLIENTS</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.2s;">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--info-gradient);">
                            <i class="fas fa-battery-half fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold" id="totalGenerators">{{ $totalGenerators }}</h3>
                            <small class="text-white-50">Total Generators</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Generator Fleet</h6>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" style="width: {{ $totalGenerators > 0 ? ($runningGenerators / $totalGenerators) * 100 : 0 }}%; background: var(--info-gradient);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--secondary-gradient);">
                            <i class="fas fa-bolt fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold" id="runningGenerators">{{ $runningGenerators }}</h3>
                            <small class="text-white-50">Running</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Active Generators</h6>
                    <div class="mt-2">
                        <span class="badge badge-success-modern badge-modern">OPERATIONAL</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.4s;">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--primary-gradient);">
                            <i class="fas fa-chart-line fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold" id="totalLogs">{{ $totalLogs + $totalWriteLogs }}</h3>
                            <small class="text-white-50">Total Logs</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Data Points</h6>
                    <div class="mt-2">
                        <small class="text-white-50">Last 24h</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Status Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="status-card {{ $generatorStatus && $generatorStatus->power ? 'status-online' : 'status-offline' }} animate-fadeInUp" style="animation-delay: 0.5s;" id="statusCard">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-4">
                                    <i class="fas fa-power-off fa-3x text-white"></i>
                                </div>
                                <div>
                                    <h2 class="mb-1 text-white fw-bold" id="statusText">
                                        {{ $generatorStatus && $generatorStatus->power ? 'OPERATIONAL' : 'OFFLINE' }}
                                    </h2>
                                    <p class="text-white-50 mb-0">
                                        Generator ID: <span class="fw-bold">{{ $generatorStatus ? $generatorStatus->generator_id : 'N/A' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h4 mb-1 text-white fw-bold" id="fuelLevel">{{ $latestLogs->first()->FL ?? 0 }}%</div>
                                        <small class="text-white-50">Fuel Level</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h4 mb-1 text-white fw-bold" id="batteryVoltage">{{ $latestLogs->first()->BV ?? 0 }}V</div>
                                        <small class="text-white-50">Battery Voltage</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="h4 mb-1 text-white fw-bold" id="lineVoltage">{{ $latestLogs->first()->LV1 ?? 0 }}V</div>
                                        <small class="text-white-50">Line Voltage</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="text-center">
                                <div class="mb-3">
                                    <i class="fas fa-clock fa-2x text-white-50"></i>
                                </div>
                                <div class="h5 mb-1 text-white fw-bold" id="lastUpdated">
                                    {{ $generatorStatus ? $generatorStatus->last_updated->format('H:i:s') : 'N/A' }}
                                </div>
                                <small class="text-white-50">Last Updated</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Data Tables -->
    <div class="row">
        <!-- Real-time Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.6s;">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-chart-area me-2"></i>
                            Real-time Performance
                        </h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-light active" data-period="1h">1H</button>
                            <button type="button" class="btn btn-sm btn-outline-light" data-period="6h">6H</button>
                            <button type="button" class="btn btn-sm btn-outline-light" data-period="24h">24H</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-lg-4 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.7s;">
                <div class="card-header border-0 bg-transparent">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-fire fa-2x text-warning mb-2"></i>
                                <div class="h5 mb-0 text-white">{{ $latestLogs->where('GS', true)->count() }}</div>
                                <small class="text-white-50">Running</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-pause fa-2x text-secondary mb-2"></i>
                                <div class="h5 mb-0 text-white">{{ $latestLogs->where('GS', false)->count() }}</div>
                                <small class="text-white-50">Stopped</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-bolt fa-2x text-info mb-2"></i>
                                <div class="h5 mb-0 text-white">{{ $latestLogs->avg('LI1') ?? 0 }}A</div>
                                <small class="text-white-50">Avg Current</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-wave-square fa-2x text-success mb-2"></i>
                                <div class="h5 mb-0 text-white">{{ $latestLogs->avg('Lf1') ?? 0 }}Hz</div>
                                <small class="text-white-50">Frequency</small>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="row">
        <!-- Latest Logs -->
        <div class="col-lg-6 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.8s;">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-list me-2"></i>
                            Latest Log Data
                        </h5>
                        <a href="{{ route('logs') }}" class="btn btn-sm btn-modern">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0" id="logsTable">
                                <thead>
                                    <tr>
                                    <th class="text-white">Time</th>
                                    <th class="text-white">ID</th>
                                    <th class="text-white">FL</th>
                                    <th class="text-white">BV</th>
                                    <th class="text-white">LV1</th>
                                    <th class="text-white">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($latestLogs->take(10) as $log)
                                <tr>
                                    <td class="text-white-50">{{ $log->log_timestamp->format('H:i:s') }}</td>
                                    <td>
                                        <span class="badge badge-info-modern badge-modern">{{ $log->generator_id }}</span>
                                    </td>
                                    <td class="text-white">{{ $log->FL }}%</td>
                                    <td class="text-white">{{ $log->BV }}V</td>
                                    <td class="text-white">{{ $log->LV1 }}V</td>
                                        <td>
                                            @if($log->GS)
                                            <span class="badge badge-success-modern badge-modern">Running</span>
                                            @else
                                            <span class="badge badge-danger-modern badge-modern">Stopped</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Write Logs -->
        <div class="col-lg-6 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.9s;">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-database me-2"></i>
                            Write Log Data
                        </h5>
                        <a href="{{ route('write-logs') }}" class="btn btn-sm btn-modern">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0" id="writeLogsTable">
                                <thead>
                                    <tr>
                                    <th class="text-white">Time</th>
                                    <th class="text-white">ID</th>
                                    <th class="text-white">FL</th>
                                    <th class="text-white">BV</th>
                                    <th class="text-white">LV1</th>
                                    <th class="text-white">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($latestWriteLogs->take(10) as $writeLog)
                                <tr>
                                    <td class="text-white-50">{{ $writeLog->write_timestamp->format('H:i:s') }}</td>
                                    <td>
                                        <span class="badge badge-info-modern badge-modern">{{ $writeLog->generator_id }}</span>
                                    </td>
                                    <td class="text-white">{{ $writeLog->FL }}%</td>
                                    <td class="text-white">{{ $writeLog->BV }}V</td>
                                    <td class="text-white">{{ $writeLog->LV1 }}V</td>
                                        <td>
                                            @if($writeLog->PS)
                                            <span class="badge badge-success-modern badge-modern">Active</span>
                                            @else
                                            <span class="badge badge-danger-modern badge-modern">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
    // Chart.js configuration
    let performanceChart;

    function initChart() {
        try {
            const ctx = document.getElementById('performanceChart');
            if (!ctx) {
                console.error('Chart canvas element not found');
                return;
            }

            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded');
                return;
            }

            performanceChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Fuel Level (%)',
                    data: [],
                    borderColor: '#34B1AA',
                    backgroundColor: 'rgba(52, 177, 170, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Battery Voltage (V)',
                    data: [],
                    borderColor: '#F29F67',
                    backgroundColor: 'rgba(242, 159, 103, 0.2)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Line Voltage (V)',
                    data: [],
                    borderColor: '#3B8FF3',
                    backgroundColor: 'rgba(59, 143, 243, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    y: {
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        } catch (error) {
            console.error('Error initializing chart:', error);
        }
    }

    function updateChart(data) {
        if (!performanceChart) return;

        const now = new Date();
        const timeLabel = now.toLocaleTimeString();

        // Add new data point
        performanceChart.data.labels.push(timeLabel);
        performanceChart.data.datasets[0].data.push(data.FL || 0);
        performanceChart.data.datasets[1].data.push(data.BV || 0);
        performanceChart.data.datasets[2].data.push(data.LV1 || 0);

        // Keep only last 20 data points
        if (performanceChart.data.labels.length > 20) {
            performanceChart.data.labels.shift();
            performanceChart.data.datasets.forEach(dataset => {
                dataset.data.shift();
            });
        }

        performanceChart.update('none');
    }

        function refreshData() {
            // Show loading state
            const refreshBtn = $('#refreshBtn');
            const originalText = refreshBtn.html();
            refreshBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...');
            refreshBtn.prop('disabled', true);

            // Update current time
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit'
            });

            // Refresh status
            $.get('/api/generator/status', function(response) {
                if (response.success && response.data) {
                    const status = response.data;
                    const statusCard = $('#statusCard');
                    const statusText = $('#statusText');
                    const lastUpdated = $('#lastUpdated');
                    const onlineGenerators = $('#onlineGenerators');

                    if (status.power) {
                        statusCard.removeClass('status-offline').addClass('status-online');
                        statusText.text('OPERATIONAL');
                        onlineGenerators.text('1');
                    } else {
                        statusCard.removeClass('status-online').addClass('status-offline');
                        statusText.text('OFFLINE');
                        onlineGenerators.text('0');
                    }

                    lastUpdated.text(new Date(status.last_updated).toLocaleTimeString());
                }
            }).always(function() {
                // Reset button state
                refreshBtn.html(originalText);
                refreshBtn.prop('disabled', false);
            });

            // Refresh logs
            $.get('/api/generator/logs', function(response) {
                if (response.success && response.data) {
                    const tbody = $('#logsTable tbody');
                    tbody.empty();

                    response.data.slice(0, 10).forEach(function(log) {
                        const row = `
                            <tr>
                                <td class="text-white-50">${new Date(log.log_timestamp).toLocaleTimeString()}</td>
                                <td><span class="badge badge-info-modern badge-modern">${log.generator_id}</span></td>
                                <td class="text-white">${log.FL}%</td>
                                <td class="text-white">${log.BV}V</td>
                                <td class="text-white">${log.LV1}V</td>
                                <td>
                                    <span class="badge ${log.GS ? 'badge-success-modern' : 'badge-danger-modern'} badge-modern">
                                        ${log.GS ? 'Running' : 'Stopped'}
                                    </span>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });

                    // Update chart with latest data
                    if (response.data.length > 0) {
                        updateChart(response.data[0]);
                    }
                }
            });

            // Refresh write logs
            $.get('/api/generator/write-logs', function(response) {
                if (response.success && response.data) {
                    const tbody = $('#writeLogsTable tbody');
                    tbody.empty();

                    response.data.slice(0, 10).forEach(function(writeLog) {
                        const row = `
                            <tr>
                                <td class="text-white-50">${new Date(writeLog.write_timestamp).toLocaleTimeString()}</td>
                                <td><span class="badge badge-info-modern badge-modern">${writeLog.generator_id}</span></td>
                                <td class="text-white">${writeLog.FL}%</td>
                                <td class="text-white">${writeLog.BV}V</td>
                                <td class="text-white">${writeLog.LV1}V</td>
                                <td>
                                    <span class="badge ${writeLog.PS ? 'badge-success-modern' : 'badge-danger-modern'} badge-modern">
                                        ${writeLog.PS ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
            });
        }

    // Initialize everything when document is ready
    $(document).ready(function() {
        // Wait for Chart.js to be available
        function waitForChart() {
            if (typeof Chart !== 'undefined') {
                console.log('Chart.js is available, initializing chart...');
                initChart();
                // Initial data load
                refreshData();
            } else {
                console.log('Waiting for Chart.js...');
                setTimeout(waitForChart, 100);
            }
        }

        waitForChart();

        // Update time every second (only time, no data refresh)
        setInterval(function() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit'
            });
        }, 1000);
        });
    </script>
@endsection
