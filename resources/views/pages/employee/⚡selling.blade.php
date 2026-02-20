<?php

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    //
    use WithPagination;
    public function render()
    {
        $data = Transaction::paginate(10);
        return $this->view()
            ->with([
                'data' => $data,
            ])
            ->title('Penjualan')
            ->layout('layouts.app');
    }
};
?>

<div class="bg-slate-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">



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
                            <th class="px-6 py-3">Transaksi</th>
                            <th class="px-6 py-3">Nominal</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Nama Kasir</th>
                            <th class="px-6 py-3">Set</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">

                        @forelse ($data as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $item->name_transaction }}</td>
                                <td class="px-6 py-4 font-semibold text-indigo-600">
                                    Rp {{ $item->total_amount }}
                                </td>
                                <td class="px-6 py-4">{{ $item->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4">{{ $item->cashier }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ Auth::user()->role == 'staff' ? route('staff.items', ['id' => $item->id]) :  route('admin.itemSell', ['id' => $item->id])  }}" wire:navigate
                                        class="bg-amber-500 px-3 py-2 rounded-xl text-slate-100">Items</a>
                                </td>
                            </tr>
                        @empty
                            <p class="text-center ">Tidak ada data</p>
                        @endforelse
                        <!-- Sample Row -->


                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
