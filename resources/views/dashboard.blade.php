<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generator Monitor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-card {
            transition: all 0.3s ease;
        }
        .status-online {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        .status-offline {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        .auto-refresh {
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="refresh-indicator">
        <div class="auto-refresh">
            <i class="fas fa-sync-alt"></i> Auto-refresh every 30s
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-bolt"></i> Generator Power Monitor
                </h1>
            </div>
        </div>

        <!-- Generator Status Card -->
        <div class="row mb-4">
            <div class="col-md-6 mx-auto">
                <div class="card status-card {{ $generatorStatus && $generatorStatus->power ? 'status-online' : 'status-offline' }}" id="statusCard">
                    <div class="card-body text-center">
                        <h3 class="card-title">
                            <i class="fas fa-power-off"></i> Generator Status
                        </h3>
                        <div class="display-4 mb-3" id="statusText">
                            {{ $generatorStatus && $generatorStatus->power ? 'ONLINE' : 'OFFLINE' }}
                        </div>
                        <p class="card-text">
                            <strong>Generator ID:</strong> {{ $generatorStatus ? $generatorStatus->generator_id : 'N/A' }}<br>
                            <strong>Last Updated:</strong> <span id="lastUpdated">
                                {{ $generatorStatus ? $generatorStatus->last_updated->format('Y-m-d H:i:s') : 'N/A' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables -->
        <div class="row">
            <!-- Generator Logs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Latest Log Data (Last 20 entries)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="logsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>ID</th>
                                        <th>FL</th>
                                        <th>BV</th>
                                        <th>LV1</th>
                                        <th>LI2</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestLogs as $log)
                                    <tr>
                                        <td>{{ $log->log_timestamp->format('H:i:s') }}</td>
                                        <td>{{ $log->generator_id }}</td>
                                        <td>{{ $log->FL }}%</td>
                                        <td>{{ $log->BV }}V</td>
                                        <td>{{ $log->LV1 }}V</td>
                                        <td>{{ $log->LI2 }}A</td>
                                        <td>
                                            @if($log->GS)
                                                <span class="badge bg-success">Running</span>
                                            @else
                                                <span class="badge bg-secondary">Stopped</span>
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

            <!-- Generator Write Logs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-database"></i> Latest Write Log Data (Last 20 entries)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm" id="writeLogsTable">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>ID</th>
                                        <th>FL</th>
                                        <th>BV</th>
                                        <th>LV1</th>
                                        <th>LI2</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestWriteLogs as $writeLog)
                                    <tr>
                                        <td>{{ $writeLog->write_timestamp->format('H:i:s') }}</td>
                                        <td>{{ $writeLog->generator_id }}</td>
                                        <td>{{ $writeLog->FL }}%</td>
                                        <td>{{ $writeLog->BV }}V</td>
                                        <td>{{ $writeLog->LV1 }}V</td>
                                        <td>{{ $writeLog->LI2 }}A</td>
                                        <td>
                                            @if($writeLog->PS)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Auto-refresh data every 30 seconds
        setInterval(function() {
            refreshData();
        }, 30000);

        function refreshData() {
            // Refresh status
            $.get('/api/generator/status', function(response) {
                if (response.success && response.data) {
                    const status = response.data;
                    const statusCard = $('#statusCard');
                    const statusText = $('#statusText');
                    const lastUpdated = $('#lastUpdated');

                    if (status.power) {
                        statusCard.removeClass('status-offline').addClass('status-online');
                        statusText.text('ONLINE');
                    } else {
                        statusCard.removeClass('status-online').addClass('status-offline');
                        statusText.text('OFFLINE');
                    }

                    lastUpdated.text(new Date(status.last_updated).toLocaleString());
                }
            });

            // Refresh logs
            $.get('/api/generator/logs', function(response) {
                if (response.success && response.data) {
                    const tbody = $('#logsTable tbody');
                    tbody.empty();

                    response.data.forEach(function(log) {
                        const row = `
                            <tr>
                                <td>${new Date(log.log_timestamp).toLocaleTimeString()}</td>
                                <td>${log.generator_id}</td>
                                <td>${log.FL}%</td>
                                <td>${log.BV}V</td>
                                <td>${log.LV1}V</td>
                                <td>${log.LI2}A</td>
                                <td>
                                    <span class="badge ${log.GS ? 'bg-success' : 'bg-secondary'}">
                                        ${log.GS ? 'Running' : 'Stopped'}
                                    </span>
                                </td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }
            });

            // Refresh write logs
            $.get('/api/generator/write-logs', function(response) {
                if (response.success && response.data) {
                    const tbody = $('#writeLogsTable tbody');
                    tbody.empty();

                    response.data.forEach(function(writeLog) {
                        const row = `
                            <tr>
                                <td>${new Date(writeLog.write_timestamp).toLocaleTimeString()}</td>
                                <td>${writeLog.generator_id}</td>
                                <td>${writeLog.FL}%</td>
                                <td>${writeLog.BV}V</td>
                                <td>${writeLog.LV1}V</td>
                                <td>${writeLog.LI2}A</td>
                                <td>
                                    <span class="badge ${writeLog.PS ? 'bg-success' : 'bg-secondary'}">
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

        // Initial refresh on page load
        $(document).ready(function() {
            refreshData();
        });
    </script>
</body>
</html>
