<template>
    <AdminLayout
        :title="`KPI Details: ${kpi.name}`"
        :description="kpi.description"
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 space-y-6">
                <DashboardCard title="KPI Information">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Name
                            </h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ kpi.name }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">
                                Description
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ kpi.description }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Measurement
                                </h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ kpi.measurement_unit }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Frequency
                                </h3>
                                <p
                                    class="mt-1 text-sm text-gray-900 capitalize"
                                >
                                    {{ kpi.frequency }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Department
                                </h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ kpi.department }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Position
                                </h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ kpi.position || "All Positions" }}
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Points Value
                                </h3>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ kpi.points_value }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500">
                                    Status
                                </h3>
                                <p class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            kpi.is_active
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        "
                                    >
                                        {{
                                            kpi.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </DashboardCard>
            </div>

            <!-- Right Column: Stats, Chart, Tables -->
            <div class="lg:col-span-2 space-y-6">

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <StatCard
                        title="Avg. Achievement"
                        :value="`${stats.avgAchievement}%`"
                        icon="trending-up"
                        color="blue"
                    />
                    <StatCard
                        title="Assigned Employees"
                        :value="stats.employeeCount"
                        icon="users"
                        color="purple"
                    />
                    <StatCard
                        title="Recent Records"
                        :value="stats.recordsCount"
                        icon="document-report"
                        color="teal"
                    />
                </div>

                
                <DashboardCard
                    v-if="!isEmployeeView && employeeKpis.length > 0"
                    title="Assigned Employees"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Employee
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Target
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Period
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="empKpi in employeeKpis"
                                    :key="empKpi.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ empKpi.employee.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ empKpi.employee.department }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ empKpi.target_value }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(empKpi.start_date) }} -
                                        {{ formatDate(empKpi.end_date) }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="
                                                getStatusClass(empKpi.status)
                                            "
                                        >
                                            {{ empKpi.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </DashboardCard>

                <!-- Recent Records Table -->
                <DashboardCard title="Recent Records">
                    <div v-if="kpiRecords.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        v-if="!isEmployeeView"
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Employee
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Date
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actual
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Target
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Achievement
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Points
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="record in kpiRecords"
                                    :key="record.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        v-if="!isEmployeeView"
                                        class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{ record.employee }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(record.record_date) }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        {{ record.actual_value }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ record.target_value }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span
                                            class="font-medium"
                                            :class="
                                                getAchievementClass(
                                                    record.achievement_percentage
                                                )
                                            "
                                        >
                                            {{ record.achievement_percentage }}%
                                        </span>
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ record.points_earned }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center text-gray-500 py-8">
                        No recent records found for this KPI.
                    </div>
                </DashboardCard>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue"; 

import {
    TrendingUpIcon,
    UsersIcon,
    DocumentReportIcon,
} from "@heroicons/vue/outline";



const props = defineProps({
    kpi: Object,
    employeeKpis: Array,
    kpiRecords: Array,
    stats: Object,

    isEmployeeView: Boolean,
});


const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const options = { month: "short", day: "numeric", year: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const getStatusClass = (status) => {
    switch (status?.toLowerCase()) {
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

const getAchievementClass = (percentage) => {
    if (percentage === null || percentage === undefined) return "text-gray-500";
    if (percentage >= 90) return "text-green-600";
    if (percentage >= 70) return "text-blue-600";
    if (percentage >= 50) return "text-yellow-600";
    return "text-red-600";
};
</script>

<style scoped>
/* Add any specific styles if needed */
</style>
