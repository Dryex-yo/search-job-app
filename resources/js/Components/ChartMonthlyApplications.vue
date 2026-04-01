<script setup>
import { ref, onMounted } from 'vue';
import ApexCharts from 'apexcharts';

const props = defineProps({
    monthlyData: {
        type: Object,
        required: true
    }
});

const chartRef = ref(null);
const chart = ref(null);

onMounted(() => {
    const options = {
        chart: {
            type: 'bar',
            stacked: false,
            background: 'transparent',
            toolbar: {
                show: false,
            },
        },
        colors: ['#3b82f6'],
        fill: {
            opacity: 0.8,
        },
        dataLabels: {
            enabled: false,
        },
        xaxis: {
            categories: props.monthlyData.categories,
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
                }
            }
        },
        states: {
            hover: {
                filter: {
                    type: 'darken',
                    value: 0.15,
                }
            }
        },
        tooltip: {
            theme: 'dark',
            x: {
                show: true,
            },
            y: {
                title: {
                    formatter: () => 'Total Aplikasi',
                }
            },
            style: {
                fontSize: '12px',
                fontFamily: 'inherit',
            },
            marker: {
                show: true,
            }
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.05)',
            strokeDashArray: 0,
            position: 'back',
            xaxis: {
                lines: {
                    show: false,
                }
            }
        },
    };

    chart.value = new ApexCharts(chartRef.value, {
        series: props.monthlyData.series,
        ...options
    });
    
    chart.value.render();
});
</script>

<template>
    <div ref="chartRef"></div>
</template>
