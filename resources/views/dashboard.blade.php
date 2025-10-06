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
                    <!-- Alert Notification Badge -->
                    <div class="position-relative">
                        <button class="btn btn-outline-light btn-sm" onclick="showAlertDetails()" title="View Alerts">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle"
                                  id="notificationBadge" style="display: none; font-size: 0.7rem;">0</span>
                        </button>
                    </div>
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
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.1s;">
                <div class="card-body text-center d-flex flex-column">
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
                    <div class="mt-auto">
                        <span class="badge badge-success-modern badge-modern">ACTIVE CLIENTS</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.2s;">
                <div class="card-body text-center d-flex flex-column">
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
                    <div class="mt-auto">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar" style="width: {{ $totalGenerators > 0 ? ($runningGenerators / $totalGenerators) * 100 : 0 }}%; background: var(--info-gradient);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.3s;">
                <div class="card-body text-center d-flex flex-column">
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
                    <div class="mt-auto">
                        <span class="badge badge-success-modern badge-modern">OPERATIONAL</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card card-modern animate-fadeInUp h-100" style="animation-delay: 0.4s;">
                <div class="card-body text-center d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="p-3 rounded-circle" style="background: var(--primary-gradient);">
                            <i class="fas fa-clock fa-2x text-white"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0 text-white fw-bold" id="runningGeneratorsCount">{{ $runningGenerators }}</h3>
                            <small class="text-white-50">Running Now</small>
                        </div>
                    </div>
                    <h6 class="text-white mb-0">Runtime Tracking</h6>
                    <div class="mt-auto">
                        <small class="text-white-50" id="totalRuntimeToday">Loading...</small>
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
                                <div class="me-4 d-flex align-items-center">
                                    <i class="fas fa-power-off fa-3x text-white"></i>
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <h2 class="mb-2 text-white fw-bold" id="statusText">
                                        {{ $generatorStatus && $generatorStatus->power ? 'OPERATIONAL' : 'OFFLINE' }}
                                    </h2>
                                    <div>
                                        <select class="form-select form-control-modern" id="mainGeneratorFilter" style="width: auto; font-size: 0.9rem;">
                                            <option value="">Select Generator</option>
                                            @foreach($generators as $generator)
                                                <option value="{{ $generator->generator_id }}">
                                                    {{ $generator->sitename }} ({{ $generator->generator_id }}) @if($generator->kva_power) - {{ $generator->kva_power }}kVA @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
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

    <!-- Generator Power Control Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.5s;">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-power-off me-2"></i>
                            Generator Power Control
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-control-modern" id="generatorFilter" style="width: auto;">
                                <option value="">All Generators</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->generator_id }}">{{ $generator->sitename ?: 'Generator ' . $generator->generator_id }} @if($generator->kva_power) - {{ $generator->kva_power }}kVA @endif</option>
                                @endforeach
                            </select>
                            <select class="form-select form-control-modern" id="clientFilter" style="width: auto;">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->display_name ?? $client->client_id }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="generatorPowerControls">
                        @foreach($generators as $generator)
                        @php
                            $generatorStatus = collect($generatorStatuses)->firstWhere('generator_id', $generator->generator_id);
                            $isActive = $generatorStatus ? $generatorStatus['is_active'] : false;
                            $powerStatus = $generatorStatus ? $generatorStatus['power_status'] : false;
                            $statusClass = $isActive ? 'status-active' : 'status-inactive';
                            $powerClass = $powerStatus ? 'power-on' : 'power-off';
                        @endphp
                        <div class="col-lg-4 col-md-6 mb-3 generator-item" data-client-id="{{ $generator->client_id }}">
                            <div class="generator-control-card p-3 rounded {{ $statusClass }}"
                                 style="background: var(--glass-bg); border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease; cursor: pointer;"
                                 onclick="viewGeneratorLogs('{{ $generator->generator_id }}')"
                                 title="Click to view generator logs">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="mb-0 text-white">{{ $generator->sitename ?: 'Generator ' . $generator->generator_id }} {{ $generator->generator_id }}</h6>
                                        <div class="mt-1">
                                            <span class="badge badge-info-modern badge-modern">{{ $generator->kva_power ? $generator->kva_power . 'kVA' : 'N/A' }}</span>
                                            <span class="badge {{ $isActive ? 'badge-success' : 'badge-secondary' }} ms-1">
                                                {{ $isActive ? 'ACTIVE' : 'INACTIVE' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="power-status-indicator {{ $powerClass }}" id="status-{{ $generator->generator_id }}">
                                        <i class="fas fa-circle {{ $powerStatus ? 'text-success' : 'text-danger' }}"></i>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-white-50">{{ $generator->client->display_name ?? 'Unknown Client' }}</small>
                                    <div class="d-flex align-items-center">
                                        <div class="power-toggle-switch">
                                        <label class="switch">
                                            <input type="checkbox"
                                                   class="power-toggle"
                                                   data-generator-id="{{ $generator->generator_id }}"
                                                   id="toggle-{{ $generator->generator_id }}"
                                                   {{ $powerStatus ? 'checked' : '' }}
                                                   {{ !$isActive ? 'disabled' : '' }}>
                                            <span class="slider round"></span>
                                        </label>
                                        <small class="text-white-50 d-block mt-1" id="power-text-{{ $generator->generator_id }}">
                                            {{ $powerStatus ? 'POWER ON' : 'POWER OFF' }}
                                        </small>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Data Tables -->
    <div class="row">
        <!-- Generator Runtime Tracking -->
        <div class="col-lg-8 mb-4">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.6s;">
                <div class="card-header border-0 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-clock me-2"></i>
                            Generator Runtime Tracking
                        </h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-control-modern" id="runtimeGeneratorFilter" style="width: auto; font-size: 0.9rem;">
                                <option value="">Select Generator</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->generator_id }}">{{ $generator->sitename ?: 'Generator ' . $generator->generator_id }} ({{ $generator->generator_id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="runtime-tracking-content" id="runtimeTrackingContent">
                        <div class="text-center py-5">
                            <i class="fas fa-arrow-up fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">Select a generator to view runtime data</h6>
                            <p class="text-muted small">Choose a generator from the dropdown above to see detailed runtime statistics</p>
                        </div>
                    </div>
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
                                <div class="h5 mb-0 text-white" id="quickStatsRunning">{{ $latestLogs->where('GS', true)->count() }}</div>
                                <small class="text-white-50">Running</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-pause fa-2x text-secondary mb-2"></i>
                                <div class="h5 mb-0 text-white" id="quickStatsStopped">{{ $latestLogs->where('GS', false)->count() }}</div>
                                <small class="text-white-50">Stopped</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-bolt fa-2x text-info mb-2"></i>
                                <div class="h5 mb-0 text-white" id="quickStatsCurrent">{{ number_format($latestLogs->avg('LI1') ?? 0, 1) }}A</div>
                                <small class="text-white-50">Avg Current</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: var(--glass-bg);">
                                <i class="fas fa-wave-square fa-2x text-success mb-2"></i>
                                <div class="h5 mb-0 text-white" id="quickStatsFrequency">{{ number_format($latestLogs->avg('Lf1') ?? 0, 3) }}Hz</div>
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
                        <div class="d-flex gap-2">
                            <select class="form-select form-control-modern" id="logGeneratorFilter" style="width: auto;">
                                <option value="">All Generators</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->generator_id }}">{{ $generator->sitename }} @if($generator->kva_power) - {{ $generator->kva_power }}kVA @endif</option>
                                @endforeach
                            </select>
                            <select class="form-select form-control-modern" id="logSitenameFilter" style="width: auto;">
                                <option value="">All Sites</option>
                                @foreach($generators->filter(function($g) { return !empty($g->sitename); })->unique('sitename') as $generator)
                                    <option value="{{ $generator->sitename }}">{{ $generator->sitename }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('logs') }}" class="btn btn-sm btn-modern">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0" id="logsTable">
                                <thead>
                                    <tr>
                                    <th class="text-white">Time</th>
                                    <th class="text-white">ID</th>
                                    <th class="text-white">Site</th>
                                    <th class="text-white">FL</th>
                                    <th class="text-white">BV</th>
                                    <th class="text-white">LV1</th>
                                    <th class="text-white">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($latestLogs->take(10) as $log)
                                <tr>
                                    <td class="text-white-50">{{ $log->log_timestamp->format('M-d g:i A') }}</td>
                                    <td>
                                        <span class="badge badge-info-modern badge-modern">{{ $log->generator_id }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning-modern badge-modern">{{ $log->sitename ?? $log->generator->sitename ?? 'N/A' }}</span>
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
                        <div class="d-flex gap-2">
                            <select class="form-select form-control-modern" id="writeLogGeneratorFilter" style="width: auto;">
                                <option value="">All Generators</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->generator_id }}">{{ $generator->sitename }} @if($generator->kva_power) - {{ $generator->kva_power }}kVA @endif</option>
                                @endforeach
                            </select>
                            <select class="form-select form-control-modern" id="writeLogSitenameFilter" style="width: auto;">
                                <option value="">All Sites</option>
                                @foreach($generators->filter(function($g) { return !empty($g->sitename); })->unique('sitename') as $generator)
                                    <option value="{{ $generator->sitename }}">{{ $generator->sitename }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route('write-logs') }}" class="btn btn-sm btn-modern">View All</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                        <div class="table-responsive">
                        <table class="table table-modern table-hover mb-0" id="writeLogsTable">
                                <thead>
                                    <tr>
                                    <th class="text-white">Time</th>
                                    <th class="text-white">ID</th>
                                    <th class="text-white">Site</th>
                                    <th class="text-white">FL</th>
                                    <th class="text-white">BV</th>
                                    <th class="text-white">LV1</th>
                                    <th class="text-white">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($latestWriteLogs->take(10) as $writeLog)
                                <tr>
                                    <td class="text-white-50">{{ $writeLog->write_timestamp->format('M-d g:i A') }}</td>
                                    <td>
                                        <span class="badge badge-info-modern badge-modern">{{ $writeLog->generator_id }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning-modern badge-modern">{{ $writeLog->sitename ?? $writeLog->generator->sitename ?? 'N/A' }}</span>
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

@section('styles')
<style>
/* Power Toggle Switch Styles */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #28a745, #20c997);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.power-status-indicator {
    font-size: 12px;
}

.power-status-indicator.online {
    color: #28a745 !important;
}

.power-status-indicator.offline {
    color: #dc3545 !important;
}

.generator-control-card:hover {
    background: rgba(255,255,255,0.15) !important;
    transition: all 0.3s ease;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Device Status Styles */
.status-active {
    border-left: 4px solid #28a745 !important;
}

.status-inactive {
    border-left: 4px solid #6c757d !important;
}

.power-on {
    animation: pulse-green 2s infinite;
}

.power-off {
    opacity: 0.6;
}

@keyframes pulse-green {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.switch input:disabled + .slider {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Quick Stats Update Animation */
.quick-stats-updated {
    animation: quickStatsPulse 0.5s ease-in-out;
}

@keyframes quickStatsPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); background-color: rgba(0, 255, 0, 0.1); }
    100% { transform: scale(1); }
}

/* Universal Dropdown Styling - Works in both Light and Dark Mode */
.form-select.form-control-modern {
    background: var(--bs-body-bg) !important;
    border: 1px solid var(--bs-border-color) !important;
    color: var(--bs-body-color) !important;
}

.form-select.form-control-modern:focus {
    background: var(--bs-body-bg) !important;
    border-color: var(--bs-primary) !important;
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25) !important;
}

.form-select.form-control-modern option {
    background: var(--bs-body-bg) !important;
    color: var(--bs-body-color) !important;
}

/* Override browser default dropdown styling */
select.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") !important;
}

/* Dark mode specific overrides */
[data-bs-theme="dark"] select.form-select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") !important;
}

[data-bs-theme="dark"] .form-select.form-control-modern {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
    color: white !important;
}

[data-bs-theme="dark"] .form-select.form-control-modern:focus {
    background: rgba(255,255,255,0.15) !important;
    border-color: rgba(255,255,255,0.3) !important;
    box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.1) !important;
}

[data-bs-theme="dark"] .form-select.form-control-modern option {
    background: #2d3748 !important;
    color: white !important;
}

.generator-item {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.generator-control-card {
    position: relative;
    overflow: hidden;
}

.generator-control-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    transition: left 0.5s;
}

.generator-control-card:hover::before {
    left: 100%;
}

/* Main Generator Filter Styling */
#mainGeneratorFilter {
    background: rgba(255,255,255,0.1) !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
    color: white !important;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

#mainGeneratorFilter:focus {
    background: rgba(255,255,255,0.15) !important;
    border-color: rgba(255,255,255,0.4) !important;
    box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.1);
    outline: none;
}

#mainGeneratorFilter option {
    background: #2c3e50;
    color: white;
    padding: 8px;
}

#mainGeneratorFilter:hover {
    background: rgba(255,255,255,0.15) !important;
    border-color: rgba(255,255,255,0.3) !important;
}

/* Status Card Alignment Fixes */
.status-card .d-flex.align-items-center {
    align-items: center !important;
}

.status-card .d-flex.flex-column {
    justify-content: center;
    height: 100%;
}

.status-card .fa-power-off {
    line-height: 1;
    vertical-align: middle;
}

/* Generator Card Clickable Styles */
.generator-control-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    border-color: rgba(255,255,255,0.2) !important;
}

.generator-control-card:active {
    transform: translateY(0);
}

/* Prevent power toggle from triggering card click */
.power-toggle-switch {
    pointer-events: auto;
}

.power-toggle-switch * {
    pointer-events: auto;
}

</style>
@endsection

@section('scripts')
    <script>

    function updateLiveGeneratorIds(logsData) {
        // Group logs by generator_id to get the latest entry for each generator
        const latestLogs = {};
        logsData.forEach(function(log) {
            if (!latestLogs[log.generator_id] || new Date(log.log_timestamp) > new Date(latestLogs[log.generator_id].log_timestamp)) {
                latestLogs[log.generator_id] = log;
            }
        });

        // Update each generator card with live ID and name
        Object.keys(latestLogs).forEach(function(generatorId) {
            const liveIdElement = document.getElementById('live-id-' + generatorId);
            const liveNameElement = document.getElementById('live-name-' + generatorId);

            if (liveIdElement) {
                const log = latestLogs[generatorId];
                // Update with live data
                liveIdElement.textContent = log.generator_id;

                // Add a subtle animation to indicate update
                liveIdElement.style.transition = 'all 0.3s ease';
                liveIdElement.style.color = '#48bb78';
                setTimeout(function() {
                    liveIdElement.style.color = '#a0aec0';
                }, 1000);
            }

            if (liveNameElement) {
                const log = latestLogs[generatorId];
                // Generate a proper name based on the generator ID
                const generatorName = generateGeneratorName(log.generator_id);
                liveNameElement.textContent = generatorName;

                // Add a subtle animation to indicate update
                liveNameElement.style.transition = 'all 0.3s ease';
                liveNameElement.style.color = '#48bb78';
                setTimeout(function() {
                    liveNameElement.style.color = '#ffffff';
                }, 1000);
            }
        });
    }

     function generateGeneratorName(generatorId) {
         // Create meaningful names based on generator ID patterns
         const id = generatorId.toLowerCase();

         // Check for specific patterns and assign names
         if (id.includes('492ff2e5')) return 'Resort 200kva';
         if (id.includes('492ff2e6')) return 'Resort 200kva';
         if (id.includes('492ff2e7')) return 'Resort 200kva';
         if (id.includes('abc1234')) return 'Axact #100';
         if (id.includes('abc567')) return 'Axact #101';
         if (id.includes('abc890')) return 'Axact #102';
         if (id.includes('1122334455')) return 'Axact #103';
         if (id.includes('55da2f89')) return 'Axact #104';
         if (id.includes('42daf728')) return 'Axact #105';
         if (id.includes('53da9f6e')) return '400yardA 200kva';
         if (id.includes('44406481')) return '400yardB 200kva';
         if (id.includes('4a2f3a40')) return 'Yacht 500kva';
         if (id.includes('4a2fc645')) return 'Yacht 500kva';
         if (id.includes('50da533a')) return 'Crest Tower 250kva';
         if (id.includes('54da27c2')) return 'Yacht 27kva';
         if (id.includes('54daa3c8')) return '350kva Crest Tower';
         if (id.includes('bf822748')) return '350kva Crest Tower';

         // Default naming pattern for other IDs
         return 'Generator ' + generatorId;
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
                        onlineGenerators.text(status.active_generators || '1');
                    } else {
                        statusCard.removeClass('status-online').addClass('status-offline');
                        statusText.text('OFFLINE');
                        onlineGenerators.text('0');
                    }

                    if (status.last_updated) {
                        lastUpdated.text(new Date(status.last_updated).toLocaleTimeString());
                    }

                    // Update individual generator statuses
                    if (status.generator_statuses) {
                        updateGeneratorStatuses(status.generator_statuses);
                    }
                }
            });

            // Refresh quick stats
            $.get('/api/generator/quick-stats', function(response) {
                if (response.success && response.data) {
                    updateQuickStats(response.data);
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
                                <td class="text-white-50">${new Date(log.log_timestamp).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} ${new Date(log.log_timestamp).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
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

                    // Update live generator IDs
                    updateLiveGeneratorIds(response.data);
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
                                <td class="text-white-50">${new Date(writeLog.write_timestamp).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} ${new Date(writeLog.write_timestamp).toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true})}</td>
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
        // Initial data load
        refreshData();

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

    // Update individual generator statuses
    function updateGeneratorStatuses(generatorStatuses) {
        generatorStatuses.forEach(function(status) {
            const generatorId = status.generator_id;
            const isActive = status.is_active;
            const powerStatus = status.power_status;

            // Update status indicator
            const statusIndicator = $(`#status-${generatorId}`);
            const statusIcon = statusIndicator.find('i');

            if (isActive) {
                statusIcon.removeClass('text-danger').addClass('text-success');
                statusIndicator.removeClass('power-off').addClass('power-on');
            } else {
                statusIcon.removeClass('text-success').addClass('text-danger');
                statusIndicator.removeClass('power-on').addClass('power-off');
            }

            // Update power toggle
            const powerToggle = $(`#toggle-${generatorId}`);
            powerToggle.prop('checked', powerStatus);
            powerToggle.prop('disabled', !isActive);

            // Update generator card border
            const generatorCard = powerToggle.closest('.generator-control-card');
            generatorCard.removeClass('status-active status-inactive');
            generatorCard.addClass(isActive ? 'status-active' : 'status-inactive');

            // Update status badge
            const statusBadge = generatorCard.find('.badge').last();
            statusBadge.removeClass('badge-success badge-secondary');
            statusBadge.addClass(isActive ? 'badge-success' : 'badge-secondary');
            statusBadge.text(isActive ? 'ACTIVE' : 'INACTIVE');

            // Update power text
            const powerText = generatorCard.find('.power-toggle-switch small');
            powerText.text(powerStatus ? 'POWER ON' : 'POWER OFF');

            // Check if this is a manual override (you might want to add this info to the API response)
            // For now, we'll assume manual overrides are indicated by recent updates
            if (status.is_manual_override) {
                powerText.addClass('text-warning');
                powerText.attr('title', 'Manual Override Active');
            } else {
                powerText.removeClass('text-warning');
                powerText.removeAttr('title');
            }
        });
    }

    // Update quick stats
    function updateQuickStats(data) {
        $('#quickStatsRunning').text(data.running);
        $('#quickStatsStopped').text(data.stopped);
        $('#quickStatsCurrent').text(data.avg_current + 'A');
        $('#quickStatsFrequency').text(data.avg_frequency + 'Hz');

        // Add visual feedback for updates
        $('.quick-stats-updated').removeClass('quick-stats-updated');
        $('#quickStatsRunning, #quickStatsStopped, #quickStatsCurrent, #quickStatsFrequency')
            .addClass('quick-stats-updated');

        // Remove the visual feedback after animation
        setTimeout(function() {
            $('.quick-stats-updated').removeClass('quick-stats-updated');
        }, 1000);
    }

    // Power Control Functions
    function loadPowerStatus() {
        const generatorIds = [];
        $('.power-toggle').each(function() {
            generatorIds.push($(this).data('generator-id'));
        });

        if (generatorIds.length === 0) return;

        $.post('{{ route("dashboard.power-status") }}', {
            ids: generatorIds,
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                Object.keys(response.data).forEach(function(generatorId) {
                    const powerStatus = response.data[generatorId].power;
                    const toggle = $('#toggle-' + generatorId);
                    const statusIndicator = $('#status-' + generatorId);

                    toggle.prop('checked', powerStatus);

                    if (powerStatus) {
                        statusIndicator.removeClass('offline').addClass('online');
                        statusIndicator.find('i').removeClass('fa-circle').addClass('fa-circle');
                    } else {
                        statusIndicator.removeClass('online').addClass('offline');
                        statusIndicator.find('i').removeClass('fa-circle').addClass('fa-circle');
                    }
                });
            }
        }).fail(function() {
            console.error('Failed to load power status');
        });
    }

    function toggleGeneratorPower(generatorId, power) {
        // Show loading state
        const toggle = $('#toggle-' + generatorId);
        const originalState = toggle.prop('checked');
        toggle.prop('disabled', true);

        $.post('{{ route("dashboard.toggle-power") }}', {
            generator_id: generatorId,
            power: power,
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                const statusIndicator = $('#status-' + generatorId);
                const powerText = $('#power-text-' + generatorId);

                if (power) {
                    statusIndicator.removeClass('offline').addClass('online');
                    statusIndicator.find('i').removeClass('text-danger').addClass('text-success');
                } else {
                    statusIndicator.removeClass('online').addClass('offline');
                    statusIndicator.find('i').removeClass('text-success').addClass('text-danger');
                }

                // Update power text
                powerText.text(power ? 'POWER ON' : 'POWER OFF');

                // Add visual feedback for manual override
                powerText.addClass('text-warning');
                powerText.attr('title', 'Manual Override Active');

                // Show success message
                showNotification(response.message, 'success');
            } else {
                showNotification(response.message || 'Failed to update power status', 'error');
                // Revert toggle state
                toggle.prop('checked', !power);
            }
        }).fail(function() {
            showNotification('Network error. Please try again.', 'error');
            // Revert toggle state
            toggle.prop('checked', !power);
        }).always(function() {
            // Re-enable toggle
            toggle.prop('disabled', false);
        });
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = $(`
            <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed"
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
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

    // Initialize power controls
    $(document).ready(function() {
        // Load initial power status
        loadPowerStatus();

        // Handle power toggle clicks
        $('.power-toggle').on('change', function(e) {
            e.stopPropagation(); // Prevent card click when toggling power
            const generatorId = $(this).data('generator-id');
            const power = $(this).is(':checked');

            toggleGeneratorPower(generatorId, power);
        });

        // Also prevent click events on the power toggle switch
        $('.power-toggle-switch').on('click', function(e) {
            e.stopPropagation(); // Prevent card click when clicking on power toggle area
        });

        // Generator filter functionality
        $('#generatorFilter').on('change', function() {
            const selectedGeneratorId = $(this).val();

            $('.generator-item').each(function() {
                const generatorId = $(this).find('.generator-id').text();

                if (selectedGeneratorId === '' || generatorId === selectedGeneratorId) {
                    $(this).show().addClass('animate-fadeInUp');
                } else {
                    $(this).hide();
                }
            });
        });

        // Client filter functionality
        $('#clientFilter').on('change', function() {
            const selectedClientId = $(this).val();

            $('.generator-item').each(function() {
                const clientId = $(this).data('client-id');

                if (selectedClientId === '' || clientId == selectedClientId) {
                    $(this).show().addClass('animate-fadeInUp');
                } else {
                    $(this).hide();
                }
            });
        });

        // Generator filter for logs
        $('#logGeneratorFilter').on('change', function() {
            const selectedGeneratorId = $(this).val();
            filterLogsTable('logsTable', selectedGeneratorId);
        });

        // Sitename filter for logs
        $('#logSitenameFilter').on('change', function() {
            const selectedSitename = $(this).val();
            filterLogsTableBySitename('logsTable', selectedSitename);
        });

        // Generator filter for write logs
        $('#writeLogGeneratorFilter').on('change', function() {
            const selectedGeneratorId = $(this).val();
            filterLogsTable('writeLogsTable', selectedGeneratorId);
        });

        // Sitename filter for write logs
        $('#writeLogSitenameFilter').on('change', function() {
            const selectedSitename = $(this).val();
            filterLogsTableBySitename('writeLogsTable', selectedSitename);
        });

        // Main generator filter (replaces static Generator ID)
        $('#mainGeneratorFilter').on('change', function() {
            const selectedGeneratorId = $(this).val();
            if (selectedGeneratorId) {
                // Find the selected generator data
                const selectedGenerator = {!! json_encode($generators) !!}.find(g => g.generator_id === selectedGeneratorId);
                if (selectedGenerator) {
                    // Find latest log for this generator
                    const latestLog = {!! json_encode($latestLogs) !!}.find(log => log.generator_id === selectedGeneratorId);
                    if (latestLog) {
                        $('#fuelLevel').text(latestLog.FL + '%');
                        $('#batteryVoltage').text(latestLog.BV + 'V');
                        $('#lineVoltage').text(latestLog.LV1 + 'V');

                        // Update status based on GS field
                        const statusCard = $('#statusCard');
                        const statusText = $('#statusText');
                        if (latestLog.GS) {
                            statusCard.removeClass('status-offline').addClass('status-online');
                            statusText.text('OPERATIONAL');
                        } else {
                            statusCard.removeClass('status-online').addClass('status-offline');
                            statusText.text('OFFLINE');
                        }
                    }
                }
            }
        });


        // Refresh power status every 30 seconds
        setInterval(loadPowerStatus, 30000);

        // Load runtime data on page load
        loadRuntimeData();

        // Refresh runtime data every 30 seconds
        setInterval(loadRuntimeData, 30000);

        // Refresh quick stats every 15 seconds for more frequent updates
        setInterval(function() {
            $.get('/api/generator/quick-stats', function(response) {
                if (response.success && response.data) {
                    updateQuickStats(response.data);
                }
            });
        }, 15000);

        // Check for alerts every 30 seconds
        setInterval(checkAlerts, 30000);

        // Initial alert check on page load
        checkAlerts();

        // Runtime generator filter change handler
        $('#runtimeGeneratorFilter').on('change', function() {
            const generatorId = $(this).val();

            if (generatorId) {
                loadGeneratorRuntime(generatorId, 'today');
            } else {
                showRuntimePlaceholder();
            }
        });
    });

    // Alert checking function
    function checkAlerts() {
        $.post('/api/alerts/check', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                // Update notification badge if there are active alerts
                if (response.active_alerts > 0) {
                    $('#notificationBadge').text(response.active_alerts).show();

                    // Show notification for new alerts
                    if (response.active_alerts > 0) {
                        showNotification(`⚠️ ${response.active_alerts} Active Alert${response.active_alerts > 1 ? 's' : ''} Detected!`, 'warning');
                    }
                } else {
                    $('#notificationBadge').hide();
                }
            }
        }).fail(function() {
            console.error('Failed to check alerts');
        });
    }

    // Show alert details function
    function showAlertDetails() {
        $.get('/api/alerts/', function(response) {
            if (response.success && response.data.length > 0) {
                let alertHtml = '<div class="alert-list">';
                response.data.forEach(function(alert) {
                    const severityClass = {
                        'low': 'text-info',
                        'medium': 'text-warning',
                        'high': 'text-danger',
                        'critical': 'text-danger fw-bold'
                    }[alert.severity] || 'text-secondary';

                    alertHtml += `
                        <div class="alert-item p-3 mb-2 border rounded" style="background: rgba(255,255,255,0.05);">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 text-white">${alert.title}</h6>
                                    <p class="mb-1 text-white-50 small">${alert.message}</p>
                                    <small class="text-muted">${alert.generator_id} • ${new Date(alert.triggered_at).toLocaleString()}</small>
                                </div>
                                <span class="badge ${severityClass}">${alert.severity.toUpperCase()}</span>
                            </div>
                        </div>
                    `;
                });
                alertHtml += '</div>';

                showNotification(alertHtml, 'info');
            } else {
                showNotification('✅ No active alerts', 'success');
            }
        }).fail(function() {
            showNotification('❌ Failed to load alerts', 'danger');
        });
    }

    // Load generator runtime data
    function loadGeneratorRuntime(generatorId, period) {
        $.get('/api/generator/runtime', {
            generator_id: generatorId,
            period: period
        }, function(response) {
            if (response.success && response.data) {
                displayGeneratorRuntime(response.data);
            } else {
                showRuntimeError(response.message || 'Failed to load runtime data');
            }
        }).fail(function() {
            showRuntimeError('Failed to load runtime data');
        });
    }

    // Display generator runtime data
    function displayGeneratorRuntime(data) {
        const container = $('#runtimeTrackingContent');
        const generator = data.generator;
        const runtime = data.runtime;

        const statusClass = generator.is_active ? 'badge-success' : 'badge-secondary';
        const statusText = generator.is_active ? 'Active' : 'Inactive';
        const statusIcon = generator.is_active ? 'text-success' : 'text-danger';

        const html = `
            <div class="runtime-card p-4 rounded" style="background: var(--glass-bg); border: 1px solid rgba(255,255,255,0.1);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1 text-white">${generator.sitename || 'Generator ' + generator.id} ${generator.id}</h5>
                        <div class="mt-1">
                            <span class="badge badge-info-modern badge-modern">${generator.kva_power ? generator.kva_power + 'kVA' : 'N/A'}</span>
                            <span class="badge ${statusClass} ms-2">${statusText}</span>
                        </div>
                    </div>
                    <div class="runtime-status">
                        <i class="fas fa-circle ${statusIcon}"></i>
                    </div>
                </div>
                <div class="runtime-stats">
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-white-50">Current Runtime:</small>
                        <small class="text-white fw-bold">${runtime.current}</small>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-white-50">Today:</small>
                        <small class="text-white fw-bold">${runtime.today}</small>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-white-50">This Week:</small>
                        <small class="text-white fw-bold">${runtime.week}</small>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-white-50">This Month:</small>
                        <small class="text-white fw-bold">${runtime.month}</small>
                    </div>
                </div>
                ${data.last_updated ? `
                    <div class="mt-3 pt-3 border-top border-secondary">
                        <small class="text-white-50">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: ${new Date(data.last_updated).toLocaleString()}
                        </small>
                    </div>
                ` : ''}
            </div>
        `;

        container.html(html);
    }

    // Show runtime placeholder
    function showRuntimePlaceholder() {
        const container = $('#runtimeTrackingContent');
        container.html(`
            <div class="text-center py-5">
                <i class="fas fa-arrow-up fa-2x text-muted mb-3"></i>
                <h6 class="text-muted">Select a generator to view runtime data</h6>
                <p class="text-muted small">Choose a generator from the dropdown above to see detailed runtime statistics</p>
            </div>
        `);
    }

    // Show runtime error
    function showRuntimeError(message) {
        const container = $('#runtimeTrackingContent');
        container.html(`
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                <h6 class="text-warning">Error Loading Runtime Data</h6>
                <p class="text-muted small">${message}</p>
                <button class="btn btn-sm btn-outline-light mt-2" onclick="location.reload()">
                    <i class="fas fa-refresh me-1"></i>Retry
                </button>
            </div>
        `);
    }

    // Runtime tracking functions
    function loadRuntimeData() {
        $.get('/api/runtime/summary', function(response) {
            if (response.success) {
                updateRuntimeSummary(response.data);
            }
        }).fail(function() {
            console.error('Failed to load runtime summary');
        });

        $.get('/api/runtime/running', function(response) {
            if (response.success) {
                updateRuntimeCards(response.data);
            }
        }).fail(function() {
console.error('Failed to load running generators');
        });
    }

    function updateRuntimeSummary(data) {
        $('#runningGeneratorsCount').text(data.currently_running);
        $('#totalRuntimeToday').text('Today: ' + data.total_today_formatted);
    }

    function updateRuntimeCards(runningGenerators) {
        const container = $('#runtimeTrackingCards');

        if (runningGenerators.length === 0) {
            container.html(`
                <div class="col-12 text-center py-4">
                    <i class="fas fa-power-off fa-2x text-muted"></i>
                    <p class="text-muted mt-2">No generators currently running</p>
                </div>
            `);
            return;
        }

        let html = '';
        runningGenerators.forEach(function(runtime) {
            const startTime = new Date(runtime.start_time);
            const now = new Date();
            const duration = Math.floor((now - startTime) / 1000);
            const formattedDuration = formatDuration(duration);

            html += `
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="runtime-card p-3 rounded" style="background: var(--glass-bg); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="mb-0 text-white">${runtime.generator?.name || 'Generator ' + runtime.generator_id}</h6>
                                <small class="text-white-50">${runtime.generator_id}</small>
                                ${runtime.sitename ? `<div class="mt-1"><span class="badge badge-info-modern badge-modern">${runtime.sitename}</span></div>` : ''}
                            </div>
                            <div class="runtime-status">
                                <i class="fas fa-circle text-success"></i>
                            </div>
                        </div>
                        <div class="runtime-details">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-white-50">Runtime:</small>
                                <span class="text-white fw-bold">${formattedDuration}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-white-50">Started:</small>
                                <small class="text-white-50">${startTime.toLocaleTimeString()}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-white-50">Voltages:</small>
                                <small class="text-white">L1:${runtime.start_voltage_l1}V L2:${runtime.start_voltage_l2}V L3:${runtime.start_voltage_l3}V</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    function formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        if (hours > 0) {
            return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        } else {
            return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
    }

    function refreshRuntimeData() {
        loadRuntimeData();
    }

    function filterLogsTable(tableId, generatorId) {
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const generatorCell = row.cells[1]; // Generator ID is in the second column

            if (generatorId === '' || generatorCell.textContent.includes(generatorId)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function filterLogsTableBySitename(tableId, sitename) {
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const sitenameCell = row.cells[2]; // Site Name is in the third column

            if (sitename === '' || sitenameCell.textContent.includes(sitename)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    // Function to view generator logs
    function viewGeneratorLogs(generatorId) {
        // Redirect to write-logs page with generator_id filter
        window.location.href = `{{ route('write-logs') }}?generator_id=${generatorId}`;
    }

    </script>
@endsection
