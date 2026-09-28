<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ITSM Portal</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="apple-touch-icon" href="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}">
    <meta name="mobile-web-app-capable" content="yes">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('PWA ServiceWorker registered');
                }).catch(function(err) {
                    console.log('PWA ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#2563eb',600:'#1d4ed8',700:'#1e40af',800:'#1e3a8a',900:'#172554' },
                    },
                    animation: {
                        'wave': 'wave 8s ease-in-out infinite',
                        'wave-slow': 'wave 12s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.4s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                    },
                    keyframes: {
                        wave: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-5px)' } },
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        slideUp: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { display: flex; align-items: center; padding: 10px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; color: #64748b; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); gap: 12px; }
        .sidebar-link:hover { color: #1e293b; background: #f8fafc; transform: translateX(2px); }
        .sidebar-link.active { color: #2563eb; background: #eff6ff; border-left: 3px solid #2563eb; margin-left: -3px; }
        .sidebar-link.active i { color: #2563eb; }
        .stat-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px -5px rgba(0,0,0,0.08); }
        .table-row { transition: all 0.15s ease; }
        .table-row:hover { background: #f8fafc; transform: scale(1.001); }
        .btn-wave { position: relative; overflow: hidden; }
        .btn-wave::after { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255,255,255,0.2); border-radius: 50%; transform: translate(-50%, -50%); transition: width 0.6s ease, height 0.6s ease; }
        .btn-wave:active::after { width: 300px; height: 300px; }
        .card-enter { animation: slideUp 0.3s ease-out forwards; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        @keyframes ripple { to { transform: scale(4); opacity: 0; } }
        
        /* Animasi Pop-up Loncat Halus */
        @keyframes float-smooth {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .animate-float-smooth {
            animation: float-smooth 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full bg-[#f8fafc]">
    <!-- Global Preloader Splash Screen (Animasi Loncat, Bold, & Ukuran Besar) -->
    <div id="itsm-preloader" class="fixed inset-0 z-[9999] bg-[#f8fafc] flex flex-col items-center justify-center transition-opacity duration-700">
        <!-- Animasi Cincin & Logo -->
        <div class="relative flex items-center justify-center mb-6">
            <!-- Outer Spinning Ring -->
            <div class="absolute w-36 h-36 border-4 border-transparent border-t-blue-600 border-b-blue-600 rounded-full animate-spin"></div>
            <!-- Inner Spinning Ring (Reverse) -->
            <div class="absolute w-28 h-28 border-4 border-transparent border-l-blue-400 border-r-blue-400 rounded-full animate-[spin_1.5s_reverse_infinite]"></div>
            
            <!-- Logo Amarin -->
            <div class="w-18 h-18 w-20 h-20 bg-white rounded-2xl flex items-center justify-center overflow-hidden shadow-xl z-10 p-2 border border-blue-100">
                <img src="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}" alt="Amarin Logo" class="w-full h-full object-contain">
            </div>
        </div>
        
        <!-- Kontainer Teks dengan Efek Loncat (Slide Up) & Bold Besar -->
        <div class="text-center px-6 max-w-lg">
            <!-- Teks Utama: ITSM (Sangat Bold & Besar) -->
            <h2 class="text-4xl font-black text-gray-900 tracking-wider mb-2 animate-[slideUp_0.6s_cubic-bezier(0.16,1,0.3,1)_0.2s_both]">
                ITSM PORTAL
            </h2>
            
            <!-- Kepanjangan (Bold & Lebih Jelas) -->
            <p class="text-xs font-extrabold text-blue-600 tracking-[0.25em] uppercase mb-4 animate-[slideUp_0.6s_cubic-bezier(0.16,1,0.3,1)_0.7s_both]">
                Information Technology Service Management
            </p>
            
            <!-- Definisi & Motto Bahasa Indonesia (Loncat Bertahap, Besar & Bold) -->
            <div class="space-y-1.5 overflow-hidden">
                <p class="text-sm font-bold text-gray-800 tracking-wide animate-[slideUp_0.6s_cubic-bezier(0.16,1,0.3,1)_1.3s_both]">
                    &ldquo;Optimalisasi Layanan & Infrastruktur Digital Armada&rdquo;
                </p>
                <p class="text-xs font-bold text-blue-700 tracking-wide animate-[slideUp_0.6s_cubic-bezier(0.16,1,0.3,1)_1.9s_both]">
                    Connecting Vessels, Securing Data, Reliable Support, Secure Operations.
                </p>
            </div>
        </div>
    </div>

    <!-- Script Penghilang Preloader (~4.5 Detik) -->
    <script>
        window.addEventListener('load', function() {
            const preloader = document.getElementById('itsm-preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 700);
                }, 4000);
            }
        });
    </script>
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <aside class="hidden lg:flex lg:flex-col w-[240px] bg-white border-r border-gray-200 fixed inset-y-0 z-30">
            <div class="h-16 flex items-center px-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center overflow-hidden shadow-sm border border-gray-100">
                        <img src="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}" alt="Amarin Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 leading-none">ITSM Portal</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Service Management</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large w-5 text-center"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="sidebar-link {{ request()->routeIs('tickets.*') && !request()->get('view') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt w-5 text-center"></i><span>All Tickets</span>
                </a>
                <a href="{{ route('tickets.index', ['view' => 'mine']) }}" class="sidebar-link {{ request()->get('view') === 'mine' ? 'active' : '' }}">
                    <i class="fas fa-user-check w-5 text-center"></i><span>My Tickets</span>
                </a>
                <a href="{{ route('assets.index') }}" class="sidebar-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                    <i class="fas fa-server w-5 text-center"></i><span>Assets</span>
                </a>
                <a href="{{ route('audits.index') }}" class="sidebar-link {{ request()->routeIs('audits.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-5 text-center"></i><span>Audit</span>
                </a>
                <a href="{{ route('knowledge.index') }}" class="sidebar-link {{ request()->routeIs('knowledge.*') ? 'active' : '' }}">
                    <i class="fas fa-book-open w-5 text-center"></i><span>Knowledge Base</span>
                </a>
                <a href="{{ route('services.index') }}" class="sidebar-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <i class="fas fa-concierge-bell w-5 text-center"></i><span>Service Catalog</span>
                </a>
                @can('viewReports')
                <div class="pt-3 mt-3 border-t border-gray-100">
                    <p class="px-4 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Analytics</p>
                </div>
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-5 text-center"></i><span>Reports</span>
                </a>
                <a href="{{ route('problems.index') }}" class="sidebar-link {{ request()->routeIs('problems.*') ? 'active' : '' }}">
                    <i class="fas fa-bug w-5 text-center"></i><span>Problems</span>
                </a>
                <a href="{{ route('vessels.index') }}" class="sidebar-link {{ request()->routeIs('vessels.*') ? 'active' : '' }}">
                    <i class="fas fa-ship w-5 text-center"></i><span>Location Overview</span>
                </a>
                @endcan
                @can('manageSettings')
                <div class="pt-3 mt-3 border-t border-gray-100">
                    <p class="px-4 text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Administration</p>
                </div>
                <a href="{{ route('approvals.index') }}" class="sidebar-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                    <i class="fas fa-check-double w-5 text-center"></i><span>Approvals</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <i class="fas fa-cog w-5 text-center"></i><span>Settings</span>
                </a>
                @endcan
            </nav>

            <!-- Tombol Log Out -->
            <div class="p-4 border-t border-gray-100 mt-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition">
                        <i class="fas fa-sign-out-alt text-base"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>

            <div class="p-4 border-t border-gray-100">
                <a href="{{ route('tickets.create') }}" class="btn-wave flex items-center justify-center gap-2 w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold py-3 rounded-lg transition-all hover:shadow-lg hover:shadow-brand-500/25">
                    <i class="fas fa-plus text-xs"></i> Create Ticket
                </a>
            </div>
        </aside>

        <!-- Main -->
        <main class="lg:pl-[240px] flex-1 min-h-screen">
            <header class="bg-white/80 backdrop-blur-sm border-b border-gray-200 h-16 flex items-center justify-between px-6 sticky top-0 z-20">
                <div class="flex items-center gap-4 flex-1">
                    <button id="mobile-menu-btn" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <div class="hidden sm:flex items-center flex-1 max-w-md">
                        <form action="{{ route('search') }}" method="GET" class="relative w-full">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search tickets, assets, or users..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                        </form>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('notifications.index') }}" class="relative w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 transition text-gray-500">
                        <i class="fas fa-bell text-[15px]"></i>
                        @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center animate-pulse">{{ Auth::user()->unreadNotifications->count() > 9 ? '9+' : Auth::user()->unreadNotifications->count() }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 pl-3 border-l border-gray-200 ml-1 hover:opacity-80 transition">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-gray-800 leading-none">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">{{ Auth::user()->position ?? ucfirst(Auth::user()->role) }}</p>
                        </div>
                        <div class="w-9 h-9 bg-brand-100 rounded-full flex items-center justify-center">
                            <span class="text-brand-700 font-semibold text-xs">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                        </div>
                    </a>
                </div>
            </header>

            <div class="p-6 lg:p-8 animate-fade-in">
                @if(session('success'))
                <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center text-sm animate-slide-up" id="alert-success">
                    <i class="fas fa-check-circle mr-3 text-green-500"></i>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button onclick="this.parentElement.style.opacity='0';setTimeout(()=>this.parentElement.remove(),300)" class="text-green-400 hover:text-green-600 ml-3 transition"><i class="fas fa-times"></i></button>
                </div>
                @endif
                @if(session('error'))
                <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center text-sm animate-slide-up" id="alert-error">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button onclick="this.parentElement.style.opacity='0';setTimeout(()=>this.parentElement.remove(),300)" class="text-red-400 hover:text-red-600 ml-3 transition"><i class="fas fa-times"></i></button>
                </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="fixed inset-0 z-50 hidden lg:hidden">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="document.getElementById('mobile-sidebar').classList.add('hidden')"></div>
        <aside class="relative w-[260px] bg-white h-full shadow-2xl overflow-y-auto animate-slide-up">
            <div class="h-16 flex items-center px-5 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center overflow-hidden shadow-sm border border-gray-100">
                        <img src="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}" alt="Amarin Logo" class="w-full h-full object-cover">
                    </div>
                    <p class="text-sm font-bold text-gray-900">ITSM Portal</p>
                </div>
            </div>
            <nav class="px-3 py-5 space-y-1">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="fas fa-th-large w-5 text-center"></i><span>Dashboard</span></a>
                <a href="{{ route('tickets.index') }}" class="sidebar-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}"><i class="fas fa-ticket-alt w-5 text-center"></i><span>All Tickets</span></a>
                <a href="{{ route('assets.index') }}" class="sidebar-link {{ request()->routeIs('assets.*') ? 'active' : '' }}"><i class="fas fa-server w-5 text-center"></i><span>Assets</span></a>
                <a href="{{ route('knowledge.index') }}" class="sidebar-link {{ request()->routeIs('knowledge.*') ? 'active' : '' }}"><i class="fas fa-book-open w-5 text-center"></i><span>Knowledge Base</span></a>
                @can('viewReports')
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="fas fa-chart-line w-5 text-center"></i><span>Reports</span></a>
                @endcan
                @can('manageSettings')
                <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"><i class="fas fa-cog w-5 text-center"></i><span>Settings</span></a>
                @endcan
            </nav>
            <div class="p-4">
                <a href="{{ route('tickets.create') }}" class="flex items-center justify-center gap-2 w-full bg-brand-500 text-white text-sm font-semibold py-3 rounded-lg">
                    <i class="fas fa-plus text-xs"></i> Create Ticket
                </a>
            </div>
        </aside>
    </div>

    <!-- PWA Install Prompt Custom Pop-up Card -->
    <div id="pwa-install-popup-app" style="display: none;" class="fixed top-5 left-1/2 -translate-x-1/2 lg:left-[calc(50%+120px)] z-[100] w-[92vw] max-w-sm">
        <div class="animate-float-smooth bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 flex items-center gap-4 relative overflow-hidden">
            <!-- Dekorasi Gradient -->
            <div class="absolute top-0 left-0 w-1.5 h-full bg-brand-500"></div>
            
            <div class="w-12 h-12 flex-shrink-0 bg-brand-50 rounded-xl flex items-center justify-center p-2 border border-brand-100">
                <img src="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}" alt="Amarin Logo" class="w-full h-full object-contain rounded">
            </div>
            <div class="flex-1">
                <h4 class="text-[13px] font-bold text-gray-900 leading-tight">Install ITSM Portal</h4>
                <p class="text-[11px] text-gray-500 mt-1 leading-snug">Akses lebih cepat, ringan, dan notifikasi real-time langsung dari Home Screen Anda.</p>
            </div>
            <div class="flex flex-col gap-2">
                <button id="btn-install-app" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-md shadow-brand-500/30">
                    Install
                </button>
                <button id="btn-close-app" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[11px] font-medium rounded-lg transition-colors">
                    Nanti
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* Select2 custom theme to match Tailwind design */
        .select2-container--default .select2-selection--single {
            height: auto !important;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            line-height: 1.5;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #111827;
            line-height: 1.5;
            padding: 0;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 0;
            right: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #9ca3af;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        .select2-dropdown {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            z-index: 9999;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.375rem 0.625rem;
            font-size: 0.8125rem;
            outline: none;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
        }
        .select2-container--default .select2-results__option {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb;
            color: #fff;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.25rem 0.5rem;
            min-height: 42px;
            font-size: 0.875rem;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            border-radius: 0.375rem;
            padding: 1px 6px;
            font-size: 0.75rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #60a5fa;
            margin-right: 4px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #1d4ed8;
        }
        /* Fix width inside modals */
        .select2-container { width: 100% !important; }
    </style>
    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-sidebar').classList.toggle('hidden');
        });
        // Auto-dismiss alerts after 5s
        setTimeout(() => {
            document.querySelectorAll('[id^="alert-"]').forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s';
                setTimeout(() => el.remove(), 500);
            });
        }, 5000);
    </script>
    <script>
        // Global Select2 auto-init
        // Excludes: .no-select2, selects with onchange attribute (filter dropdowns), selects inside audit scan forms
        function initSelect2(context) {
            $(context || document).find('select').not('.no-select2').not('[onchange]').not('.select2-hidden-accessible').each(function() {
                var $el = $(this);
                var isMultiple = $el.prop('multiple');
                var placeholder = $el.find('option[value=""]').first().text() || 'Pilih...';
                $el.select2({
                    placeholder: placeholder,
                    allowClear: !$el.prop('required'),
                    width: '100%',
                    dropdownAutoWidth: false,
                    minimumResultsForSearch: isMultiple ? 0 : 6,
                });
            });
        }
        $(document).ready(function() {
            initSelect2();

            // Re-init when modals become visible
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    if (m.type === 'attributes' && m.attributeName === 'class') {
                        var el = m.target;
                        if (!el.classList.contains('hidden')) {
                            initSelect2(el);
                        }
                    }
                });
            });
            // Watch all elements with "modal" in id (case-insensitive via filter)
            document.querySelectorAll('[id]').forEach(function(el) {
                if (/modal/i.test(el.id)) {
                    observer.observe(el, { attributes: true });
                }
            });
        });
    </script>
    <script>
        // PWA Install Prompt Listener dengan Custom Animasi Card
        let deferredPromptApp;
        const pwaPopupApp = document.getElementById('pwa-install-popup-app');
        const btnInstallApp = document.getElementById('btn-install-app');
        const btnCloseApp = document.getElementById('btn-close-app');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPromptApp = e;
            if (pwaPopupApp) {
                pwaPopupApp.style.display = 'block';
            }
        });

        if (btnInstallApp) {
            btnInstallApp.addEventListener('click', async () => {
                if (!deferredPromptApp) return;
                deferredPromptApp.prompt();
                const { outcome } = await deferredPromptApp.userChoice;
                if (outcome === 'accepted') {
                    console.log('PWA berhasil diinstal dari Dashboard');
                }
                deferredPromptApp = null;
                pwaPopupApp.style.display = 'none';
            });
        }

        if (btnCloseApp) {
            btnCloseApp.addEventListener('click', () => {
                pwaPopupApp.style.display = 'none';
            });
        }
    </script>
    @stack('scripts')
</body>
</html>