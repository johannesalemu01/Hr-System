<template>
    <AdminLayout
        title="KPI Details"
        description="Detailed view of the selected KPI"
    >
        <div>
            <h1 class="text-2xl font-bold">{{ kpi.name }}</h1>
            <p class="text-gray-600">{{ kpi.description }}</p>
            <div class="mt-4">
                <h2 class="text-lg font-semibold">Details</h2>
                <ul class="list-disc pl-5">
                    <li>Measurement Unit: {{ kpi.measurement_unit }}</li>
                    <li>Frequency: {{ kpi.frequency }}</li>
                    <li>Department: {{ kpi.department.name }}</li>
                    <li>Position: {{ kpi.position.name }}</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const { props } = usePage();
const kpi = ref(props.kpi);
</script>
