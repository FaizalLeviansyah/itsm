<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - ITSM Portal</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="apple-touch-icon" href="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}">
    <meta name="mobile-web-app-capable" content="yes">

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
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
<body class="min-h-screen bg-white flex">
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
    <!-- Left: Branding (Two-tone Blue Gradient) -->
    <div class="hidden lg:flex lg:w-3/5 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900 items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-20 left-20 w-64 h-64 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-20 w-80 h-80 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="relative max-w-xl">
            <!-- Logo Amarin Ship Management -->
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center overflow-hidden shadow-lg">
                    <img src="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}" alt="Amarin Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white leading-tight">Amarin Ship Management</h2>
                    <p class="text-xs text-blue-200">IT Department</p>
                </div>
            </div>

            <h1 class="text-3xl lg:text-4xl font-bold text-white mb-4 leading-tight">
                IT Service Management Portal
            </h1>
            <p class="text-blue-100 text-base leading-relaxed mb-8">
                One platform to manage IT services, support tickets, and assets professionally and measurably. Secure, intelligent, and seamlessly connected.
            </p>

            <!-- Motto R.E.S.P.E.C.T -->
            <div class="pt-2">
                <div class="flex flex-wrap gap-2 mb-3">
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">R</div>
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">E</div>
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">S</div>
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">P</div>
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">E</div>
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">C</div>
                    <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/10 border border-white/20 font-bold text-sm text-white backdrop-blur-sm">T</div>
                </div>
                <p class="text-[11px] text-blue-200/80 font-medium tracking-widest uppercase">
                    Responsible &bull; Ethic &bull; Safety &bull; People &bull; Environment &bull; Care &bull; Trust
                </p>
            </div>
        </div>
    </div>

    <!-- Right: Login Form (Clean White Background) -->
    <div class="flex-2 flex-1 flex items-center justify-center p-6 lg:p-12 bg-white">
        <div class="w-full max-w-sm">
            <div class="lg:hidden text-center mb-8">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-headset text-white text-lg"></i>
                </div>
                <h1 class="text-xl font-bold text-gray-900">ITSM Portal</h1>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h2>
            <p class="text-sm text-gray-500 mb-8">Sign in to your account to continue.</p>

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-400"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Email</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="email@amarinshipmgmt.com">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-11 pr-12 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye text-sm" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition text-sm shadow-md shadow-blue-500/20">
                        Sign In
                    </button>
                </div>
            </form>

            <p class="text-center text-xs text-gray-400 mt-8">
                &copy; {{ date('Y') }} Amarin Ship Management &mdash; IT Department
            </p>
        </div>
    </div>

    <!-- PWA Install Prompt Custom Pop-up Card -->
    <div id="pwa-install-popup-login" style="display: none;" class="fixed top-5 left-1/2 -translate-x-1/2 lg:top-8 lg:right-8 lg:left-auto lg:translate-x-0 z-[100] w-[92vw] max-w-sm">
        <div class="animate-float-smooth bg-white rounded-2xl shadow-2xl border border-gray-100 p-4 flex items-center gap-4 relative overflow-hidden">
            <!-- Dekorasi Gradient -->
            <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-600"></div>
            
            <div class="w-12 h-12 flex-shrink-0 bg-blue-50 rounded-xl flex items-center justify-center p-2 border border-blue-100">
                <img src="{{ asset('storage/companies/OZhBiZbGGW5cbErTTOVLpXHflaJcfZsM8ycrj1Ev.jpg') }}" alt="Amarin Logo" class="w-full h-full object-contain rounded">
            </div>
            <div class="flex-1">
                <h4 class="text-[13px] font-bold text-gray-900 leading-tight">Install Aplikasi ITSM</h4>
                <p class="text-[11px] text-gray-500 mt-1 leading-snug">Dapatkan pengalaman lebih cepat dan akses langsung dari layar utama perangkat Anda.</p>
            </div>
            <div class="flex flex-col gap-2">
                <button id="btn-install-login" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-md shadow-blue-500/30">
                    Install
                </button>
                <button id="btn-close-login" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[11px] font-medium rounded-lg transition-colors">
                    Nanti
                </button>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye', 'fa-eye-slash'); }
            else { input.type = 'password'; icon.classList.replace('fa-eye-slash', 'fa-eye'); }
        }

        // PWA Install Prompt Listener dengan Custom Animasi Card
        let deferredPromptLogin;
        const pwaPopupLogin = document.getElementById('pwa-install-popup-login');
        const btnInstallLogin = document.getElementById('btn-install-login');
        const btnCloseLogin = document.getElementById('btn-close-login');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPromptLogin = e;
            if (pwaPopupLogin) {
                pwaPopupLogin.style.display = 'block';
            }
        });

        if (btnInstallLogin) {
            btnInstallLogin.addEventListener('click', async () => {
                if (!deferredPromptLogin) return;
                deferredPromptLogin.prompt();
                const { outcome } = await deferredPromptLogin.userChoice;
                if (outcome === 'accepted') {
                    console.log('PWA berhasil diinstal dari halaman Login');
                }
                deferredPromptLogin = null;
                pwaPopupLogin.style.display = 'none';
            });
        }

        if (btnCloseLogin) {
            btnCloseLogin.addEventListener('click', () => {
                pwaPopupLogin.style.display = 'none';
            });
        }
    </script>
</body>
</html>