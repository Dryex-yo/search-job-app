<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex items-center justify-center bg-[#0b0f1a] relative overflow-hidden font-sans">
        
        <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 blur-[150px] rounded-full"></div>

        <div class="w-full max-w-md bg-white/5 backdrop-blur-2xl border border-white/10 p-10 rounded-[2.5rem] shadow-2xl z-10 relative">
            
            <div class="text-center mb-10">
                <h2 class="text-3xl font-black text-cyan-400 tracking-tighter uppercase">Dryex</h2>
                <p class="text-gray-400 text-sm mt-2">Sign in to your account</p>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label class="block text-gray-300 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Email Address</label>
                    <input v-model="form.email" type="email" 
                           class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50 transition-all"
                           placeholder="name@company.com" required>
                </div>

                <div class="mb-8">
                    <label class="block text-gray-300 text-xs font-bold uppercase tracking-widest mb-2 ml-1">Password</label>
                    <input v-model="form.password" type="password" 
                           class="w-full bg-white/5 border border-white/10 rounded-2xl py-3 px-4 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50 transition-all"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" :disabled="form.processing"
                        class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-4 rounded-2xl shadow-[0_0_20px_rgba(6,182,212,0.4)] transition-all duration-300 uppercase tracking-widest text-sm">
                    {{ form.processing ? 'Signing in...' : 'Sign In' }}
                </button>
            </form>

            <div class="mt-8 text-center">
                <a href="#" class="text-xs text-gray-500 hover:text-cyan-400 transition">Don't have an account? Create one</a>
            </div>
        </div>
    </div>
</template>