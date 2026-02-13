<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new class extends Component {
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

        <form class="space-y-5">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" placeholder="you@example.com"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
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
