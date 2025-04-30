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
                <div>
                    <button
                        @click="openAddModal"
                        class="px-4 py-2 bg-primary-600 text-black rounded-md shadow hover:bg-primary-700 border border-gray-300"
                    >
                        Add Attendance
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
                                {{ record.clock_in || "-" }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ record.clock_out || "-" }}
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
                                >
                                    Edit
                                </button>
                                <button
                                    @click="confirmDelete(record)"
                                    class="ml-4 text-red-600 hover:text-red-900"
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
                    {{ isEditing ? "Edit Attendance" : "Add Attendance" }}
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
                            <select
                                id="employee"
                                v-model="form.employee_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="" disabled>
                                    Select employee
                                </option>
                                <option
                                    v-for="employee in employees"
                                    :key="employee.id"
                                    :value="employee.id"
                                >
                                    {{ employee.name }}
                                </option>
                            </select>
                            <p
                                v-if="formErrors.employee_id"
                                class="text-red-600 text-sm"
                            >
                                {{ formErrors.employee_id }}
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <p
                                v-if="formErrors.date"
                                class="text-red-600 text-sm"
                            >
                                {{ formErrors.date }}
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
                                type="datetime-local"
                                id="clock_in"
                                v-model="form.clock_in"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <p
                                v-if="formErrors.clock_in"
                                class="text-red-600 text-sm"
                            >
                                {{ formErrors.clock_in }}
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
                                type="datetime-local"
                                id="clock_out"
                                v-model="form.clock_out"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <p
                                v-if="formErrors.clock_out"
                                class="text-red-600 text-sm"
                            >
                                {{ formErrors.clock_out }}
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
                                v-if="formErrors.status"
                                class="text-red-600 text-sm"
                            >
                                {{ formErrors.status }}
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
import { ref, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
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
});

const page = usePage();
const flash = computed(() => page.props.flash);

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const form = ref({
    id: null,
    employee_id: "",
    date: "", // Add the date field
    clock_in: "",
    clock_out: "",
    status: "present",
});
const selectedRecord = ref(null);
const formErrors = ref({}); 

const openAddModal = () => {
    isEditing.value = false;
    form.value = {
        id: null,
        employee_id: "",
        date: "", // Reset the date field
        clock_in: "",
        clock_out: "",
        status: "present",
    };
    showModal.value = true;
};

const openEditModal = (record) => {
    isEditing.value = true;
    form.value = {
        id: record.id,
        employee_id: record.employee.id, // Populate employee_id
        date: record.date, // Populate date
        clock_in: record.clock_in ? record.clock_in.replace(" ", "T") : "", // Populate clock_in
        clock_out: record.clock_out ? record.clock_out.replace(" ", "T") : "", // Populate clock_out
        status: record.status, // Populate status
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
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
    formErrors.value = {}; // Reset errors before submission

    if (
        form.value.clock_in &&
        form.value.clock_out &&
        form.value.clock_out < form.value.clock_in
    ) {
        formErrors.value.clock_out =
            "Clock out must be after or equal to clock in.";
        return;
    }

    if (!form.value.date) {
        formErrors.value.date = "The date field is required.";
        return;
    }

    const action = isEditing.value
        ? router.put(route("attendance.update", form.value.id), form.value)
        : router.post(route("attendance.store"), form.value);

    action
        .then(() => {
            closeModal();
            form.value = {
                id: null,
                employee_id: "",
                date: "",
                clock_in: "",
                clock_out: "",
                status: "present",
            };
        })
        .catch((errors) => {
            formErrors.value = errors.response.data.errors || {};
        });
};

const deleteAttendance = () => {
    if (selectedRecord.value) {
        router.delete(route("attendance.destroy", selectedRecord.value.id), {
            onSuccess: () => closeDeleteModal(),
        });
    }
};
</script>
