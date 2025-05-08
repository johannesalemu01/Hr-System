<template>
    <div style="height: 320px">
        <canvas
            ref="chartCanvas"
            height="320"
            style="width: 100% !important"
        ></canvas>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { Chart } from "chart.js/auto";

const props = defineProps({
    labels: {
        type: Array,
        required: true,
    },
    datasets: {
        type: Array,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chartCanvas = ref(null);
let chartInstance = null;

const createChart = () => {
    if (!chartCanvas.value) return;
    if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
    }

    // Debug: log incoming props
    console.log("BarChart labels:", props.labels);
    console.log("BarChart datasets:", props.datasets);

    // Clone datasets to avoid Vue Proxy/reactivity issues
    const datasets = props.datasets.map((ds) => ({
        ...ds,
        data: Array.isArray(ds.data) ? [...ds.data] : ds.data, // always pass a copy
    }));

    // Debug: log chart.js data structure
    console.log("Chart.js data:", {
        labels: [...props.labels],
        datasets: datasets,
    });

    // Merge responsive options with user options
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        ...props.options,
    };

    chartInstance = new Chart(chartCanvas.value, {
        type: "bar",
        data: {
            labels: [...props.labels],
            datasets: datasets,
        },
        options: chartOptions,
    });
};

onMounted(() => {
    createChart();
});

watch(
    () => [props.labels, props.datasets],
    () => {
        createChart();
    },
    { deep: true }
);
</script>

<style scoped>
canvas {
    max-width: 100%;
    display: block;
}
</style>
