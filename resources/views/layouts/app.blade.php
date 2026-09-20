<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title ?? 'NZ Group' }} | VFMS ফ্লিট পোর্টাল</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Hind Siliguri', 'Figtree', 'sans-serif'],
                        },
                        colors: {
                            primary: {
                                50: '#fff7ed',
                                100: '#ffedd5',
                                200: '#fed7aa',
                                500: '#f97316',
                                600: '#ea580c',
                                700: '#c2410c',
                                800: '#9a3412',
                                900: '#7c2d12',
                            }
                        }
                    }
                }
            }
        </script>

        @livewireStyles
    </head>
    <body class="h-full flex flex-col font-sans text-slate-800 antialiased bg-slate-50">
        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Brand & Logo -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ url('/') }}" class="flex items-center space-x-2">
                            <span class="w-10 h-10 rounded-xl bg-orange-600 flex items-center justify-center text-white font-bold text-xl shadow-md shadow-orange-200">
                                🚚
                            </span>
                            <div>
                                <span class="text-lg font-bold text-slate-900 block leading-tight">VFMS</span>
                                <span class="text-xs text-orange-700 font-semibold block leading-tight">
                                    {{ app()->getLocale() === 'bn' ? 'ফ্লিট পোর্টাল' : 'Fleet Portal' }}
                                </span>
                            </div>
                        </a>

                        <!-- Navigation Links -->
                        <nav class="hidden md:flex space-x-1 pl-6">
                            @php
                                $currentUser = auth()->user();
                                $isDriver = $currentUser?->isDriver();
                                $isGuard = $currentUser?->isSecurityGuard();
                                $isAdminOrTransport = $currentUser?->isAdmin() || $currentUser?->isTransportOfficer();
                            @endphp

                            @if(!$isGuard)
                                <a href="{{ route('portal.requests') }}" 
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.requests') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    📝 {{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট' : 'Trip Requests' }}
                                </a>
                            @endif

                            @if($isGuard || $isAdminOrTransport)
                                <a href="{{ route('portal.gate-pass') }}" 
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.gate-pass') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    🛡️ {{ app()->getLocale() === 'bn' ? 'গেট পাস টার্মিনাল' : 'Security Gate-Pass' }}
                                </a>
                            @endif

                            @if($isDriver || $isAdminOrTransport)
                                <a href="{{ route('portal.driver') }}" 
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.driver') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    🛞 {{ app()->getLocale() === 'bn' ? 'ড্রাইভার পোর্টাল' : 'Driver Portal' }}
                                </a>
                            @endif

                            @if($isAdminOrTransport)
                                <a href="{{ route('portal.mobile') }}" 
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.mobile') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    📱 {{ app()->getLocale() === 'bn' ? 'মোবাইল টার্মিনাল' : 'Mobile App' }}
                                </a>
                            @endif
                        </nav>
                    </div>

                    <!-- Right Controls: Language Switcher, User Status & Logout -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <!-- Language Toggle -->
                        <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-100 text-xs font-semibold">
                            <a href="{{ url('/locale/bn') }}" 
                               class="px-2.5 py-1 rounded-md transition {{ app()->getLocale() === 'bn' ? 'bg-white text-orange-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                বাংলা
                            </a>
                            <a href="{{ url('/locale/en') }}" 
                               class="px-2.5 py-1 rounded-md transition {{ app()->getLocale() === 'en' ? 'bg-white text-orange-700 shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                                English
                            </a>
                        </div>

                        @auth
                            <!-- Logged In User Pill -->
                            <div class="hidden lg:flex items-center gap-2 pl-2 border-l border-slate-200 text-xs">
                                <span class="w-7 h-7 rounded-full bg-slate-900 text-white font-bold flex items-center justify-center">
                                    {{ auth()->user()->initials() }}
                                </span>
                                <div>
                                    <div class="font-bold text-slate-800 leading-tight truncate max-w-[130px]">{{ auth()->user()->name }}</div>
                                    <div class="text-[10px] font-mono text-orange-600 font-bold">{{ auth()->user()->role }}</div>
                                </div>
                            </div>

                            @if(auth()->user()->isAdmin() || auth()->user()->isTransportOfficer())
                                <a href="{{ url('/admin') }}" 
                                   class="hidden sm:inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-bold rounded-lg text-white bg-slate-900 hover:bg-orange-600 transition shadow-xs">
                                    ⚙️ {{ app()->getLocale() === 'bn' ? 'অ্যাডমিন' : 'Admin' }}
                                </a>
                            @endif

                            <a href="{{ route('logout') }}"
                               class="hidden sm:inline-flex items-center px-2.5 py-1.5 border border-slate-200 text-xs font-bold rounded-lg text-slate-600 hover:text-red-700 hover:bg-red-50 transition">
                                🚪 {{ app()->getLocale() === 'bn' ? 'লগআউট' : 'Logout' }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center px-3.5 py-1.5 border border-transparent text-xs font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 transition shadow-xs">
                                🔑 {{ app()->getLocale() === 'bn' ? 'লগইন' : 'Field Login' }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Strip (Role-Filtered) -->
            <div class="md:hidden border-t border-slate-100 bg-white px-2 py-1.5 flex justify-around text-xs font-semibold shadow-inner">
                @php
                    $currentUser = auth()->user();
                    $isDriver = $currentUser?->isDriver();
                    $isGuard = $currentUser?->isSecurityGuard();
                    $isAdminOrTransport = $currentUser?->isAdmin() || $currentUser?->isTransportOfficer();
                @endphp

                @if(!$isGuard)
                    <a href="{{ route('portal.requests') }}" class="flex flex-col items-center py-1 px-2 rounded-lg {{ request()->routeIs('portal.requests') ? 'text-orange-700 font-bold bg-orange-50' : 'text-slate-600' }}">
                        <span class="text-base">📝</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট' : 'Requests' }}</span>
                    </a>
                @endif

                @if($isGuard || $isAdminOrTransport)
                    <a href="{{ route('portal.gate-pass') }}" class="flex flex-col items-center py-1 px-2 rounded-lg {{ request()->routeIs('portal.gate-pass') ? 'text-orange-700 font-bold bg-orange-50' : 'text-slate-600' }}">
                        <span class="text-base">🛡️</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'গেট পাস' : 'Gate Pass' }}</span>
                    </a>
                @endif

                @if($isDriver || $isAdminOrTransport)
                    <a href="{{ route('portal.driver') }}" class="flex flex-col items-center py-1 px-2 rounded-lg {{ request()->routeIs('portal.driver') ? 'text-orange-700 font-bold bg-orange-50' : 'text-slate-600' }}">
                        <span class="text-base">🛞</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'ড্রাইভার' : 'Driver' }}</span>
                    </a>
                @endif

                @if($isAdminOrTransport)
                    <a href="{{ route('portal.mobile') }}" class="flex flex-col items-center py-1 px-2 rounded-lg {{ request()->routeIs('portal.mobile') ? 'text-orange-700 font-bold bg-orange-50' : 'text-slate-600' }}">
                        <span class="text-base">📱</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'টার্মিনাল' : 'Mobile' }}</span>
                    </a>
                @endif

                @auth
                    <a href="{{ route('logout') }}" class="flex flex-col items-center py-1 px-2 rounded-lg text-slate-500 hover:text-red-600">
                        <span class="text-base">🚪</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'লগআউট' : 'Logout' }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex flex-col items-center py-1 px-2 rounded-lg text-orange-600 font-bold">
                        <span class="text-base">🔑</span>
                        <span>{{ app()->getLocale() === 'bn' ? 'লগইন' : 'Login' }}</span>
                    </a>
                @endauth
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-5 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center space-x-2.5">
                    <img src="{{ asset('images/nz-group.png') }}" alt="NZ Group" class="h-6 w-auto object-contain" />
                    <span class="font-bold text-slate-800 tracking-wide">NZ GROUP</span>
                    <span class="text-slate-300">&bull;</span>
                    <span>Transport & Fleet Operations</span>
                </div>
                <div class="text-slate-400">
                    &copy; {{ date('Y') }} NZ Group &bull; Internal Enterprise Fleet Operations
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
