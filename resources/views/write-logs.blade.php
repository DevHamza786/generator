@extends('layouts.app')

@section('title', 'Write Logs')

@section('content')
<div class="container-fluid px-4">
    <!-- Modern Page Header -->
    <div class="page-header-modern animate-fadeInUp">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-modern">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}" class="text-white-50">
                        <i class="fas fa-home me-1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="fas fa-database me-1"></i>
                    Write Logs
                </li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-4 fw-bold mb-2 text-white">
                    Write Logs
                </h1>
                <p class="lead text-white-50 mb-0">Complete write log data from all generators</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex justify-content-end gap-3 align-items-center">
                    <div class="text-center">
                            <div class="h5 mb-0 text-white fw-bold" id="writeLogCount">{{ $writeLogs->total() }}</div>
                        <small class="text-white-50">Total Write Logs</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.1s;">
                <div class="card-header">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-filter me-2"></i>
                        Filter & Search
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Client</label>
                            <select class="form-select form-control-modern" id="clientFilter">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->display_name ?? $client->client_id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Generator</label>
                            <select class="form-select form-control-modern" id="generatorFilter">
                                <option value="">All Generators</option>
                                @foreach($generators as $generator)
                                    <option value="{{ $generator->generator_id }}">{{ $generator->name }} ({{ $generator->generator_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Site Name</label>
                            <select class="form-select form-control-modern" id="sitenameFilter">
                                <option value="">All Sites</option>
                                @foreach($generators->filter(function($g) { return !empty($g->sitename); })->unique('sitename') as $generator)
                                    <option value="{{ $generator->sitename }}">{{ $generator->sitename }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Date Range</label>
                            <input type="date" class="form-control form-control-modern" id="dateFilter" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Status</label>
                            <select class="form-select form-control-modern" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-modern" onclick="applyFilters()">
                                    <i class="fas fa-search me-1"></i>Apply Filters
                                </button>
                                <button class="btn btn-outline-secondary btn-modern" onclick="clearFilters()">
                                    <i class="fas fa-times me-1"></i>Clear
                                </button>
                                <button class="btn btn-outline-success btn-modern" onclick="exportWriteLogs()">
                                    <i class="fas fa-download me-1"></i>Export CSV
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Write Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card card-modern animate-fadeInUp" style="animation-delay: 0.2s;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-table me-2"></i>
                        Generator Write Logs
                        <span class="badge badge-primary-modern badge-modern ms-2" id="writeLogCount">{{ $writeLogs->total() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-modern btn-sm" onclick="exportWriteLogs()">
                            <i class="fas fa-download me-1"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern" id="writeLogsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Write Timestamp</th>
                                    <th>Client</th>
                                    <th>Generator ID</th>
                                    <th>Site Name</th>
                                    <th>Power Status</th>
                                    <th>Fuel Level</th>
                                    <th>Battery Voltage</th>
                                    <th>Line Voltage 1</th>
                                    <th>Line Voltage 2</th>
                                    <th>Line Voltage 3</th>
                                    <th>Line Current 1</th>
                                    <th>Line Current 2</th>
                                    <th>Line Current 3</th>
                                    <th>Frequency 1</th>
                                    <th>Frequency 2</th>
                                    <th>Frequency 3</th>
                                    <th>Power Factor 1</th>
                                    <th>Power Factor 2</th>
                                    <th>Power Factor 3</th>
                                    <th>KVA 1</th>
                                    <th>KVA 2</th>
                                    <th>KVA 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Debug: Total write logs = {{ $writeLogs->count() }} -->
                                @if($writeLogs->count() > 0)
                                    @foreach($writeLogs as $writeLog)
                                    <tr>
                                        <td class="text-white-50">{{ $writeLog->write_timestamp->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <span class="badge badge-primary-modern badge-modern">{{ $writeLog->client->display_name ?? $writeLog->client->client_id ?? $writeLog->client ?? 'Unknown' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info-modern badge-modern">{{ $writeLog->generator->generator_id ?? $writeLog->generator_id_old ?? $writeLog->generator_id }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning-modern badge-modern">{{ $writeLog->sitename ?? $writeLog->generator->sitename ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($writeLog->PS)
                                                <span class="badge badge-success-modern badge-modern">Active</span>
                                            @else
                                                <span class="badge badge-danger-modern badge-modern">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-white">{{ $writeLog->FL }}%</td>
                                        <td class="text-white">{{ $writeLog->BV }}V</td>
                                        <td class="text-white">{{ $writeLog->LV1 }}V</td>
                                        <td class="text-white">{{ $writeLog->LV2 }}V</td>
                                        <td class="text-white">{{ $writeLog->LV3 }}V</td>
                                        <td class="text-white">{{ $writeLog->LI1 }}A</td>
                                        <td class="text-white">{{ $writeLog->LI2 }}A</td>
                                        <td class="text-white">{{ $writeLog->LI3 }}A</td>
                                        <td class="text-white">{{ $writeLog->Lf1 }}Hz</td>
                                        <td class="text-white">{{ $writeLog->Lf2 }}Hz</td>
                                        <td class="text-white">{{ $writeLog->Lf3 }}Hz</td>
                                        <td class="text-white">{{ $writeLog->Lpf1 }}</td>
                                        <td class="text-white">{{ $writeLog->Lpf2 }}</td>
                                        <td class="text-white">{{ $writeLog->Lpf3 }}</td>
                                        <td class="text-white">{{ $writeLog->Lkva1 }}KVA</td>
                                        <td class="text-white">{{ $writeLog->Lkva2 }}KVA</td>
                                        <td class="text-white">{{ $writeLog->Lkva3 }}KVA</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="22" class="text-center text-white-50 py-4">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No write logs found. Data will appear here when generator write logs are received.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination and Write Log Count -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-white-50">
                                Showing {{ $writeLogs->firstItem() ?? 0 }} to {{ $writeLogs->lastItem() ?? 0 }} of {{ $writeLogs->total() }} write logs
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="text-white-50 mb-0">Show:</label>
                                <select class="form-select form-control-modern" id="perPageSelect" style="width: auto; font-size: 0.8rem;" onchange="changePerPage(this.value)">
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 || !request('per_page') ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            @if($writeLogs->hasPages())
                                {{ $writeLogs->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function applyFilters() {
        const clientId = document.getElementById('clientFilter').value;
        const generatorId = document.getElementById('generatorFilter').value;
        const sitename = document.getElementById('sitenameFilter').value;
        const date = document.getElementById('dateFilter').value;
        const status = document.getElementById('statusFilter').value;

        let url = new URL(window.location);
        url.searchParams.set('client_id', clientId);
        url.searchParams.set('generator_id', generatorId);
        url.searchParams.set('sitename', sitename);
        url.searchParams.set('date', date);
        url.searchParams.set('status', status);

        window.location.href = url.toString();
    }

    function changePerPage(perPage) {
        let url = new URL(window.location);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    }

    function clearFilters() {
        document.getElementById('clientFilter').value = '';
        document.getElementById('generatorFilter').value = '';
        document.getElementById('sitenameFilter').value = '';
        document.getElementById('dateFilter').value = '';
        document.getElementById('statusFilter').value = '';
        applyFilters();
    }

    function exportWriteLogs() {
        // Create CSV content
        const table = document.getElementById('writeLogsTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');

            for (let j = 0; j < cols.length; j++) {
                let cellText = cols[j].innerText.replace(/"/g, '""');
                row.push('"' + cellText + '"');
            }

            csv.push(row.join(','));
        }

        // Download CSV
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'generator_write_logs_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
</script>
@endsection
