<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public $email;
    public $password;
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];


    protected $messages = [
        'email.required' => 'Email harus diisi',
        'email.email' => 'Format email tidak valid',
        'password.required' => 'Password harus diisi',
        'password.min' => 'Password minimal 6 karakter',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password
        ], $this->remember)) {

            session()->regenerate();

            $user = Auth::user();

            if ($user->role === 'owner') {
                return redirect()->route('admin.items');
            }

            return redirect()->route('staff.transaction');
        }

        $this->addError('email', 'Email atau password salah.');
    }

    public function render()
    {
        return $this->view()->title('Login')->layout('layouts.guest');
    }
};
?>

<div class="bg-slate-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white shadow-xl rounded-2xl p-8">

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800">Welcome Back 👋</h2>
            <p class="text-slate-500 text-sm mt-2">Please login to your account</p>
        </div>

        <form class="space-y-5" wire:submit.prevent="login">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" wire:model="email" placeholder="you@example.com"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('email')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" wire:model="password" placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('password')
                <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="remember"
                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>
                <a href="#" class="text-indigo-600 hover:underline">Forgot password?</a>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition duration-200">
                Login
            </button>

        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            Don't have an account?
            <a href="#" class="text-indigo-600 font-medium hover:underline">Register</a>
        </p>

    </div>
</div>