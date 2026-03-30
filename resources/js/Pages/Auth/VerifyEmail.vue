<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Email Verification" />

    <div class="min-h-screen flex items-center justify-center bg-[#0b0f1a] relative overflow-hidden font-sans">
        
        <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-cyan-500/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 blur-[150px] rounded-full"></div>

        <div class="w-full max-w-md bg-white/5 backdrop-blur-2xl border border-white/10 p-10 rounded-[2.5rem] shadow-2xl z-10 relative">
            
            <div class="text-center mb-10">
                <h2 class="text-3xl font-black text-cyan-400 tracking-tighter uppercase">Dryex</h2>
                <p class="text-gray-400 text-sm mt-2">Verify your email</p>
            </div>

            <p class="text-gray-400 text-sm mb-6 leading-relaxed">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>

            <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-2xl">
                <p class="text-green-400 text-sm">A new verification link has been sent to the email address you provided during registration.</p>
            </div>

            <form @submit.prevent="submit">
                <button type="submit" :disabled="form.processing"
                        class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-4 rounded-2xl shadow-[0_0_20px_rgba(6,182,212,0.4)] transition-all duration-300 uppercase tracking-widest text-sm disabled:opacity-50 mb-4">
                    {{ form.processing ? 'Sending...' : 'Resend Verification Email' }}
                </button>
            </form>

            <div class="flex items-center justify-center gap-3">
                <div class="h-px bg-white/10 flex-grow"></div>
                <span class="text-xs text-gray-500">Or</span>
                <div class="h-px bg-white/10 flex-grow"></div>
            </div>

            <div class="mt-6 text-center">
                <Link :href="route('logout')" method="post" as="button" class="text-gray-400 hover:text-cyan-400 transition text-sm">Log out</Link>
            </div>
        </div>
    </div>
</template>
