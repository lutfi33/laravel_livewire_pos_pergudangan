<?php

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    //
    use WithPagination;
    public $search = '';
    public $adjustProductId, $adjustProductName;
    public $currentStock = 0;
    public $adjustType = 'add'; // Default: Tambah stok
    public $adjustAmount = 0;

    public function openAdjustModal($id)
    {
        $this->resetValidation();

        $product = Product::findOrFail($id);
        $this->adjustProductId = $product->id;
        $this->adjustProductName = $product->name;
        $this->currentStock = $product->stock;

        // Reset form
        $this->adjustType = 'add';
        $this->adjustAmount = null;

        // Kirim sinyal ke AlpineJS
        $this->dispatch('open-adjust-modal');
    }

    // Fungsi untuk menyimpan perubahan stok
    public function saveAdjustStock()
    {
        $this->validate([
            'adjustAmount' => 'required|numeric|min:1',
            'adjustType' => 'required|in:add,subtract',
        ], [
            'adjustAmount.required' => 'Jumlah stok harus diisi.',
            'adjustAmount.min' => 'Jumlah minimal adalah 1.',
        ]);

        $product = Product::findOrFail($this->adjustProductId);

        if ($this->adjustType === 'add') {
            $product->stock += $this->adjustAmount;
        } else {
            // Validasi agar stok tidak minus
            if ($this->adjustAmount > $product->stock) {
                $this->addError('adjustAmount', 'Jumlah pengurangan melebihi stok saat ini!');
                return;
            }
            $product->stock -= $this->adjustAmount;
        }

        $product->save();

        session()->flash('success', 'Stok barang ' . $product->name . ' berhasil disesuaikan!');

        // Tutup modal
        $this->dispatch('close-adjust-modal');
    }

    public function render()
    {
        $dataProduct = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')->orWhere('code_product', 'like', '%' . $this->search . '%');
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


    <div class="max-w-7xl mx-auto">


        <!-- 🔍 SEARCH -->
        <div class="bg-white rounded-2xl shadow p-5 mb-6">
            <label class="block text-sm font-medium text-slate-600 mb-2">
                Cari Barang (Nama / Kode)
            </label>
            <input type="text" wire:model.live="search" placeholder="Masukkan nama atau kode barang..."
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">

        </div>

        <!-- 📊 TABLE -->
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
                                <button wire:click="openAdjustModal({{ $item->id }})"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition shadow-sm">
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

    <div x-data="{ showAdjustModal: false }" @open-adjust-modal.window="showAdjustModal = true"
        @close-adjust-modal.window="showAdjustModal = false" x-cloak>
        <div x-show="showAdjustModal" class="fixed inset-0 z-[70] overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">

                <div x-show="showAdjustModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click="showAdjustModal = false"
                    class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

                <div x-show="showAdjustModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <form wire:submit.prevent="saveAdjustStock">
                        <div class="bg-white px-6 pt-6 pb-4">
                            <div class="flex justify-between items-center mb-5 border-b pb-3">
                                <h3 class="text-lg font-bold text-slate-800">
                                    Penyesuaian Stok
                                </h3>
                                <button type="button" @click="showAdjustModal = false"
                                    class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 mb-5">
                                <p class="text-sm font-medium text-slate-700">{{ $adjustProductName }}</p>
                                <p class="text-xs text-slate-500 mt-1">Stok saat ini: <span
                                        class="font-bold text-indigo-600">{{ $currentStock }}</span></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Tindakan</label>
                                    <div class="flex gap-4">
                                        <label
                                            class="flex items-center gap-2 cursor-pointer p-2 border rounded-lg hover:bg-slate-50 w-full">
                                            <input type="radio" wire:model="adjustType" value="add"
                                                class="text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm font-medium text-slate-700">Tambah Stok (+)</span>
                                        </label>
                                        <label
                                            class="flex items-center gap-2 cursor-pointer p-2 border rounded-lg hover:bg-slate-50 w-full">
                                            <input type="radio" wire:model="adjustType" value="subtract"
                                                class="text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-sm font-medium text-slate-700">Kurangi Stok (-)</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                                    <input type="number" wire:model="adjustAmount" min="1"
                                        placeholder="Masukkan angka..."
                                        class="w-full border border-slate-300 rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none sm:text-sm">
                                    @error('adjustAmount')
                                    <span class="text-red-500 text-xs mt-1 inline-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-2">
                            <button type="submit" wire:loading.attr="disabled"
                                class="bg-yellow-500 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-yellow-600 transition disabled:opacity-50 flex items-center">
                                <span wire:loading.remove wire:target="saveAdjustStock">Simpan Penyesuaian</span>
                                <span wire:loading wire:target="saveAdjustStock">Menyimpan...</span>
                            </button>
                            <button type="button" @click="showAdjustModal = false"
                                class="bg-white text-slate-700 border border-slate-300 px-5 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>