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
        <DashboardCard title="Assign to Employees">
            <form @submit.prevent="submitAssign" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Select Employees</label
                        >
                        <select
                            v-model="assignForm.assign_employee_ids"
                            multiple
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option
                                v-for="emp in employees"
                                :key="emp.id"
                                :value="emp.id"
                            >
                                {{ emp.name }} ({{ emp.employee_id }}) -
                                {{ emp.department }}
                            </option>
                        </select>
                        <div
                            v-if="assignForm.errors.assign_employee_ids"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_employee_ids }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Target Value</label
                        >
                        <input
                            v-model="assignForm.assign_target_value"
                            type="number"
                            step="any"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="assignForm.errors.assign_target_value"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_target_value }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Minimum Value</label
                        >
                        <input
                            v-model="assignForm.assign_minimum_value"
                            type="number"
                            step="any"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="assignForm.errors.assign_minimum_value"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_minimum_value }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Maximum Value</label
                        >
                        <input
                            v-model="assignForm.assign_maximum_value"
                            type="number"
                            step="any"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="assignForm.errors.assign_maximum_value"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_maximum_value }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Weight (0.1 - 5)</label
                        >
                        <input
                            v-model="assignForm.assign_weight"
                            type="number"
                            step="0.1"
                            min="0.1"
                            max="5"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="assignForm.errors.assign_weight"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_weight }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Start Date</label
                        >
                        <input
                            v-model="assignForm.assign_start_date"
                            type="date"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="assignForm.errors.assign_start_date"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_start_date }}
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >End Date</label
                        >
                        <input
                            v-model="assignForm.assign_end_date"
                            type="date"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                        <div
                            v-if="assignForm.errors.assign_end_date"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ assignForm.errors.assign_end_date }}
                        </div>
                    </div>
                </div>
                <div>
                    <button
                        type="submit"
                        class="bg-primary-600 hover:bg-primary-700 text-white font-semibold px-6 py-2 rounded shadow"
                        :disabled="assignForm.processing"
                    >
                        <span v-if="assignForm.processing">Assigning...</span>
                        <span v-else>Assign KPI to Employees</span>
                    </button>
                </div>
            </form>
        </DashboardCard>
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Confirm Deletion
                </h2>

                <p class="mt-1 text-sm text-gray-700">
                    Are you sure you want to delete this KPI? This action cannot
                    be undone.
                </p>
            </div>
            <div class="flex justify-end p-4 bg-gray-50 rounded-b-md">
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
    employees: {
        type: Array,
        required: false,
        default: () => [],
    },
});

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

const isEditing = ref(false);

const enableEditing = () => {
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
    form.reset();
};

const showDeleteModal = ref(false);

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteKpi = () => {
    console.log("Attempting to delete KPI with ID:", props.kpi.id);
    router.delete(route("kpis.destroy", props.kpi.id), {
        preserveScroll: true,
        onSuccess: () => {
            console.log(
                "KPI deletion request finished. Inertia will handle navigation/flash messages."
            );
            showDeleteModal.value = false;
        },
        onError: (errors) => {
            console.error("Inertia onError triggered:", errors);

            showDeleteModal.value = false;
        },
    });
};

const assignForm = useForm({
    assign_employee_ids: [],
    assign_target_value: "",
    assign_minimum_value: "",
    assign_maximum_value: "",
    assign_weight: 1,
    assign_start_date: "",
    assign_end_date: "",
});

const today = new Date().toISOString().slice(0, 10);
const nextMonth = new Date();
nextMonth.setMonth(nextMonth.getMonth() + 1);
const nextMonthStr = nextMonth.toISOString().slice(0, 10);
assignForm.assign_start_date = today;
assignForm.assign_end_date = nextMonthStr;

const submitAssign = () => {
    assignForm.post(route("kpis.store-employee-kpi"), {
        preserveScroll: true,
        onSuccess: () => {
            assignForm.reset();
        },
    });
};
</script>
