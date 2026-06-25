<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Eclosion+') — Formation en ligne</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        eclosion: {
                            50: '#e8f5ec', 100: '#c8e7cf', 200: '#a4d8af',
                            300: '#7cc98c', 400: '#55b96a', 500: '#2ea948',
                            600: '#0d6025', 700: '#0b4f1e', 800: '#093e18', 900: '#062d11'
                        },
                        ocean: {
                            50: '#e8f0fa', 100: '#c4d7f2', 200: '#9dbdea',
                            300: '#73a2e1', 400: '#4b87d8', 500: '#1e6dcf',
                            600: '#06429a', 700: '#05357d', 800: '#042860', 900: '#031b43'
                        },
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * { font-family: 'Roboto', sans-serif; }
        .font-display { font-family: 'Google Sans', 'Roboto', sans-serif; }
        .material-icons { font-family: 'Material Icons' !important; font-size: 20px; vertical-align: middle; display: inline-block; line-height: 1; text-transform: none; letter-spacing: normal; word-wrap: normal; white-space: nowrap; direction: ltr; -webkit-font-smoothing: antialiased; }
        .material-icons.large { font-size: 36px; }
        .course-card { transition: box-shadow 0.2s ease, transform 0.15s ease; }
        .course-card:hover { box-shadow: 0 1px 3px 0 rgba(60,64,67,.3), 0 4px 8px 3px rgba(60,64,67,.15); transform: translateY(-1px); }
        .card-banner { background-size: cover; background-position: center; min-height: 100px; border-radius: 8px 8px 0 0; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    {{-- HEADER PRO --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 h-14 flex items-center gap-3">
            {{-- Hamburger mobile --}}
            <button id="menu-toggle" class="p-2 rounded-full hover:bg-gray-100 transition lg:hidden shrink-0">
                <span class="material-icons text-gray-600">menu</span>
            </button>

            {{-- Logo + Marque --}}
            <a href="{{ url('/') }}" class="flex items-center shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Eclosion+" class="h-8 w-auto">
            </a>

            {{-- Navigation desktop --}}
            <nav class="hidden lg:flex items-center gap-0.5 ml-6">
                <a href="{{ url('/') }}" class="px-3.5 py-2 rounded-full text-sm font-medium {{ request()->is('/') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-100' }} transition flex items-center gap-1.5">
                    <span class="material-icons text-lg">home</span> Accueil
                </a>
                <a href="{{ route('courses.index') }}" class="px-3.5 py-2 rounded-full text-sm font-medium {{ request()->routeIs('courses.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-100' }} transition flex items-center gap-1.5">
                    <span class="material-icons text-lg">school</span> Formations
                </a>
                @auth
                <a href="{{ route('enrollments.index') }}" class="px-3.5 py-2 rounded-full text-sm font-medium {{ request()->routeIs('enrollments.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-100' }} transition flex items-center gap-1.5">
                    <span class="material-icons text-lg">book</span> Mes cours
                </a>
                <a href="{{ route('helpdesk.index') }}" class="px-3.5 py-2 rounded-full text-sm font-medium {{ request()->routeIs('helpdesk.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-100' }} transition flex items-center gap-1.5">
                    <span class="material-icons text-lg">support_agent</span> Support IA
                </a>
                @endauth
            </nav>

            {{-- Spacer --}}
            <div class="flex-1"></div>

            {{-- Actions droite --}}
            <div class="flex items-center gap-2">
                @auth
                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                            <span class="material-icons text-lg">admin_panel_settings</span>
                            <span class="hidden md:inline">Admin</span>
                        </a>
                    @endif

                    {{-- Notifications --}}
                    @php $pendingCount = \App\Models\Enrollment::pending()->count(); @endphp
                    @if(Auth::user()->isAdmin() && $pendingCount > 0)
                    <a href="{{ route('admin.enrollments.index') }}" class="relative p-2 rounded-full hover:bg-gray-100 transition">
                        <span class="material-icons text-gray-600">notifications</span>
                        <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">{{ $pendingCount }}</span>
                    </a>
                    @endif

                    {{-- Avatar dropdown --}}
                    <div class="relative group">
                        <button class="w-8 h-8 rounded-full bg-eclosion-600 text-white font-bold text-xs flex items-center justify-center ring-2 ring-eclosion-100">
                            {{ strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        </button>
                        <div class="absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 py-2 hidden group-hover:block z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                                <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ Auth::user()->role }}</span>
                            </div>
                            <div class="py-1">
                                @if(Auth::user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <span class="material-icons text-lg">dashboard</span> Administration
                                </a>
                                @endif
                                <a href="{{ route('enrollments.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                    <span class="material-icons text-lg">book</span> Mes formations
                                </a>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 text-left">
                                        <span class="material-icons text-lg">logout</span> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-100 transition">
                        <span class="material-icons text-lg">login</span> Connexion
                    </a>
                    <a href="{{ route('register') }}" class="bg-eclosion-600 text-white px-5 py-2 rounded-full text-sm font-medium hover:bg-eclosion-700 transition shadow-sm">
                        S'inscrire
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- MOBILE DRAWER --}}
    <div id="mobile-drawer" class="fixed inset-0 z-40 hidden">
        <div class="absolute inset-0 bg-black/50" onclick="closeDrawer()"></div>
        <div class="absolute left-0 top-0 bottom-0 w-72 bg-white shadow-2xl flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <img src="{{ asset('images/logo.png') }}" alt="Eclosion+" class="h-7 w-auto">
                <button onclick="closeDrawer()" class="p-1.5 rounded-full hover:bg-gray-100"><span class="material-icons">close</span></button>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
                <a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 text-sm font-medium {{ request()->is('/') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600' }}">
                    <span class="material-icons">home</span> Accueil
                </a>
                <a href="{{ route('courses.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 text-sm font-medium {{ request()->routeIs('courses.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600' }}">
                    <span class="material-icons">school</span> Formations
                </a>
                @auth
                <a href="{{ route('enrollments.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    <span class="material-icons">book</span> Mes cours
                </a>
                <a href="{{ route('helpdesk.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    <span class="material-icons">support_agent</span> Support IA
                </a>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 text-sm font-medium">
                    <span class="material-icons">admin_panel_settings</span> Administration
                </a>
                @endif
                <hr class="my-2">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="w-full flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-red-50 text-sm font-medium text-red-600 text-left">
                        <span class="material-icons">logout</span> Déconnexion
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 text-sm font-medium text-gray-600">
                    <span class="material-icons">login</span> Connexion
                </a>
                <a href="{{ route('register') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg bg-eclosion-600 text-white text-sm font-medium mt-2">
                    <span class="material-icons">person_add</span> S'inscrire
                </a>
                @endauth
            </nav>
        </div>
    </div>

    {{-- CONTENU --}}
    <main class="min-h-[calc(100vh-3.5rem)]">
        @yield('content')
    </main>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function() {
            document.getElementById('mobile-drawer').classList.remove('hidden');
        });
        function closeDrawer() {
            document.getElementById('mobile-drawer').classList.add('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
