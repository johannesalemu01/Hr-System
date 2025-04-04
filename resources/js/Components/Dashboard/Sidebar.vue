<template>
    <div>
        <!-- Mobile sidebar backdrop -->
        <div
            v-if="isSidebarOpen"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 md:hidden text-black"
            @click="$emit('closeSidebar')"
        >
            open
        </div>

        <!-- Sidebar -->
        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-primary-900 text-black border-r transition-transform duration-300 ease-in-out transform h-screen',
                isSidebarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full md:translate-x-0',
                'md:static md:z-0',
            ]"
        >
            <!-- Logo -->
            <div
                class="h-16 flex items-center px-6 border-b border-primary-700"
            >
                <div class="flex items-center space-x-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-primary-300"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <span class="text-xl font-semibold text-black"
                        >HR System</span
                    >
                </div>

                <!-- Close button (mobile only) -->
                <button
                    class="md:hidden absolute top-4 right-4 text-white"
                    @click="$emit('closeSidebar')"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-4 px-2 space-y-1 flex flex-col gap-4">
                <Link
                    v-for="item in navigationItems"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        item.current
                            ? 'bg-primary-800 text-red-500'
                            : 'text-primary-100 hover:bg-primary-700',
                        'group flex items-center px-3 py-2 text-sm font-medium rounded-md',
                    ]"
                >
                    <component
                        :is="item.icon"
                        class="mr-3 h-5 w-5 text-primary-300"
                        aria-hidden="true"
                    />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User profile -->
            <!-- <div
                class="absolute bottom-0 w-full border-t border-primary-700 p-4"
            >
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img
                            class="h-10 w-10 rounded-full bg-primary-700"
                            :src="
                                user?.profile_picture ||
                                '/placeholder.svg?height=40&width=40'
                            "
                            alt="User avatar"
                        />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-white">
                            {{ user?.name || "User Name" }}
                        </p>
                        <p class="text-xs text-primary-300">{{ userRole }}</p>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import {
    HomeIcon,
    UsersIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    CogIcon,
    ClipboardCheckIcon,
    UserGroupIcon,
} from "@heroicons/vue/outline";

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: false,
    },
});

defineEmits(["closeSidebar"]);

const { props: pageProps, url } = usePage();
const user = pageProps.auth.user;

const userRole = computed(() => {
    if (user?.roles?.length > 0) {
        return (
            user.roles[0].name.charAt(0).toUpperCase() +
            user.roles[0].name.slice(1)
        );
    }
    return "Employee";
});

// Check if the current URL starts with the given path
const isCurrentPath = (path) => {
    return url.startsWith(path);
};

const navigationItems = [
    {
        name: "Dashboard",
        href: "/dashboard",
        icon: HomeIcon,
        current: isCurrentPath("/dashboard"),
    },
    {
        name: "Employees",
        href: "/employees",
        icon: UsersIcon,
        current: isCurrentPath("/employees"),
    },
    {
        name: "Departments",
        href: "/departments",
        icon: UserGroupIcon,
        current: isCurrentPath("/departments"),
    },
    {
        name: "KPI Management",
        href: "/kpis",
        icon: ChartBarIcon,
        current: isCurrentPath("/kpis"),
    },
    {
        name: "Payroll",
        href: "/payroll",
        icon: CurrencyDollarIcon,
        current: isCurrentPath("/payroll"),
    },
    {
        name: "Attendance",
        href: "/attendance",
        icon: ClipboardCheckIcon,
        current: isCurrentPath("/attendance"),
    },
    {
        name: "Leave Management",
        href: "/leave",
        icon: CalendarIcon,
        current: isCurrentPath("/leave"),
    },
    {
        name: "Settings",
        href: "/settings",
        icon: CogIcon,
        current: isCurrentPath("/settings"),
    },
];
</script>
