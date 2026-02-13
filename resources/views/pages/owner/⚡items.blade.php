<?php

use Livewire\Component;

new class extends Component {
    //
    public function render()
    {
        return $this->view()->title('Login')->layout('layouts.app');
    }
};
?>

<div>

    <h1 class="text-3xl font-bold text-slate-800 mb-6">
        Dashboard Overview
    </h1>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-slate-500 text-sm">Total Users</h3>
            <p class="text-2xl font-bold text-slate-800 mt-2">1,245</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-slate-500 text-sm">Total Revenue</h3>
            <p class="text-2xl font-bold text-slate-800 mt-2">$12,340</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-slate-500 text-sm">Active Projects</h3>
            <p class="text-2xl font-bold text-slate-800 mt-2">18</p>
        </div>

    </div>
</div>