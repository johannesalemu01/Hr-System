<script setup>
import { ref, computed } from "vue";
import { format, parseISO } from "date-fns";
import { router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    SearchIcon,
} from "@heroicons/vue/outline";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    attendanceData: {
        type: Array,
        required: true,
    },
    currentDate: {
        type: String,
        default: null,
    },
    departments: {
        type: Array,
        default: () => [],
    },
    employees: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            totalEmployees: 0,
            presentToday: 0,
            lateToday: 0,
            absentToday: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            department_id: "",
            status: "",
            search: "",
            date: null,
        }),
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash);

const filters = ref({
    date: null, // Default to show all dates
    department_id: "", // Default to "All Departments"
    status: "", // Default to "All Statuses"
    search: "",
});

// Apply filters
const applyFilters = () => {
    router.get(
        route("attendance.index"),
        {
            date: filters.value.date || null, // Include date only if selected
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
        date: null, // Reset date filter to show all dates
        department_id: "",
        status: "",
        search: "",
    };
    applyFilters();
};

const formatDate = (date) => {
    if (!date) return "-";
    return format(parseISO(date), "MMM dd, yyyy");
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
                 
                
                    <input
                        type="date"
                        v-model="filters.date"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        placeholder="Select a date"
                    />
                </div>
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
                    <p class="text-sm text-gray-500">Present </p>
                    <p class="text-lg font-semibold text-green-800">
                        {{ stats.presentToday }}
                    </p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Late </p>
                    <p class="text-lg font-semibold text-yellow-800">
                        {{ stats.lateToday }}
                    </p>
                </div>
                <div class="bg-red-100 p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-500">Absent </p>
                    <p class="text-lg font-semibold text-red-800">
                        {{ stats.absentToday }}
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 mb-6">
                
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
                <abbr class="flex space-x-2">
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

                </abbr>
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
                                Notes
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
                                {{ record.notes || "-" }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :links="page.props.attendance?.links" />
        </div>
    </AuthenticatedLayout>
</template>
