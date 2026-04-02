<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GalaxyBackground from '@/Components/GalaxyBackground.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen flex items-center justify-center bg-[#0b0f1a] relative overflow-hidden font-sans">
        <!-- Galaxy Background -->
        <GalaxyBackground />
        
        <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/10 blur-[150px] rounded-full z-10"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 blur-[150px] rounded-full z-10"></div>

        <div class="w-full max-w-md bg-white/5 backdrop-blur-2xl border border-white/10 p-10 rounded-[2.5rem] shadow-2xl z-20 relative">
            
            <div class="text-center mb-10">
                <h2 class="text-3xl font-black text-cyan-400 tracking-tighter uppercase">Dryex</h2>
                <p class="text-gray-400 text-sm mt-2">Forgot your password?</p>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="absolute top-6 right-6">
                <DarkModeToggle />
            </div>

            <div v-if="status" class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-2xl">
                <p class="text-green-400 text-sm">{{ status }}</p>
            </div>

            <p class="text-gray-400 text-sm mb-8 leading-relaxed">
                No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
            </p>

            <form @submit.prevent="submit">
                <div class="mb-8">
                    <label class="block text-gray-300 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Email Address</label>
                    <input v-model="form.email" type="email" 
                           class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50 transition-all"
                           placeholder="name@company.com" required autofocus>
                    <span v-if="form.errors.email" class="text-red-400 text-xs mt-1 ml-1">{{ form.errors.email }}</span>
                </div>

                <button type="submit" :disabled="form.processing"
                        class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-4 rounded-2xl shadow-[0_0_20px_rgba(6,182,212,0.4)] transition-all duration-300 uppercase tracking-widest text-sm disabled:opacity-50">
                    {{ form.processing ? 'Sending...' : 'Send Password Reset Link' }}
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-gray-400 text-xs mb-3">Remember your password?</p>
                <Link :href="route('login')" class="text-cyan-400 hover:text-cyan-300 transition font-bold text-sm">Sign in here</Link>
            </div>
        </div>
    </div>
</template>
