@extends('layouts.app')

@section('title', 'Preventive Maintenance')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Page Header -->
    <div class="page-header-modern animate-fadeInUp">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-modern">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}" class="text-white-50">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="fas fa-tools me-2"></i>
                    Preventive Maintenance
                </li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-2 text-white">
                    <i class="fas fa-tools me-3"></i>
                    Preventive Maintenance
                </h1>
                <p class="lead text-white-50 mb-0">Generator maintenance scheduling and recommendations based on runtime data</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex justify-content-end gap-3 align-items-center">
                    <div class="text-center">
                        <div class="h3 mb-0 text-white fw-bold">{{ $maintenanceSummary['total_generators'] }}</div>
                        <small class="text-white-50">Total Generators</small>
                    </div>
                    <div class="vr text-white-50"></div>
                    <div class="text-center">
                        <div class="h3 mb-0 text-white fw-bold">{{ $maintenanceSummary['overdue_maintenance'] }}</div>
                        <small class="text-white-50">Overdue</small>
                    </div>
                    <div class="vr text-white-50"></div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light btn-sm" onclick="refreshMaintenanceData()" id="refreshBtn">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.1s;">
                <div class="card-body text-center d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--danger-gradient);">
                            <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold">{{ $maintenanceSummary['critical_alerts'] }}</h3>
                            <small class="text-white-50">Critical Alerts</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Immediate Action Required</h6>
                    <div class="mt-auto">
                        <span class="badge badge-danger-modern badge-modern">URGENT</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.2s;">
                <div class="card-body text-center d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--warning-gradient);">
                            <i class="fas fa-clock fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold">{{ $maintenanceSummary['high_priority'] }}</h3>
                            <small class="text-white-50">High Priority</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Schedule Soon</h6>
                    <div class="mt-auto">
                        <span class="badge badge-warning-modern badge-modern">HIGH</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.3s;">
                <div class="card-body text-center d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--info-gradient);">
                            <i class="fas fa-calendar-check fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold">{{ $maintenanceSummary['due_soon'] }}</h3>
                            <small class="text-white-50">Due Soon</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Next 7 Days</h6>
                    <div class="mt-auto">
                        <span class="badge badge-info-modern badge-modern">PLANNED</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.4s;">
                <div class="card-body text-center d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--success-gradient);">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold">{{ $maintenanceSummary['medium_priority'] }}</h3>
                            <small class="text-white-50">Medium Priority</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Routine Maintenance</h6>
                    <div class="mt-auto">
                        <span class="badge badge-success-modern badge-modern">ROUTINE</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generator Maintenance Cards -->
    <div class="row">
        @foreach($maintenanceData as $data)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-white">
                            <i class="fas fa-microchip me-2"></i>
                            {{ $data['generator']->name ?: 'Generator ' . $data['generator']->generator_id }}
                        </h6>
                        <div class="d-flex gap-2">
                            @if($data['current_runtime'])
                                <span class="badge badge-success-modern badge-modern">Running</span>
                            @else
                                <span class="badge badge-secondary-modern badge-modern">Stopped</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Generator Info -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-white-50">Generator ID:</small>
                            <span class="text-white">{{ $data['generator']->generator_id }}</span>
                        </div>
                        @if($data['generator']->sitename)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-white-50">Site:</small>
                            <span class="text-white">{{ $data['generator']->sitename }}</span>
                        </div>
                        @endif
                        @if($data['generator']->kva_power)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-white-50">Power:</small>
                            <span class="text-white">{{ $data['generator']->kva_power }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Runtime Statistics -->
                    <div class="mb-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-history me-2"></i>
                            Past 30 Days Runtime History
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Total Sessions:</small>
                            <span class="text-white">{{ $data['runtime_stats']['total_sessions'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Total Runtime:</small>
                            <span class="text-white">{{ $data['runtime_stats']['total_duration_formatted'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Average Session:</small>
                            <span class="text-white">{{ $data['runtime_stats']['average_duration_formatted'] }}</span>
                        </div>
                        <div class="mt-2">
                            <small class="text-warning">
                                <i class="fas fa-info-circle me-1"></i>
                                This shows historical data, not current status
                            </small>
                        </div>
                    </div>

                    <!-- Current Runtime -->
                    @if($data['current_runtime'])
                    <div class="mb-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-play-circle me-2 text-success"></i>
                            Currently Running
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Started:</small>
                            <span class="text-white">{{ $data['current_runtime']->start_time->format('M d, H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Duration:</small>
                            <span class="text-success fw-bold">{{ $data['current_runtime']->start_time->diffForHumans(null, true) }}</span>
                        </div>
                    </div>
                    @else
                    <div class="mb-3">
                        <h6 class="text-white mb-2">
                            <i class="fas fa-stop-circle me-2 text-danger"></i>
                            Currently Stopped
                        </h6>
                        <div class="text-center py-2">
                            <span class="text-white-50">
                                <i class="fas fa-power-off me-1"></i>
                                Generator is not running
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Maintenance Schedule -->
                    <div class="mb-3">
                        <h6 class="text-white mb-2">Maintenance Schedule</h6>
                        @if($data['last_maintenance'])
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Last Maintenance:</small>
                            <span class="text-white">{{ $data['last_maintenance']->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($data['next_maintenance'])
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-white-50">Next Maintenance:</small>
                            <span class="text-white {{ $data['next_maintenance']->isPast() ? 'text-danger' : ($data['next_maintenance']->diffInDays(now()) <= 7 ? 'text-warning' : '') }}">
                                {{ $data['next_maintenance']->format('M d, Y') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Recommendations -->
                    @if(count($data['recommendations']) > 0)
                    <div class="mb-3">
                        <h6 class="text-white mb-2">Recommendations</h6>
                        @foreach($data['recommendations'] as $recommendation)
                        <div class="alert alert-{{ $recommendation['priority'] === 'critical' ? 'danger' : ($recommendation['priority'] === 'high' ? 'warning' : 'info') }} alert-sm mb-2">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-{{ $recommendation['priority'] === 'critical' ? 'exclamation-triangle' : ($recommendation['priority'] === 'high' ? 'clock' : 'info-circle') }} me-2 mt-1"></i>
                                <div>
                                    <small class="fw-bold">{{ $recommendation['message'] }}</small>
                                    <br>
                                    <small class="text-muted">{{ $recommendation['action'] }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-white-50 mb-0">No maintenance required</p>
                    </div>
                    @endif
                </div>
                <div class="card-footer border-0 bg-transparent">
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary flex-fill" onclick="viewGeneratorDetails('{{ $data['generator']->generator_id }}')">
                            <i class="fas fa-eye me-1"></i>View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('styles')
<style>
.alert-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.alert-sm .fw-bold {
    font-size: 0.8rem;
}

.alert-sm small {
    font-size: 0.75rem;
}
</style>
@endsection

@section('scripts')
<script>
function refreshMaintenanceData() {
    // Show loading state
    const refreshBtn = $('#refreshBtn');
    const originalText = refreshBtn.html();
    refreshBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...');
    refreshBtn.prop('disabled', true);

    // Reload the page
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function viewGeneratorDetails(generatorId) {
    // This would open a modal or navigate to detailed view
    showNotification('Viewing details for generator ' + generatorId, 'info');
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
    // Only refresh if user is on this page
    if (window.location.pathname.includes('preventive-maintenance')) {
        refreshMaintenanceData();
    }
}, 300000); // 5 minutes
</script>
@endsection

