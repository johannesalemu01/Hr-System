<script setup>
import { ref, computed } from "vue";
import { format, parseISO, addDays, subDays } from "date-fns";
import { router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    ClockIcon,
    UserGroupIcon,
    CalendarIcon,
    SearchIcon,
    ExclamationIcon,
} from "@heroicons/vue/outline";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import Modal from "@/Components/Modal.vue";
import Pagination from "@/Components/Pagination.vue";
const props = defineProps({
    attendanceData: {
        type: Array,
        required: true,
    },
    currentDate: {
        type: String,
        required: true,
    },
    departments: {
        type: Array,
        default: () => [],
    },
    employees: {  // Add this prop
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            department_id: "",
            status: "",
            search: "",
        }),
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },

});

const page = usePage();
const flash = computed(() => page.props.flash);

const statusColors = {
    present: "bg-green-100 text-green-800",
    late: "bg-yellow-100 text-yellow-800",
    absent: "bg-red-100 text-red-800",
};

const formatTime = (time) => {
    if (!time) return "-";
    return format(parseISO(time), "hh:mm a");
};

const formatDate = (date) => {
    if (!date) return "-";
    return format(parseISO(date), "MMM dd, yyyy");
};

const displayDate = computed(() => {
    return format(parseISO(props.currentDate), "MMMM yyyy");
});

// Filters
const filters = ref({ ...props.filters });

// Apply filters
const applyFilters = () => {
    router.get(
        route("attendance.index"),
        {
            date: props.currentDate,
            department_id: filters.value.department_id,
            status: filters.value.status,
            search: filters.value.search,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Reset filters
const resetFilters = () => {
    filters.value = {
        department_id: "",
        status: "",
        search: "",
    };
    applyFilters();
};

// Navigation
const goToPreviousDay = () => {
    const prevDay = subDays(parseISO(props.currentDate), 1);
    router.get(
        route("attendance.index"),
        {
            date: format(prevDay, "yyyy-MM-dd"),
            department_id: filters.value.department_id,
            status: filters.value.status,
            search: filters.value.search,
        },
        {
            preserveState: true,
        }
    );
};

const goToNextDay = () => {
    const nextDay = addDays(parseISO(props.currentDate), 1);
    router.get(
        route("attendance.index"),
        {
            date: format(nextDay, "yyyy-MM-dd"),
            department_id: filters.value.department_id,
            status: filters.value.status,
            search: filters.value.search,
        },
        {
            preserveState: true,
        }
    );
};

// Edit attendance
const showEditModal = ref(false);
const selectedAttendance = ref(null);
const editForm = ref({
    clock_in: "",
    clock_out: "",
    status: "",
    notes: "",
});

const openEditModal = (attendance) => {
    selectedAttendance.value = attendance;
    editForm.value = {
        clock_in: attendance.clock_in
            ? attendance.clock_in.substring(0, 16).replace(" ", "T")
            : "",
        clock_out: attendance.clock_out
            ? attendance.clock_out.substring(0, 16).replace(" ", "T")
            : "",
        status: attendance.status,
        notes: attendance.notes || "",
    };
    showEditModal.value = true;
};

const updateAttendance = () => {
    // Format datetime for backend
    const formattedForm = {
        ...editForm.value,
        clock_in: editForm.value.clock_in
            ? editForm.value.clock_in.replace("T", " ") + ":00"
            : null,
        clock_out: editForm.value.clock_out
            ? editForm.value.clock_out.replace("T", " ") + ":00"
            : null,
    };

    router.patch(
        route("attendance.update", selectedAttendance.value.id),
        formattedForm,
        {
            onSuccess: () => {
                showEditModal.value = false;
            },
        }
    );
};

// Add attendance
const showAddModal = ref(false);
const addForm = ref({
    employee_id: "",
    date: props.currentDate,
    clock_in: "",
    clock_out: "",
    status: "present",
    notes: "",
    location: "",
});

const openAddModal = () => {
    addForm.value = {
        employee_id: "",
        date: props.currentDate,
        clock_in: "",
        clock_out: "",
        status: "present",
        notes: "",
        location: "",
    };
    showAddModal.value = true;
};

const createAttendance = () => {
    // Format datetime for backend
    const formattedForm = {
        ...addForm.value,
        clock_in: addForm.value.clock_in
            ? addForm.value.clock_in.replace("T", " ") + ":00"
            : null,
        clock_out: addForm.value.clock_out
            ? addForm.value.clock_out.replace("T", " ") + ":00"
            : null,
    };

    router.post(route("attendance.store"), formattedForm, {
        onSuccess: () => {
            showAddModal.value = false;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout
        title="Attendance Dashboard"
        description="View and manage employee attendance records"
    >
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

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-5 sm:p-6">
                <!-- Header -->
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Attendance Dashboard
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            View and manage employee attendance records
                        </p>
                    </div>
                    <div class="mt-4 flex items-center sm:mt-0">
                        <button
                            @click="goToPreviousDay"
                            class="inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-10 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            <span class="sr-only">Previous day</span>
                            <ChevronLeftIcon
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </button>
                        <button
                            @click="goToNextDay"
                            class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 focus:z-10 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        >
                            <span class="sr-only">Next day</span>
                            <ChevronRightIcon
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </button>
                        <div class="ml-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ formatDate(currentDate) }}
                            </h2>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UserGroupIcon
                                        class="h-6 w-6 text-gray-400"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Employees
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.totalEmployees }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ClockIcon
                                        class="h-6 w-6 text-green-400"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Present Today
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.presentToday }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ExclamationIcon
                                        class="h-6 w-6 text-yellow-400"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Late Today
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.lateToday }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <CalendarIcon
                                        class="h-6 w-6 text-red-400"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Absent Today
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.absentToday }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Actions -->
                <div
                    class="mt-6 flex flex-col sm:flex-row justify-between space-y-4 sm:space-y-0 sm:space-x-4"
                >
                    <div
                        class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4"
                    >
                        <!-- Search -->
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                            >
                                <SearchIcon
                                    class="h-5 w-5 text-gray-400"
                                    aria-hidden="true"
                                />
                            </div>
                            <input
                                type="text"
                                v-model="filters.search"
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Search employees"
                            />
                        </div>

                        <!-- Department Filter -->
                        <select
                            v-if="isAdmin"
                            v-model="filters.department_id"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
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

                        <!-- Status Filter -->
                        <select
                            v-model="filters.status"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Statuses</option>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                        </select>

                        <!-- Filter Buttons -->
                        <div class="flex space-x-2">
                            <button
                                @click="applyFilters"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Apply Filters
                            </button>
                            <button
                                @click="resetFilters"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Add Attendance Button (Admin Only) -->
                    <div v-if="isAdmin">
                        <button
                            @click="openAddModal"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            Add Attendance
                        </button>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="mt-8 flex flex-col">
                    <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div
                            class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
                        >
                            <div
                                class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-300"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                scope="col"
                                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                            >
                                                Employee
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Clock In
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Clock Out
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Hours
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Status
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Location
                                            </th>
                                            <th
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Notes
                                            </th>
                                            <th
                                                v-if="isAdmin"
                                                scope="col"
                                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                                            >
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white"
                                    >
                                        <tr v-if="attendanceData.length === 0">
                                            <td
                                                :colspan="isAdmin ? 8 : 7"
                                                class="px-3 py-4 text-sm text-gray-500 text-center"
                                            >
                                                No attendance records found for
                                                this date.
                                            </td>
                                        </tr>
                                        <tr
                                            v-else
                                            v-for="record in attendanceData"
                                            :key="record.id"
                                        >
                                            <td
                                                class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                            >
                                                {{ record.employee.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{
                                                    formatTime(record.clock_in)
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{
                                                    formatTime(record.clock_out)
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{ record.hours_worked ?? "-" }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm"
                                            >
                                                <span
                                                    :class="[
                                                        statusColors[
                                                            record.status
                                                        ],
                                                        'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                                    ]"
                                                >
                                                    {{ record.status }}
                                                </span>
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{ record.location ?? "-" }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                {{ record.notes ?? "-" }}
                                            </td>
                                            <td
                                                v-if="isAdmin"
                                                class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                                            >
                                                <button
                                                    @click="
                                                        openEditModal(record)
                                                    "
                                                    class="text-primary-600 hover:text-primary-900"
                                                >
                                                    Edit
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination Component -->
                <Pagination
                    :links="page.props.attendance.links"
                    :meta="page.props.attendance.meta"
                />
            </div>
        </div>

        <!-- Edit Attendance Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Attendance Record
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Update attendance details for
                    {{ selectedAttendance?.employee?.name }}
                </p>
                <div class="mt-4 space-y-4">
                    <!-- Clock In -->
                    <div>
                        <label
                            for="clock-in"
                            class="block text-sm font-medium text-gray-700"
                            >Clock In</label
                        >
                        <input
                            type="datetime-local"
                            id="clock-in"
                            v-model="editForm.clock_in"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                    </div>

                    <!-- Clock Out -->
                    <div>
                        <label
                            for="clock-out"
                            class="block text-sm font-medium text-gray-700"
                            >Clock Out</label
                        >
                        <input
                            type="datetime-local"
                            id="clock-out"
                            v-model="editForm.clock_out"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-700"
                            >Status</label
                        >
                        <select
                            id="status"
                            v-model="editForm.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            for="notes"
                            class="block text-sm font-medium text-gray-700"
                            >Notes</label
                        >
                        <textarea
                            id="notes"
                            v-model="editForm.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            placeholder="Add any notes about this attendance record"
                        ></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="showEditModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="updateAttendance"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Add Attendance Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add Attendance Record
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Create a new attendance record for
                    {{ formatDate(currentDate) }}
                </p>
                <div class="mt-4 space-y-4">
                    <!-- Employee -->
                    <div>
                        <label
                            for="employee"
                            class="block text-sm font-medium text-gray-700"
                            >Employee</label
                        >
                        <select
                            id="employee"
                            v-model="addForm.employee_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="" disabled>Select employee</option>
                            <option
                                v-for="employee in employees"
                                :key="employee.id"
                                :value="employee.id"
                            >
                                {{ employee.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Clock In -->
                    <div>
                        <label
                            for="add-clock-in"
                            class="block text-sm font-medium text-gray-700"
                            >Clock In</label
                        >
                        <input
                            type="datetime-local"
                            id="add-clock-in"
                            v-model="addForm.clock_in"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                    </div>

                    <!-- Clock Out -->
                    <div>
                        <label
                            for="add-clock-out"
                            class="block text-sm font-medium text-gray-700"
                            >Clock Out</label
                        >
                        <input
                            type="datetime-local"
                            id="add-clock-out"
                            v-model="addForm.clock_out"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        />
                    </div>

                    <!-- Status -->
                    <div>
                        <label
                            for="add-status"
                            class="block text-sm font-medium text-gray-700"
                            >Status</label
                        >
                        <select
                            id="add-status"
                            v-model="addForm.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>

                    <!-- Location -->
                    <div>
                        <label
                            for="location"
                            class="block text-sm font-medium text-gray-700"
                            >Location</label
                        >
                        <input
                            type="text"
                            id="location"
                            v-model="addForm.location"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            placeholder="Office, Remote, etc."
                        />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            for="add-notes"
                            class="block text-sm font-medium text-gray-700"
                            >Notes</label
                        >
                        <textarea
                            id="add-notes"
                            v-model="addForm.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            placeholder="Add any notes about this attendance record"
                        ></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="showAddModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="createAttendance"
                    >
                        Create Record
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
