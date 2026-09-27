<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - ITSM Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-white flex">
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

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye', 'fa-eye-slash'); }
            else { input.type = 'password'; icon.classList.replace('fa-eye-slash', 'fa-eye'); }
        }
    </script>
</body>
</html>