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
                            <span class="text-success fw-bold">{{ $data['current_runtime']->formatted_duration }}</span>
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

                        <!-- Current Maintenance Status with Timestamps -->
                        @if($data['current_runtime'] && $data['current_runtime']->maintenance_status !== 'none')
                        <div class="mt-2 pt-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <small class="text-white-50">Current Maintenance:</small>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-white-50">Status:</small>
                                <span class="text-white">{{ ucfirst(str_replace('_', ' ', $data['current_runtime']->maintenance_status)) }}</span>
                            </div>
                            @if($data['current_runtime']->maintenance_started_at)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-white-50">Started:</small>
                                <span class="text-white">{{ $data['current_runtime']->maintenance_started_at->format('M d, H:i') }}</span>
                            </div>
                            @endif
                            @if($data['current_runtime']->maintenance_completed_at)
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-white-50">Completed:</small>
                                <span class="text-white">{{ $data['current_runtime']->maintenance_completed_at->format('M d, H:i') }}</span>
                            </div>
                            @endif
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

                    <!-- Maintenance Status Section -->
                    @php
                        $maintenanceStatus = $maintenanceStatuses[$data['generator']->generator_id] ?? 'none';
                    @endphp
                    <div class="maintenance-status-section mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="maintenance-status-info">
                                <i class="{{ $maintenanceStatus === 'none' ? 'fas fa-check-circle text-success' : ($maintenanceStatus === 'completed' ? 'fas fa-check-circle text-success' : 'fas fa-tools text-warning') }} me-1"></i>
                                <small class="text-white-50" id="maintenance-text-{{ $data['generator']->generator_id }}">
                                    {{ $maintenanceStatus === 'none' ? 'No maintenance required' : ucfirst(str_replace('_', ' ', $maintenanceStatus)) }}
                                </small>
                            </div>
                            <button class="btn btn-sm btn-outline-light maintenance-btn"
                                    data-generator-id="{{ $data['generator']->generator_id }}"
                                    data-current-status="{{ $maintenanceStatus }}"
                                    onclick="showMaintenanceModal('{{ $data['generator']->generator_id }}', '{{ $maintenanceStatus }}')"
                                    title="Update Maintenance Status">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                        </div>
                    </div>
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

<!-- Maintenance Status Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: 1px solid rgba(255,255,255,0.1);">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="maintenanceModalLabel">
                    <i class="fas fa-tools me-2"></i>Maintenance Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Current Status Display -->
                <div class="maintenance-status-display text-center mb-4">
                    <div id="currentStatusIcon" class="mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h6 class="text-white mb-2" id="currentStatusText">No maintenance required</h6>
                    <small class="text-white-50" id="currentGeneratorId">Generator ID: </small>
                </div>

                <!-- Maintenance Actions -->
                <div class="maintenance-actions">
                    <h6 class="text-white mb-3">Update Maintenance Status:</h6>

                    <!-- No Maintenance Required -->
                    <button class="btn btn-outline-light maintenance-action-btn w-100 mb-2 text-start"
                            data-status="none" onclick="updateMaintenanceStatus('none')">
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        No Maintenance Required
                    </button>

                    <!-- Maintenance Completed -->
                    <button class="btn btn-outline-light maintenance-action-btn w-100 mb-2 text-start"
                            data-status="completed" onclick="updateMaintenanceStatus('completed')">
                        <i class="fas fa-check-circle me-2 text-success"></i>
                        Maintenance Completed
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

/* Maintenance Status Styles */
.maintenance-status-section {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 8px;
    margin-top: 8px;
}

.maintenance-status-info {
    display: flex;
    align-items: center;
}

.maintenance-btn {
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.maintenance-btn:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.3);
    transform: translateY(-1px);
}

.maintenance-action-btn {
    text-align: left;
    transition: all 0.3s ease;
}

.maintenance-action-btn:hover {
    transform: translateX(5px);
}

.maintenance-action-btn.active {
    background: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.maintenance-status-display {
    padding: 20px;
    border-radius: 10px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
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

// Maintenance Modal Functions
let currentGeneratorId = null;

function showMaintenanceModal(generatorId, currentStatus) {
    currentGeneratorId = generatorId;
    updateMaintenanceModalDisplay(generatorId, currentStatus);
    $('#maintenanceModal').modal('show');
}

function updateMaintenanceModalDisplay(generatorId, status) {
    const statusIcon = $('#currentStatusIcon i');
    const statusText = $('#currentStatusText');
    const generatorIdText = $('#currentGeneratorId');

    // Update generator ID
    generatorIdText.text('Generator ID: ' + generatorId);

    // Update status display
    if (status === 'none') {
        statusIcon.removeClass().addClass('fas fa-check-circle fa-3x text-success');
        statusText.text('No maintenance required');
    } else if (status === 'completed') {
        statusIcon.removeClass().addClass('fas fa-check-circle fa-3x text-success');
        statusText.text('Maintenance completed');
    } else {
        statusIcon.removeClass().addClass('fas fa-tools fa-3x text-warning');
        statusText.text('Maintenance in progress');
    }

    // Update button states
    $('.maintenance-action-btn').removeClass('active');
    $(`.maintenance-action-btn[data-status="${status}"]`).addClass('active');
}

function updateMaintenanceStatus(newStatus) {
    if (!currentGeneratorId) {
        showNotification('No generator selected', 'error');
        return;
    }

    const data = {
        generator_id: currentGeneratorId,
        maintenance_status: newStatus,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    $.ajax({
        url: '/dashboard/maintenance-status',
        type: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                // Show success notification
                showNotification(response.message, 'success');

                // Close the modal
                $('#maintenanceModal').modal('hide');

                // Refresh the page after a short delay to show updated data
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            showNotification(response?.message || 'Error updating maintenance status', 'error');
        }
    });
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

// Auto-refresh every 5 minutes
setInterval(function() {
    // Only refresh if user is on this page
    if (window.location.pathname.includes('preventive-maintenance')) {
        refreshMaintenanceData();
    }
}, 300000); // 5 minutes
</script>
@endsection

