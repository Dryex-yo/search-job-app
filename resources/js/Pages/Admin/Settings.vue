<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminPageLayout from '@/Layouts/AdminPageLayout.vue';

defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

const currentRoute = computed(() => route().current());

const menus = [
    { name: 'Dashboard', icon: '📊', route: 'admin.dashboard' },
    { name: 'Analytics', icon: '📈', route: 'admin.analytics' }, 
    { name: 'Jobs', icon: '💼', route: 'admin.jobs' },
    { name: 'Applicants', icon: '👥', route: 'admin.applicants' },
    { name: 'Settings', icon: '⚙️', route: 'admin.settings' },
];

const settingsSaved = ref(false);

const handleSave = () => {
    settingsSaved.value = true;
    setTimeout(() => {
        settingsSaved.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Dryex Admin - Settings" />

    <AdminPageLayout title="Settings ⚙️" subtitle="Configure platform settings and preferences">
                    <!-- Success Message -->
                    <div v-if="settingsSaved" class="mb-8 p-4 bg-green-500/20 border border-green-500/30 rounded-2xl flex items-center gap-3 animate-pulse">
                        <span class="text-2xl">✅</span>
                        <div>
                            <p class="font-bold text-green-300">Settings Saved</p>
                            <p class="text-xs text-green-400">Your changes have been saved successfully</p>
                        </div>
                    </div>

                    <!-- Platform Settings -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">
                        <!-- General Settings -->
                        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">General Settings</h4>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Platform Name</label>
                                    <input type="text" value="DRYEX" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Support Email</label>
                                    <input type="email" value="support@dryex.com" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Max File Upload (MB)</label>
                                    <input type="number" value="10" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-cyan-500/50" />
                                </div>

                                <button @click="handleSave" class="w-full mt-6 bg-cyan-500 hover:bg-cyan-600 text-slate-900 py-3 rounded-2xl font-bold uppercase tracking-widest transition-all">Save Changes</button>
                            </div>
                        </div>

                        <!-- Email Settings -->
                        <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner">
                            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Email Notifications</h4>
                            
                            <div class="space-y-4">
                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" checked class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">New Applications</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" checked class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">Job Expiry Reminders</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" checked class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">Weekly Reports</span>
                                </label>

                                <label class="flex items-center gap-3 p-3 hover:bg-white/5 rounded-xl cursor-pointer transition-all">
                                    <input type="checkbox" class="w-5 h-5 rounded border-white/20" />
                                    <span class="font-medium">User Feedback</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div class="bg-white/[0.01] border border-white/10 rounded-[3.5rem] p-12 shadow-inner mb-10">
                        <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-8 italic">Security & Access</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="border border-white/10 rounded-2xl p-6">
                                <p class="text-sm font-bold text-gray-400 mb-4">Two-Factor Auth</p>
                                <button class="w-full bg-white/10 hover:bg-white/20 border border-white/10 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Enable</button>
                            </div>

                            <div class="border border-white/10 rounded-2xl p-6">
                                <p class="text-sm font-bold text-gray-400 mb-4">Change Password</p>
                                <button class="w-full bg-white/10 hover:bg-white/20 border border-white/10 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Update</button>
                            </div>

                            <div class="border border-white/10 rounded-2xl p-6">
                                <p class="text-sm font-bold text-gray-400 mb-4">Active Sessions</p>
                                <button class="w-full bg-white/10 hover:bg-white/20 border border-white/10 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all">Manage</button>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="bg-gradient-to-br from-cyan-500/10 to-cyan-500/5 border border-cyan-500/30 rounded-[2.5rem] p-8">
                            <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-4">🔧 System Status</p>
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Status</span>
                                    <span class="text-green-400 font-bold">Operational</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Uptime</span>
                                    <span class="text-cyan-400 font-bold">99.9%</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Last Update</span>
                                    <span class="text-blue-400 font-bold">{{ new Date().toLocaleDateString() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-green-500/10 to-green-500/5 border border-green-500/30 rounded-[2.5rem] p-8">
                            <p class="text-sm font-black text-gray-600 uppercase tracking-widest mb-4">📊 Platform Stats</p>
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Total Users</span>
                                    <span class="text-green-400 font-bold">{{ analytics.total_users }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Active Jobs</span>
                                    <span class="text-green-400 font-bold">{{ analytics.active_jobs }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Total Apps</span>
                                    <span class="text-green-400 font-bold">{{ analytics.total_applications }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
    </AdminPageLayout>
</template>
