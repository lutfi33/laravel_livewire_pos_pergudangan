<?php

use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // model input
    public $supplier, $name, $stock, $code_product, $harga_beli, $harga_jual;
    public $search = '';

    public $item_id;
    public $price;
    public $qty;
    public $purchase_date;

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    protected $rules = [
        'code_product' => 'required',
        'name' => 'required',
        'stock' => 'required|numeric|min:0',
        'harga_beli' => 'required|numeric|min:0',
        'harga_jual' => 'required|numeric|gte:harga_beli',
        'supplier' => 'required',
    ];

    protected $messages = [
        'harga_jual.gte' => 'Harga jual tidak boleh lebih rendah dari harga beli.',
    ];

    public function save()
    {
        $this->validate();

        Product::create([
            'code_product' => $this->code_product,
            'name' => $this->name,
            'stock' => $this->stock,
            'harga_beli' => $this->harga_beli,
            'harga_jual' => $this->harga_jual,
            'supplier_id' => $this->supplier,
        ]);

        $this->reset(['code_product', 'supplier', 'name', 'stock', 'harga_beli', 'harga_jual']);
        // $this->purchase_date = now()->format('Y-m-d');

        session()->flash('success', 'Pembelian berhasil disimpan!');

        $this->dispatch('close-modal');
    }

    public function render()
    {
        $dataPembelian = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code_product', 'like', '%' . $this->search . '%');
                });
            })
            ->with('productToSupplier')
            ->latest()
            ->paginate(10);

        $dataSup = Supplier::get();
        return $this->view()
            ->with([
                'data' => $dataPembelian,
                'dataSup' => $dataSup,
            ])
            ->title('Data Pembelian')
            ->layout('layouts.app');
    }
    //
};
?>

<div class="bg-slate-100 min-h-screen p-6" x-data="{ openModal: false }">

    <div class="max-w-7xl mx-auto">

        <!-- ========================= -->
        <!-- 🔹 HEADER -->
        <!-- ========================= -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-800">
                Data Pembelian Barang
            </h1>

            <button @click="openModal = true"
                class="mt-3 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm transition">
                + Tambah Pembelian
            </button>
        </div>

        <!-- ========================= -->
        <!-- 🔎 SEARCH -->
        <!-- ========================= -->
        <div class="bg-white rounded-2xl shadow p-5 mb-6">
            <input wire:model.live="search" type="text" placeholder="Cari berdasarkan nama / kode barang..."
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>



        @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="mb-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
        @endif
        <!-- ========================= -->
        <!-- 📊 TABLE -->
        <!-- ========================= -->
        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Kode Barang</th>
                            <th class="px-6 py-3">Nama Barang</th>
                            <th class="px-6 py-3">Harga Beli</th>
                            <th class="px-6 py-3">Harga jual</th>
                            <th class="px-6 py-3">Jumlah Stok</th>
                            <th class="px-6 py-3">Tanggal Masuk</th>
                            <th class="px-6 py-3">Supplier</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($data as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $item->code_product }}</td>
                            <td class="px-6 py-4">{{ $item->name }}</td>
                            <td class="px-6 py-4">{{ $item->harga_beli }}</td>
                            <td class="px-6 py-4">{{ $item->harga_jual }}</td>
                            <td class="px-6 py-4">{{ $item->stock }}</td>
                            <td class="px-6 py-4">{{ $item->created_at->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $item->productToSupplier->name }}</td>
                        </tr>
                        @empty
                        <p class="flex justify-center text-sm my-5">Data tidak tersedia</p>
                        @endforelse


                    </tbody>
                </table>
                <div class="flex justify-end">
                    {{ $data->links() }}
                </div>
            </div>

        </div>

    </div>


    <!-- ========================= -->
    <!-- 🪟 MODAL TAMBAH PEMBELIAN -->
    <!-- ========================= -->
    <template x-if="openModal">
        <div x-data="{ show: false }" x-init="document.body.classList.add('overflow-hidden');
        setTimeout(() => show = true, 10)"
            @keydown.escape.window="show = false; setTimeout(() => openModal = false, 200)"
            class="fixed inset-0 z-50 flex items-center justify-center">

            <!-- Overlay -->
            <div @click="show = false; setTimeout(() => openModal = false, 200)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            <!-- Modal Box -->
            <div x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-90 translate-y-6"
                @click.away="show = false; setTimeout(() => openModal = false, 200)"
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">

                <h2 class="text-lg font-bold text-slate-800 mb-4">
                    Tambah Pembelian
                </h2>

                <form class="space-y-4" wire:submit.prevent="save">

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Supplier
                        </label>
                        <select wire:model="supplier"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                            <option value="">-- Pilih Supplier --</option>

                            @foreach ($dataSup as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>

                        @error('supplier')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror


                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Kode Barang
                        </label>
                        <input type="number" wire:model="code_product"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('code_product')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Nama Barang
                        </label>
                        <input type="text" wire:model='name'
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Harga Beli
                        </label>
                        <input type="number" wire:model='harga_beli'
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('harga_beli')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Harga Jual
                        </label>
                        <input type="number" wire:model='harga_jual'
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('harga_jual')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Jumlah Beli
                        </label>
                        <input type="number" wire:model='stock'
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('stock')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>



                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="openModal = false"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100">
                            Batal
                        </button>

                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </template>

</div>