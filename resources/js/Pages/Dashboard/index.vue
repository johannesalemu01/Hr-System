<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="px-4">
            <!-- Stats, Charts, Recent Activities (Admin/Manager Only) -->
            <div v-if="!isEmployeeView">
                <div
                    class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-4"
                >
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
                    <DashboardCard
                        title="KPI Performance"
                        subtitle="Last 6 months"
                    >
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
            </div>

            <div
                class="mt-8 grid grid-cols-1 gap-6"
                :class="{ 'lg:grid-cols-3': !isEmployeeView }"
            >
                <!-- Recent Activities (Admin/Manager Only) -->
                <DashboardCard
                    v-if="!isEmployeeView"
                    title="Recent Activities"
                    class="lg:col-span-2"
                >
                    <ActivityFeed :activities="recentActivities" />
                </DashboardCard>

                <!-- Upcoming Events (Always Shown, adjusted span for employee) -->
                <DashboardCard
                    title="Upcoming Events"
                    :class="{
                        'lg:col-span-3': isEmployeeView,
                        'lg:col-span-1': !isEmployeeView,
                    }"
                >
                    <EventsList
                        :events="upcomingEvents"
                        :can-manage-events="isAdminOrManager"
                        @edit="(event) => event && editEvent(event)"
                        @delete="deleteEvent"
                    />
                    <button
                        v-if="isAdminOrManager"
                        class="mt-4 mx-auto block w-32 py-2 px-4 bg-primary-600 text-black rounded-md hover:bg-primary-700 border border-gray-300"
                        @click="showAddEventModal = true"
                    >
                        Add Event
                    </button>
                </DashboardCard>
            </div>
        </div>

        <!-- Add/Edit Event Modal (Only rendered if user is admin/manager) -->
        <Modal
            v-if="isAdminOrManager"
            :show="showAddEventModal || showEditEventModal"
            @close="closeEventModal"
        >
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ showEditEventModal ? "Edit Event" : "Add Event" }}
                </h2>
                <form @submit.prevent="submitEvent">
                    <div class="mt-4 space-y-4">
                        <div>
                            <label
                                for="title"
                                class="block text-sm font-medium text-gray-700"
                                >Title</label
                            >
                            <input
                                id="title"
                                v-model="eventForm.title"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label
                                for="event_date"
                                class="block text-sm font-medium text-gray-700"
                                >Date & Time</label
                            >
                            <input
                                id="event_date"
                                v-model="eventForm.event_date"
                                type="datetime-local"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label
                                for="type"
                                class="block text-sm font-medium text-gray-700"
                                >Type</label
                            >
                            <select
                                id="type"
                                v-model="eventForm.type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="meeting">Meeting</option>
                                <option value="holiday">Holiday</option>
                                <option value="training">Training</option>
                            </select>
                        </div>
                        <div>
                            <label
                                for="description"
                                class="block text-sm font-medium text-gray-700"
                                >Description</label
                            >
                            <textarea
                                id="description"
                                v-model="eventForm.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            ></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                            @click="closeEventModal"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-primary-600 text-black border border-gray-300 rounded-md hover:bg-primary-700"
                        >
                            {{ showEditEventModal ? "Update" : "Add" }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import KpiPerformanceChart from "@/Components/Dashboard/Charts/KpiPerformanceChart.vue";
import DepartmentDistributionChart from "@/Components/Dashboard/Charts/DepartmentDistributionChart.vue";
import ActivityFeed from "@/Components/Dashboard/ActivityFeed.vue";
import EventsList from "@/Components/Dashboard/EventsList.vue";
import Modal from "@/Components/Modal.vue";

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
    isEmployeeView: {
        type: Boolean,
        default: false,
    },
    isAdminOrManager: {
        type: Boolean,
        default: false,
    },
});

const showAddEventModal = ref(false);
const showEditEventModal = ref(false);
const eventForm = ref({
    id: null,
    title: "",
    event_date: "",
    type: "meeting",
    description: "",
});

const closeEventModal = () => {
    showAddEventModal.value = false;
    showEditEventModal.value = false;
    eventForm.value = {
        id: null,
        title: "",
        event_date: "",
        type: "meeting",
        description: "",
    };
};

const submitEvent = () => {
    if (!props.isAdminOrManager) return;
    if (showEditEventModal.value) {
        const numericId = String(eventForm.value.id).split("_").pop();
        if (!numericId || isNaN(parseInt(numericId))) {
            console.error(
                "Invalid event ID format for update:",
                eventForm.value.id
            );

            return;
        }
        const updateUrl = `/events/${numericId}`;
        router.put(updateUrl, eventForm.value, {
            onSuccess: () => {
                closeEventModal();
                console.log("Event updated successfully.");
                router.reload({ only: ["upcomingEvents"] });
            },
            onError: (errors) => {
                console.error("Failed to update event:", errors);
            },
            preserveScroll: true,
        });
    } else {
        const storeUrl = `/events`;
        router.post(storeUrl, eventForm.value, {
            onSuccess: () => {
                closeEventModal();
                router.reload({ only: ["upcomingEvents"] });
            },
            onError: (errors) => {
                console.error("Failed to add event:", errors);
            },
            preserveScroll: true,
        });
    }
};

const formatDateTimeForInput = (dateTimeString) => {
    console.log("Formatting date for input:", dateTimeString);
    if (!dateTimeString) return "";
    try {
        const date = new Date(dateTimeString);
        // Check if the date is valid
        if (isNaN(date.getTime())) {
            console.warn("Could not parse date for input:", dateTimeString);
            return "";
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0"); // Months are 0-indexed
        const day = String(date.getDate()).padStart(2, "0");
        const hours = String(date.getHours()).padStart(2, "0");
        const minutes = String(date.getMinutes()).padStart(2, "0");
        const formatted = `${year}-${month}-${day}T${hours}:${minutes}`;
        console.log("Formatted date:", formatted); // <-- Add log
        return formatted;
    } catch (e) {
        console.error("Error formatting date for input:", dateTimeString, e);
        return "";
    }
};

const editEvent = (event) => {
    if (!props.isAdminOrManager) return;
    console.log("Event data received in editEvent:", event); // Debug log
    if (!event || !event.id) {
        console.error("Invalid event object received in editEvent:", event);
        return;
    }

    // Populate the event form with the selected event's data
    eventForm.value = {
        id: event.id,
        title: event.title,
        event_date: formatDateTimeForInput(`${event.date}T${event.time}`), // Combine date and time
        type: event.type,
        description: event.description,
    };
    console.log("Populated eventForm:", eventForm.value); // Debug log
    showEditEventModal.value = true; // Open the modal in edit mode
};

const deleteEvent = (eventId) => {
    if (!props.isAdminOrManager) return;

    const numericId = String(eventId).split("_").pop();

    if (!numericId || isNaN(parseInt(numericId))) {
        console.error("Invalid event ID format received:", eventId);

        return;
    }

    console.log("Attempting to delete event with numeric ID:", numericId);
    const deleteUrl = `/events/${numericId}`;
    router.delete(deleteUrl, {
        onSuccess: () => {
            console.log("Event deleted successfully.");
            router.reload({ only: ["upcomingEvents"] });
        },
        onError: (errors) => {
            console.error("Failed to delete event:", errors);
        },
        preserveScroll: true,
    });
};
</script>
