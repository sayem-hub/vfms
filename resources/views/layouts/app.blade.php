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
                            <a href="{{ route('portal.requests') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.requests') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                📝 {{ app()->getLocale() === 'bn' ? 'ট্রিপ রিকোয়েস্ট' : 'Trip Requests' }}
                            </a>
                            <a href="{{ route('portal.gate-pass') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.gate-pass') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                🛡️ {{ app()->getLocale() === 'bn' ? 'গেট পাস টার্মিনাল' : 'Security Gate-Pass' }}
                            </a>
                            <a href="{{ route('portal.driver') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.driver') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                🛞 {{ app()->getLocale() === 'bn' ? 'ড্রাইভার পোর্টাল' : 'Driver Portal' }}
                            </a>
                            <a href="{{ route('portal.mobile') }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('portal.mobile') ? 'bg-orange-50 text-orange-700 font-semibold border-b-2 border-orange-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                📱 {{ app()->getLocale() === 'bn' ? 'মোবাইল টার্মিনাল' : 'Mobile App' }}
                            </a>
                        </nav>
                    </div>

                    <!-- Right Controls: Language Switcher & Admin Link -->
                    <div class="flex items-center space-x-3">
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

                        <!-- Admin Panel Button -->
                        <a href="{{ url('/admin') }}" 
                           class="hidden sm:inline-flex items-center px-3.5 py-1.5 border border-transparent text-xs font-bold rounded-lg text-white bg-slate-900 hover:bg-orange-600 transition shadow-xs">
                            ⚙️ {{ app()->getLocale() === 'bn' ? 'অ্যাডমিন ড্যাশবোর্ড' : 'Admin Panel' }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Strip -->
            <div class="md:hidden border-t border-slate-100 bg-slate-50 px-4 py-2 flex justify-around text-xs font-medium">
                <a href="{{ route('portal.requisitions') }}" class="{{ request()->routeIs('portal.requisitions') ? 'text-orange-700 font-bold' : 'text-slate-600' }}">
                    📝 {{ app()->getLocale() === 'bn' ? 'রিকুইজিশন' : 'Requisitions' }}
                </a>
                <a href="{{ route('portal.gate-pass') }}" class="{{ request()->routeIs('portal.gate-pass') ? 'text-orange-700 font-bold' : 'text-slate-600' }}">
                    🛡️ {{ app()->getLocale() === 'bn' ? 'গেট পাস' : 'Gate Pass' }}
                </a>
                <a href="{{ route('portal.driver') }}" class="{{ request()->routeIs('portal.driver') ? 'text-orange-700 font-bold' : 'text-slate-600' }}">
                    🛞 {{ app()->getLocale() === 'bn' ? 'ড্রাইভার' : 'Driver' }}
                </a>
                <a href="{{ url('/admin') }}" class="text-slate-700 font-semibold">
                    ⚙️ {{ app()->getLocale() === 'bn' ? 'অ্যাডমিন' : 'Admin' }}
                </a>
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
