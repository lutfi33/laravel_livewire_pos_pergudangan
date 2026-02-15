<?php

use App\Models\Product;
use Livewire\Component;

new class extends Component {
    //
    public $qty = [];
    public $harga_jual;
    public $total;

    public function getSubtotal($productId, $price)
    {
        $qty = $this->qty[$productId] ?? 0;
        return $qty * $price;
    }

    public function render()
    {
        $dataProduct = Product::paginate(10);
        return $this->view()
            ->with([
                'dataProduct' => $dataProduct,
            ])
            ->title('Transaksi')
            ->layout('layouts.app');
    }
};
?>

<div class="bg-slate-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <h1 class="text-2xl font-bold text-slate-800 mb-6">
            Transaksi Penjualan
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ============================ -->
            <!-- 🛒 COLUMN 1 - TABEL BARANG -->
            <!-- ============================ -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow p-6">

                <!-- Search -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Cari Kode Barang
                    </label>
                    <input type="text" placeholder="Scan / Masukkan kode barang..."
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">Nama Barang</th>
                                <th class="px-4 py-3">Harga</th>
                                <th class="px-4 py-3">Jumlah</th>
                                <th class="px-4 py-3">Subtotal</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($dataProduct as $item)
                                <tr class="hover:bg-slate-50">

                                    <td class="px-4 py-3 font-medium text-slate-800">
                                        {{ $item->name }}
                                    </td>

                                    <td class="px-4 py-3">
                                        Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="number" min="1" wire:model.live="qty.{{ $item->id }}"
                                            class="w-16 border rounded-md px-2 py-1 text-center">
                                    </td>

                                    <td class="px-4 py-3 font-semibold text-indigo-600">
                                        Rp
                                        {{ number_format($this->getSubtotal($item->id, $item->harga_jual), 0, ',', '.') }}
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <button wire:click="$set('qty.{{ $item->id }}', 0)"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs">
                                            Hapus
                                        </button>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        Data belum ada
                                    </td>
                                </tr>
                            @endforelse



                        </tbody>
                    </table>
                </div>

            </div>


            <!-- ============================ -->
            <!-- 💳 COLUMN 2 - SUMMARY -->
            <!-- ============================ -->
            <div class="bg-white rounded-2xl shadow p-6 flex flex-col">

                <h2 class="text-lg font-bold text-slate-800 mb-6">
                    Ringkasan Pembayaran
                </h2>

                <!-- Total Item -->
                <div class="flex justify-between mb-3 text-sm">
                    <span class="text-slate-600">Jumlah Item</span>
                    <span class="font-semibold">1</span>
                </div>

                <!-- Total Harga -->
                <div class="flex justify-between mb-4 text-lg">
                    <span class="text-slate-700 font-medium">Total Harga</span>
                    <span class="font-bold text-indigo-600">
                        Rp 7.000.000
                    </span>
                </div>

                <hr class="mb-4">

                <!-- Jumlah Bayar -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Jumlah Bayar
                    </label>
                    <input type="number" placeholder="Masukkan jumlah bayar"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Sisa Kembalian -->
                <div class="flex justify-between text-lg mb-6">
                    <span class="text-slate-700 font-medium">
                        Kembalian
                    </span>
                    <span class="font-bold text-green-600">
                        Rp 0
                    </span>
                </div>

                <!-- Checkout Button -->
                <button
                    class="mt-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition">
                    Checkout
                </button>

            </div>

        </div>

    </div>
</div>
