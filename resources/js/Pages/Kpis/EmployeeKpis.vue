<template>
    <AdminLayout
        title="Employee KPIs"
        description="Manage and track employee KPI assignments"
    >
        <!-- Action buttons and filters -->
        <div
            class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
        >
            <div
                class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-4"
            >
                <div class="w-full sm:w-64">
                    <label for="search" class="sr-only">Search</label>
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
                            id="search"
                            v-model="filters.search"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                            placeholder="Search employees"
                            type="search"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <div class="w-full sm:w-auto">
                    <select
                        v-model="filters.department_id"
                        class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm"
                        @change="applyFilters"
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

                <div class="w-full sm:w-auto">
                    <select
                        v-model="filters.status"
                        class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-primary-500 focus:outline-none focus:ring-primary-500 sm:text-sm"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{
                                status.charAt(0).toUpperCase() + status.slice(1)
                            }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <Link
                    :href="route('kpis.leaderboard')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <StarIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Leaderboard
                </Link>
                <Link
                    v-if="isAdmin"
                    :href="route('kpis.dashboard')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <ChartPieIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Dashboard
                </Link>
                <Link
                    :href="route('kpis.assign')"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <PlusIcon class="h-5 w-5 mr-2" />
                    Assign KPI
                </Link>
            </div>
        </div>

        <!-- Employee KPIs Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Employee
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            KPI
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Target
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Progress
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Status
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Period
                        </th>
                        <th
                            scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-if="employeeKpis.data.length === 0">
                        <td
                            colspan="7"
                            class="px-6 py-4 text-center text-gray-500"
                        >
                            No employee KPIs found matching your criteria.
                        </td>
                    </tr>
                    <tr
                        v-for="empKpi in employeeKpis.data"
                        :key="empKpi.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ empKpi.employee.name }}
                                </div>
                                <div class="text-sm text-gray-500 ml-1">
                                    ({{ empKpi.employee.employee_id }})
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ empKpi.employee.department }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ empKpi.kpi.name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ empKpi.kpi.measurement_unit }}
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ empKpi.target_value }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="mr-2 text-sm font-medium"
                                    :class="
                                        getAchievementClass(
                                            empKpi.achievement_percentage
                                        )
                                    "
                                >
                                    {{
                                        empKpi.achievement_percentage
                                            ? `${empKpi.achievement_percentage}%`
                                            : "N/A"
                                    }}
                                </div>
                                <div
                                    class="w-24 bg-gray-200 rounded-full h-2.5"
                                >
                                    <div
                                        class="h-2.5 rounded-full"
                                        :class="
                                            getProgressColorClass(
                                                empKpi.achievement_percentage
                                            )
                                        "
                                        :style="{
                                            width: `${
                                                empKpi.achievement_percentage ||
                                                0
                                            }%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                            <div
                                class="text-xs text-gray-500 mt-1"
                                v-if="empKpi.current_value !== null"
                            >
                                Current: {{ empKpi.current_value }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                :class="getStatusClass(empKpi.status)"
                            >
                                {{ empKpi.status }}
                            </span>
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                        >
                            {{ formatDate(empKpi.start_date) }} -
                            {{ formatDate(empKpi.end_date) }}
                        </td>
                        <td
                            class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                        >
                            <Link
                                :href="route('kpis.record', empKpi.id)"
                                class="text-primary-600 hover:text-primary-900 mr-3"
                                v-if="empKpi.status === 'active'"
                            >
                                Record
                            </Link>
                            <Link
                                :href="route('kpis.show', empKpi.kpi.id)"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                Details
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <Pagination :links="employeeKpis.links" :meta="employeeKpis.meta" />
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import {
    SearchIcon,
    PlusIcon,
    ChartPieIcon,
    StarIcon,
} from "@heroicons/vue/outline";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    employeeKpis: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            department_id: "",
            search: "",
            status: "",
        }),
    },
});


const page = usePage();


const isAdmin = computed(() => page.props.auth?.user?.role === "admin");


const filters = ref({
    search: props.filters.search || "",
    department_id: props.filters.department_id || "",
    status: props.filters.status || "",
});


const applyFilters = () => {
    router.get(
        route("kpis.employee-kpis"),
        {
            department_id: filters.value.department_id,
            search: filters.value.search,
            status: filters.value.status,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Format date
const formatDate = (dateString) => {
    const options = { month: "short", day: "numeric", year: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

// Get status class
const getStatusClass = (status) => {
    switch (status) {
        case "active":
            return "bg-green-100 text-green-800";
        case "completed":
            return "bg-blue-100 text-blue-800";
        case "pending":
            return "bg-yellow-100 text-yellow-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

// Get achievement class
const getAchievementClass = (percentage) => {
    if (!percentage) return "text-gray-500";
    if (percentage >= 90) return "text-green-600";
    if (percentage >= 70) return "text-blue-600";
    if (percentage >= 50) return "text-yellow-600";
    return "text-red-600";
};

// Get progress color class
const getProgressColorClass = (percentage) => {
    if (!percentage) return "bg-gray-300";
    if (percentage >= 90) return "bg-green-600";
    if (percentage >= 70) return "bg-blue-600";
    if (percentage >= 50) return "bg-yellow-500";
    return "bg-red-600";
};
</script>
