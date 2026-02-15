<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-slate-100 min-h-screen" x-data="{ open: false }">
    <!-- 🔹 Navbar -->
    <nav class="bg-white shadow-sm border-b border-slate-200 fixed top-0 left-0 right-0 z-50
">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">

                <!-- Logo (Always Visible) -->
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold text-indigo-600">
                        Toko Dua Putra
                    </span>
                </div>

                @if (Auth::user()->role === 'owner')
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="{{ route('admin.items') }}" wire:navigate
                            class="  text-slate-600 hover:text-indigo-600 ">Inventory</a>
                        <a href="{{ route('admin.selling') }}" wire:navigate
                            class="text-slate-600 hover:text-indigo-600 ">Penjualan</a>
                        <a href="{{ route('admin.purchase') }}" wire:navigate
                            class="text-slate-600 hover:text-indigo-600">Pembelian</a>
                        <a href="{{ route('admin.supplier') }}" wire:navigate
                            class="text-slate-600 hover:text-indigo-600">Supplier</a>


                        <form method="POST" action="{{ route('logout') }}"
                            onsubmit="return confirm('Yakin ingin logout?')">
                            @csrf
                            <button type="submit"
                                class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-500">
                                Logout
                            </button>
                        </form>

                    </div>
                @endif

                @if (Auth::user()->role === 'staff')
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="{{ route('staff.transaction') }}" wire:navigate
                            class="  text-slate-600 hover:text-indigo-600 ">Transaksi</a>
                        <a href="{{ route('staff.selling') }}" wire:navigate
                            class="text-slate-600 hover:text-indigo-600 ">Penjualan</a>
                        <a href="{{ route('staff.stock') }}" wire:navigate
                            class="text-slate-600 hover:text-indigo-600">Stock</a>


                        <form method="POST" action="{{ route('logout') }}"
                            onsubmit="return confirm('Yakin ingin logout?')">
                            @csrf
                            <button type="submit"
                                class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                                Logout
                            </button>
                        </form>

                    </div>
                @endif


            </div>
        </div>
    </nav>

    <!-- 🔹 Content -->
    <main class="max-w-7xl mx-auto px-6 py-8">



        @livewireScripts
    </main>

    <!-- ===================== -->
    <!-- 📱 MOBILE BOTTOM NAV -->
    <!-- ===================== -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow md:hidden">
        <div class="flex justify-around items-center h-16">

            <!-- Dashboard -->
            <a href="{{ route('admin.items') }}" class="flex flex-col items-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h4V3H3v7zm7 11h4v-8h-4v8zm7 0h4V5h-4v16z" />
                </svg>
                <span class="text-xs mt-1">Inventory</span>
            </a>

            <!-- Users -->
            <a href="{{ route('admin.supplier') }}"
                class="flex flex-col items-center text-slate-500 hover:text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5V4H2v16h5m10 0v-4a4 4 0 00-8 0v4m8 0H9" />
                </svg>
                <span class="text-xs mt-1">Supplier</span>
            </a>

            <!-- Reports -->
            <a href="{{ route('admin.purchase') }}"
                class="flex flex-col items-center text-slate-500 hover:text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-6h13v6M9 17H5a2 2 0 01-2-2V7a2 2 0 012-2h4m0 12h6" />
                </svg>
                <span class="text-xs mt-1">Pembelian</span>
            </a>

            <!-- Settings -->
            <a href="{{ route('admin.selling') }}"
                class="flex flex-col items-center text-slate-500 hover:text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-2.21 0-4 1.79-4 4m8 0a4 4 0 00-8 0m8 0v4m-8-4v4" />
                </svg>
                <span class="text-xs mt-1">Penjualan</span>
            </a>

        </div>
    </nav>
    {{ $slot }}

</body>

</html>
