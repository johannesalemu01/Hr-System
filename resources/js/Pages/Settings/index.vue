<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

const activeTab = ref("profile"); // Default active tab

const tabs = [
    { id: "profile", name: "Profile Settings" },
    { id: "security", name: "Security" },
    { id: "notifications", name: "Notifications" },
    { id: "company", name: "Company Settings" },
];
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Settings Header -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-gray-800">
                            Settings
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage your account settings and preferences.
                        </p>
                    </div>

                    <!-- Settings Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-primary-500 text-primary-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm',
                                ]"
                            >
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>

                    <!-- Settings Content -->
                    <div class="p-6">
                        <!-- Profile Settings -->
                        <div v-if="activeTab === 'profile'" class="space-y-6">
                            <div class="max-w-xl">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Profile Information
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Update your account's profile information.
                                </p>

                                <!-- Profile form will go here -->
                                <div class="mt-6">
                                    <!-- Add your profile form fields here -->
                                </div>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div v-if="activeTab === 'security'" class="space-y-6">
                            <div class="max-w-xl">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Security Settings
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Manage your password and security
                                    preferences.
                                </p>

                                <!-- Security form will go here -->
                                <div class="mt-6">
                                    <!-- Add your security form fields here -->
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Settings -->
                        <div
                            v-if="activeTab === 'notifications'"
                            class="space-y-6"
                        >
                            <div class="max-w-xl">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Notification Preferences
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Configure how you receive notifications.
                                </p>

                                <!-- Notifications form will go here -->
                                <div class="mt-6">
                                    <!-- Add your notifications preferences here -->
                                </div>
                            </div>
                        </div>

                        <!-- Company Settings -->
                        <div v-if="activeTab === 'company'" class="space-y-6">
                            <div class="max-w-xl">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Company Settings
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    Manage your company information and
                                    preferences.
                                </p>

                                <!-- Company form will go here -->
                                <div class="mt-6">
                                    <!-- Add your company settings form fields here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
