<?php

use Livewire\Component;

new class extends Component {
    //
    public function render()
    {
        return $this->view()->title('Selling')->layout('layouts.app');
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Tanggal Dari -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Dari Tanggal
                    </label>
                    <input type="date"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Tanggal Sampai -->
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Sampai Tanggal
                    </label>
                    <input type="date"
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- Search -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Cari (Nama / Kode Barang)
                    </label>
                    <input type="text" placeholder="Masukkan nama atau kode barang..."
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

            </div>

            <div class="mt-4 flex justify-end">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition">
                    Terapkan Filter
                </button>
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
                            <th class="px-6 py-3">Harga Jual</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Nama Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">

                        <!-- Sample Row -->
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-800">BRG001</td>
                            <td class="px-6 py-4">Laptop Asus</td>
                            <td class="px-6 py-4">Rp 7.000.000</td>
                            <td class="px-6 py-4">1</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">
                                Rp 7.000.000
                            </td>
                            <td class="px-6 py-4">14-02-2026</td>
                            <td class="px-6 py-4">Andi</td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-medium text-slate-800">BRG002</td>
                            <td class="px-6 py-4">Mouse Logitech</td>
                            <td class="px-6 py-4">Rp 250.000</td>
                            <td class="px-6 py-4">2</td>
                            <td class="px-6 py-4 font-semibold text-indigo-600">
                                Rp 500.000
                            </td>
                            <td class="px-6 py-4">14-02-2026</td>
                            <td class="px-6 py-4">Budi</td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>