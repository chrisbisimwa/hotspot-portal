@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Info boxes -->
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Active Users</span>
                    <span class="info-box-number">
                        150
                        <small>%</small>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-wifi"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sessions</span>
                    <span class="info-box-number">41,410</span>
                </div>
            </div>
        </div>
        
        <div class="clearfix hidden-md-up"></div>
        
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Revenue</span>
                    <span class="info-box-number">$760</span>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Growth</span>
                    <span class="info-box-number">2%</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts row -->
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title">Online Users</h3>
                        <a href="javascript:void(0);">View Report</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex">
                        <p class="d-flex flex-column">
                            <span class="text-bold text-lg">820</span>
                            <span>Users Online</span>
                        </p>
                        <p class="ml-auto d-flex flex-column text-right">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                12.5%
                            </span>
                            <span class="text-muted">Since last week</span>
                        </p>
                    </div>
                    <!-- Chart placeholder -->
                    <div class="position-relative mb-4">
                        <canvas id="users-chart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Recent Activity</h3>
                    <div class="card-tools">
                        <a href="#" class="btn btn-tool btn-sm">
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="#" class="btn btn-tool btn-sm">
                            <i class="fas fa-bars"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="{{ asset('images/user1-128x128.jpg') }}" alt="User" class="img-circle img-size-32 mr-2">
                                    user001
                                </td>
                                <td>
                                    <small class="text-success mr-1">
                                        <i class="fas fa-circle"></i>
                                        Online
                                    </small>
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-clock"></i> 2 min ago
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="{{ asset('images/user2-160x160.jpg') }}" alt="User" class="img-circle img-size-32 mr-2">
                                    user002
                                </td>
                                <td>
                                    <small class="text-warning mr-1">
                                        <i class="fas fa-circle"></i>
                                        Away
                                    </small>
                                </td>
                                <td>
                                    <a href="#" class="text-muted">
                                        <i class="fas fa-clock"></i> 1 hour ago
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TODO: Add Livewire components for real-time data -->
    <!-- TODO: Integrate Chart.js for actual charts -->
    <!-- TODO: Connect to Mikrotik API for real data -->
@endsection

@push('scripts')
<script>
// TODO: Initialize Chart.js charts
// TODO: Add real-time updates with Livewire
</script>
@endpush