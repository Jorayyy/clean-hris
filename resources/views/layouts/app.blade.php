<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $systemSettings->app_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-dark: #1e293b;
            --secondary-dark: #334155;
            --accent-color: #3b82f6;
        }
        body { background-color: #f1f5f9; min-height: 100vh; display: flex; flex-direction: column; }
        .sidebar { width: var(--sidebar-width); background: var(--primary-dark); color: #cbd5e1; height: 100vh; position: fixed; left: 0; top: 0; transition: transform 0.3s ease, left 0.3s ease; z-index: 1000; overflow-y: auto; will-change: transform; }
        .sidebar-header { padding: 1.5rem; background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1.5rem; color: #cbd5e1; text-decoration: none; transition: all 0.2s; border-left: 4px solid transparent; font-size: 0.9rem; font-weight: 500; }
        .sidebar-link:hover { background: var(--secondary-dark); color: #fff; }
        .sidebar-link.active { background: var(--secondary-dark); color: #fff; border-left-color: var(--accent-color); }
        .sidebar-link i { font-size: 1.1rem; width: 25px; margin-right: 10px; }
        .main-content { margin-left: var(--sidebar-width); flex: 1; transition: all 0.3s; }
        .top-navbar { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 0.75rem 1.5rem; }
        
        /* Mobile Sidebar Overlay */
        .sidebar-overlay { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100vw; 
            height: 100vh; 
            background: rgba(0,0,0,0.5); 
            z-index: 999; 
        }
        
        @media (max-width: 991.98px) {
            .sidebar { left: 0 !important; transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); box-shadow: 10px 0 30px rgba(0,0,0,0.2); }
            .sidebar-overlay.show { display: block; }
            .main-content { margin-left: 0 !important; }
        }

        @media (max-width: 767.98px) {
            .top-navbar {
                padding: 0.75rem 1rem;
                gap: 0.75rem;
                flex-wrap: wrap;
            }

            .top-navbar .d-none.d-lg-block {
                display: none !important;
            }

            .top-navbar .dropdown {
                margin-left: auto;
            }

            .main-content > .p-4 {
                padding: 1rem !important;
            }

            .container-fluid.px-4.py-4 {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .sidebar-header {
                padding: 1rem 1.25rem;
            }

            .sidebar-link {
                padding: 0.85rem 1.25rem;
                font-size: 0.95rem;
            }

            .nav-category {
                padding: 1rem 1.25rem 0.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .top-navbar {
                padding: 0.65rem 0.85rem;
            }

            .top-navbar .dropdown-toggle span {
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .main-content > .p-4 {
                padding: 0.85rem !important;
            }
        }
        .logo-img { height: 35px; border-radius: 4px; }
        .nav-category { padding: 1.2rem 1.5rem 0.5rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; }

        @media print {
            .sidebar, .top-navbar, .btn, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            body { background: white !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
    @auth
    @php
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $isAdmin = $currentUser->isAdmin();
        $isEmployee = $currentUser->isEmployee();
    @endphp
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between gap-2">
            @if($systemSettings->app_logo)
                @php 
                    $logoPath = str_starts_with($systemSettings->app_logo, 'logos/') 
                                ? $systemSettings->app_logo 
                                : 'logos/' . $systemSettings->app_logo;
                @endphp
                <img src="/{{ $logoPath }}" alt="Logo" class="logo-img me-2">
            @endif
            <span class="fw-bold text-white text-truncate">{{ $systemSettings->app_name }}</span>
            <button type="button" class="btn btn-sm btn-outline-light d-lg-none ms-auto" onclick="toggleSidebar()" aria-label="Close sidebar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <div class="py-3">
            @if($isSuperAdmin)
                <div class="nav-category">Main Menu</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                
                <div class="nav-category">Management</div>
                <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Employees
                </a>
                <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Attendance
                </a>
                <a href="{{ route('schedules.index') }}" class="sidebar-link {{ request()->routeIs('schedules.*') || request()->routeIs('admin.settings.sites.*') || request()->routeIs('admin.settings.schedule-groups.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> Schedules & Plotting
                </a>

                <div class="nav-category">Payroll & Finance</div>
                <a href="{{ route('payroll.index') }}" class="sidebar-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i> Payroll
                </a>
                <a href="{{ route('admin.dtrs.index') }}" class="sidebar-link {{ request()->routeIs('admin.dtrs.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> DTR Logs
                </a>
                <a href="{{ route('salaries.index') }}" class="sidebar-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i> Salaries History
                </a>
                
                <div class="nav-category">Structure & Settings</div>
                <a href="{{ route('sites.index') }}" class="sidebar-link {{ request()->routeIs('sites.index') || (request()->routeIs('sites.*') && !request()->routeIs('admin.settings.sites.*') && !request()->routeIs('admin.settings.schedule-groups.*')) ? 'active' : '' }}">
                    <i class="bi bi-geo-alt"></i> Sites
                </a>
                <a href="{{ route('payroll-groups.index') }}" class="sidebar-link {{ request()->routeIs('payroll-groups.*') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i> Payroll Groups
                </a>
                <a href="{{ route('admin.designations.index') }}" class="sidebar-link {{ request()->routeIs('admin.designations.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> BPO Designations
                </a>
                <a href="{{ route('authorized-networks.index') }}" class="sidebar-link {{ request()->routeIs('authorized-networks.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Authorized IP
                </a>
                <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> System Settings
                </a>
                
                <div class="nav-category">System Admin</div>
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-lock"></i> User Management
                </a>
                <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i> Roles & Permissions
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-eye"></i> Audit Logs
                </a>
                <a href="{{ route('admin.queue-monitor.index') }}" class="sidebar-link {{ request()->routeIs('admin.queue-monitor.*') ? 'active' : '' }}">
                    <i class="bi bi-activity"></i> System Health
                </a>
                <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('admin.tickets.index') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i> Transactions
                </a>

            @elseif($isAdmin)
                <div class="nav-category">Main Menu</div>
                <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                @if(Auth::user()->hasRole('HR Admin'))
                    <div class="nav-category">Management</div>
                    <a href="{{ route('employees.index') }}" class="sidebar-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Employees
                    </a>
                    <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Attendance
                    </a>
                    
                    <div class="nav-category">DTR & Records</div>
                    <a href="{{ route('admin.dtrs.index') }}" class="sidebar-link {{ request()->routeIs('admin.dtrs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> DTR Logs
                    </a>

                    <div class="nav-category">Support</div>
                    <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('admin.tickets.index') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots"></i> Transactions
                    </a>
                @elseif(Auth::user()->hasRole('Accounting Admin'))
                    <div class="nav-category">Payroll & Finance</div>
                    <a href="{{ route('payroll.index') }}" class="sidebar-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i> Payroll
                    </a>
                    <a href="{{ route('admin.dtrs.index') }}" class="sidebar-link {{ request()->routeIs('admin.dtrs.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> DTR Logs
                    </a>
                    <a href="{{ route('salaries.index') }}" class="sidebar-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow"></i> Salaries History
                    </a>
                    
                    <div class="nav-category">Management</div>
                    <a href="{{ route('attendance.index') }}" class="sidebar-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Attendance
                    </a>

                    <div class="nav-category">Support</div>
                    <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('admin.tickets.index') ? 'active' : '' }}">
                        <i class="bi bi-chat-dots"></i> Transactions
                    </a>
                @endif
            @else
                <div class="nav-category">Employee Portal</div>
                <a href="{{ route('employee.dashboard') }}" class="sidebar-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <a href="{{ route('employee.dtr.index') }}" class="sidebar-link {{ request()->routeIs('employee.dtr.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> DTR Record
                </a>
                <a href="{{ route('employee.attendance') }}" class="sidebar-link {{ request()->routeIs('employee.attendance') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Attendance Calendar
                </a>
                <a href="{{ route('employee.schedule') }}" class="sidebar-link {{ request()->routeIs('employee.schedule') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i> Duty Schedule
                </a>
                <a href="{{ route('employee.tickets.index') }}" class="sidebar-link {{ request()->routeIs('employee.tickets.*') ? 'active' : '' }}">
                    <i class="bi bi-chat-dots"></i> Transactions
                </a>
            @endif
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center justify-content-between shadow-sm sticky-top">
            <button class="btn btn-sm btn-light d-lg-none" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="d-none d-lg-block">
                <span class="text-muted small fw-medium text-uppercase">
                    Welcome back, <span class="text-dark fw-bold">{{ Auth::user()->email }}</span>
                </span>
            </div>
            <div class="dropdown">
                <a class="nav-link dropdown-toggle fw-bold text-dark d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="d-none d-sm-inline">{{ Auth::user()->email }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                        <div class="small text-muted text-uppercase" style="font-size: 0.7rem;">{{ Auth::user()->email }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ $isAdmin ? route('admin.profile') : route('employee.profile') }}">
                            <i class="bi bi-person-circle me-2"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <button type="submit" form="logout-form" class="dropdown-item py-2 text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="p-4 pt-4">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm mb-4">{{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
    @else
        <div class="container mt-5">
            @yield('content')
        </div>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        }

        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            if (window.innerWidth < 992) {
                sidebar.classList.remove('show');
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) overlay.classList.remove('show');
            }
            
            // 1. Restore the scroll position when the page loads
            const scrollPos = localStorage.getItem('sidebar-scroll');
            if (scrollPos) {
                sidebar.scrollTop = scrollPos;
            }

            // 2. Save the scroll position whenever the user scrolls
            sidebar.addEventListener('scroll', function() {
                localStorage.setItem('sidebar-scroll', sidebar.scrollTop);
            });

            // 3. Reset scroll if clicking on the Logo
            const sidebarHeader = sidebar.querySelector('.sidebar-header');
            if (sidebarHeader) {
                sidebarHeader.addEventListener('click', function() {
                    localStorage.setItem('sidebar-scroll', 0);
                });
            }

            // 4. Close sidebar when clicking a link (Mobile only)
            const sidebarLinks = sidebar.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 992) {
                        toggleSidebar();
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
