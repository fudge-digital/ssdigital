<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 text-gray-800 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col">
        {{-- Header / Navbar --}}
        <header class="bg-white shadow flex items-center justify-between px-6 py-3">

            <h1 class="text-xl font-semibold text-gray-700">@yield('page_title', 'Dashboard')</h1>

            <div class="flex items-center gap-6">

                {{-- Notification Bell --}}
                <div class="relative">
                    <button id="notificationButton" class="relative">
                        {{-- Bell Icon --}}
                        <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.4-1.4a2 2 0 01-.6-1.4V11a7 7 0 10-14 0v3.2c0 .5-.2 1-.6 1.4L3 17h5m7 0a3 3 0 11-6 0h6z" />
                        </svg>

                        {{-- BADGE --}}
                        @php $role = Auth::user()->role; @endphp

                        {{-- ADMIN BADGE --}}
                        @if($role === 'admin' && ($pendingCount + $requestBillingCount + ($newParentCount ?? 0)) > 0)
                            <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
                                {{ $pendingCount + $requestBillingCount + ($newParentCount ?? 0) }}
                            </span>
                        @endif

                        {{-- PARENT BADGE --}}
                        @if($role === 'orang_tua' && $notificationCount > 0)
                            <span id="notificationBadge"
                                class="absolute -top-1 -right-1 bg-red-600 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full">
                                {{ $notificationCount }}
                            </span>
                        @endif
                    </button>

                    {{-- DROPDOWN BODY --}}
                    <div id="notificationDropdown"
                        class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg border rounded-xl overflow-hidden z-50">

                        <div class="px-4 py-2 font-semibold border-b bg-gray-50">
                            Notifikasi
                        </div>

                        {{-- ================= ADMIN ================= --}}
                        @if($role === 'admin')
                            @forelse($combinedNotif as $item)

                                {{-- Pending pembayaran --}}
                                @if($item['type'] === 'pending')
                                    @if($item['parent'])
                                        <a href="{{ route('admin.iuran.index', ['parent' => $item['parent']->id]) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 border-b">
                                            <p class="font-medium">
                                                {{ $item['parent']->userProfile->nama_lengkap ?? $item['parent']->name }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Pembayaran untuk {{ $item['total_transaksi'] }} siswa pending
                                            </p>
                                            <p class="text-sm font-bold text-gray-800">
                                                Rp {{ number_format($item['total_nominal'], 0, ',', '.') }}
                                            </p>
                                        </a>
                                    @endif

                                {{-- Request Billing --}}
                                @elseif($item['type'] === 'request')
                                    <a href="{{ route('admin.iuran.requests') }}"
                                        class="block px-4 py-3 hover:bg-gray-100 border-b">
                                        <p class="font-medium">{{ $item['title'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $item['message'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $item['created_at']->diffForHumans() }}</p>
                                    </a>

                                {{-- New Parent Registered --}}
                                @elseif($item['type'] === 'new_parent')
                                    @if($item['parent'])
                                        <a href="{{ route('admin.iuran.index', $item['parent']->id) }}"
                                            class="block px-4 py-3 hover:bg-gray-100 border-b">
                                            <p class="font-medium">
                                                Parent baru: {{ $item['parent']->userProfile->nama_lengkap ?? $item['parent']->name }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Baru mendaftar, silakan verifikasi
                                            </p>
                                            <p class="text-xs text-gray-400">{{ $item['created_at']->diffForHumans() }}</p>
                                        </a>
                                    @endif
                                @endif

                            @empty
                                <p class="px-4 py-3 text-gray-500 text-sm">Tidak ada notifikasi</p>
                            @endforelse
                        @endif

                        {{-- ================= ORANG TUA ================= --}}
                        @if($role === 'orang_tua')
                            @forelse($approvedNotification as $notif)
                                <a href="{{ route('parent.iuran.index') }}"
                                    class="block px-4 py-3 hover:bg-gray-100 border-b">

                                    <p class="font-medium">
                                        Pembayaran bulan {{ $notif->data['bulan'] }} telah diverifikasi
                                    </p>

                                    <p class="text-sm font-bold text-gray-800">
                                        Rp {{ number_format($notif->data['amount'], 0, ',', '.') }}
                                    </p>

                                    <p class="text-xs text-gray-400">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </p>
                                </a>
                            @empty
                                <p class="px-4 py-3 text-gray-500 text-sm">Tidak ada notifikasi</p>
                            @endforelse
                        @endif

                    </div>
                </div>

                {{-- Avatar --}}
                <span class="text-gray-600 font-medium">{{ Auth::user()->name ?? 'User' }}</span>
                <img src="{{ avatar_url(Auth::user()) }}" alt="Avatar" class="w-10 h-10 rounded-full object-cover">
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @yield('scripts')

    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        const button = document.getElementById('notificationButton');
        const dropdown = document.getElementById('notificationDropdown');

        button.addEventListener('click', () => {
            dropdown.classList.toggle('hidden');
        });

        // close when clicking outside
        document.addEventListener('click', function (e) {
            if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        document.getElementById('notificationButton').addEventListener('click', function () {
            fetch("{{ route('notifications.readAll') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Content-Type': 'application/json',
                },
            })
            .then(() => {
                const badge = document.getElementById('notificationBadge');
                if (badge) badge.classList.add('hidden');
            });
        });
    </script>
    
</body>
</html>
