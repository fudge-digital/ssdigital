<aside class="bg-[#064e3b] text-white w-64 flex flex-col">
    <div class="px-6 py-4 text-2xl font-bold border-b border-green-700">
        <img src="{{ asset('images/SATRIASILIWANGIFONT-1.png') }}" 
            alt="Logo KlubMan" 
            class="object-contain mx-auto" style="width: 150px; height: auto;">
        <p class="text-xs font-normal text-center">Club Management System</p>
    </div>

    <nav 
        x-data="{ openMenu: null }" 
        class="flex-1 p-4 space-y-2 overflow-y-auto text-sm">

        {{-- === MENU UNTUK PARENT === --}}
        @if(Auth::user()?->hasRole('orang_tua'))
            @php
                // ambil anak pertama terkait parent
                $student = Auth::user()?->children()->first()?->siswaProfile ?? null;
            @endphp
            <a href="{{ route('parent.dashboard') }}"
            class="block px-4 py-2 rounded-lg hover:bg-green-700 transition-colors {{ request()->routeIs('parent.dashboard') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-house mr-2"></i> Dashboard
            </a>

            @if($student && !in_array($student->status, ['tidak_aktif', 'suspended']))
                <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700">
                    <i class="fa-regular fa-user mr-2"></i> Absensi Siswa
                </a>
            @else
                <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700 cursor-not-allowed opacity-50">
                    <i class="fa-regular fa-user mr-2"></i> Absensi Siswa
                </a>
            @endif

            <button 
                @click="openMenu === 'pembayaran' ? openMenu = null : openMenu = 'pembayaran'"
                class="flex items-center justify-between w-full px-3 py-2 rounded-lg hover:bg-green-700 transition-colors"
                :class="openMenu === 'pembayaran' ? 'bg-green-800 text-white' : ''">
                <span><i class="fa-regular fa-house mr-2"></i> Kelola Pembayaran</span>
                <i class="fa-solid" :class="openMenu === 'parent' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <div 
                x-show="openMenu === 'pembayaran'" 
                x-transition 
                class="pl-3 mt-1 space-y-1"
                x-cloak>
                @if(!in_array($student->status, ['tidak_aktif']))
                    <a href="{{ route('parent.iuran.index') }}" class="block px-3 py-1.5 rounded hover:bg-green-700 transition-colors {{ request()->routeIs('parent.iuran.index') ? 'bg-green-800 text-white' : '' }}">
                        <i class="fa-regular fa-credit-card mr-2"></i> Iuran Bulanan
                    </a>
                @else
                    <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700 cursor-not-allowed opacity-50">
                        <i class="fa-regular fa-credit-card mr-2"></i> Iuran Bulanan
                    </a>
                @endif

                @if(!in_array($student->status, ['tidak_aktif', 'suspended']))
                    <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700">
                        <i class="fa-regular fa-file-invoice mr-2"></i> Pembayaran Pertandingan
                    </a>
                    <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700">
                        <i class="fa-solid fa-shirt mr-2"></i> Pembayaran Jersey
                    </a>
                @else
                    <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700 cursor-not-allowed opacity-50">
                        <i class="fa-regular fa-file-invoice mr-2"></i> Pembayaran Pertandingan
                    </a>
                    <a href="#" class="block px-3 py-1.5 rounded hover:bg-green-700 cursor-not-allowed opacity-50">
                        <i class="fa-solid fa-shirt mr-2"></i> Pembayaran Jersey
                    </a>
                @endif
            </div>
            <a href="{{ route('posts.public') }}"
            class="block px-4 py-2 rounded-lg hover:bg-green-700 transition-colors {{ request()->routeIs('posts.public') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-newspaper mr-2"></i> Berita & Info Terbaru
            </a>
        @endif

        {{-- === MENU DASHBOARD SISWA === --}}
        @if(Auth::user()?->hasRole(['siswa']))
            <a href="{{ route('siswa.dashboard') }}"
            class="block px-4 py-2 rounded-lg hover:bg-green-700 transition-colors {{ request()->routeIs('siswa.dashboard') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-house mr-2"></i> Dashboard
            </a>
            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fa-solid fa-clipboard-user mr-2"></i> Absensi
            </a>
            <a href="{{ route('siswa.edit', auth()->id()) }}" class="block px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fa-solid fa-user-pen mr-2"></i> Edit Profil
            </a>
            <a href="#" class="block px-4 py-2 rounded-lg hover:bg-green-700">
                <i class="fa-solid fa-stopwatch mr-2"></i> Pertandingan
            </a>
            <a href="{{ route('posts.public') }}"
            class="block px-4 py-2 rounded-lg hover:bg-green-700 transition-colors {{ request()->routeIs('posts.public') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-newspaper mr-2"></i> Berita & Info Terbaru
            </a>
        @endif

        {{-- === MENU DASHBOARD ADMIN === --}}
        @if(Auth::user()?->hasRole(['super_admin', 'admin']))
            <a href="{{ route('admin.dashboard') }}"
            class="block px-4 py-2 rounded-lg hover:bg-green-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-house mr-2"></i> Dashboard
            </a>

            {{-- === KELOLA PEMBAYARAN (accordion) === --}}
            <button 
                @click="openMenu === 'pembayaran' ? openMenu = null : openMenu = 'pembayaran'"
                class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                :class="openMenu === 'pembayaran' ? 'bg-green-800 text-white' : ''">
                <span><i class="fa-regular fa-credit-card mr-2"></i> Kelola Pembayaran</span>
                <i class="fa-solid" :class="openMenu === 'pembayaran' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <div 
                x-show="openMenu === 'pembayaran'"
                x-transition 
                x-cloak
                class="pl-2 mt-1 space-y-1">
                
                <a href="{{ route('admin.iuran.index') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700">
                <i class="fa-solid fa-money-bills mr-2"></i> Iuran Bulanan
                </a>

                <a href="{{ route('admin.pembayaran.index') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('admin.pembayaran.index') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-solid fa-file-invoice mr-2"></i> Pembayaran Pendaftaran
                </a>

                <a href="#"
                class="block px-3 py-1.5 rounded hover:bg-green-700">
                <i class="fa-regular fa-futbol mr-2"></i> Pembayaran Pertandingan
                </a>

                <a href="#"
                class="block px-3 py-1.5 rounded hover:bg-green-700">
                <i class="fa-solid fa-ellipsis-h mr-2"></i> Pembayaran Lainnya
                </a>
            </div>

            {{-- === KELOLA BERITA === --}}
            <button 
                @click="openMenu === 'berita' ? openMenu = null : openMenu = 'berita'"
                class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                :class="openMenu === 'berita' ? 'bg-green-800 text-white' : ''">
                <span><i class="fa-regular fa-credit-card mr-2"></i> Kelola Berita</span>
                <i class="fa-solid" :class="openMenu === 'berita' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <div 
                x-show="openMenu === 'berita'"
                x-transition 
                x-cloak
                class="pl-2 mt-1 space-y-1">
                
                <a href="{{ route('posts.index') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('posts.index') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-solid fa-newspaper mr-2"></i> Daftar Berita
                </a>

                <a href="{{ route('categories.index') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('categories.index') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-solid fa-file-invoice mr-2"></i> Daftar Category
                </a>

                <a href="{{ route('posts.create') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('posts.create') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-futbol mr-2"></i> Buat Berita
                </a>
            </div>

            {{-- === DATA SISWA (accordion) === --}}
            <button 
                @click="openMenu === 'siswa' ? openMenu = null : openMenu = 'siswa'"
                class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                :class="openMenu === 'siswa' ? 'bg-green-800 text-white' : ''">
                <span><i class="fa-solid fa-users mr-2"></i> Data Siswa</span>
                <i class="fa-solid" :class="openMenu === 'siswa' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <div 
                x-show="openMenu === 'siswa'"
                x-transition 
                x-cloak
                class="pl-2 mt-1 space-y-1">

                <a href="{{ route('siswa.index') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('admin.siswa.index') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-rectangle-list mr-2"></i> Daftar Siswa
                </a>

                <a href="#"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('admin.absensi.index') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-calendar-check mr-2"></i> Absensi Siswa
                </a>
            </div>

            {{-- === DATA PARENT (accordion) === --}}
            <button 
                @click="openMenu === 'parent' ? openMenu = null : openMenu = 'parent'"
                class="flex items-center justify-between w-full px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
                :class="openMenu === 'parent' ? 'bg-green-800 text-white' : ''">
                <span><i class="fa-solid fa-users mr-2"></i> Data Parent</span>
                <i class="fa-solid" :class="openMenu === 'parent' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <div 
                x-show="openMenu === 'parent'"
                x-transition 
                x-cloak
                class="pl-2 mt-1 space-y-1">

                <a href="{{ route('parents.index') }}"
                class="block px-3 py-1.5 rounded hover:bg-green-700 {{ request()->routeIs('admin.siswa.index') ? 'bg-green-800 text-white' : '' }}">
                <i class="fa-regular fa-rectangle-list mr-2"></i> Daftar Parent
                </a>
            </div>
        @endif

    </nav>

    <div class="p-4 border-t border-green-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full px-4 py-2 bg-green-700 rounded-lg hover:bg-green-800">
                Logout
            </button>
        </form>
    </div>
</aside>