<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Gestion de Factures</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <style>
        /* ═══════════════════════════════════════════════════════════════════
           PROFESSIONAL MODERN DASHBOARD DESIGN SYSTEM
           Color Palette: Corporate Blue & Teal
           Typography: Inter (system-ui)
           ═══════════════════════════════════════════════════════════════════ */

        :root {
            --primary-color: #1e3a5f;
            --primary-light: #2d5380;
            --primary-dark: #152d48;
            --accent-color: #14B8A6;
            --accent-light: #2dd4bf;
            --accent-hover: #0d9488;
            --bg-primary: #f8fafc;
            --bg-secondary: #f1f5f9;
            --bg-tertiary: #eff6ff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --border-color: #e2e8f0;
            --border-light: #cbd5e1;
            --white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 4px 16px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 12px 32px rgba(15, 23, 42, 0.12);
            --shadow-xl: 0 20px 48px rgba(15, 23, 42, 0.15);

            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color-scheme: light;
            font-size: 15px;
            line-height: 1.5;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-tertiary) 100%);
            color: var(--text-primary);
            display: flex;
        }

        /* ═══════════════ SIDEBAR ═══════════════ */
        .sidebar {
            width: 280px;
            min-width: 280px;
            height: 100vh;
            background: var(--primary-color);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.1), rgba(255,255,255,0));
        }

        .sidebar-header {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            transition: opacity 0.2s ease;
        }

        .brand:hover {
            opacity: 0.9;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary-color);
        }

        .sidebar-nav {
            list-style: none;
            padding: 16px 8px;
            margin: 0;
        }

        .sidebar-nav-group-title {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px 8px;
            margin-top: 8px;
        }

        .sidebar-nav-item {
            margin: 4px 0;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 11px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            position: relative;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .nav-link.active {
            background: var(--accent-color);
            color: var(--primary-color);
            font-weight: 600;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            width: 3px;
            height: 100%;
            background: transparent;
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: auto;
            position: sticky;
            bottom: 0;
            background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.1) 100%);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--accent-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 16px;
        }

        .user-details {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .logout-btn {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
        }

        /* ═══════════════ MAIN CONTENT ═══════════════ */
        .main-container {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ═══════════════ TOP NAVBAR ═══════════════ */
        .navbar {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .navbar-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .navbar-breadcrumb {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .navbar-breadcrumb a {
            color: var(--accent-color);
            text-decoration: none;
        }

        .navbar-breadcrumb a:hover {
            text-decoration: underline;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .navbar-search {
            display: none;
        }

        .search-input {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            color: var(--text-primary);
            width: 240px;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            background: var(--white);
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 20px;
            transition: color 0.2s ease;
            padding: 4px;
        }

        .action-btn:hover {
            color: var(--accent-color);
        }

        .user-menu {
            position: relative;
        }

        .user-menu-trigger {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-light));
            border: none;
            border-radius: 50%;
            color: var(--primary-color);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-menu-trigger:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow-md);
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            margin-top: 8px;
            display: none;
            z-index: 1001;
        }

        .user-dropdown.active {
            display: block;
        }

        .dropdown-item {
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease;
            font-size: 14px;
        }

        .dropdown-item:hover {
            background: var(--bg-secondary);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 4px 0;
        }

        /* ═══════════════ MAIN CONTENT AREA ═══════════════ */
        .content {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .page-actions {
            display: flex;
            gap: 12px;
        }

        /* ═══════════════ ALERTS & MESSAGES ═══════════════ */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #047857;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #dc2626;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            color: #d97706;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .alert-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: inherit;
            padding: 0;
            margin-left: auto;
        }

        /* ═══════════════ RESPONSIVE ═══════════════ */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            .main-container {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 240px;
                z-index: 1100;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-container {
                margin-left: 0;
            }

            .navbar {
                padding: 12px 16px;
            }

            .navbar-search {
                display: none;
            }

            .navbar-title {
                font-size: 16px;
            }

            .content {
                padding: 16px;
            }

            .page-title {
                font-size: 24px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
        }

        /* ═══════════════ SCROLLBAR ═══════════════ */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--border-light);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="{{ route('invoices.index') }}" class="brand">
                <div class="brand-icon">📊</div>
                <span>Invoice Hub</span>
            </a>
        </div>

        <nav class="sidebar-nav">
            <!-- Main Menu -->
            <div class="sidebar-nav-group-title">Menu Principal</div>

            <li class="sidebar-nav-item">
                <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                    <span class="nav-icon">📄</span>
                    <span>Factures</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <span class="nav-icon">👥</span>
                    <span>Clients</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('creances.index') }}" class="nav-link {{ request()->routeIs('creances.*') ? 'active' : '' }}">
                    <span class="nav-icon">💰</span>
                    <span>Créances</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('alertes.index') }}" class="nav-link {{ request()->routeIs('alertes.*') ? 'active' : '' }}">
                    <span class="nav-icon">🔔</span>
                    <span>Alertes</span>
                </a>
            </li>

            <!-- Admin Menu -->
            @if(auth()->user() && auth()->user()->role === 'admin')
            <div class="sidebar-nav-group-title">Administration</div>

            <li class="sidebar-nav-item">
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <span class="nav-icon">🔐</span>
                    <span>Utilisateurs</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('vendeurs.index') }}" class="nav-link {{ request()->routeIs('vendeurs.*') ? 'active' : '' }}">
                    <span class="nav-icon">🏷️</span>
                    <span>Vendeurs</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <span class="nav-icon">📂</span>
                    <span>Catégories</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('objectifs.index') }}" class="nav-link {{ request()->routeIs('objectifs.*') ? 'active' : '' }}">
                    <span class="nav-icon">🎯</span>
                    <span>Objectifs</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('dashboard.vendeurs') }}" class="nav-link {{ request()->routeIs('dashboard.vendeurs') ? 'active' : '' }}">
                    <span class="nav-icon">📈</span>
                    <span>Tableau Vendeurs</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('dashboard.categories') }}" class="nav-link {{ request()->routeIs('dashboard.categories') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    <span>Tableau Catégories</span>
                </a>
            </li>
            @endif
        </nav>

        <!-- SIDEBAR FOOTER -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="user-details">
                    <span class="user-name">{{ auth()->user()->name ?? 'User' }}</span>
                    <span class="user-role">{{ auth()->user()->role ?? 'Member' }}</span>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <span>🚪</span>
                    <span>Déconnexion</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTAINER -->
    <div class="main-container">
        <!-- NAVBAR -->
        <nav class="navbar">
            <div class="navbar-left">
                <button class="action-btn" id="sidebarToggle" style="display: none;">
                    ☰
                </button>
                <h1 class="navbar-title">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="navbar-right">
                <input type="text" class="search-input navbar-search" placeholder="Rechercher...">

                <div class="user-menu">
                    <button class="user-menu-trigger" id="userMenuTrigger">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="#" class="dropdown-item">👤 Profil</a>
                        <a href="#" class="dropdown-item">⚙️ Paramètres</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer;">
                                🚪 Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- CONTENT AREA -->
        <div class="content">
            <!-- FLASH MESSAGES -->
            @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <span>✅</span>
                <span>{{ $message }}</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
            @endif

            @if ($message = Session::get('error'))
            <div class="alert alert-error">
                <span>❌</span>
                <span>{{ $message }}</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
            @endif

            @if ($message = Session::get('warning'))
            <div class="alert alert-warning">
                <span>⚠️</span>
                <span>{{ $message }}</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
            @endif

            @if ($message = Session::get('info'))
            <div class="alert alert-info">
                <span>ℹ️</span>
                <span>{{ $message }}</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
            @endif

            <!-- VALIDATION ERRORS -->
            @if ($errors->any())
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>
                    <strong>Erreurs de validation:</strong>
                    <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            <!-- PAGE CONTENT -->
            @yield('content')
        </div>
    </div>

    <script>
        // Sidebar toggle for mobile
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                sidebarToggle.style.display = 'block';
            } else {
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('active');
            }
        });

        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // User menu dropdown
        const userMenuTrigger = document.getElementById('userMenuTrigger');
        const userDropdown = document.getElementById('userDropdown');

        userMenuTrigger?.addEventListener('click', () => {
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.user-menu')) {
                userDropdown.classList.remove('active');
            }
        });

        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
    @stack('scripts')
</body>
</html>
