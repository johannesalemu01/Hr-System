<template>
    <AuthenticatedLayout
        title="Employee Management"
        description="Manage your organization's employees"
    >
        <!-- Action buttons -->
        <div class="mb-6 flex justify-between items-center">
            <div class="flex-1 flex items-center space-x-4">
                <div class="w-64">
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
                            v-model="search"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                            placeholder="Search employees"
                            type="search"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>

                <div>
                    <Listbox
                        v-model="departmentFilter"
                        @update:modelValue="filterByDepartment"
                    >
                        <div class="relative">
                            <ListboxButton
                                class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default sm:text-sm"
                            >
                                <span class="block truncate">{{
                                    departmentFilter.name
                                }}</span>
                                <span
                                    class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
                                >
                                    <SelectorIcon
                                        class="h-5 w-5 text-gray-400"
                                        aria-hidden="true"
                                    />
                                </span>
                            </ListboxButton>

                            <transition
                                leave-active-class="transition ease-in duration-100"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0"
                            >
                                <ListboxOptions
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto sm:text-sm"
                                >
                                    <ListboxOption
                                        v-for="department in departments"
                                        :key="department.id"
                                        v-slot="{ active, selected }"
                                        :value="department"
                                    >
                                        <div
                                            :class="[
                                                active
                                                    ? 'text-white bg-primary-600'
                                                    : 'text-gray-900',
                                                'cursor-default select-none relative py-2 pl-3 pr-9',
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    selected
                                                        ? 'font-semibold'
                                                        : 'font-normal',
                                                    'block truncate',
                                                ]"
                                                >{{ department.name }}</span
                                            >
                                            <span
                                                v-if="selected"
                                                :class="[
                                                    active
                                                        ? 'text-white'
                                                        : 'text-primary-600',
                                                    'absolute inset-y-0 right-0 flex items-center pr-4',
                                                ]"
                                            >
                                                <CheckIcon
                                                    class="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        </div>
                                    </ListboxOption>
                                </ListboxOptions>
                            </transition>
                        </div>
                    </Listbox>
                </div>

                <div>
                    <Listbox
                        v-model="employmentStatusFilter"
                        @update:modelValue="applyFilters"
                    >
                        <div class="relative">
                            <ListboxButton
                                class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                            >
                                <span class="block truncate">{{
                                    employmentStatusFilter.name
                                }}</span>
                                <span
                                    class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
                                >
                                    <SelectorIcon
                                        class="h-5 w-5 text-gray-400"
                                        aria-hidden="true"
                                    />
                                </span>
                            </ListboxButton>

                            <transition
                                leave-active-class="transition ease-in duration-100"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0"
                            >
                                <ListboxOptions
                                    class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                >
                                    <ListboxOption
                                        v-for="status in employmentStatuses"
                                        :key="status.id"
                                        v-slot="{ active, selected }"
                                        :value="status"
                                    >
                                        <div
                                            :class="[
                                                active
                                                    ? 'text-white bg-primary-600'
                                                    : 'text-gray-900',
                                                'cursor-default select-none relative py-2 pl-3 pr-9',
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    selected
                                                        ? 'font-semibold'
                                                        : 'font-normal',
                                                    'block truncate',
                                                ]"
                                            >
                                                {{ status.name }}
                                            </span>
                                            <span
                                                v-if="selected"
                                                :class="[
                                                    active
                                                        ? 'text-white'
                                                        : 'text-primary-600',
                                                    'absolute inset-y-0 right-0 flex items-center pr-4',
                                                ]"
                                            >
                                                <CheckIcon
                                                    class="h-5 w-5"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        </div>
                                    </ListboxOption>
                                </ListboxOptions>
                            </transition>
                        </div>
                    </Listbox>
                </div>
            </div>

            <Link
                v-if="
                    $page.props.auth.user.permissions.includes(
                        'create employees'
                    )
                "
                :href="route('employees.create')"
                class="inline-flex items-center px-4 py-2 border border-gray-400 text-sm font-medium rounded-md shadow-md text-black "
            >
                <button> Add Employee </button>
            </Link>
        </div>

        <!-- Employee table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul
                v-if="employees.data.length > 0"
                role="list"
                class="divide-y divide-gray-200"
            >
                <li v-for="employee in employees.data" :key="employee.id">
                    <div class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <img
                                            :src="
                                                employee.profile_picture ||
                                                'https://via.placeholder.com/150'
                                            "
                                            alt="Profile Picture"
                                            class="h-10 w-10 rounded-full object-cover"
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-primary-600"
                                        >
                                            {{ employee.full_name }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 text-left"
                                        >
                                            {{ employee.position }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="mr-6 flex flex-col items-start">
                                        <div class="text-sm text-gray-900">
                                            {{ employee.department }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ employee.employee_id }}
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <MailIcon
                                            class="h-5 w-5 text-gray-400 mr-1"
                                        />
                                        <div class="text-sm text-gray-500">
                                            {{ employee.email }}
                                        </div>
                                    </div>
                                </div>
                                <!-- Fix the link to point to the correct route -->
                                <div class="ml-4 flex items-center space-x-4">
                                    <Link
                                        :href="
                                            route('employees.show', employee.id)
                                        "
                                        class="text-primary-600 hover:text-primary-900 text-sm"
                                    >
                                        View Profile
                                    </Link>
                                    <!-- Edit Button (Optional - Add if needed) -->
                                    <Link
                                        v-if="
                                            $page.props.auth.user.permissions.includes(
                                                'edit employees'
                                            )
                                        "
                                        :href="
                                            route('employees.edit', employee.id)
                                        "
                                           class="text-gray-500 hover:text-gray-700"
                            >
                                <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <!-- Delete Button -->
                                    <button
                                        v-if="
                                            $page.props.auth.user.permissions.includes(
                                                'delete employees'
                                            )
                                        "
                                        @click.stop="confirmDelete(employee)"
                                        type="button"
                                        class="text-gray-400 hover:text-red-500"
                                        
                                        title="Delete Employee"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <div v-else class="py-10 text-center">
                <p class="text-gray-500">
                    No employees found matching your criteria.
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="employees.data.length > 0" class="mt-6">
            <Pagination :links="employees.links" />
        </div>

        <!-- Inline Delete Confirmation Modal -->
        <div
            v-if="confirmingDeletion"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="closeModal"
                ></div>

                <!-- Modal panel -->
                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                            >
                                <!-- Heroicon name: outline/exclamation -->
                                <svg
                                    class="h-6 w-6 text-red-600"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    />
                                </svg>
                            </div>
                            <div
                                class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left"
                            >
                                <h3
                                    class="text-lg leading-6 font-medium text-gray-900"
                                    id="modal-title"
                                >
                                    Delete Employee
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete employee
                                        "{{ employeeToDelete?.full_name }}"?
                                        This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                    >
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            :class="{
                                'opacity-50 cursor-not-allowed':
                                    processingDelete,
                            }"
                            :disabled="processingDelete"
                            @click="deleteEmployee"
                        >
                            {{ processingDelete ? "Deleting..." : "Delete" }}
                        </button>
                        <button
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="closeModal"
                            :disabled="processingDelete"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Inline Modal -->
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from "@headlessui/vue";
import {
    SearchIcon,
    UserAddIcon,
    MailIcon,
    SelectorIcon,
    CheckIcon,
    TrashIcon,
    PencilIcon,
} from "@heroicons/vue/outline";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import debounce from "lodash/debounce";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    employees: {
        type: Object,
        default: () => ({ data: [], links: [] }), 
    },
    departments: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            search: "",
            department: null,
            employment_status: "",
        }),
    },
});

const employmentStatuses = [
    { id: "", name: "All Statuses" },
    { id: "full_time", name: "Full-Time" },
    { id: "part_time", name: "Part-Time" },
    { id: "contract", name: "Contract" },
    { id: "intern", name: "Intern" },
    { id: "probation", name: "Probation" },
    { id: "terminated", name: "Terminated" },
    { id: "retired", name: "Retired" },
];

const employmentStatusFilter = ref(
    employmentStatuses.find(
        (status) => status.id === props.filters.employment_status
    ) || employmentStatuses[0]
);


const search = ref(props.filters.search || "");
const departmentFilter = ref(
    props.departments.find(
        (dept) => dept.id === Number(props.filters.department)
    ) || props.departments[0]
);


const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);


const applyFilters = () => {
    router.get(
        route("employees.index"),
        {
            search: search.value,
            department:
                departmentFilter.value.id === 0
                    ? null
                    : departmentFilter.value.id,
            employment_status: employmentStatusFilter.value.id,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};


const filterByDepartment = () => {
    applyFilters();
};


watch(search, (newValue, oldValue) => {
    if (newValue === "") {
        applyFilters();
    }
});


const confirmingDeletion = ref(false);
const employeeToDelete = ref(null);
const processingDelete = ref(false);

const confirmDelete = (employee) => {
    if (processingDelete.value) return;
    employeeToDelete.value = employee;
    confirmingDeletion.value = true;
};

const closeModal = () => {
    if (processingDelete.value) return;
    confirmingDeletion.value = false;

    setTimeout(() => {
        if (!confirmingDeletion.value) {

            employeeToDelete.value = null;
        }
    }, 300);
};

const deleteEmployee = () => {
    if (!employeeToDelete.value || processingDelete.value) return;

    processingDelete.value = true;
    router.delete(route("employees.destroy", employeeToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            console.log("Employee deleted successfully.");

        },
        onError: (errors) => {
            console.error("Error deleting employee:", errors);

            processingDelete.value = false; 
        },
        onFinish: () => {
        
            processingDelete.value = false;

        },
    });
};
</script>
