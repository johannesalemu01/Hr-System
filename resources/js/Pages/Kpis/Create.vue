<template>
    <AuthenticatedLayout
        title="Create KPI"
        description="Define a new Key Performance Indicator"
    >
        <form @submit.prevent="submitForm" class="space-y-6">
            <DashboardCard title="KPI Details">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-medium text-gray-700"
                            >Name</label
                        >
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="measurement_unit"
                            class="block text-sm font-medium text-gray-700"
                            >Measurement Unit</label
                        >
                        <input
                            id="measurement_unit"
                            v-model="form.measurement_unit"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="form.errors.measurement_unit"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.measurement_unit }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="frequency"
                            class="block text-sm font-medium text-gray-700"
                            >Frequency</label
                        >
                        <select
                            id="frequency"
                            v-model="form.frequency"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="">Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annually">Annually</option>
                        </select>
                        <div
                            v-if="form.errors.frequency"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.frequency }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="points_value"
                            class="block text-sm font-medium text-gray-700"
                            >Points Value</label
                        >
                        <input
                            id="points_value"
                            v-model="form.points_value"
                            type="number"
                            min="1"
                            max="100"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="form.errors.points_value"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.points_value }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="department_id"
                            class="block text-sm font-medium text-gray-700"
                            >Department</label
                        >
                        <select
                            id="department_id"
                            v-model="form.department_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="">Select Department</option>
                            <option
                                v-for="department in departments"
                                :key="department.id"
                                :value="department.id"
                            >
                                {{ department.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.department_id"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.department_id }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="position_id"
                            class="block text-sm font-medium text-gray-700"
                            >Position</label
                        >
                        <select
                            id="position_id"
                            v-model="form.position_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="">All Positions</option>
                            <option
                                v-for="position in positions"
                                :key="position.id"
                                :value="position.id"
                            >
                                {{ position.title }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.position_id"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.position_id }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="is_active"
                            class="block text-sm font-medium text-gray-700"
                            >Status</label
                        >
                        <select
                            id="is_active"
                            v-model="form.is_active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <div
                            v-if="form.errors.is_active"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.is_active }}
                        </div>
                    </div>
                </div>
            </DashboardCard>

            <DashboardCard title="Description">
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    placeholder="Provide a detailed description of the KPI"
                ></textarea>
                <div
                    v-if="form.errors.description"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ form.errors.description }}
                </div>
            </DashboardCard>

            <div class="flex justify-end space-x-3">
                <Link
                    :href="route('kpis.index')"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Create KPI</span>
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";

const props = defineProps({
    departments: {
        type: Array,
        required: true,
    },
    positions: {
        type: Array,
        required: true,
    },
});

// Form handling
const form = useForm({
    name: "",
    description: "",
    measurement_unit: "",
    frequency: "",
    department_id: "",
    position_id: "",
    is_active: "1",
    points_value: "",
});

// Submit form
const submitForm = () => {
    form.post(route("kpis.store")); // Submits the form to the backend
};
</script>

<style lang="scss" scoped></style>
