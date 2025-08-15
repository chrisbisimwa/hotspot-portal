<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('images/logo.png') }}" alt="HotspotPortal Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">HotspotPortal</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('images/default-avatar.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name ?? 'Administrator' }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- User Management -->
                <li class="nav-item {{ request()->is('admin/users*') || request()->is('admin/profiles*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/users*') || request()->is('admin/profiles*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            User Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/profiles*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Profils</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Hotspot Utilisateurs</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sessions & Monitoring -->
                <li class="nav-item {{ request()->is('admin/sessions*') || request()->is('admin/monitoring*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/sessions*') || request()->is('admin/monitoring*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-wifi"></i>
                        <p>
                            Hotspot Management
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/sessions*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Sessions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/monitoring*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Monitoring</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Billing & Payments -->
                <li class="nav-item {{ request()->is('admin/orders*') || request()->is('admin/payments*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/orders*') || request()->is('admin/payments*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-credit-card"></i>
                        <p>
                            Billing
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Commandes</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/payments*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Paiements</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Notifications -->
                <li class="nav-item">
                    <a href="#" class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>Notifications</p>
                    </a>
                </li>

                <!-- System -->
                <li class="nav-item {{ request()->is('admin/logs*') || request()->is('admin/settings*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/logs*') || request()->is('admin/settings*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Système
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/logs*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Logs</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Paramètres</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>