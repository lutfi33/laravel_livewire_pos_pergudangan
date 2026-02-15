<?php

use App\Models\Supplier;
use Livewire\Component;

new class extends Component {
    //

    public $name, $email, $contact, $address, $id_sup;
    public function mount($id)
    {
        $data = Supplier::findOrfail($id);
        $this->id_sup = $data->id;
        $this->name = $data->name;
        $this->address = $data->address;
        $this->contact = $data->contact;
        $this->email = $data->email;
    }

    public function update()
    {
        Supplier::where('id', $this->id_sup)->update([
            'name' => $this->name,
            'address' => $this->address,
            'contact' => $this->contact,
            'email' => $this->email,
        ]);

        return redirect()->route('admin.supplier')->with('success', 'Data berhasi diperbaharui');
    }
    public function render()
    {
        return $this->view()
            ->title('Edit Data Supplier')
            ->layout('layouts.app');
    }
};
?>

<div class="bg-slate-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="mb-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
        @endif

        <!-- Form -->
        <form wire:submit.prevent="update" class="space-y-4">

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

                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                    Update
                </button>
            </div>

        </form>

    </div>
    {{-- The only way to do great work is to love what you do. - Steve Jobs --}}
</div>