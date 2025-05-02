<template>
    <AuthenticatedLayout
        title="Attendance Dashboard"
        description="View and manage employee attendance records"
    >
        <!-- Flash Messages -->
        <div
            v-if="flash.success"
            class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded"
        >
            {{ flash.success }}
        </div>
        <div
            v-if="flash.error"
            class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded"
        >
            {{ flash.error }}
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-100 p-4 rounded-lg shadow">
                <p class="text-sm text-gray-500">Total Employees</p>
                <p class="text-lg font-semibold text-gray-900">
                    {{ stats.totalEmployees }}
                </p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg shadow">
                <p class="text-sm text-gray-500">Present</p>
                <p class="text-lg font-semibold text-green-800">
                    {{ stats.presentToday }}
                </p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg shadow">
                <p class="text-sm text-gray-500">Late</p>
                <p class="text-lg font-semibold text-yellow-800">
                    {{ stats.lateToday }}
                </p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg shadow">
                <p class="text-sm text-gray-500">Absent</p>
                <p class="text-lg font-semibold text-red-800">
                    {{ stats.absentToday }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Attendance Dashboard
                    </h3>
                    <p class="text-sm text-gray-500">
                        View and manage employee attendance records
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <input
                        type="date"
                        v-model="filters.date"
                        @change="applyFilters"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        placeholder="Select a date"
                    />
                    <!-- Show button for everyone, adjust text for employee -->
                    <button
                        @click="openAddModal"
                        class="px-4 py-2 bg-primary-600 text-black rounded-md shadow hover:bg-primary-700 border border-gray-300 whitespace-nowrap"
                    >
                        {{
                            isEmployeeView
                                ? "Add My Attendance"
                                : "Add Attendance"
                        }}
                    </button>
                </div>
            </div>

            <!-- Filters (Conditionally hide for employee view) -->
            <div v-if="!isEmployeeView" class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1">
                    <input
                        type="text"
                        v-model="filters.search"
                        placeholder="Search employees"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    />
                </div>
                <div class="flex-1">
                    <select
                        v-model="filters.department_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">All Departments</option>
                        <option
                            v-for="department in departments"
                            :key="department.id"
                            :value="department.id"
                        >
                            {{ department.name }}
                        </option>
                    </select>
                </div>
                <div class="flex-1">
                    <select
                        v-model="filters.status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                    >
                        <option value="">All Statuses</option>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-primary-600 text-black rounded-md shadow hover:bg-primary-700"
                    >
                        Apply
                    </button>
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow hover:bg-gray-200"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Employee
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Clock In
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Clock Out
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="attendanceData?.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-4 text-center text-sm text-gray-500"
                            >
                                No attendance records found.
                            </td>
                        </tr>
                        <tr v-for="record in attendanceData" :key="record.id">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ record.employee.name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDateTime(record.clock_in) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDateTime(record.clock_out) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span
                                    :class="{
                                        'text-green-800 bg-green-100':
                                            record.status === 'present',
                                        'text-yellow-800 bg-yellow-100':
                                            record.status === 'late',
                                        'text-red-800 bg-red-100':
                                            record.status === 'absent',
                                    }"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ record.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <button
                                    @click="openEditModal(record)"
                                    class="text-blue-600 hover:text-blue-900"
                                    v-if="isEmployeeView || !isEmployeeView"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDelete(record)"
                                    class="ml-4 text-red-600 hover:text-red-900"
                                    v-if="!isEmployeeView"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :links="page.props.attendance?.links" />
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{
                        isEditing
                            ? "Edit Attendance"
                            : isEmployeeView
                            ? "Add My Attendance"
                            : "Add Attendance"
                    }}
                </h2>
                <form @submit.prevent="submitAttendance">
                    <div class="mt-4 space-y-4">
                        <div>
                            <label
                                for="employee"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Employee
                            </label>
                            <!-- Conditional Rendering: Text input for Employee adding, Select otherwise -->
                            <input
                                v-if="isEmployeeView && !isEditing"
                                type="text"
                                id="employee_name"
                                :value="loggedInEmployee?.name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 bg-gray-100 text-gray-700"
                                readonly
                            />
                            <select
                                v-else
                                id="employee"
                                v-model="form.employee_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                                :disabled="isEmployeeView && isEditing"
                            >
                                <option value="" disabled>
                                    Select employee
                                </option>
                                <!-- Show logged-in employee if in employee view (for editing) -->
                                <option
                                    v-if="isEmployeeView && loggedInEmployee"
                                    :value="loggedInEmployee.id"
                                >
                                    {{ loggedInEmployee.name }}
                                </option>
                                <!-- Show all employees if admin view -->
                                <template v-if="!isEmployeeView">
                                    <option
                                        v-for="employee in employees"
                                        :key="employee.id"
                                        :value="employee.id"
                                    >
                                        {{ employee.name }}
                                    </option>
                                </template>
                            </select>
                            <!-- Display error for employee_id (still relevant for submission) -->
                            <p
                                v-if="form.errors.employee_id"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.employee_id }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="date"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Date
                            </label>
                            <input
                                type="date"
                                id="date"
                                v-model="form.date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:bg-gray-100 disabled:text-gray-500"
                                :disabled="isEmployeeView && !isEditing"
                            />
                            <p
                                v-if="form.errors.date"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.date }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="clock_in"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Clock In
                            </label>
                            <input
                                v-if="isEmployeeView && !isEditing"
                                type="time"
                                id="clock_in_time"
                                v-model="form.clock_in"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                step="60"
                            />
                            <input
                                v-else
                                type="datetime-local"
                                id="clock_in_datetime"
                                v-model="form.clock_in"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                step="60"
                            />
                            <p
                                v-if="form.errors.clock_in"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.clock_in }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="clock_out"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Clock Out
                            </label>
                            <input
                                v-if="isEmployeeView && !isEditing"
                                type="time"
                                id="clock_out_time"
                                v-model="form.clock_out"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                step="60"
                            />
                            <input
                                v-else
                                type="datetime-local"
                                id="clock_out_datetime"
                                v-model="form.clock_out"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                step="60"
                            />
                            <p
                                v-if="form.errors.clock_out"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.clock_out }}
                            </p>
                        </div>
                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Status
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                            <p
                                v-if="form.errors.status"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ form.errors.status }}
                            </p>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow hover:bg-gray-200"
                                @click="closeModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow hover:bg-gray-200"
                                :disabled="form.processing"
                            >
                                {{ isEditing ? "Update" : "Add" }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeDeleteModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Attendance Record
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this attendance record? This
                    action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow hover:bg-gray-200"
                        @click="closeDeleteModal"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 bg-red-600 text-white rounded-md shadow hover:bg-red-700"
                        @click="deleteAttendance"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { usePage, router, useForm } from "@inertiajs/vue3"; // Import useForm
import { format, parseISO } from "date-fns";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    attendanceData: {
        type: Array,
        required: true,
    },
    employees: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            date: null,
            department_id: "",
            status: "",
            search: "",
        }),
    },
    isEmployeeView: {
        // Prop passed from controller
        type: Boolean,
        default: false,
    },
    loggedInEmployeeData: {
        // Add the new prop
        type: Object,
        default: null,
    },
    attendance: {
        // Ensure attendance prop is defined if used for pagination
        type: Object,
        default: () => ({ links: [], meta: {} }),
    },
});

const page = usePage();
const flash = computed(() => page.props.flash);
const stats = computed(() => props.stats);
const isEmployeeView = computed(() => props.isEmployeeView);

// Get logged-in employee details from the new prop
const loggedInEmployee = computed(() => {
    // Use the dedicated prop passed from the controller
    if (isEmployeeView.value && props.loggedInEmployeeData) {
        return props.loggedInEmployeeData;
    }
    return null;
});

const filters = ref({
    date: props.filters.date,
    department_id: props.filters.department_id || "",
    status: props.filters.status || "",
    search: props.filters.search || "",
});

const applyFilters = () => {
    router.get(
        route("attendance.index"),
        {
            date: filters.value.date,
            department_id: filters.value.department_id,
            status: filters.value.status,
            search: filters.value.search,
        },
        {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        }
    );
};

const resetFilters = () => {
    filters.value = {
        date: new Date().toISOString().split("T")[0],
        department_id: "",
        status: "",
        search: "",
    };
    applyFilters();
};

watch(
    () => props.filters,
    (newFilters) => {
        filters.value = {
            date: newFilters.date,
            department_id: newFilters.department_id || "",
            status: newFilters.status || "",
            search: newFilters.search || "",
        };
    },
    { deep: true }
);

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const selectedRecord = ref(null);

// Use Inertia's useForm for the modal form
const form = useForm({
    id: null,
    employee_id: "",
    date: "",
    clock_in: null, // Initialize as null
    clock_out: null, // Initialize as null
    status: "present",
});

const openAddModal = () => {
    isEditing.value = false;
    form.reset(); // Reset form fields and errors using useForm's reset
    form.employee_id =
        isEmployeeView.value && loggedInEmployee.value
            ? loggedInEmployee.value.id
            : "";
    form.date = new Date().toISOString().split("T")[0]; // Default date to today
    // Reset clock times to null, datetime-local input will handle defaults
    form.clock_in = null;
    form.clock_out = null;
    showModal.value = true;
};

const openEditModal = (record) => {
    isEditing.value = true;
    form.id = record.id;
    form.employee_id = record.employee.id;
    form.date = record.date;
    form.clock_in = record.clock_in
        ? record.clock_in.replace(" ", "T").substring(0, 16)
        : null;
    form.clock_out = record.clock_out
        ? record.clock_out.replace(" ", "T").substring(0, 16)
        : null;
    form.status = record.status;
    form.clearErrors(); // Clear errors from useForm
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset(); // Reset form on close
};

const confirmDelete = (record) => {
    selectedRecord.value = record;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    selectedRecord.value = null;
};

const submitAttendance = () => {
    // Basic frontend validation (optional, as backend validates)
    if (form.clock_in && form.clock_out && form.clock_out < form.clock_in) {
        // Use form.setError provided by useForm
        form.setError(
            "clock_out",
            "Clock out must be after or equal to clock in."
        );
        return;
    }
    if (!form.date) {
        form.setError("date", "The date field is required.");
        return;
    }
    if (!form.employee_id) {
        form.setError("employee_id", "Please select an employee.");
        return;
    }

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.error("Form submission errors:", errors);
        },
    };

    if (isEditing.value) {
        form.put(route("attendance.update", form.id), options);
    } else {
        // Ensure employee_id is set correctly from computed property
        if (isEmployeeView.value && loggedInEmployee.value) {
            form.employee_id = loggedInEmployee.value.id;
        }
        form.post(route("attendance.store"), options);
    }
};

const deleteAttendance = () => {
    if (selectedRecord.value) {
        router.delete(route("attendance.destroy", selectedRecord.value.id), {
            preserveScroll: true,
            onSuccess: () => closeDeleteModal(),
            onError: (errors) => {
                console.error("Delete error:", errors);
                page.props.flash.error =
                    errors.message || "Failed to delete attendance record.";
                closeDeleteModal(); // Close modal even on error
            },
        });
    }
};

// Helper function to format date and time
const formatDateTime = (dateTimeString) => {
    if (!dateTimeString) return "-";
    try {
        const date = parseISO(dateTimeString.replace(" ", "T")); // Handle potential space separator
        return format(date, "MMM dd, yyyy hh:mm a");
    } catch (error) {
        console.error("Error formatting date:", error);
        return dateTimeString;
    }
};
</script>
