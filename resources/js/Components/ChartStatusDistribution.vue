<script setup>
import { ref, onMounted } from 'vue';
import ApexCharts from 'apexcharts';

const props = defineProps({
    statusData: {
        type: Object,
        required: true
    }
});

const chartRef = ref(null);
const chart = ref(null);

onMounted(() => {
    const options = {
        chart: {
            type: 'donut',
            background: 'transparent',
            toolbar: {
                show: false,
            },
            sparkline: {
                enabled: false,
            }
        },
        colors: props.statusData.colors,
        labels: props.statusData.labels,
        dataLabels: {
            enabled: true,
            formatter: function(val) {
                return Math.round(val) + '%'
            },
            style: {
                fontSize: '14px',
                fontWeight: 600,
                colors: '#ffffff'
            },
            dropShadow: {
                enabled: true,
                top: 3,
                left: 3,
                blur: 4,
                color: '#000000',
                opacity: 0.45
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    background: 'transparent',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#cbd5e1',
                            offsetY: -10
                        },
                        value: {
                            show: true,
                            fontSize: '20px',
                            fontWeight: 700,
                            color: '#06b6d4',
                            offsetY: 16,
                            formatter: function(val) {
                                return val
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Aplikasi',
                            fontSize: '12px',
                            color: '#9ca3af',
                            formatter: function(w) {
                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                return total
                            }
                        }
                    }
                },
                expandOnClick: true,
            }
        },
        stroke: {
            width: 0,
        },
        legend: {
            position: 'bottom',
            labels: {
                colors: '#cbd5e1',
                useSeriesColors: false,
            },
            fontSize: '13px',
            fontFamily: 'inherit',
            offsetY: 0,
        },
        tooltip: {
            theme: 'dark',
            style: {
                fontSize: '12px',
            },
            y: {
                formatter: function(val) {
                    return val + ' aplikasi'
                }
            }
        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        ],
    };

    chart.value = new ApexCharts(chartRef.value, {
        series: props.statusData.series,
        ...options
    });

    chart.value.render();
});
</script>

<template>
    <div class="bg-white/[0.01] border border-white/10 rounded-[3rem] p-10 shadow-inner relative overflow-hidden">
        <!-- Glow effect background - multiple layers for more glow -->
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-green-500/5 via-orange-500/5 to-red-500/5 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-1/3 right-0 w-72 h-72 bg-green-500/5 rounded-full blur-[80px] pointer-events-none opacity-60"></div>
        <div class="absolute bottom-1/3 left-0 w-72 h-72 bg-red-500/5 rounded-full blur-[80px] pointer-events-none opacity-60"></div>
        
        <div class="relative z-10">
            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-10 italic text-center">
                📊 Sebaran Status Aplikasi
            </h4>
            <div ref="chartRef" class="w-full" style="height: 380px;"></div>
        </div>
    </div>
</template>

<style scoped>
:deep(.apexcharts-canvas) {
    filter: drop-shadow(0 0 30px rgba(6, 182, 212, 0.08)) drop-shadow(0 0 60px rgba(16, 185, 129, 0.05)) drop-shadow(0 0 40px rgba(239, 68, 68, 0.05));
}

:deep(.apexcharts-tooltip) {
    background: rgba(8, 11, 20, 0.95) !important;
    border: 1px solid rgba(6, 182, 212, 0.3) !important;
    box-shadow: 0 0 30px rgba(6, 182, 212, 0.2) !important;
}

:deep(.apexcharts-pie) {
    filter: drop-shadow(0 0 20px rgba(6, 182, 212, 0.1));
}
</style>
