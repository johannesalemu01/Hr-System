<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="px-4">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-4">
                <!-- Stats cards -->
                <StatCard
                    title="Total Employees"
                    :value="stats?.totalEmployees"
                    icon="users"
                    trend="up"
                    :percentage="5.2"
                    color="blue"
                />
                <StatCard
                    title="Average KPI Score"
                    :value="stats?.averageKpiScore + '%'"
                    icon="chart-bar"
                    trend="up"
                    :percentage="2.3"
                    color="green"
                />
                <StatCard
                    title="Leave Requests"
                    :value="stats?.pendingLeaveRequests"
                    icon="calendar"
                    trend="down"
                    :percentage="1.5"
                    color="amber"
                />
                <StatCard
                    title="Attendance Rate"
                    :value="stats?.attendanceRate + '%'"
                    icon="clipboard-check"
                    trend="up"
                    :percentage="0.8"
                    color="indigo"
                />
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- KPI Performance Chart -->
                <DashboardCard title="KPI Performance" subtitle="Last 6 months">
                    <KpiPerformanceChart :data="kpiPerformanceData" />
                </DashboardCard>

                <!-- Department Distribution -->
                <DashboardCard
                    title="Employee Distribution"
                    subtitle="By department"
                >
                    <DepartmentDistributionChart
                        :data="departmentDistributionData"
                    />
                </DashboardCard>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Recent Activities -->
                <DashboardCard title="Recent Activities" class="lg:col-span-2">
                    <ActivityFeed :activities="recentActivities" />
                </DashboardCard>

                <!-- Upcoming Events -->
                <DashboardCard title="Upcoming Events">
                    <EventsList :events="upcomingEvents" />
                </DashboardCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import KpiPerformanceChart from "@/Components/Dashboard/Charts/KpiPerformanceChart.vue";
import DepartmentDistributionChart from "@/Components/Dashboard/Charts/DepartmentDistributionChart.vue";
import ActivityFeed from "@/Components/Dashboard/ActivityFeed.vue";
import EventsList from "@/Components/Dashboard/EventsList.vue";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    kpiPerformanceData: {
        type: Object,
        default: () => ({}),
    },
    departmentDistributionData: {
        type: Object,
        default: () => ({}),
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
    upcomingEvents: {
        type: Array,
        default: () => [],
    },
});

console.log(props.stats);
// Remove this destructuring - keep props as an object
// const { ... } = props;
</script>
