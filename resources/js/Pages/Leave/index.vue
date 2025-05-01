-- Active: 1744537085826@@127.0.0.1@3306@db_kpi
<template>
    <AdminLayout title="Leave Management" description="Manage leave requests">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Leave Request Form -->
            <div class="lg:col-span-1">
                <DashboardCard title="Request Leave">
                    <form @submit.prevent="submitLeaveRequest">
                        <div class="space-y-4">
                            <!-- Employee Selection (Admin Only) -->
                            <div v-if="isAdmin">
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
                                        {{ employee.name }} ({{
                                            employee.employee_id
                                        }}) - {{ employee.department }}
                                    </option>
                                </select>
                                <div
                                    v-if="errors.employee_id"
                                    class="text-red-500 text-sm mt-1"
                                >
                                    {{ errors.employee_id }}
                                </div>
                            </div>

                            <!-- Leave Type -->
                            <div>
                                <label
                                    for="leaveType"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Leave Type
                                </label>
                                <select
                                    id="leaveType"
                                    v-model="form.leave_type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="" disabled>
                                        Select leave type
                                    </option>
                                    <option
                                        v-for="type in leaveTypes"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }} ({{ type.days_allowed }}
                                        days allowed)
                                    </option>
                                </select>
                                <div
                                    v-if="errors.leave_type_id"
                                    class="text-red-500 text-sm mt-1"
                                >
                                    {{ errors.leave_type_id }}
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label
                                    for="startDate"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Start Date
                                </label>
                                <input
                                    type="date"
                                    id="startDate"
                                    v-model="form.start_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :min="minDate"
                                />
                                <div
                                    v-if="errors.start_date"
                                    class="text-red-500 text-sm mt-1"
                                >
                                    {{ errors.start_date }}
                                </div>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label
                                    for="endDate"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    End Date
                                </label>
                                <input
                                    type="date"
                                    id="endDate"
                                    v-model="form.end_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    :min="form.start_date || minDate"
                                />
                                <div
                                    v-if="errors.end_date"
                                    class="text-red-500 text-sm mt-1"
                                >
                                    {{ errors.end_date }}
                                </div>
                            </div>

                            <!-- Reason -->
                            <div>
                                <label
                                    for="reason"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Reason
                                </label>
                                <textarea
                                    id="reason"
                                    v-model="form.reason"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Please provide a reason for the leave request"
                                ></textarea>
                                <div
                                    v-if="errors.reason"
                                    class="text-red-500 text-sm mt-1"
                                >
                                    {{ errors.reason }}
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div>
                                <button
                                    type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-grey shadow-sm text-sm font-medium rounded-md text-black bg-primary-600 hover:bg-primary-700"
                                    :disabled="processing"
                                >
                                    <span v-if="processing">Processing...</span>
                                    <span v-else>Submit Request</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </DashboardCard>

                <!-- Filters (Admin Only) -->
                <DashboardCard v-if="isAdmin" title="Filters" class="mt-6">
                    <form @submit.prevent="applyFilters">
                        <div class="space-y-4">
                            <!-- Search -->
                            <div>
                                <label
                                    for="search"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Search
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input
                                        type="text"
                                        id="search"
                                        v-model="filters.search"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Search by name or employee ID"
                                    />
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Status
                                </label>
                                <select
                                    id="status"
                                    v-model="filters.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>

                            <!-- Department -->
                            <div>
                                <label
                                    for="department"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Department
                                </label>
                                <select
                                    id="department"
                                    v-model="filters.department_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
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

                            <!-- Leave Type -->
                            <div>
                                <label
                                    for="leaveTypeFilter"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Leave Type
                                </label>
                                <select
                                    id="leaveTypeFilter"
                                    v-model="filters.leave_type_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">All Types</option>
                                    <option
                                        v-for="type in leaveTypes"
                                        :key="type.id"
                                        :value="type.id"
                                    >
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="flex space-x-2">
                                <button
                                    type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-black bg-primary-600 hover:bg-primary-700"
                                >
                                    Apply Filters
                                </button>
                                <button
                                    type="button"
                                    @click="resetFilters"
                                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </DashboardCard>
            </div>

            <!-- Leave Requests Table -->
            <div class="lg:col-span-2">
                <DashboardCard
                    :title="
                        isAdmin ? 'All Leave Requests' : 'My Leave Requests'
                    "
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full table-auto divide-y divide-gray-200"
                        >
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        v-if="isAdmin"
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Employee
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Type
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Dates
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Days
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                    <th
                                        v-if="isAdmin"
                                        scope="col"
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="leaveRequests.data.length === 0">
                                    <td
                                        :colspan="isAdmin ? 6 : 5"
                                        class="px-4 py-2 text-center text-sm text-gray-500"
                                    >
                                        No leave requests found
                                    </td>
                                </tr>
                                <tr
                                    v-for="leave in leaveRequests.data"
                                    :key="leave.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        v-if="isAdmin"
                                        class="px-4 py-2 whitespace-normal break-words text-sm text-gray-900"
                                    >
                                        <div>{{ leave.employee.name }}</div>
                                        <div class="text-gray-500">
                                            {{ leave.employee.department }}
                                        </div>
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-normal break-words text-sm text-gray-900"
                                    >
                                        {{ leave.type }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-normal break-words text-sm text-gray-500"
                                    >
                                        {{ formatDate(leave.start_date) }} -
                                        {{ formatDate(leave.end_date) }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-normal break-words text-sm text-gray-500"
                                    >
                                        {{ leave.total_days }}
                                    </td>
                                    <td
                                        class="px-4 py-2 whitespace-normal break-words"
                                    >
                                        <span
                                            :class="
                                                getStatusClass(leave.status)
                                            "
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ leave.status }}
                                        </span>
                                        <!-- <p
                                            v-if="leave.rejection_reason"
                                            class="text-xs text-red-500 mt-1"
                                        >
                                            {{ leave.rejection_reason }}
                                        </p> -->
                                    </td>
                                    <td
                                        v-if="isAdmin"
                                        class="px-4 py-2 whitespace-normal break-words text-sm text-gray-500"
                                    >
                                        <div
                                            class="flex items-center space-x-4"
                                        >
                                            <button
                                                @click="viewDetails(leave)"
                                                class="text-primary-600 hover:text-primary-900"
                                            >
                                                Details
                                            </button>
                                            <!-- Edit Menu -->
                                            <div class="relative">
                                                <button
                                                    :ref="
                                                        (el) => {
                                                            if (el)
                                                                editButtonRefs[
                                                                    leave.id
                                                                ] = el;
                                                        }
                                                    "
                                                    @click="
                                                        toggleEditMenu(leave.id)
                                                    "
                                                    class="text-gray-500 hover:text-gray-700"
                                                >
                                                    <PencilIcon
                                                        class="h-5 w-5"
                                                    />
                                                </button>
                                                <div
                                                    v-if="
                                                        editMenuVisible ===
                                                        leave.id
                                                    "
                                                    :ref="
                                                        (el) => {
                                                            if (el)
                                                                editMenuRefs[
                                                                    leave.id
                                                                ] = el;
                                                        }
                                                    "
                                                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
                                                >
                                                    <button
                                                        @click="
                                                            editLeave(leave)
                                                        "
                                                        class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="
                                                            deleteLeave(
                                                                leave.id
                                                            )
                                                        "
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </div>
                                            <!-- Actions Menu -->
                                            <div class="relative">
                                                <button
                                                    :ref="
                                                        (el) => {
                                                            if (el)
                                                                actionButtonRefs[
                                                                    leave.id
                                                                ] = el;
                                                        }
                                                    "
                                                    @click="
                                                        toggleActionsMenu(
                                                            leave.id
                                                        )
                                                    "
                                                    class="text-gray-500 hover:text-gray-700"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M6 12h.01M12 12h.01M18 12h.01"
                                                        />
                                                    </svg>
                                                </button>
                                                <div
                                                    v-if="
                                                        actionsMenuVisible ===
                                                        leave.id
                                                    "
                                                    :ref="
                                                        (el) => {
                                                            if (el)
                                                                actionMenuRefs[
                                                                    leave.id
                                                                ] = el;
                                                        }
                                                    "
                                                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-10"
                                                >
                                                    <button
                                                        @click="
                                                            approveLeave(
                                                                leave.id
                                                            )
                                                        "
                                                        class="block w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-100"
                                                    >
                                                        Approve
                                                    </button>
                                                    <button
                                                        @click="
                                                            openRejectModal(
                                                                leave.id
                                                            )
                                                        "
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                                    >
                                                        Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <Pagination :links="leaveRequests.links" />
                    </div>
                </DashboardCard>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Reject Leave Request
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Please provide a reason for rejecting this leave request.
                </p>
                <div class="mt-4">
                    <textarea
                        v-model="rejectForm.rejection_reason"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Rejection reason"
                    ></textarea>
                    <div
                        v-if="rejectForm.errors.rejection_reason"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ rejectForm.errors.rejection_reason }}
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="showRejectModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        @click="rejectLeave"
                        :disabled="rejectForm.processing"
                    >
                        Reject
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Details Modal -->
        <Modal :show="showDetailsModal" @close="showDetailsModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Leave Request Details
                </h2>
                <div v-if="selectedLeave" class="mt-4 space-y-4">
                    <div v-if="isAdmin" class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Employee
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ selectedLeave.employee.name }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Department
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ selectedLeave.employee.department }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Leave Type
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ selectedLeave.type }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Status
                            </p>
                            <p class="mt-1 text-sm">
                                <span
                                    :class="
                                        getStatusClass(selectedLeave.status)
                                    "
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                >
                                    {{ selectedLeave.status }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                Start Date
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(selectedLeave.start_date) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                End Date
                            </p>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ formatDate(selectedLeave.end_date) }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Total Days
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ selectedLeave.total_days }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Reason</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ selectedLeave.reason }}
                        </p>
                    </div>
                    <div v-if="selectedLeave.rejection_reason">
                        <p class="text-sm font-medium text-gray-500">
                            Rejection Reason
                        </p>
                        <p class="mt-1 text-sm text-red-600">
                            {{ selectedLeave.rejection_reason }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">
                            Submitted On
                        </p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ formatDateTime(selectedLeave.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="showDetailsModal = false"
                    >
                        Close
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="closeEditModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Edit Leave Request
                </h2>
                <form @submit.prevent="submitEdit" class="mt-4">
                    <div class="space-y-4">
                        <!-- Employee (Readonly) -->
                        <div v-if="isAdmin && editForm.employee_name">
                             <label class="block text-sm font-medium text-gray-700">Employee</label>
                             <input type="text" :value="editForm.employee_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" readonly />
                        </div>

                        <!-- Leave Type -->
                        <div>
                            <label for="editLeaveType" class="block text-sm font-medium text-gray-700">Leave Type</label>
                            <select id="editLeaveType" v-model="editForm.leave_type_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="" disabled>Select leave type</option>
                                <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                                    {{ type.name }} ({{ type.days_allowed }} days allowed)
                                </option>
                            </select>
                            <div v-if="editForm.errors.leave_type_id" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.leave_type_id }}
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="editStartDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="editStartDate" v-model="editForm.start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" :min="minDate" />
                            <div v-if="editForm.errors.start_date" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.start_date }}
                            </div>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="editEndDate" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="editEndDate" v-model="editForm.end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" :min="editForm.start_date || minDate" />
                            <div v-if="editForm.errors.end_date" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.end_date }}
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label for="editReason" class="block text-sm font-medium text-gray-700">Reason</label>
                            <textarea id="editReason" v-model="editForm.reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                            <div v-if="editForm.errors.reason" class="text-red-500 text-sm mt-1">
                                {{ editForm.errors.reason }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none" @click="closeEditModal">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none" :disabled="editForm.processing">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue"; // Import onMounted, onUnmounted, nextTick
import { useForm, usePage, router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import DashboardCard from "@/Components/Dashboard/DashboardCard.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import { PencilIcon } from "@heroicons/vue/outline";

const props = defineProps({
    leaveTypes: {
        type: Array,
        required: true,
    },
    leaveRequests: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        default: () => [],
    },
    employees: {
        type: Array,
        default: () => [],
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
    filters: {
        type: Object,
        default: () => ({
            status: "",
            department_id: "",
            leave_type_id: "",
            search: "",
        }),
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const flash = computed(() => page.props.flash);

// Form handling
const form = useForm({
    employee_id: "",
    leave_type_id: "",
    start_date: "",
    end_date: "",
    reason: "",
});

const processing = ref(false);

// Minimum date for leave requests (today)
const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split("T")[0];
});

// Submit leave request
const submitLeaveRequest = () => {
    processing.value = true;
    form.post(route("leave.store"), {
        onSuccess: () => {
            form.reset();
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

// Format date for display
const formatDate = (dateString) => {
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

// Format date and time for display
const formatDateTime = (dateTimeString) => {
    const options = {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    };
    return new Date(dateTimeString).toLocaleDateString(undefined, options);
};

// Get status badge class
const getStatusClass = (status) => {
    switch (status.toLowerCase()) {
        case "approved":
            return "bg-green-100 text-green-800";
        case "rejected":
            return "bg-red-100 text-red-800";
        case "pending":
            return "bg-yellow-100 text-yellow-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

// Filters
const filters = ref({ ...props.filters });

// Apply filters
const applyFilters = () => {
    router.get(
        route("leave.index"),
        {
            status: filters.value.status,
            department_id: filters.value.department_id,
            leave_type_id: filters.value.leave_type_id,
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
        status: "",
        department_id: "",
        leave_type_id: "",
        search: "",
    };
    applyFilters();
};

// Approve leave
const approveLeave = (leaveId) => {
    // Reverted to const
    closeMenus(); // Added closeMenus call here
    router.patch(route("leave.update-status", leaveId), {
        status: "approved",
    });
};

// Reject leave
const showRejectModal = ref(false);
const selectedLeaveId = ref(null);
const rejectForm = useForm({
    status: "rejected",
    rejection_reason: "",
});

const openRejectModal = (leaveId) => {
    // Reverted to const
    closeMenus(); // Added closeMenus call here
    selectedLeaveId.value = leaveId;
    showRejectModal.value = true;
    rejectForm.reset();
};

const rejectLeave = () => {
    rejectForm.patch(route("leave.update-status", selectedLeaveId.value), {
        onSuccess: () => {
            showRejectModal.value = false;
        },
    });
};

// View details
const showDetailsModal = ref(false);
const selectedLeave = ref(null);

const viewDetails = (leave) => {
    // Reverted to const
    closeMenus(); // Added closeMenus call here
    editMenuVisible.value = false; // This might be redundant now but kept for clarity
    actionsMenuVisible.value = false; // This might be redundant now but kept for clarity
    selectedLeave.value = leave;
    showDetailsModal.value = true;
};

// --- Edit Modal Logic ---
const showEditModal = ref(false);
const editingLeaveId = ref(null);
const editForm = useForm({
    leave_type_id: '',
    start_date: '',
    end_date: '',
    reason: '',
    employee_name: '', // Only for display in modal if admin
    _method: 'PUT' // To spoof PUT request
});

const editLeave = (leave) => {
    closeMenus(); // Close dropdown menu
    editingLeaveId.value = leave.id;
    // Find the original full leave object from props to get leave_type_id
    const fullLeaveData = props.leaveRequests.data.find(l => l.id === leave.id);
    if (fullLeaveData) {
        editForm.leave_type_id = fullLeaveData.leave_type_id; // Need original ID
        editForm.start_date = fullLeaveData.start_date.split('T')[0]; // Format date for input
        editForm.end_date = fullLeaveData.end_date.split('T')[0]; // Format date for input
        editForm.reason = fullLeaveData.reason;
        if (props.isAdmin) {
             editForm.employee_name = fullLeaveData.employee.name; // For display only
        }
        editForm.errors = {}; // Clear previous errors
        showEditModal.value = true;
    } else {
        console.error("Could not find leave data for editing:", leave.id);
        // Optionally show an error message to the user
    }
};

const closeEditModal = () => {
    showEditModal.value = false;
    editForm.reset();
    editingLeaveId.value = null;
};

const submitEdit = () => {
    if (!editingLeaveId.value) return;
    editForm.put(route('leave.update', editingLeaveId.value), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
            // Flash message handled by controller redirect
        },
        onError: (errors) => {
            console.error("Error updating leave request:", errors);
            // Errors are automatically bound to editForm.errors
        }
    });
};
// --- End Edit Modal Logic ---


// --- Dropdown Menu Logic ---
const actionsMenuVisible = ref(null);
const editMenuVisible = ref(null);

// Refs for menu elements and buttons
const actionMenuRefs = ref({});
const actionButtonRefs = ref({});
const editMenuRefs = ref({});
const editButtonRefs = ref({});

const closeMenus = () => {
    actionsMenuVisible.value = null;
    editMenuVisible.value = null;
    document.removeEventListener("click", handleClickOutside);
};

const handleClickOutside = (event) => {
    const clickedElement = event.target;

    // Check Actions Menu
    if (actionsMenuVisible.value !== null) {
        const menu = actionMenuRefs.value[actionsMenuVisible.value];
        const button = actionButtonRefs.value[actionsMenuVisible.value];
        if (
            menu &&
            !menu.contains(clickedElement) &&
            button &&
            !button.contains(clickedElement)
        ) {
            closeMenus();
            return; // Exit early if closed
        }
    }

    // Check Edit Menu
    if (editMenuVisible.value !== null) {
        const menu = editMenuRefs.value[editMenuVisible.value];
        const button = editButtonRefs.value[editMenuVisible.value];
        if (
            menu &&
            !menu.contains(clickedElement) &&
            button &&
            !button.contains(clickedElement)
        ) {
            closeMenus();
        }
    }
};

const addClickListener = () => {
    nextTick(() => {
        // Ensure refs are populated
        document.addEventListener("click", handleClickOutside);
    });
};

const removeClickListener = () => {
    document.removeEventListener("click", handleClickOutside);
};

const toggleActionsMenu = (leaveId) => {
    removeClickListener(); // Remove listener before potentially adding a new one
    if (actionsMenuVisible.value === leaveId) {
        actionsMenuVisible.value = null; // Close if already open
    } else {
        editMenuVisible.value = null; // Close other menu
        actionsMenuVisible.value = leaveId; // Open this menu
        addClickListener(); // Add listener only when opening
    }
};

const toggleEditMenu = (leaveId) => {
    removeClickListener(); // Remove listener before potentially adding a new one
    if (editMenuVisible.value === leaveId) {
        editMenuVisible.value = null; // Close if already open
    } else {
        actionsMenuVisible.value = null; // Close other menu
        editMenuVisible.value = leaveId; // Open this menu
        addClickListener(); // Add listener only when opening
    }
};

// Cleanup listener on component unmount
onUnmounted(() => {
    removeClickListener();
});
// --- End Dropdown Menu Logic ---

const deleteLeave = (leaveId) => {
    closeMenus(); // Close menu before confirmation

        router.delete(route("leave.destroy", leaveId), {
            preserveScroll: true, // Keep scroll position
            onSuccess: () => {
                // Flash message is handled by controller redirect
            },
            onError: (errors) => {
                 console.error("Error deleting leave request:", errors);
                 // Optionally show a generic error flash message
                 // page.props.flash.error = 'Failed to delete leave request.';
            }
        });

};
</script>
