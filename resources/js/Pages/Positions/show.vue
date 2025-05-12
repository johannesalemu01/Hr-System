<template>
    <AuthenticatedLayout>
        <div class="max-w-2xl mx-auto bg-white shadow rounded p-6">
            <h2 class="text-2xl font-bold mb-4">{{ position.title }}</h2>
            <div class="mb-2">
                <strong>Department:</strong>
                {{ position.department?.name || "N/A" }}
            </div>
            <div class="mb-2">
                <strong>Min Salary:</strong> {{ position.min_salary }}
            </div>
            <div class="mb-2">
                <strong>Max Salary:</strong> {{ position.max_salary }}
            </div>
            <div class="mb-2">
                <strong>Description:</strong> {{ position.description }}
            </div>
            <div class="mt-4">
                <Link
                    :href="route('positions.edit', position.id)"
                    class="text-blue-600 hover:underline"
                    >Edit</Link
                >
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
const props = defineProps({ position: Object });
</script>
