<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('admin.recruiters.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register Recruiter" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 to-slate-900 p-4">
        <div class="w-full max-w-md bg-slate-800/50 backdrop-blur border border-slate-700 p-8 rounded-2xl shadow-2xl">
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-cyan-400 mb-2">Register Recruiter</h2>
                <p class="text-gray-400 text-sm">Create a new recruiter account</p>
            </div>

            <!-- Dark Mode Toggle -->
            <div class="absolute top-6 right-6">
                <DarkModeToggle />
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Name Field -->
                <div>
                    <label class="block text-gray-300 text-sm font-semibold mb-2">Full Name</label>
                    <input v-model="form.name" type="text" 
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/50 transition"
                           placeholder="John Doe" required autofocus>
                    <span v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</span>
                </div>

                <!-- Email Field -->
                <div>
                    <label class="block text-gray-300 text-sm font-semibold mb-2">Email Address</label>
                    <input v-model="form.email" type="email" 
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/50 transition"
                           placeholder="recruiter@company.com" required>
                    <span v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</span>
                </div>

                <!-- Phone Field (Optional) -->
                <div>
                    <label class="block text-gray-300 text-sm font-semibold mb-2">Phone Number</label>
                    <input v-model="form.phone" type="tel" 
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/50 transition"
                           placeholder="+62 8xx xxxx xxxx">
                    <span v-if="form.errors.phone" class="text-red-400 text-xs mt-1">{{ form.errors.phone }}</span>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-gray-300 text-sm font-semibold mb-2">Password</label>
                    <input v-model="form.password" type="password" 
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/50 transition"
                           placeholder="••••••••" required>
                    <span v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</span>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label class="block text-gray-300 text-sm font-semibold mb-2">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" 
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 text-white placeholder-gray-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/50 transition"
                           placeholder="••••••••" required>
                    <span v-if="form.errors.password_confirmation" class="text-red-400 text-xs mt-1">{{ form.errors.password_confirmation }}</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="form.processing"
                        class="w-full bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-bold py-3 rounded-lg transition duration-300 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ form.processing ? 'Creating recruiter...' : 'Create Recruiter' }}
                </button>
            </form>

            <!-- Back Link -->
            <div class="mt-6 text-center">
                <Link :href="route('admin.dashboard')" class="text-cyan-400 hover:text-cyan-300 transition font-semibold text-sm">
                    ← Back to Dashboard
                </Link>
            </div>
        </div>
    </div>
</template>
