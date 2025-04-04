<template>
    <AuthenticatedLayout>
        <div class="leave-page">
            <!-- <h1 class="text-2xl font-bold mb-4">Leave Management</h1> -->
            <div class="leave-form mb-6">
                <!-- <h2 class="text-xl font-semibold mb-2">Request Leave</h2> -->
                <form @submit.prevent="submitLeaveRequest">
                    <div class="mb-4">
                        <label for="leaveType" class="block text-sm font-medium"
                            >Leave Type</label
                        >
                        <select
                            id="leaveType"
                            v-model="leaveRequest.type"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select leave type</option>
                            <option value="sick">Sick Leave</option>
                            <option value="vacation">Vacation</option>
                            <option value="personal">Personal Leave</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="startDate" class="block text-sm font-medium"
                            >Start Date</label
                        >
                        <input
                            type="date"
                            id="startDate"
                            v-model="leaveRequest.startDate"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div class="mb-4">
                        <label for="endDate" class="block text-sm font-medium"
                            >End Date</label
                        >
                        <input
                            type="date"
                            id="endDate"
                            v-model="leaveRequest.endDate"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium"
                            >Reason</label
                        >
                        <textarea
                            id="reason"
                            v-model="leaveRequest.reason"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Submit Request
                    </button>
                </form>
            </div>
            <div class="leave-list">
                <h2 class="text-xl font-semibold mb-2">Leave History</h2>
                <table
                    class="min-w-full border-collapse border border-gray-300"
                >
                    <thead>
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">
                                Type
                            </th>
                            <th class="border border-gray-300 px-4 py-2">
                                Start Date
                            </th>
                            <th class="border border-gray-300 px-4 py-2">
                                End Date
                            </th>
                            <th class="border border-gray-300 px-4 py-2">
                                Reason
                            </th>
                            <th class="border border-gray-300 px-4 py-2">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="leave in leaveHistory" :key="leave.id">
                            <td class="border border-gray-300 px-4 py-2">
                                {{ leave.type }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ leave.startDate }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ leave.endDate }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ leave.reason }}
                            </td>
                            <td class="border border-gray-300 px-4 py-2">
                                {{ leave.status }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
const leaveRequest = reactive({
    type: "",
    startDate: "",
    endDate: "",
    reason: "",
});

const leaveHistory = reactive([
    {
        id: 1,
        type: "Sick Leave",
        startDate: "2023-10-01",
        endDate: "2023-10-03",
        reason: "Flu",
        status: "Approved",
    },
    {
        id: 2,
        type: "Vacation",
        startDate: "2023-09-15",
        endDate: "2023-09-20",
        reason: "Family trip",
        status: "Pending",
    },
]);

function submitLeaveRequest() {
    if (
        leaveRequest.type &&
        leaveRequest.startDate &&
        leaveRequest.endDate &&
        leaveRequest.reason
    ) {
        const newLeave = {
            ...leaveRequest,
            id: Date.now(),
            status: "Pending",
        };
        leaveHistory.push(newLeave);
        leaveRequest.type = "";
        leaveRequest.startDate = "";
        leaveRequest.endDate = "";
        leaveRequest.reason = "";
        alert("Leave request submitted successfully!");
    } else {
        alert("Please fill out all fields.");
    }
}
</script>

<style scoped>
.leave-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
</style>
