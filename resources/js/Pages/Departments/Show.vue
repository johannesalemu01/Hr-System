<template>
    <AuthenticatedLayout>
        <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
            <div
                class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h3
                            class="text-2xl font-bold text-gray-900 flex items-center"
                        >
                            <OfficeBuildingIcon
                                class="h-6 w-6 mr-2 text-primary-600"
                            />
                            {{ department.name }}
                            <span class="ml-2 text-sm font-medium text-gray-500"
                                >({{ department.code }})</span
                            >
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Created on {{ formatDate(department.created_at) }}
                        </p>
                    </div>
                    <div v-if="isAdmin" class="flex space-x-2">
                        <Link
                            :href="route('departments.edit', department.id)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            <PencilIcon class="h-4 w-4 mr-1" />
                            Edit
                        </Link>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">
                            Department Information
                        </h4>
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <dl class="space-y-4">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Department Code
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ department.code }}
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Description
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{
                                            department.description ||
                                            "No description available"
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">
                            Department Manager
                        </h4>
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <div
                                v-if="department.manager"
                                class="flex items-center"
                            >
                                <div class="flex-shrink-0">
                                    <img
                                        class="h-16 w-16 rounded-full shadow-md"
                                        :src="department.manager.avatar"
                                        alt="Manager avatar"
                                    />
                                </div>
                                <div class="ml-4">
                                    <h4
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ department.manager.name }}
                                    </h4>
                                    <p class="text-sm text-gray-500">
                                        Department Manager
                                    </p>
                                    <Link
                                        :href="
                                            route(
                                                'employees.show',
                                                department.manager.id
                                            )
                                        "
                                        class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-900"
                                    >
                                        View Profile
                                        <ChevronRightIcon
                                            class="ml-1 h-4 w-4"
                                        />
                                    </Link>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex items-center text-gray-400 italic"
                            >
                                <UserIcon class="h-10 w-10 mr-3" />
                                <div>
                                    <p class="text-sm">No manager assigned</p>
                                    <Link
                                        v-if="isAdmin"
                                        :href="
                                            route(
                                                'departments.edit',
                                                department.id
                                            )
                                        "
                                        class="mt-2 inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-900"
                                    >
                                        Assign Manager
                                        <ChevronRightIcon
                                            class="ml-1 h-4 w-4"
                                        />
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees List -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div
                class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100"
            >
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">
                        Department Employees
                    </h3>
                    <Link
                        v-if="isAdmin"
                        :href="
                            route('employees.create', {
                                department_id: department.id,
                            })
                        "
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Add Employee
                    </Link>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Employee
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Position
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Hire Date
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="employees.length === 0">
                            <td
                                colspan="4"
                                class="px-6 py-4 text-sm text-gray-500 text-center"
                            >
                                No employees found in this department.
                            </td>
                        </tr>
                        <tr
                            v-for="employee in employees"
                            :key="employee.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img
                                            class="h-10 w-10 rounded-full shadow-md"
                                            :src="employee.avatar"
                                            alt=""
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ employee.name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ employee.position }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatDate(employee.hire_date) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                            >
                                <Link
                                    :href="route('employees.show', employee.id)"
                                    class="text-primary-600 hover:text-primary-900"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                <Pagination :links="pagination.links" :meta="pagination.meta" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import {
    OfficeBuildingIcon,
    UserIcon,
    PencilIcon,
    PlusIcon,
    ChevronRightIcon,
} from "@heroicons/vue/outline";

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
    employees: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isAdmin = computed(() =>
    page.props.auth.user.roles.some((role) =>
        ["super-admin", "hr-admin", "manager"].includes(role.name)
    )
);

// Format date for display
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString(undefined, options);
};
</script>
