<?php

use Livewire\Component;

new class extends Component {
    //
    public function render()
    {
        return $this->view()->title('Setting')->layout('layouts.app');
    }
};
?>

<div class="bg-slate-100 min-h-screen p-6">
    <div class="bg-white rounded-2xl shadow p-6 max-w-2xl">
        <h2 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Konfigurasi Perangkat Kasir</h2>

        <div x-data="{
            printerType: localStorage.getItem('pos_printer_type') || '',
            savePrinter() {
                localStorage.setItem('pos_printer_type', this.printerType);
                alert('Pengaturan printer berhasil disimpan di perangkat ini.');
            }
        }" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Metode Cetak Struk (Printer Thermal)</label>
                <p class="text-xs text-slate-500 mb-3">Pengaturan ini disimpan khusus untuk perangkat/browser ini.</p>

                <select x-model="printerType" @change="savePrinter()"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none font-medium text-slate-700">
                    <option value="" disabled>-- Pilih Koneksi Printer --</option>
                    <option value="bluetooth">Bluetooth (Wireless BLE)</option>
                    <option value="usb">Kabel USB (WebUSB API)</option>
                </select>
            </div>

            <button type="button" onclick="testPrint()"
                class="mt-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm transition">
                Test Koneksi Printer
            </button>
        </div>
    </div>

    <script>
        // Tambahkan window. di depan semua nama fungsi agar dikenali oleh tombol
        window.testPrint = async function() {
            // Ambil pilihan printer saat ini dari localStorage
            const printerType = localStorage.getItem('pos_printer_type');

            if (!printerType) {
                alert('Silakan pilih metode koneksi printer terlebih dahulu pada dropdown di atas!');
                return;
            }

            // Buat data struk contoh (dummy)
            const dummyData = {
                invoice: 'TEST-PRINT-001',
                kasir: 'Sistem POS',
                tanggal: new Date().toLocaleString('id-ID'),
                items: [{
                        name: 'Barang Tes Koneksi 1',
                        qty: 1,
                        price: 15000
                    },
                    {
                        name: 'Barang Tes Koneksi 2',
                        qty: 2,
                        price: 5000
                    }
                ],
                total: 25000,
                payment: 50000,
                kembalian: 25000
            };

            try {
                // Generate bytes ESC/POS
                const receiptBytes = window.generateReceiptBytes(dummyData);

                alert(
                    `Memulai test print via ${printerType.toUpperCase()}...\nPastikan printer sudah menyala dan siap.`);

                if (printerType === 'bluetooth') {
                    await window.printViaBluetooth(receiptBytes);
                } else if (printerType === 'usb') {
                    await window.printViaUSB(receiptBytes);
                }

            } catch (error) {
                console.error('Test Print Error:', error);
                alert('Gagal terhubung ke printer:\n' + error.message);
            }
        }

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

            let text = INIT + CENTER + BOLD_ON + "NAMA TOKO ANDA" + LF + BOLD_OFF;
            text += "Jl. Contoh Alamat No.123" + LF;
            text += "--------------------------------" + LF + LEFT;
            text += "Trx: " + data.invoice + LF;
            text += "Ksr: " + data.kasir + LF;
            text += "Tgl: " + data.tanggal + LF;
            text += "--------------------------------" + LF;

            for (let i = 0; i < data.items.length; i++) {
                let item = data.items[i];
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
        }

        // ==========================================
        // FUNGSI PRINT VIA BLUETOOTH
        // ==========================================
        window.printViaBluetooth = async function(rawBytes) {
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'] // UUID Standar Printer
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
            alert('Test print Bluetooth berhasil dikirim!');
        }

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
            alert('Test print USB berhasil dikirim!');
        }
    </script>
</div>
