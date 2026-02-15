<?php

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    //
    use WithPagination;
    public $search = '';
    public function render()
    {
        $dataProduct = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code_product', 'like', '%' . $this->search . '%');
                });
            })
            ->with('productToSupplier')
            ->latest()
            ->paginate(10);

        return $this->view()
            ->with([
                'dataProduct' => $dataProduct,
            ])
            ->title('Inventory')
            ->layout('layouts.app');
    }
};
?>

<div class="bg-slate-100 min-h-screen p-6">

    <h1 class="text-3xl font-bold text-slate-800 mb-6">
        Dashboard Overview
    </h1>

    <div class="max-w-7xl mx-auto">

        <!-- ============================ -->
        <!-- 🔹 HEADER -->
        <!-- ============================ -->
        <div class="mb-6">
            <p class="text-sm text-slate-500">
                Kelola data barang dan stok tersedia
            </p>
        </div>

        <!-- ============================ -->
        <!-- 🔍 SEARCH -->
        <!-- ============================ -->
        <div class="bg-white rounded-2xl shadow p-5 mb-6">
            <label class="block text-sm font-medium text-slate-600 mb-2">
                Cari Barang (Nama / Kode)
            </label>
            <input type="text" wire:model.live="search" placeholder="Masukkan nama atau kode barang..."
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">

        </div>

        <!-- ============================ -->
        <!-- 📊 TABLE -->
        <!-- ============================ -->
        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Kode Barang</th>
                            <th class="px-6 py-3">Nama Barang</th>
                            <th class="px-6 py-3">Harga Jual</th>
                            <th class="px-6 py-3">Harga Beli</th>
                            <th class="px-6 py-3">Stock</th>
                            <th class="px-6 py-3">Update Date</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($dataProduct as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $item->code_product }}
                            </td>
                            <td class="px-6 py-4 capitalize">
                                {{ $item->name }}
                            </td>
                            <td class="px-6 py-4">
                                Rp. {{ $item->harga_jual }}
                            </td>
                            <td class="px-6 py-4">
                                Rp. {{ $item->harga_beli }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-3 py-1 text-xs font-semibold {{ $item->stock < '20' ? 'bg-red-100 text-red-700' : 'bg-green-200 text-green-700' }}  rounded-full">
                                    {{ $item->stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->created_at->format('d ' . 'M ' . 'Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg text-xs transition">
                                    Adjust Stock
                                </button>
                            </td>
                        </tr>

                        @empty
                        <p class="text-center flex">Data belum tersedia</p>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>