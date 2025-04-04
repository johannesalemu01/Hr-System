<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- <AdminLayout title="Dashboard" description="Overview of your HR system"> -->
        <div class="px-4">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-4">
                <!-- Stats cards -->
                <StatCard
                    title="Total Employees"
                    :value="stats.totalEmployees"
                    icon="users"
                    trend="up"
                    :percentage="5.2"
                    color="blue"
                />
                <StatCard
                    title="Average KPI Score"
                    :value="stats.averageKpiScore + '%'"
                    icon="chart-bar"
                    trend="up"
                    :percentage="2.3"
                    color="green"
                />
                <StatCard
                    title="Leave Requests"
                    :value="stats.pendingLeaveRequests"
                    icon="calendar"
                    trend="down"
                    :percentage="1.5"
                    color="amber"
                />
                <StatCard
                    title="Attendance Rate"
                    :value="stats.attendanceRate + '%'"
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
        <!-- </AdminLayout> -->
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import KpiPerformanceChart from "@/Components/Dashboard/Charts/KpiPerformanceChart.vue";
import DepartmentDistributionChart from "@/Components/Dashboard/Charts/DepartmentDistributionChart.vue";
import ActivityFeed from "@/Components/Dashboard/ActivityFeed.vue";
import EventsList from "@/Components/Dashboard/EventsList.vue";
import { Head } from "@inertiajs/vue3";

// Sample data - in a real app, this would come from the backend
const stats = ref({
    totalEmployees: 124,
    averageKpiScore: 87,
    pendingLeaveRequests: 8,
    attendanceRate: 95.7,
});

const kpiPerformanceData = ref({
    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
    datasets: [
        {
            label: "Average KPI Score",
            data: [78, 81, 80, 83, 85, 87],
            borderColor: "#10b981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
        },
    ],
});

const departmentDistributionData = ref({
    labels: ["FronEnd", "BackEnd", "Devops", "Design", "Marketing"],
    datasets: [
        {
            data: [15, 42, 28, 18, 21],
            backgroundColor: [
                "#3b82f6",
                "#10b981",
                "#f59e0b",
                "#6366f1",
                "#ec4899",
            ],
        },
    ],
});

const recentActivities = ref([
    {
        id: 1,
        user: {
            name: "John Doe",
            avatar: "/placeholder.svg?height=40&width=40",
        },
        action: "completed KPI assessment",
        target: "Q2 Sales Target",
        date: "2 hours ago",
        icon: "chart-bar",
        iconColor: "bg-green-100 text-green-600",
    },
    {
        id: 2,
        user: {
            name: "Jane Smith",
            avatar: "/placeholder.svg?height=40&width=40",
        },
        action: "requested leave",
        target: "Annual Leave (5 days)",
        date: "4 hours ago",
        icon: "calendar",
        iconColor: "bg-amber-100 text-amber-600",
    },
    {
        id: 3,
        user: {
            name: "Robert Johnson",
            avatar: "/placeholder.svg?height=40&width=40",
        },
        action: "approved payroll",
        target: "June 2023",
        date: "Yesterday",
        icon: "currency-dollar",
        iconColor: "bg-blue-100 text-blue-600",
    },
    {
        id: 4,
        user: {
            name: "Emily Davis",
            avatar: "/placeholder.svg?height=40&width=40",
        },
        action: "earned badge",
        target: "Top Performer",
        date: "2 days ago",
        icon: "badge-check",
        iconColor: "bg-indigo-100 text-indigo-600",
    },
]);

const upcomingEvents = ref([
    {
        id: 1,
        title: "Payroll Processing",
        date: "Jul 25, 2023",
        time: "10:00 AM",
        type: "payroll",
        icon: "currency-dollar",
        iconColor: "bg-blue-100 text-blue-600",
    },
    {
        id: 2,
        title: "KPI Review Meeting",
        date: "Jul 27, 2023",
        time: "2:00 PM",
        type: "meeting",
        icon: "chart-bar",
        iconColor: "bg-green-100 text-green-600",
    },
    {
        id: 3,
        title: "New Employee Onboarding",
        date: "Jul 31, 2023",
        time: "9:00 AM",
        type: "onboarding",
        icon: "user-add",
        iconColor: "bg-indigo-100 text-indigo-600",
    },
]);
</script>
