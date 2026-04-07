<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRIS Payroll System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ Auth::check() ? (Auth::user()->role == 'admin' ? route('admin.dashboard') : route('employee.dashboard')) : '/login' }}">HRIS Payroll</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                @auth
                    <ul class="navbar-nav me-auto text-uppercase small">
                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employees.*') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('employees.index') }}">Employees</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('payroll-groups.*') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('payroll-groups.index') }}">Groups</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('schedules.*') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('schedules.index') }}">Schedules</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('attendance.*') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('attendance.index') }}">Attendance</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('payroll.*') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('payroll.index') }}">Payroll</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('salaries.*') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('salaries.index') }}">Salaries History</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active fw-bold border-bottom text-warning' : 'text-warning' }}" href="{{ route('admin.tickets.index') }}">Support Tickets</a></li>
                        @else
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('employee.dashboard') }}">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employee.attendance') ? 'active fw-bold border-bottom' : '' }}" href="{{ route('employee.attendance') }}">DTR</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('employee.tickets.*') ? 'active fw-bold border-bottom text-info' : 'text-info' }}" href="{{ route('employee.tickets.index') }}">Tickets</a></li>
                        @endif
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
