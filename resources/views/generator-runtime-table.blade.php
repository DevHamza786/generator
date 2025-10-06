@extends('layouts.app')

@section('title', 'Generator Runtime Table')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-white">
                    <i class="fas fa-table me-2"></i>Generator Runtime Table
                </h2>
                <div class="text-white-50">
                    <small>Last Updated: {{ now()->format('M d, Y H:i:s') }}</small>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ count($runtimeData) }}</h4>
                                    <small>Total Generators</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-cogs fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ collect($runtimeData)->where('current_runtime', '!=', null)->count() }}</h4>
                                    <small>Currently Running</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-play-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ collect($runtimeData)->sum('statistics.total_sessions') }}</h4>
                                    <small>Total Sessions (30d)</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-history fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ collect($runtimeData)->sum('statistics.total_duration_seconds') / 3600 | number_format(1) }}h</h4>
                                    <small>Total Runtime (30d)</small>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generator Runtime Table -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>Generator Runtime Data
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Generator</th>
                                    <th>Current Status</th>
                                    <th>Runtime (30d)</th>
                                    <th>Sessions (30d)</th>
                                    <th>Avg Duration</th>
                                    <th>Last Runtime</th>
                                    <th>Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($runtimeData as $data)
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-white">{{ $data['generator']->name }}</strong><br>
                                            <small class="text-white-50">{{ $data['generator']->generator_id }}</small><br>
                                            <small class="text-white-50">{{ $data['generator']->sitename }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($data['current_runtime'])
                                            <span class="badge bg-success">
                                                <i class="fas fa-play me-1"></i>RUNNING
                                            </span><br>
                                            <small class="text-white-50">
                                                Since: {{ $data['current_runtime']->start_time->format('M d, H:i') }}<br>
                                                Duration: {{ $data['current_runtime']->formatted_duration }}
                                            </small>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-stop me-1"></i>STOPPED
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-white">{{ $data['statistics']['total_duration_formatted'] }}</span>
                                    </td>
                                    <td>
                                        <span class="text-white">{{ $data['statistics']['total_sessions'] }}</span>
                                    </td>
                                    <td>
                                        <span class="text-white">{{ $data['statistics']['average_duration_formatted'] }}</span>
                                    </td>
                                    <td>
                                        @if($data['recent_runtimes']->count() > 0)
                                            @php $lastRuntime = $data['recent_runtimes']->first(); @endphp
                                            <small class="text-white-50">
                                                {{ $lastRuntime->start_time->format('M d, H:i') }}<br>
                                                Duration: {{ $lastRuntime->formatted_duration }}
                                            </small>
                                        @else
                                            <span class="text-white-50">No data</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($data['current_runtime'])
                                            @php
                                                $status = $data['current_runtime']->maintenance_status;
                                                $badgeClass = match($status) {
                                                    'none' => 'bg-secondary',
                                                    'scheduled' => 'bg-info',
                                                    'overdue' => 'bg-danger',
                                                    'in_progress' => 'bg-warning',
                                                    'completed' => 'bg-success',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="viewRuntimeDetails('{{ $data['generator']->generator_id }}')"
                                                title="View Runtime Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Runtime Sessions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Recent Runtime Sessions (All Generators)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Generator</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Maintenance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $allRecentRuntimes = collect($runtimeData)
                                                ->flatMap(function($data) {
                                                    return $data['recent_runtimes']->map(function($runtime) use ($data) {
                                                        return [
                                                            'generator' => $data['generator'],
                                                            'runtime' => $runtime
                                                        ];
                                                    });
                                                })
                                                ->sortByDesc('runtime.start_time')
                                                ->take(20);
                                        @endphp

                                        @foreach($allRecentRuntimes as $item)
                                        <tr>
                                            <td>
                                                <strong class="text-white">{{ $item['generator']->name }}</strong><br>
                                                <small class="text-white-50">{{ $item['generator']->generator_id }}</small>
                                            </td>
                                            <td>
                                                <span class="text-white">{{ $item['runtime']->start_time->format('M d, H:i:s') }}</span>
                                            </td>
                                            <td>
                                                <span class="text-white">
                                                    {{ $item['runtime']->end_time ? $item['runtime']->end_time->format('M d, H:i:s') : 'Still running' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-white">{{ $item['runtime']->formatted_duration }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $item['runtime']->status === 'running' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ ucfirst($item['runtime']->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $status = $item['runtime']->maintenance_status;
                                                    $badgeClass = match($status) {
                                                        'none' => 'bg-secondary',
                                                        'scheduled' => 'bg-info',
                                                        'overdue' => 'bg-danger',
                                                        'in_progress' => 'bg-warning',
                                                        'completed' => 'bg-success',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
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
    </div>
</div>

<!-- Runtime Details Modal -->
<div class="modal fade" id="runtimeDetailsModal" tabindex="-1" aria-labelledby="runtimeDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid rgba(255,255,255,0.1);">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="runtimeDetailsModalLabel">
                    <i class="fas fa-chart-line me-2"></i>Runtime Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="runtimeDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function viewRuntimeDetails(generatorId) {
    // Show loading state
    showNotification('Loading runtime details...', 'info');

    // Fetch runtime details
    $.ajax({
        url: `/dashboard/runtime-details/${generatorId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                showRuntimeDetailsModal(response.data);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            showNotification(response?.message || 'Error loading runtime details', 'error');
        }
    });
}

// Runtime Details Modal Functions
function showRuntimeDetailsModal(data) {
    const generator = data.generator;
    const currentRuntime = data.current_runtime;
    const runtimeHistory = data.runtime_history;
    const statistics = data.statistics;
    const recentLogs = data.recent_logs;

    let content = `
        <div class="runtime-details-container">
            <!-- Generator Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="card-body">
                            <h5 class="text-white mb-3">
                                <i class="fas fa-cog me-2"></i>${generator.name}
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-white-50 mb-1"><strong>Generator ID:</strong> ${generator.id}</p>
                                    <p class="text-white-50 mb-1"><strong>Site:</strong> ${generator.sitename}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-white-50 mb-1"><strong>Power:</strong> ${generator.kva_power}</p>
                                    <p class="text-white-50 mb-1"><strong>Client:</strong> ${generator.client_name}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="card-body">
                            <h6 class="text-white mb-3">
                                <i class="fas fa-play-circle me-2"></i>Current Status
                            </h6>
    `;

    if (currentRuntime) {
        content += `
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-1"><strong>Status:</strong> <span class="text-success">RUNNING</span></p>
                    <p class="text-white-50 mb-1"><strong>Started:</strong> ${new Date(currentRuntime.start_time).toLocaleString()}</p>
                    <p class="text-white-50 mb-1"><strong>Duration:</strong> <span class="text-success">${currentRuntime.duration}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="text-white-50 mb-1"><strong>Maintenance:</strong> ${currentRuntime.maintenance_status}</p>
                    ${currentRuntime.maintenance_started_at ? `<p class="text-white-50 mb-1"><strong>Maintenance Started:</strong> ${new Date(currentRuntime.maintenance_started_at).toLocaleString()}</p>` : ''}
                    ${currentRuntime.maintenance_completed_at ? `<p class="text-white-50 mb-1"><strong>Maintenance Completed:</strong> ${new Date(currentRuntime.maintenance_completed_at).toLocaleString()}</p>` : ''}
                    <p class="text-white-50 mb-1"><strong>Start Voltages:</strong> LV1: ${currentRuntime.start_voltages.LV1}V, LV2: ${currentRuntime.start_voltages.LV2}V, LV3: ${currentRuntime.start_voltages.LV3}V</p>
                </div>
            </div>
        `;
    } else {
        content += `
            <div class="text-center py-3">
                <i class="fas fa-stop-circle text-danger fa-2x mb-2"></i>
                <p class="text-white-50">Generator is currently stopped</p>
            </div>
        `;
    }

    content += `
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="card-body">
                            <h6 class="text-white mb-3">
                                <i class="fas fa-chart-bar me-2"></i>Runtime Statistics (Last 30 Days)
                            </h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary">${statistics.total_sessions}</h4>
                                        <small class="text-white-50">Total Sessions</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success">${statistics.total_duration_formatted}</h4>
                                        <small class="text-white-50">Total Runtime</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info">${statistics.average_duration_formatted}</h4>
                                        <small class="text-white-50">Average Duration</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning">${runtimeHistory.length}</h4>
                                        <small class="text-white-50">Recent Sessions</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Runtime History -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="card-body">
                            <h6 class="text-white mb-3">
                                <i class="fas fa-history me-2"></i>Recent Runtime History
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm">
                                    <thead>
                                        <tr>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Maintenance</th>
                                            <th>Maintenance Times</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    `;

    runtimeHistory.slice(0, 10).forEach(runtime => {
        const startTime = new Date(runtime.start_time).toLocaleString();
        const endTime = runtime.end_time ? new Date(runtime.end_time).toLocaleString() : 'Still running';
        const statusBadge = runtime.status === 'running' ? 'badge-success' : 'badge-secondary';

        let maintenanceTimes = '';
        if (runtime.maintenance_started_at) {
            maintenanceTimes += `Started: ${new Date(runtime.maintenance_started_at).toLocaleString()}<br>`;
        }
        if (runtime.maintenance_completed_at) {
            maintenanceTimes += `Completed: ${new Date(runtime.maintenance_completed_at).toLocaleString()}`;
        }
        if (!maintenanceTimes) {
            maintenanceTimes = '-';
        }

        content += `
            <tr>
                <td>${startTime}</td>
                <td>${endTime}</td>
                <td>${runtime.duration}</td>
                <td><span class="badge ${statusBadge}">${runtime.status}</span></td>
                <td>${runtime.maintenance_status}</td>
                <td><small>${maintenanceTimes}</small></td>
            </tr>
        `;
    });

    content += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Voltage Logs -->
            <div class="row">
                <div class="col-12">
                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="card-body">
                            <h6 class="text-white mb-3">
                                <i class="fas fa-bolt me-2"></i>Recent Voltage Readings
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>LV1 (V)</th>
                                            <th>LV2 (V)</th>
                                            <th>LV3 (V)</th>
                                            <th>Frequency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    `;

    recentLogs.slice(0, 10).forEach(log => {
        const timestamp = new Date(log.timestamp).toLocaleString();
        const voltageClass = (log.voltages.LV1 > 0 && log.voltages.LV2 > 0 && log.voltages.LV3 > 0) ? 'text-success' : 'text-danger';

        content += `
            <tr>
                <td>${timestamp}</td>
                <td class="${voltageClass}">${log.voltages.LV1}</td>
                <td class="${voltageClass}">${log.voltages.LV2}</td>
                <td class="${voltageClass}">${log.voltages.LV3}</td>
                <td>${log.frequency} Hz</td>
            </tr>
        `;
    });

    content += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#runtimeDetailsContent').html(content);
    $('#runtimeDetailsModal').modal('show');
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle')} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);

    $('body').append(notification);

    // Auto remove after 5 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (window.location.pathname.includes('generator-runtime-table')) {
        window.location.reload();
    }
}, 300000); // 5 minutes
</script>
@endsection
