<?php

use App\Models\ItemSelling;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    //
    use WithPagination;

    // Simpan ID transaksinya saja, BUKAN datanya
    public $transactionId;

    public function mount($id)
    {
        $this->transactionId = $id;
    }

    public function render()
    {
        // 1. Lakukan query dan paginasi DI SINI, bukan di mount()
        $itemData = ItemSelling::where('transaction_id', $this->transactionId)->paginate(10);
        $totalTransaksi = ItemSelling::where('transaction_id', $this->transactionId)->sum('qty');
        $totalBelanja = ItemSelling::where('transaction_id', $this->transactionId)->sum('price');

        return $this->view()
            ->with([
                'itemData' => $itemData,
                'totalTransaksi' => $totalTransaksi, 
                'totalBelanja' => $totalBelanja,
            ])
            ->title('Item Barang')
            ->layout('layouts.app');
    }
};
?>


<div class="bg-slate-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">

        <!-- ============================= -->
        <!-- 🔎 FILTER & SEARCH -->
        <!-- ============================= -->
        <div class="bg-white shadow rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-slate-800 mb-4">
                Filter Penjualan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

                <div class="bg-white rounded-2xl shadow p-6 flex items-center border border-slate-100">
                    <div class="p-4 bg-indigo-50 text-indigo-600 rounded-xl mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Pembelian</p>
                        <h3 class="text-2xl font-bold text-slate-800">{{ $totalTransaksi ?? 0 }} <span
                                class="text-sm font-medium text-slate-500"></span></h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-6 flex items-center border border-slate-100">
                    <div class="p-4 bg-red-50 text-red-500 rounded-xl mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Belanja</p>
                        <h3 class="text-2xl font-bold text-slate-800">Rp
                            {{ number_format($totalBelanja ?? 0, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>


            </div>
        </div>


        <!-- ============================= -->
        <!-- 📊 TABEL PENJUALAN -->
        <!-- ============================= -->
        <div class="bg-white shadow rounded-2xl overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-800">
                    Daftar Penjualan
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Kode Barang</th>
                            <th class="px-6 py-3">Nama Barang</th>
                            <th class="px-6 py-3">Harga Satuan</th>
                            <th class="px-6 py-3">Jumlah</th>
                            <th class="px-6 py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($itemData as $item)
                        <!-- Sample Row -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $item->id_product }}</td>
                            <td class="px-6 py-4">{{ $item->product }}</td>
                            <td class="px-6 py-4">Rp {{ $item->price }}</td>
                            <td class="px-6 py-4">{{ $item->qty }}</td>
                            <td class="px-6 py-4">{{ $item->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td>Tidak ada</td>
                        </tr>
                        @endforelse


                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>