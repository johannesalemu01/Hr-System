<script setup>
import { usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    UserIcon,
    BriefcaseIcon,
    PhoneIcon,
    HomeIcon,
    CurrencyDollarIcon, 
    CalendarIcon,
} from "@heroicons/vue/outline";

const employee = usePage().props.employee; 
</script>

<template>
    <AuthenticatedLayout
        title="Employee Details"
        description="Detailed information about the employee"
    >
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <!-- Header Section -->
            <div class="px-4 py-5 sm:px-6 bg-primary-600 text-white">
                <div class="flex items-center space-x-4 ">
                    <img
                        :src="
                            employee.profile_picture
                                ? `/storage/${employee.profile_picture}`
                                : 'https://via.placeholder.com/150'
                        "
                        alt="Profile Picture"
                        class="w-24 h-24 rounded-full object-cover border-2 border-white"
                    />
                    <div class="flex flex-col gap-3">
                        <h3 class="text-2xl font-bold text-black">
                            {{ employee.first_name }} {{ employee.last_name }}
                        </h3>
                        <p class="text-sm text-gray-400">
                            <UserIcon class="h-5 w-5 inline-block mr-1" />
                            {{ employee.user.email }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            <BriefcaseIcon
                                class="h-5 w-5 inline-block mr-1"
                            />
                            {{ employee.position.title }} -
                            {{ employee.department.name }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Details Section -->
            <div class="border-t border-gray-200">
                <dl>
                    <!-- Department -->
                    <div
                        class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Department
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            {{ employee.department.name }}
                        </dd>
                    </div>

                    <!-- Salary -->
                    <div
                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Salary
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            <CurrencyDollarIcon
                                class="h-5 w-5 inline-block mr-1"
                            />
                            {{ employee.position.min_salary }} ETB
                        </dd>
                    </div>

                    <!-- Position -->
                    <div
                        class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Position
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            {{ employee.position.title }}
                        </dd>
                    </div>

                    <!-- Hire Date -->
                    <div
                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Hire Date
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            <CalendarIcon class="h-5 w-5 inline-block mr-1" />
                            {{ employee.hire_date }}
                        </dd>
                    </div>

                    <!-- Termination Date -->
                    <div
                        class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Termination Date
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            {{ employee.termination_date ?? "Not Applicable" }}
                        </dd>
                    </div>

                    <!-- Marital Status -->
                    <div
                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Marital Status
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            {{ employee.marital_status }}
                        </dd>
                    </div>

                    <!-- Phone Number -->
                    <div
                        class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Phone Number
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            <PhoneIcon class="h-5 w-5 inline-block mr-1" />
                            {{ employee.phone_number }}
                        </dd>
                    </div>

                    <!-- Bank Account -->
                    <div
                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Bank Account
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            {{ employee.bank_account_number }} ({{
                                employee.bank_name
                            }})
                        </dd>
                    </div>

                    <!-- Address -->
                    <div
                        class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Address
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            <HomeIcon class="h-5 w-5 inline-block mr-1" />
                            {{ employee.address }}
                        </dd>
                    </div>

                    <!-- Emergency Contact -->
                    <div
                        class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                    >
                        <dt class="text-sm font-medium text-gray-500">
                            Emergency Contact
                        </dt>
                        <dd
                            class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"
                        >
                            {{ employee.emergency_contact_name }} ({{
                                employee.emergency_contact_relationship
                            }})
                            <br />
                            <PhoneIcon class="h-5 w-5 inline-block mr-1" />
                            {{ employee.emergency_contact_phone }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
