<template>
    <AuthenticatedLayout
        title="KPI Management"
        description="Manage and track Key Performance Indicators"
    >
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <StatCard
                title="Total KPIs"
                :value="stats.total"
                icon="chart-bar"
                color="blue"
            />
            <StatCard
                title="Active KPIs"
                :value="stats.active"
                icon="check-circle"
                color="green"
            />
            <StatCard
                title="Inactive KPIs"
                :value="stats.inactive"
                icon="x-circle"
                color="red"
            />
            <StatCard
                title="Departments"
                :value="stats.departments.length"
                icon="users"
                color="purple"
            />
        </div>

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
                            placeholder="Search KPIs"
                            type="search"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                </div>

                <div class="w-full sm:w-auto">
                    <select
                        v-model="filters.department_id"
                        class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-primary-500 sm:text-sm"
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
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <Link
                    href="/kpis/dashboard"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <ChartPieIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Dashboard
                </Link>
                <Link
                    href="/kpis/employee-kpis"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <UserIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Employee KPIs
                </Link>
                <Link
                    href="/kpis/create"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <PlusIcon class="h-5 w-5 mr-2 text-gray-500" />
                    Create KPI
                </Link>
            </div>
        </div>

        <!-- KPI Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul role="list" class="divide-y divide-gray-200">
                <li
                    v-if="kpis?.data.length === 0"
                    class="px-6 py-4 text-center text-gray-500"
                >
                    No KPIs found matching your criteria.
                </li>
                <li
                    v-for="kpi in kpis?.data"
                    :key="kpi.id"
                    class="block hover:bg-gray-50"
                >
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full"
                                    :class="
                                        kpi.is_active
                                            ? 'bg-green-100'
                                            : 'bg-gray-100'
                                    "
                                >
                                    <ChartBarIcon
                                        class="h-6 w-6"
                                        :class="
                                            kpi.is_active
                                                ? 'text-green-600'
                                                : 'text-gray-400'
                                        "
                                    />
                                </div>
                                <div class="ml-4">
                                    <div
                                        class="text-sm font-medium text-primary-600"
                                    >
                                        <Link :href="`/kpis/${kpi.id}`">{{
                                            kpi.name
                                        }}</Link>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ kpi.measurement_unit }} |
                                        {{ kpi.frequency }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="mr-6">
                                    <div class="text-sm text-gray-900">
                                        {{ kpi.department }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ kpi.position || "All Positions" }}
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
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
                                    <div class="flex items-center">
                                        <Link
                                            :href="
                                                route('kpis.edit', {
                                                    kpi: kpi.id,
                                                })
                                            "
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click="confirmDelete(kpi)"
                                            class="ml-2 text-gray-400 hover:text-red-500"
                                            v-if="canDelete"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 line-clamp-2">
                                {{ kpi.description }}
                            </p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <Pagination :links="kpis?.links" :meta="kpis?.meta" />
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete KPI</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this KPI? This action cannot
                    be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        @click="deleteKpi"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import {
    SearchIcon,
    PlusIcon,
    ChartBarIcon,
    PencilIcon,
    TrashIcon,
    UserIcon,
    ChartPieIcon,
    CheckCircleIcon,
    XCircleIcon,
    UsersIcon,
} from "@heroicons/vue/outline";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import StatCard from "@/Components/Dashboard/StatCard.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    kpis: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            departments: [],
        }),
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
const canDelete =
    page.props.auth?.permissions?.includes("delete kpis") || false;

// Filters
const filters = ref({
    department_id: "",
    search: "",
    status: "",
});

// Apply filters
const applyFilters = () => {
    router.get(
        "/kpis", // Replace route('kpis.index') with the hardcoded URL
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

// Delete KPI
const showDeleteModal = ref(false);
const kpiToDelete = ref(null);

const confirmDelete = (kpi) => {
    kpiToDelete.value = kpi;
    showDeleteModal.value = true;
};

const deleteKpi = () => {
    fetch(`/kpis/${kpiToDelete.value.id}`, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
    })
        .then((response) => {
            if (response.ok) {
                router.get(route("kpis.index")); // Refresh the page after deletion
            } else {
                alert("Failed to delete KPI.");
            }
        })
        .finally(() => {
            showDeleteModal.value = false;
            kpiToDelete.value = null;
        });
};
</script>
