<template>
    <div>
        <canvas ref="chartCanvas"></canvas>
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
    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(chartCanvas.value, {
        type: "bar",
        data: {
            labels: props.labels,
            datasets: props.datasets,
        },
        options: props.options,
    });
};

onMounted(() => {
    createChart();
});

watch(() => [props.labels, props.datasets], createChart, { deep: true });
</script>

<style scoped>
canvas {
    max-width: 100%;
    height: auto;
}
</style>
