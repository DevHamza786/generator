@extends('layouts.app')

@section('title', 'Runtime Table Access')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-table me-2"></i>Generator Runtime Table Access
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Direct Access Link:</strong> This page provides access to the comprehensive generator runtime table.
                    </div>

                    <div class="text-center">
                        <h5 class="mb-3">Generator Runtime Table</h5>
                        <p class="text-muted mb-4">
                            View detailed runtime data for all generators including current status,
                            runtime statistics, maintenance information, and recent sessions.
                        </p>

                        <a href="{{ route('generator-runtime-table') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-table me-2"></i>Open Runtime Table
                        </a>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-chart-line me-2"></i>Features</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Real-time generator status</li>
                                <li><i class="fas fa-check text-success me-2"></i>Runtime statistics (30 days)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Maintenance status tracking</li>
                                <li><i class="fas fa-check text-success me-2"></i>Recent runtime sessions</li>
                                <li><i class="fas fa-check text-success me-2"></i>Detailed runtime history</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog me-2"></i>Data Includes</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Current running status</li>
                                <li><i class="fas fa-check text-success me-2"></i>Total runtime hours</li>
                                <li><i class="fas fa-check text-success me-2"></i>Session counts</li>
                                <li><i class="fas fa-check text-success me-2"></i>Average duration</li>
                                <li><i class="fas fa-check text-success me-2"></i>Voltage readings</li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> This page is not visible in the navigation menu for regular users.
                        Only users with the direct link can access this comprehensive runtime data.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
