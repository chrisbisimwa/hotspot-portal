@props(['role' => 'user'])

<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        @if($role === 'admin' || auth()->user()->hasRole('admin'))
            <!-- Admin Menu Items -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            
            <li class="nav-header">MANAGEMENT</li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shopping-cart"></i>
                    <p>Orders</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-credit-card"></i>
                    <p>Payments</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('admin.hotspot-users.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-wifi"></i>
                    <p>Hotspot Users</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clock"></i>
                    <p>Sessions</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('admin.profiles.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-users"></i>
                    <p>User Profiles</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-bell"></i>
                    <p>Notifications</p>
                </a>
            </li>
            
            <li class="nav-header">MONITORING</li>
            
            <li class="nav-item">
                <a href="{{ route('admin.monitoring.metrics') }}" class="nav-link {{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <p>Metrics</p>
                </a>
            </li>
        
        @else
            <!-- User Menu Items -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>
            
            <li class="nav-header">MY ACCOUNT</li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('user.profiles.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user"></i>
                    <p>My Profiles</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('user.orders.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-shopping-cart"></i>
                    <p>My Orders</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('user.payments.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-credit-card"></i>
                    <p>My Payments</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('user.hotspot-users.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-wifi"></i>
                    <p>My Hotspot Users</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('user.sessions.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clock"></i>
                    <p>My Sessions</p>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link {{ request()->routeIs('user.notifications.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-bell"></i>
                    <p>My Notifications</p>
                </a>
            </li>
        @endif
        
        <li class="nav-header">SETTINGS</li>
        
        <li class="nav-item">
            <a href="{{ route('settings.profile') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cog"></i>
                <p>Settings</p>
            </a>
        </li>
    </ul>
</nav>