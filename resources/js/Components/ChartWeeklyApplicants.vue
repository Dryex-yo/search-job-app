<script setup>
import { ref, onMounted } from 'vue';
import ApexCharts from 'apexcharts';

const props = defineProps({
    weeklyData: {
        type: Object,
        required: true
    }
});

const chartRef = ref(null);
const chart = ref(null);

onMounted(() => {
    const options = {
        chart: {
            type: 'line',
            stacked: false,
            background: 'transparent',
            toolbar: {
                show: false,
            },
            sparkline: {
                enabled: false,
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            lineCap: 'round',
        },
        colors: ['#06b6d4'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 0.1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        markers: {
            size: 5,
            colors: ['#06b6d4'],
            strokeWidth: 0,
            hover: {
                size: 7
            }
        },
        dataLabels: {
            enabled: false,
        },
        xaxis: {
            categories: props.weeklyData.categories,
            labels: {
                style: {
                    colors: '#9ca3af',
                    fontSize: '12px',
                    fontWeight: 500,
                }
            },
            axisBorder: {
                show: true,
                color: 'rgba(255, 255, 255, 0.1)',
            },
            axisTicks: {
                show: true,
                color: 'rgba(255, 255, 255, 0.05)',
            },
            crosshairs: {
                show: true,
                background: 'rgba(6, 182, 212, 0.1)',
                stroke: {
                    color: '#06b6d4',
                    width: 1,
                    dashArray: 0,
                },
            },
        },
        yaxis: {
            title: {
                text: 'Jumlah Aplikasi',
                offsetX: -10,
                style: {
                    color: '#9ca3af',
                    fontSize: '12px',
                    fontWeight: 600,
                }
            },
            axisBorder: {
                show: true,
                color: 'rgba(255, 255, 255, 0.1)',
            },
            axisTicks: {
                show: true,
                color: 'rgba(255, 255, 255, 0.05)',
            },
            labels: {
                style: {
                    colors: '#9ca3af',
                    fontSize: '12px',
                    fontWeight: 500,
                }
            }
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.05)',
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: {
            theme: 'dark',
            style: {
                fontSize: '12px',
            },
            markers: {
                fillColors: ['#06b6d4']
            },
            y: {
                formatter: function(value) {
                    return value + ' aplikasi'
                }
            }
        },
        legend: {
            position: 'top',
            labels: {
                colors: '#cbd5e1',
            }
        }
    };

    chart.value = new ApexCharts(chartRef.value, {
        series: props.weeklyData.series,
        ...options
    });

    chart.value.render();
});
</script>

<template>
    <div class="bg-white/[0.01] border border-white/10 rounded-[3rem] p-10 shadow-inner relative overflow-hidden">
        <!-- Glow effect background -->
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-cyan-500/5 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-cyan-500/10 rounded-full blur-[80px] pointer-events-none opacity-50"></div>
        
        <div class="relative z-10">
            <h4 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.5em] mb-10 italic">
                📊 Aplikasi Per Minggu (12 Minggu Terakhir)
            </h4>
            <div ref="chartRef" class="w-full h-96"></div>
        </div>
    </div>
</template>

<style scoped>
:deep(.apexcharts-canvas) {
    filter: drop-shadow(0 0 30px rgba(6, 182, 212, 0.1));
}

:deep(.apexcharts-tooltip) {
    background: rgba(8, 11, 20, 0.95) !important;
    border: 1px solid rgba(6, 182, 212, 0.3) !important;
    box-shadow: 0 0 30px rgba(6, 182, 212, 0.2) !important;
}
</style>
