<?php

use App\Models\ItemSelling;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component {
    //
    public $search = '';
    public $qty = []; // Menyimpan input jumlah dari masing-masing barang
    public $cart = [];

    // State untuk Checkout Modal
    public $showCheckoutModal = false;
    public $paymentAmount; // Input uang pelanggan
    public $kembalian = 0;

    // State untuk Struk Cetak
    public $receiptData = null;

    // Menghitung total harga secara dinamis
    public function getTotalHargaProperty()
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }

    // Fungsi Konfirmasi Pembayaran
    public function confirmCheckout()
    {
        $totalHarga = $this->getTotalHargaProperty();

        // 1. Validasi Pembayaran
        $this->validate(
            [
                'paymentAmount' => 'required|numeric|min:' . $totalHarga,
            ],
            [
                'paymentAmount.required' => 'Masukkan nominal pembayaran.',
                'paymentAmount.min' => 'Uang pembayaran kurang dari total belanja!',
            ],
        );

        if (empty($this->cart)) {
            return;
        }

        DB::beginTransaction();
        try {
            $invoiceNumber = 'TRX-' . strtoupper(uniqid());
            $kasirName = Auth::check() ? Auth::user()->name : 'Kasir Default';

            // 2. Simpan Transaksi Utama
            $transaction = Transaction::create([
                'name_transaction' => $invoiceNumber,
                'total_amount' => $totalHarga,
                'cash' => $this->paymentAmount,
                'return' => $this->kembalian,
                'cashier' => $kasirName,
                'payment' => 'Cash',
            ]);

            // 3. Simpan Item Transaksi
            foreach ($this->cart as $item) {
                ItemSelling::create([
                    'transaction_id' => $transaction->id,
                    'id_product' => $item['id'],
                    'product' => $item['name'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);

                // Opsional: Kurangi stok barang di tabel Product di sini
                Product::find($item['id'])->decrement('stock', $item['qty']);
            }

            DB::commit();

            // 4. Siapkan Data Struk untuk di-print
            $this->receiptData = [
                'invoice' => $invoiceNumber,
                'kasir' => $kasirName,
                'tanggal' => now()->format('d/m/Y H:i'),
                'items' => $this->cart,
                'total' => $totalHarga,
                'payment' => $this->paymentAmount,
                'kembalian' => $this->kembalian,
            ];

            // 5. Bersihkan Keranjang & Tutup Modal
            $this->cart = [];
            $this->paymentAmount = null;
            $this->kembalian = 0;
            $this->showCheckoutModal = false;

            // 6. Trigger event untuk Print Struk beserta datanya
            $this->dispatch('print-bluetooth', receipt: $this->receiptData);
             session()->flash('success', 'Transaksi Berhasil Dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('paymentAmount', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // Fungsi ini berjalan otomatis tiap kali user mengetik nominal pembayaran
    public function updatedPaymentAmount($value)
    {
        $totalHarga = $this->getTotalHargaProperty();
        $this->kembalian = (int) $value - $totalHarga;
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        // Ambil qty dari input, jika kosong/tidak diisi defaultnya 1
        $quantity = !empty($this->qty[$productId]) ? $this->qty[$productId] : 1;

        // Cek apakah barang sudah ada di keranjang
        if (isset($this->cart[$productId])) {
            // Jika ada, tambahkan qty-nya
            $this->cart[$productId]['qty'] += $quantity;
        } else {
            // Jika belum, buat item baru di keranjang
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->harga_jual,
                'qty' => $quantity,
            ];
        }

        // Reset input angka kembali ke 1 setelah sukses ditambah
        $this->qty[$productId] = 1;
    }

    // Fungsi menghapus dari keranjang
    public function removeFromCart($productId)
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
        }
    }

    public function render()
    {
        $dataProduct = Product::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')->orWhere('code_product', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        // Kalkulasi Total
        $totalHarga = 0;
        $totalItem = 0;

        foreach ($this->cart as $item) {
            $totalHarga += $item['price'] * $item['qty'];
            $totalItem += $item['qty'];
        }

        return $this->view()
            ->with([
                'dataProduct' => $dataProduct,
                'totalHarga' => $totalHarga,
                'totalItem' => $totalItem,
            ])
            ->title('Transaksi')
            ->layout('layouts.app');
    }
};
?>

<div class="bg-slate-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto print:hidden">

        <h1 class="text-2xl font-bold text-slate-800 mb-6">
            Transaksi Penjualan
        </h1>

        <!-- NOTIFICATION -->
        @if (session()->has('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
                class="mb-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 bg-white rounded-2xl shadow p-6">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Cari Kode Barang</label>
                    <input type="text" wire:model.live="search" placeholder="Scan / Masukkan kode barang..."
                        class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">Nama Barang</th>
                                <th class="px-4 py-3">Harga</th>
                                <th class="px-4 py-3">Stock</th>
                                <th class="px-4 py-3 text-center">Jumlah</th>
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
                                        {{ $item->stock }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{-- Binding array qty ke ID barang --}}
                                        <input type="number" min="1" wire:model="qty.{{ $item->id }}"
                                            placeholder="1"
                                            class="w-16 border border-slate-300 rounded-md px-2 py-1 text-center outline-none focus:border-indigo-500">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{-- Panggil addToCart sambil mengirim ID barang --}}
                                        <button wire:click="addToCart({{ $item->id }})"
                                            class="bg-indigo-500 cursor-pointer hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all active:scale-95">
                                            Pilih
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-slate-400 italic">
                                        Data barang tidak ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="bg-white rounded-2xl shadow p-6 flex flex-col h-fit">

                <h2 class="text-lg font-bold text-slate-800 mb-6">Ringkasan Pembayaran</h2>

                <div class="flex justify-between mb-3 text-sm">
                    <span class="text-slate-600">Jumlah Item</span>
                    <span class="font-semibold">{{ $totalItem }}</span>
                </div>

                <div class="flex justify-between mb-4 text-lg">
                    <span class="text-slate-700 font-medium">Total Harga</span>
                    <span class="font-bold text-indigo-600">
                        Rp {{ number_format($totalHarga, 0, ',', '.') }}
                    </span>
                </div>

                <hr class="mb-4">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-3">Item Terpilih</label>

                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2">
                        @forelse ($cart as $key => $cartItem)
                            <div
                                class="flex justify-between items-center text-sm p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800 truncate w-32"
                                        title="{{ $cartItem['name'] }}">
                                        {{ $cartItem['name'] }}
                                    </p>
                                    <p class="text-slate-500 text-xs">
                                        {{ $cartItem['qty'] }} x Rp
                                        {{ number_format($cartItem['price'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="font-bold text-slate-700 mr-3">
                                    Rp {{ number_format($cartItem['qty'] * $cartItem['price'], 0, ',', '.') }}
                                </div>

                                {{-- Panggil removeFromCart dengan key array --}}
                                <button wire:click="removeFromCart({{ $key }})" title="Hapus"
                                    class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div
                                class="text-center py-6 text-xs text-slate-400 italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                Keranjang masih kosong
                            </div>
                        @endforelse
                    </div>
                </div>

                <button wire:click="$set('showCheckoutModal', true)" @if (empty($cart)) disabled @endif
                    class="mt-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Lanjut Pembayaran
                </button>

            </div>
        </div>

        @if ($showCheckoutModal)
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 print:hidden">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                    wire:click="$set('showCheckoutModal', false)"></div>

                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                    <div class="bg-indigo-600 p-6 text-white text-center">
                        <h3 class="text-lg font-medium opacity-80">Total Tagihan</h3>
                        <p class="text-4xl font-black mt-1">Rp {{ number_format($this->totalHarga, 0, ',', '.') }}</p>
                    </div>

                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Uang Diterima (Rp)</label>
                            <input type="number" wire:model.live.debounce.300ms="paymentAmount" autofocus
                                class="w-full text-2xl font-bold bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all"
                                placeholder="0">
                            @error('paymentAmount')
                                <span class="text-rose-500 text-xs font-bold mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div
                            class="p-4 rounded-xl border-2 {{ $kembalian < 0 ? 'bg-rose-50 border-rose-100 text-rose-700' : 'bg-emerald-50 border-emerald-100 text-emerald-700' }}">
                            <p class="text-sm font-bold opacity-80 mb-1">Kembalian</p>
                            <p class="text-2xl font-black">
                                Rp {{ number_format($kembalian > 0 ? $kembalian : 0, 0, ',', '.') }}
                            </p>
                            @if ($kembalian < 0 && $paymentAmount > 0)
                                <p class="text-xs font-bold mt-1">Uang kurang Rp
                                    {{ number_format(abs($kembalian), 0, ',', '.') }}
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button wire:click="$set('showCheckoutModal', false)"
                                class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl hover:bg-slate-200 transition-all">
                                Batal
                            </button>
                            <button wire:click="confirmCheckout" wire:loading.attr="disabled"
                                class="flex-1 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all flex justify-center items-center gap-2">
                                <span wire:loading.remove wire:target="confirmCheckout">Konfirmasi & Cetak</span>
                                <span wire:loading wire:target="confirmCheckout" class="flex items-center gap-2">
                                    Memproses...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif



    </div>
    <script>
        window.addEventListener('print-bluetooth', async (event) => {
            // Livewire 3 mengirim named parameters di dalam event.detail
            const data = event.detail.receipt;

            // Ambil pengaturan printer dari LocalStorage
            const printerType = localStorage.getItem('pos_printer_type');

            // Validasi jika user belum mengatur printer di halaman settings
            if (!printerType) {
                alert(
                    'Pengaturan printer belum dilakukan! Silakan ke halaman Settings untuk memilih metode cetak (Bluetooth/USB).'
                    );
                return;
            }

            try {
                // Generate bytes ESC/POS
                const receiptBytes = window.generateReceiptBytes(data);

                // Eksekusi berdasarkan pilihan dari localStorage
                if (printerType === 'bluetooth') {
                    await window.printViaBluetooth(receiptBytes);
                } else if (printerType === 'usb') {
                    await window.printViaUSB(receiptBytes);
                }

            } catch (error) {
                console.error('Gagal mencetak:', error);
                alert('Gagal terhubung ke printer:\n' + error.message);
            }
        });

        // ==========================================
        // FUNGSI PENDUKUNG FORMAT ESC/POS
        // ==========================================
        window.generateReceiptBytes = function(data) {
            const ESC = '\x1B',
                GS = '\x1D',
                INIT = ESC + '@',
                CENTER = ESC + 'a' + '\x01',
                LEFT = ESC + 'a' + '\x00',
                BOLD_ON = ESC + 'E' + '\x01',
                BOLD_OFF = ESC + 'E' + '\x00',
                LF = '\x0A';

            let text = INIT + CENTER + BOLD_ON + "Toko Dua Putra" + LF + BOLD_OFF;
            text += "Jl. Contoh Alamat No.123" + LF;
            text += "--------------------------------" + LF + LEFT;
            text += "Trx: " + data.invoice + LF;
            text += "Ksr: " + data.kasir + LF;
            text += "Tgl: " + data.tanggal + LF;
            text += "--------------------------------" + LF;

            // Loop item keranjang
            // Menggunakan Object.values() atau for..in tergantung format array/object dari PHP
            const items = Array.isArray(data.items) ? data.items : Object.values(data.items);
            for (let i = 0; i < items.length; i++) {
                let item = items[i];
                text += item.name.substring(0, 32) + LF;
                text += item.qty + " x " + item.price.toLocaleString('id-ID') + " = " + (item.qty * item.price)
                    .toLocaleString('id-ID') + LF;
            }

            text += "--------------------------------" + LF + BOLD_ON;
            text += "TOTAL   : Rp " + data.total.toLocaleString('id-ID') + LF;
            text += "TUNAI   : Rp " + data.payment.toLocaleString('id-ID') + LF;
            text += "KEMBALI : Rp " + data.kembalian.toLocaleString('id-ID') + LF;
            text += BOLD_OFF + CENTER + LF + "Terima Kasih!" + LF + LF + LF + LF;

            return new TextEncoder().encode(text);
        };

        // ==========================================
        // FUNGSI PRINT VIA BLUETOOTH
        // ==========================================
        window.printViaBluetooth = async function(rawBytes) {
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
            });

            const server = await device.gatt.connect();
            const services = await server.getPrimaryServices();
            let printChar = null;

            for (const service of services) {
                const chars = await service.getCharacteristics();
                for (const char of chars) {
                    if (char.properties.write || char.properties.writeWithoutResponse) {
                        printChar = char;
                        break;
                    }
                }
                if (printChar) break;
            }

            if (!printChar) throw new Error('Karakteristik Bluetooth Print tidak ditemukan.');

            const chunkSize = 100;
            for (let i = 0; i < rawBytes.length; i += chunkSize) {
                await printChar.writeValue(rawBytes.slice(i, i + chunkSize));
            }
            device.gatt.disconnect();
        };

        // ==========================================
        // FUNGSI PRINT VIA USB
        // ==========================================
        window.printViaUSB = async function(rawBytes) {
            const device = await navigator.usb.requestDevice({
                filters: []
            });
            await device.open();
            await device.selectConfiguration(1);
            await device.claimInterface(0);

            let outEndpoint = null;
            for (const endpoint of device.configuration.interfaces[0].alternate.endpoints) {
                if (endpoint.direction === 'out') {
                    outEndpoint = endpoint.endpointNumber;
                    break;
                }
            }

            if (!outEndpoint) throw new Error('Endpoint output USB tidak ditemukan.');
            await device.transferOut(outEndpoint, rawBytes);
            await device.close();
        };
    </script>

</div>
