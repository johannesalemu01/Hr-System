<template>
    <AuthenticatedLayout
        title="Edit KPI"
        description="Edit Key Performance Indicator details"
    >
        <form @submit.prevent="submit" class="space-y-6">
            <DashboardCard title="KPI Details">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
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
                            :disabled="!isEditing"
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Measurement Unit -->
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
                            :disabled="!isEditing"
                        />
                        <div
                            v-if="form.errors.measurement_unit"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.measurement_unit }}
                        </div>
                    </div>

                    <!-- Frequency -->
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
                            :disabled="!isEditing"
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

                    <!-- Points Value -->
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
                            :disabled="!isEditing"
                        />
                        <div
                            v-if="form.errors.points_value"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.points_value }}
                        </div>
                    </div>

                    <!-- Department -->
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
                            :disabled="!isEditing"
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

                    <!-- Position -->
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
                            :disabled="!isEditing"
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

                    <!-- Status -->
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
                            :disabled="!isEditing"
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
                    :disabled="!isEditing"
                ></textarea>
                <div
                    v-if="form.errors.description"
                    class="text-red-500 text-sm mt-1"
                >
                    {{ form.errors.description }}
                </div>
            </DashboardCard>

            <div class="flex justify-end space-x-3">
                <button
                    v-if="isEditing"
                    @click.prevent="cancelEditing"
                    type="button"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    v-if="isEditing"
                    type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Update KPI</span>
                </button>
                <template v-else>
                    <button
                        v-if="
                            $page.props.auth.user.permissions.includes(
                                'edit kpis'
                            )
                        "
                        @click.prevent="enableEditing"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Edit KPI
                    </button>
                    <button
                        v-if="
                            $page.props.auth.user.permissions.includes(
                                'delete kpis'
                            )
                        "
                        @click.prevent="confirmDelete"
                        type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50"
                    >
                        Delete KPI
                    </button>
                </template>
            </div>
        </form>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Confirm Deletion
                </h2>
                <!-- Add title here -->
                <p class="mt-1 text-sm text-gray-700">
                    <!-- Adjusted margin-top -->
                    Are you sure you want to delete this KPI? This action cannot
                    be undone.
                </p>
            </div>
            <div class="flex justify-end p-4 bg-gray-50 rounded-b-md">
                <!-- Added background and rounded bottom -->
                <button
                    @click.prevent="showDeleteModal = false"
                    type="button"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 mr-3"
                >
                    Cancel
                </button>
                <button
                    @click.prevent="deleteKpi"
                    type="button"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                >
                    Delete
                </button>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import Modal from "@/Components/Modal.vue"; // Import Modal
import {
    PencilIcon,
    UserAddIcon,
    ArrowLeftIcon,
    TrashIcon,
} from "@heroicons/vue/outline";

const props = defineProps({
    kpi: {
        type: Object,
        required: true,
    },
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
    name: props.kpi.name,
    description: props.kpi.description,
    measurement_unit: props.kpi.measurement_unit,
    frequency: props.kpi.frequency,
    department_id: props.kpi.department_id,
    position_id: props.kpi.position_id,
    is_active: props.kpi.is_active,
    points_value: props.kpi.points_value,
});

// Submit form
const submit = () => {
    form.put(route("kpis.update", props.kpi.id), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

// --- Editing State ---
const isEditing = ref(false);

const enableEditing = () => {
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
    form.reset();
};

// --- Delete Functionality ---
const showDeleteModal = ref(false);

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteKpi = () => {
    console.log("Attempting to delete KPI with ID:", props.kpi.id);
    router.delete(route("kpis.destroy", props.kpi.id), {
        preserveScroll: true,
        onSuccess: () => {
            // This will be triggered on successful redirect (even if it's back() with an error flash)
            // Or on the redirect to index after successful deletion.
            console.log(
                "KPI deletion request finished. Inertia will handle navigation/flash messages."
            );
            showDeleteModal.value = false;
            // No need to manually check for errors here, flash messages handle it.
        },
        onError: (errors) => {
            // This callback is less likely to be hit now for application errors,
            // but might catch network errors or unexpected server issues (e.g., 500 without redirect).
            console.error("Inertia onError triggered:", errors);
            // Display a generic error message if needed, but prefer flash messages.
            alert(`An unexpected error occurred. Please check the console.`); // Generic fallback alert
            showDeleteModal.value = false;
        },
    });
};
</script>
