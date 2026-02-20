<?php

use App\Models\Product;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // Model input - Diseragamkan menggunakan $supplier_id
    public $supplier_id, $name, $stock, $code_product, $harga_beli, $harga_jual, $productId;
    public $search = '';
    public $purchase_date;
    public $isModalOpen = false;

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
    }

    // WAJIB: Reset halaman ke 1 setiap kali mengetik pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected $rules = [
        'code_product' => 'required|numeric',
        'name' => 'required',
        'stock' => 'required|numeric|min:0',
        'harga_beli' => 'required|numeric|min:0',
        'harga_jual' => 'required|numeric|gte:harga_beli',
        'supplier_id' => 'required', // Ubah menjadi supplier_id agar konsisten
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
            'supplier_id' => $this->supplier_id,
        ]);

        $this->reset(['code_product', 'supplier_id', 'name', 'stock', 'harga_beli', 'harga_jual']);

        session()->flash('success', 'Pembelian berhasil disimpan!');

        // Kirim sinyal ke AlpineJS untuk menutup modal tambah
        $this->dispatch('close-create-modal');
    }

    public function edit($id)
    {
        $this->resetValidation(); // Tambahkan ini agar error sebelumnya hilang

        $product = Product::findOrFail($id);
        $this->productId = $id;
        $this->code_product = $product->code_product;
        $this->name = $product->name;
        $this->harga_beli = $product->harga_beli;
        $this->harga_jual = $product->harga_jual;
        $this->stock = $product->stock;
        $this->supplier_id = $product->supplier_id;

        // GANTI baris $this->openModal(); menjadi ini:
        $this->dispatch('open-edit-modal');
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->reset(['productId', 'code_product', 'name', 'harga_beli', 'harga_jual', 'stock', 'supplier_id']);
    }

    // Fungsi Update Data
    public function update()
    {
        $this->validate();

        if ($this->productId) {
            $product = Product::find($this->productId);
            $product->update([
                'code_product' => $this->code_product,
                'name' => $this->name,
                'harga_beli' => $this->harga_beli,
                'harga_jual' => $this->harga_jual,
                'stock' => $this->stock,
                'supplier_id' => $this->supplier_id,
            ]);

            session()->flash('success', 'Produk berhasil diperbaharui.');

            // GANTI baris $this->closeModal(); menjadi ini:
            $this->dispatch('close-edit-modal');
        }
    }

    // Fungsi Delete Data
    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('success', 'Produk berhasil dihapus.'); // Ubah jadi 'success'
    }

    public function render()
    {
        $dataPembelian = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')->orWhere('code_product', 'like', '%' . $this->search . '%');
                });
            })
            ->with('productToSupplier')
            ->latest()
            ->paginate(10);

        // Seragamkan variabel menjadi $suppliers agar Blade tidak error
        $suppliers = Supplier::get();

        return $this->view()
            ->with([
                'data' => $dataPembelian,
                'suppliers' => $suppliers,
            ])
            ->title('Data Pembelian')
            ->layout('layouts.app');
    }
    //
};
?>

<div class="bg-slate-100 min-h-screen p-6"
    x-data="{ showUpdateModal: false, openModal: false }"
    @open-edit-modal.window="showUpdateModal = true"
    @close-edit-modal.window="showUpdateModal = false"
    x-cloak>

    <div class="max-w-7xl mx-auto">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Data Pembelian Barang</h1>
            <button @click="openModal = true"
                class="mt-3 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm transition">
                + Tambah Pembelian
            </button>
        </div>

        <!-- SEARCH -->
        <div class="bg-white rounded-2xl shadow p-5 mb-6">
            <input wire:model.live="search" type="text" placeholder="Cari berdasarkan nama / kode barang..."
                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <!-- NOTIFICATION -->
        @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="mb-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
        @endif

        <!-- TABLE -->
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
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($data as $item)
                        <tr wire:key="product-row-{{ $item->id }}" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $item->code_product }}</td>
                            <td class="px-6 py-4">{{ $item->name }}</td>
                            <td class="px-6 py-4">Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $item->stock }}</td>
                            <td class="px-6 py-4">{{ $item->created_at->format('d-m-Y') }}</td>
                            <td class="px-6 py-4">{{ $item->productToSupplier->name ?? '-' }}</td>
                            <td class="px-6 py-4 flex justify-center space-x-2">
                                <button wire:click="edit({{ $item->id }})"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    Edit
                                </button>
                                <button
                                    onclick="confirm('Yakin ingin menghapus?') || event.stopImmediatePropagation()"
                                    wire:click="delete({{ $item->id }})"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs flex items-center transition">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 italic">Data tidak tersedia</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="flex justify-end p-4">
                    {{ $data->links() }}
                </div>
            </div>
        </div>



    </div>

    {{-- modal update --}}
    <div x-show="showUpdateModal" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">

            <div x-show="showUpdateModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" @click="showUpdateModal = false"
                class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

            <div x-show="showUpdateModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="update">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-bold text-slate-800">Update Produk</h3>
                            <button type="button" @click="showUpdateModal = false"
                                class="text-slate-400 hover:text-slate-600">
                                ✕
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Kode Barang</label>
                                <input type="number" wire:model="code_product"
                                    class="mt-1 p-2 block w-full rounded-xl border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('code_product')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Nama Barang</label>
                                <input type="text" wire:model="name"
                                    class="mt-1 p-2 block w-full rounded-xl border-slate-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('name')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Harga Beli</label>
                                    <input type="number" wire:model="harga_beli"
                                        class="mt-1 block p-2 w-full rounded-xl border-slate-300 shadow-sm sm:text-sm">
                                    @error('harga_beli')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Harga Jual</label>
                                    <input type="number" wire:model="harga_jual"
                                        class="mt-1 block p-2 w-full rounded-xl border-slate-300 shadow-sm sm:text-sm">
                                    @error('harga_jual')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Stok</label>
                                    <input type="number" wire:model="stock"
                                        class="mt-1 block p-2 w-full rounded-xl border-slate-300 shadow-sm sm:text-sm">
                                    @error('stock')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Supplier</label>
                                    <select wire:model="supplier_id"
                                        class="mt-1 block p-2 w-full rounded-xl border-slate-300 shadow-sm sm:text-sm">
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($suppliers as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id')
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
                            Simpan Perubahan
                        </button>
                        <button type="button" @click="showUpdateModal = false"
                            class="bg-white text-slate-700 border border-slate-300 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PEMBELIAN (MENGGUNAKAN ALPINEJS STATE) -->
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
                class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">

                <h2 class="text-lg font-bold text-slate-800 mb-4">Tambah Pembelian</h2>

                <form class="space-y-4" wire:submit.prevent="save">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Supplier</label>
                        <select wire:model="supplier_id"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($suppliers as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Kode Barang</label>
                        <input type="number" wire:model="code_product"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('code_product')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nama Barang</label>
                        <input type="text" wire:model="name"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('name')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Harga Beli</label>
                            <input type="number" wire:model="harga_beli"
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @error('harga_beli')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Harga Jual</label>
                            <input type="number" wire:model="harga_jual"
                                class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @error('harga_jual')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Jumlah Beli</label>
                        <input type="number" wire:model="stock"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('stock')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

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