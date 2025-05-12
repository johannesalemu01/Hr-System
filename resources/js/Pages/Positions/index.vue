<template>
    <AuthenticatedLayout>
        <div
            class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4"
        >
            <div>
                <label
                    for="department"
                    class="block text-sm font-medium text-gray-700"
                    >Filter by Department</label
                >
                <select
                    id="department"
                    v-model="filters.department_id"
                    @change="applyFilters"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                >
                    <option value="">All Departments</option>
                    <option
                        v-for="dept in departments"
                        :key="dept.id"
                        :value="dept.id"
                    >
                        {{ dept.name }}
                    </option>
                </select>
            </div>
            <Link
                :href="route('positions.create')"
                class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-md shadow-sm text-black bg-primary-600 hover:bg-primary-700"
            >
                <svg
                    class="h-5 w-5 mr-2 text-black"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Add Position
            </Link>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="position in positions.data"
                :key="position.id"
                class="bg-white shadow-md rounded-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 ease-in-out flex flex-col"
            >
                <div
                    class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
                >
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 truncate">
                            {{ position.title }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ position.department.name || "N/A" }}
                        </p>
                        <!-- HR Manager info for Human Resources department -->
                        <template
                            v-if="position.department === 'Human Resources'"
                        >
                            <div
                                class="mt-2 flex items-center text-xs text-primary-700 font-semibold"
                            >
                                <svg
                                    class="h-4 w-4 mr-1 text-primary-700"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                HR Manager: <span class="ml-1">ID 4</span>
                            </div>
                        </template>
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            :href="route('positions.edit', position.id)"
                            class="text-blue-600 hover:text-blue-900"
                            title="Edit"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m2 2l-6 6"
                                />
                            </svg>
                        </Link>
                        <button
                            @click="confirmDelete(position)"
                            class="text-red-600 hover:text-red-900"
                            title="Delete"
                        >
                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 flex-1 flex flex-col">
                    <div class="flex justify-between mb-2">
                        <span class="text-xs text-gray-500">Min Salary</span>
                        <span class="text-xs text-gray-500">Max Salary</span>
                    </div>
                    <div class="flex justify-between mb-4">
                        <span class="font-semibold text-gray-700">{{
                            position.min_salary ?? "-"
                        }}</span>
                        <span class="font-semibold text-gray-700">{{
                            position.max_salary ?? "-"
                        }}</span>
                    </div>
                    <div class="text-sm text-gray-600 flex-1 line-clamp-2">
                        {{ position.description || "No description provided." }}
                    </div>
                </div>
            </div>
            <div
                v-if="positions.data.length === 0"
                class="col-span-full flex flex-col items-center justify-center py-12 bg-white rounded-lg shadow"
            >
                <svg
                    class="h-16 w-16 text-gray-300"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m-6 0h6"
                    />
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">
                    No positions found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Try adjusting your filters or add a new position.
                </p>
            </div>
        </div>
        <div class="mt-6">
            <Pagination :links="positions.links" />
        </div>
        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Delete Position
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete the position '{{
                        positionToDelete?.title
                    }}'? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700"
                        @click="deletePosition"
                        :disabled="deleting"
                    >
                        {{ deleting ? "Deleting..." : "Delete" }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    positions: Object,
    departments: Array,
    filters: Object,
});

const filters = ref({
    department_id: props.filters.department_id ?? "",
});

const applyFilters = () => {
    router.get(
        route("positions.index"),
        {
            department_id: filters.value.department_id,
        },
        { preserveState: true, replace: true }
    );
};

const showDeleteModal = ref(false);
const positionToDelete = ref(null);
const deleting = ref(false);

const confirmDelete = (position) => {
    positionToDelete.value = position;
    showDeleteModal.value = true;
};

const deletePosition = () => {
    if (!positionToDelete.value) return;
    deleting.value = true;
    router.delete(route("positions.destroy", positionToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            positionToDelete.value = null;
        },
        onError: () => {},
        onFinish: () => {
            deleting.value = false;
        },
    });
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
