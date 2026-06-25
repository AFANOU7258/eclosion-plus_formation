<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Eclosion+</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{eclosion:{50:'#e8f5ec',100:'#c8e7cf',200:'#a4d8af',300:'#7cc98c',400:'#55b96a',500:'#2ea948',600:'#0d6025',700:'#0b4f1e',800:'#093e18',900:'#062d11'},ocean:{50:'#e8f0fa',100:'#c4d7f2',200:'#9dbdea',300:'#73a2e1',400:'#4b87d8',500:'#1e6dcf',600:'#06429a',700:'#05357d',800:'#042860',900:'#031b43'}}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>*{font-family:'Roboto',sans-serif}.font-display{font-family:'Google Sans','Roboto',sans-serif}.material-icons{font-family:'Material Icons'!important;font-size:20px;vertical-align:middle;display:inline-block;line-height:1;text-transform:none;letter-spacing:normal;word-wrap:normal;white-space:nowrap;direction:ltr;-webkit-font-smoothing:antialiased}</style>
    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800">

<header class="bg-white border-b border-gray-200 h-14 flex items-center px-4 sticky top-0 z-30 shadow-sm">
    <button id="menu-toggle" class="p-2 rounded-full hover:bg-gray-100 transition lg:hidden mr-2"><span class="material-icons text-gray-600">menu</span></button>
    <a href="/" class="flex items-center gap-2 shrink-0"><img src="{{ asset('images/logo.png') }}" class="h-7 w-auto"></a>
    <span class="font-display text-gray-500 mx-3 hidden sm:inline">|</span>
    <span class="font-display text-sm font-medium text-gray-700 hidden sm:inline">Administration</span>
    <div class="flex-1"></div>
    <div class="flex items-center gap-2">
        <span class="text-xs text-gray-500 hidden md:inline">{{ Auth::user()->name }}</span>
        <span class="w-8 h-8 rounded-full bg-eclosion-600 text-white text-xs font-bold flex items-center justify-center">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</span>
    </div>
</header>

<div class="flex">
    <aside id="sidebar" class="w-60 bg-white border-r border-gray-200 h-[calc(100vh-3.5rem)] sticky top-14 shrink-0 overflow-y-auto fixed lg:sticky z-20 transition-transform -translate-x-full lg:translate-x-0">
        <nav class="p-3 space-y-0.5">
            <p class="px-3 pt-1 pb-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Principal</p>
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-50' }}"><span class="material-icons">dashboard</span> Tableau de bord</a>

            <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Gestion</p>
            <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.courses.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-50' }}"><span class="material-icons">school</span> Formations</a>
            <a href="{{ route('admin.enrollments.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.enrollments.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-50' }}"><span class="material-icons">how_to_reg</span> Demandes @php $p=\App\Models\Enrollment::pending()->count(); @endphp @if($p>0)<span class="ml-auto bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold">{{$p}}</span>@endif</a>
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-eclosion-50 text-eclosion-700' : 'text-gray-600 hover:bg-gray-50' }}"><span class="material-icons">people</span> Utilisateurs</a>

            <p class="px-3 pt-3 pb-1 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Site</p>
            <a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-50 transition"><span class="material-icons">open_in_new</span> Voir le site</a>
            <form action="{{ route('logout') }}" method="POST"><button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-500 hover:bg-red-50 transition text-left"><span class="material-icons">logout</span> Déconnexion</button></form>
        </nav>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-10 hidden lg:hidden" onclick="closeSidebar()"></div>

    <main class="flex-1 min-h-[calc(100vh-3.5rem)] w-full">
        @yield('content')
    </main>
</div>

<script>const s=document.getElementById('sidebar'),o=document.getElementById('sidebar-overlay');document.getElementById('menu-toggle').addEventListener('click',()=>{s.classList.remove('-translate-x-full');o.classList.remove('hidden')});function closeSidebar(){s.classList.add('-translate-x-full');o.classList.add('hidden')}</script>
@stack('scripts')
</body>
</html>
