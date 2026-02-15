<?php

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    //

    public $name;
    public $address;
    public $contact;
    public $email;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'contact' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ];
    }

    public function save()
    {
        $this->validate();

        Supplier::create([
            'name' => $this->name,
            'address' => $this->address,
            'contact' => $this->contact,
            'email' => $this->email,
        ]);

        $this->reset();

        session()->flash('success', 'Supplier berhasil ditambahkan!');
        $this->dispatch('close-modal'); // event untuk close modal
    }

    public function delete($id)
    {
        $del = Supplier::where('id', $id);
        if ($del) {
            $del->delete();
            session()->flash('success', 'Ucapan berhasil dihapus.');
        } else {
            session()->flash('error', 'Gagal menghapus. Data tidak ditemukan atau Anda tidak memiliki akses.');
        }
    }

    public function render()
    {
        $dataSupplier = Supplier::paginate(15);
        return $this->view()
            ->with([
                'data' => $dataSupplier,
            ])
            ->title('Data Supplier')
            ->layout('layouts.app');
    }
};
?>


<div class="bg-slate-100 min-h-screen p-6">

    <div class="max-w-7xl mx-auto" x-data="{ openModal: false }" x-on:close-modal.window="openModal = false">


        @if (session()->has('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
                class="mb-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
                {{ session('success') }}
            </div>
        @endif
        <!-- ================= HEADER ================= -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Data Supplier</h1>
                <p class="text-sm text-slate-500">Kelola data supplier barang</p>
            </div>

            <!-- Button Add -->
            <button @click="openModal = true"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl shadow transition">
                + Add Supplier
            </button>
        </div>

        <!-- ================= TABLE ================= -->
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Nama Supplier</th>
                            <th class="px-6 py-3">Alamat</th>
                            <th class="px-6 py-3">Kontak</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-800">
                                    {{ $item->name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->address }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->contact }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $item->email }}
                                </td>
                                <td class="px-6 py-4 text-center space-x-2">
                                    <a href="{{ route('admin.updatesup', ['id' => $item->id]) }}"
                                        wire:navigate class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-1.5 rounded-lg text-xs transition">
                                        Edit
                                    </a>
                                    <button wire:click="delete({{ $item->id }})"
                                    wire:confirm="Yakin ingin menghapus?" title="Hapus"
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg text-xs transition">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <p class="text-sm text-center">Data Tidak Tersedia</p>
                        @endforelse


                    </tbody>
                </table>
                <div class="flex justify-end py-4 me-3">
                    {{ $data->links() }}
                </div>
            </div>
        </div>


        <!-- ================= MODAL ================= -->
        <div x-show="openModal" x-transition.opacity @keydown.escape.window="openModal = false"
            class="fixed inset-0 bg-black/40 flex items-center justify-center p-4" style="display: none;">

            <!-- Modal Box -->
            <div @click.away="openModal = false" x-transition
                class="bg-white w-full max-w-lg rounded-2xl shadow-xl p-6">

                <!-- Header -->
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-slate-800">
                        Tambah Supplier
                    </h2>
                    <button @click="openModal = false" class="text-slate-400 hover:text-red-500 text-xl">
                        &times;
                    </button>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save" class="space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Nama Supplier
                        </label>
                        <input type="text" wire:model="name"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('name')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Alamat
                        </label>
                        <textarea wire:model="address"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                        @error('address')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Kontak
                        </label>
                        <input type="text" wire:model="contact"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('contact')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Email
                        </label>
                        <input type="email" wire:model="email"
                            class="w-full border border-slate-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        @error('email')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-2 pt-4">
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

    </div>

</div>
