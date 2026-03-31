<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AnalyticsCounter from '@/Components/AnalyticsCounter.vue';

defineProps({
    analytics: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head title="Dryex Admin - Dashboard" />

    <AdminLayout>
        <template #title>Dashboard</template>

        <!-- Analytics Summary Grid -->
        <div class="mb-8">
            <h3 class="text-sm font-black text-gray-400 uppercase tracking-[0.3em] mb-6 italic">📊 Summary Analytics</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <AnalyticsCounter 
                    icon="💼"
                    label="Total Jobs Posted"
                    :value="analytics.total_jobs"
                    color="cyan"
                />
                <AnalyticsCounter 
                    icon="📋"
                    label="Total Applications"
                    :value="analytics.total_applications"
                    color="blue"
                />
                <AnalyticsCounter 
                    icon="✨"
                    label="Active Jobs"
                    :value="analytics.active_jobs"
                    color="purple"
                />
                <AnalyticsCounter 
                    icon="⏳"
                    label="Pending Applications"
                    :value="analytics.pending_applications"
                    color="orange"
                />
                <AnalyticsCounter 
                    icon="✅"
                    label="Hired Candidates"
                    :value="analytics.hired_count"
                    color="pink"
                />
            </div>
        </div>

        <!-- Additional Details Grid -->
        <div class="grid grid-cols-12 gap-6">
            
            <div class="col-span-12 lg:col-span-8 bg-white/[0.01] border border-white/10 rounded-[2rem] p-8 relative shadow-inner">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.5em] mb-8 italic">Performance Metrics</h4>
                
                <div class="h-48 flex items-end gap-2 mb-8 border-b border-white/5 pb-4 px-2">
                    <div v-for="n in 12" :key="n" 
                        class="flex-grow bg-gradient-to-t from-cyan-500/40 via-cyan-500/10 to-transparent border-t border-cyan-400/30 rounded-t-xl transition-all duration-1000 hover:h-full cursor-pointer shadow-[0_-8px_30px_rgba(6,182,212,0.15)]"
                        :style="{height: Math.floor(Math.random() * 60 + 30) + '%'}">
                    </div>
                </div>

                <div class="flex gap-8 justify-between">
                    <div>
                        <p class="text-4xl font-extrabold text-white tracking-tighter">$14.5k</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Revenue</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-white tracking-tighter">92%</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Success Rate</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-white tracking-tighter">{{ analytics.active_jobs }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-2">Active Jobs</p>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 bg-white/[0.01] border border-white/10 rounded-[2rem] p-8 flex flex-col justify-between shadow-inner">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.4em] text-center italic">Application Status Breakdown</h4>
                <div class="space-y-6 py-4">
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3">
                            <span>Pending</span>
                            <span class="text-orange-400">{{ Math.round((analytics.pending_applications / analytics.total_applications) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-orange-600 to-orange-400 rounded-full shadow-[0_0_20px_rgba(249,115,22,0.7)] transition-all duration-1000" :style="{width: Math.round((analytics.pending_applications / analytics.total_applications) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3">
                            <span>Shortlisted</span>
                            <span class="text-blue-400">50%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full shadow-[0_0_20px_rgba(59,130,246,0.7)] transition-all duration-1000" style="width: 50%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3">
                            <span>Hired</span>
                            <span class="text-green-400">{{ Math.round((analytics.hired_count / analytics.total_applications) * 100) || 0 }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-white/5 rounded-full p-[1px]">
                            <div class="h-full bg-gradient-to-r from-green-600 to-green-400 rounded-full shadow-[0_0_20px_rgba(34,197,94,0.7)] transition-all duration-1000" :style="{width: Math.round((analytics.hired_count / analytics.total_applications) * 100) + '%' || '0%'}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(6, 182, 212, 0.3);
    border-radius: 10px;
}
</style>