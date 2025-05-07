<template>
    <AdminLayout>

    
    <div>
        <div
            v-if="
                topOverall.length === 0 &&
                Object.keys(topByDepartment).length === 0
            "
        >
            <p class="text-center text-gray-500">
                No data available for the leaderboard.
            </p>
        </div>
        <div v-else>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Overall Leaderboard -->
                <div class="lg:col-span-2">
                    <DashboardCard
                        title="Overall Top Performers"
                        subtitle="Based on total accumulated KPI points"
                    >
                        <ul role="list" class="divide-y divide-gray-200">
                            <li
                                v-if="topOverall.length === 0"
                                class="py-4 text-center text-gray-500"
                            >
                                No employees with KPI points yet.
                            </li>
                            <li
                                v-for="(employee, index) in topOverall"
                                :key="employee.id"
                                class="py-4 flex items-center"
                            >
                                <div class="flex-shrink-0 mr-4">
                                    <div class="relative">
                                        <img
                                            class="h-12 w-12 rounded-full"
                                            :src="employee.profile_picture"
                                            :alt="`${employee.name}'s profile picture`"
                                        />
                                        <span
                                            class="absolute -top-1 -right-1 h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center text-xs text-red-800 font-bold border-2 border-red-200"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900 truncate"
                                    >
                                        {{ employee.name }}
                                        <span class="text-xs text-gray-500"
                                            >({{ employee.employee_id }})</span
                                        >
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ employee.position }} -
                                        {{ employee.department }}
                                    </p>
                                    <div
                                        class="mt-1 flex items-center space-x-1"
                                    >
                                        <span
                                            v-for="badge in employee.earned_badges"
                                            :key="badge.id"
                                            :title="`${badge.name}: ${badge.description} (${badge.points_required} points)`"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                            :style="{
                                                backgroundColor: badge.color,
                                                color: getTextColor(
                                                    badge.color
                                                ),
                                            }"
                                        >
                                            <component
                                                :is="
                                                    getIconComponent(badge.icon)
                                                "
                                                class="h-3 w-3 mr-0.5"
                                            />
                                            {{ badge.name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4 text-right">
                                    <p
                                        class="text-lg font-semibold text-primary-600"
                                    >
                                        {{ employee.total_points }}
                                    </p>
                                    <p class="text-xs text-gray-500">Points</p>
                                </div>
                            </li>
                        </ul>
                    </DashboardCard>
                </div>

                <!-- Department Leaderboards -->
                <div class="lg:col-span-1 space-y-6">
                    <DashboardCard
                        v-for="(topEmployees, deptId) in topByDepartment"
                        :key="deptId"
                        :title="`${departments[deptId]} Leaders`"
                        subtitle="Top 3 in department"
                    >
                        <ul role="list" class="divide-y divide-gray-200">
                            <li
                                v-if="topEmployees.length === 0"
                                class="py-3 text-center text-gray-500 text-sm"
                            >
                                No employees with points in this department.
                            </li>
                            <li
                                v-for="(employee, index) in topEmployees"
                                :key="employee.id"
                                class="py-3 flex items-center"
                            >
                                <div class="flex-shrink-0 mr-3">
                                    <span
                                        class="h-6 w-6 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600 font-medium"
                                    >
                                        {{ index + 1 }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900 truncate"
                                    >
                                        {{ employee.name }}
                                    </p>
                                    <div
                                        class="mt-1 flex items-center space-x-1"
                                    >
                                        <span
                                            v-for="badge in employee.earned_badges"
                                            :key="badge.id"
                                            :title="`${badge.name} (${badge.points_required} points)`"
                                            class="inline-flex items-center px-1 py-0 rounded text-xs font-medium"
                                            :style="{
                                                backgroundColor: badge.color,
                                                color: getTextColor(
                                                    badge.color
                                                ),
                                            }"
                                        >
                                            <component
                                                :is="
                                                    getIconComponent(badge.icon)
                                                "
                                                class="h-3 w-3"
                                            />
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3 text-right">
                                    <p
                                        class="text-sm font-semibold text-primary-600"
                                    >
                                        {{ employee.total_points }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </DashboardCard>
                    <div
                        v-if="Object.keys(topByDepartment).length === 0"
                        class="text-center text-gray-500 py-4"
                    >
                        No departmental data available.
                    </div>
                </div>
            </div>
            <!-- Action buttons -->
            <div class="mt-6 flex justify-end space-x-3">
                <Link
                    :href="route('kpis.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <ChartBarIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Manage KPIs
                </Link>
                <Link
                    :href="route('kpis.employee-kpis')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <UserIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Employee KPIs
                </Link>
                <Link
                    :href="route('kpis.dashboard')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <ChartPieIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Dashboard
                </Link>
            </div>
        </div>
    </div>
</AdminLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
// Import using v1 outline syntax
import {
    StarIcon,
    LightBulbIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ChartBarIcon,
    CalendarIcon,
    BadgeCheckIcon,
    UserIcon,
    ChartPieIcon,
} from "@heroicons/vue/outline";

const props = defineProps({
    topOverall: {
        type: Array,
        required: true,
    },
    topByDepartment: {
        type: Object, // Dept ID => Array of employees
        required: true,
    },
    departments: {
        type: Object, // Dept ID => Dept Name
        required: true,
    },
    availableBadges: {
        type: Array,
        required: true,
    },
});

// Debugging: Log props to ensure data is being received
console.log("topOverall:", props.topOverall);
console.log("topByDepartment:", props.topByDepartment);
console.log("departments:", props.departments);
console.log("availableBadges:", props.availableBadges);

const iconMap = {
    lightbulb: LightBulbIcon,
    star: StarIcon,
    calendar: CalendarIcon,
    "check-circle": CheckCircleIcon,
    "badge-check": BadgeCheckIcon,
    "chart-bar": ChartBarIcon,
};

const getIconComponent = (iconName) => {
    const iconComponent = iconMap[iconName];
    if (!iconComponent) {
        console.warn(
            `Icon mapping missing for: ${iconName}. Defaulting to StarIcon.`
        );
        return StarIcon;
    }
    return iconComponent;
};

const getTextColor = (bgColor) => {
    if (!bgColor) return "#000000";

    const color = bgColor.charAt(0) === "#" ? bgColor.substring(1, 7) : bgColor;
    const r = parseInt(color.substring(0, 2), 16);
    const g = parseInt(color.substring(2, 4), 16);
    const b = parseInt(color.substring(4, 6), 16);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 155 ? "#000000" : "#FFFFFF";
};
</script>

<style scoped></style>
