<template>
    <div>
        <!-- Mobile sidebar backdrop -->
        <div
            v-if="isSidebarOpen"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 md:hidden text-black"
            @click="$emit('closeSidebar')"
        ></div>

        <!-- Sidebar -->
        <div
            :class="[
                'fixed inset-y-0  z-50 w-64 bg-white text-black border-r transition-transform duration-300 ease-in-out transform h-screen flex flex-col',
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
                    <img src="/logo.png" alt="Logo" class="h-8 w-16" />
                    <span class="text-xl font-semibold text-[#1098ad]"
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
            <nav
                class="mt-12 px-2 space-y-1 flex flex-col gap-4 justify-between flex-1 mb-24"
            >
                <div class="flex flex-col gap-6">
                    <Link
                        v-for="item in navigationItems"
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            item.current
                                ? 'bg-primary-800 text-[#2e6e77] font-bold'
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
                        <!-- Add badge for Leave Management/Leave Requests -->
                        <span
                            v-if="
                                (item.name === 'Leave Management' ||
                                    item.name === 'Leave Requests') &&
                                pendingLeaveRequestsCount > 0
                            "
                            class="ml-auto bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
                        >
                            {{ pendingLeaveRequestsCount }}
                        </span>
                    </Link>
                </div>
                <div class="logout text-[#1098ad] rounded-lg">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="block w-full px-4 py-2 text-start text-lg leading-5 rounded-lg text-[#2c6a74] transition duration-150 ease-in-out focus:outline-none"
                    >
                        <LogoutIcon
                            class="h-5 w-5 inline-block mr-2 text-[#2c6a74] hover:text-[white]"
                        />
                        <span>Log Out</span>
                    </Link>
                </div>
            </nav>
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
    LogoutIcon,
} from "@heroicons/vue/outline";

import DropdownLink from "@/Components/DropdownLink.vue";
import useSideBarStore from "@/stores/sidebarStore";

const sidebarStore = useSideBarStore();

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
    // Log the full user object for debugging
    console.log("Sidebar user object:", user);

    // Handle both array of objects and array of strings for roles
    if (
        user &&
        user.roles &&
        Array.isArray(user.roles) &&
        user.roles.length > 0
    ) {
        // If roles are array of objects with .name
        if (
            typeof user.roles[0] === "object" &&
            user.roles[0] !== null &&
            user.roles[0].name
        ) {
            console.log("Sidebar user.roles (object):", user.roles);
            return user.roles[0].name.trim().toLowerCase();
        }
        // If roles are array of strings
        if (typeof user.roles[0] === "string") {
            console.log("Sidebar user.roles (string):", user.roles);
            return user.roles[0].trim().toLowerCase();
        }
    }
    console.log(
        "Sidebar user.roles missing or malformed, defaulting to employee"
    );
    return "employee";
});

console.log("Sidebar userRole:", userRole.value);

// Console log the current user role for debugging
console.log("Sidebar userRole:", userRole.value);

// Check if the current URL starts with the given path
const isCurrentPath = (path) => {
    return url.startsWith(path);
};

const sidebarItems = [
    {
        name: { employee: "Dashboard", default: "Dashboard" },
        href: "/dashboard",
        icon: HomeIcon,
        employeeOnly: false,
        adminOnly: false,
        match: "/dashboard",
    },
    {
        name: { employee: "My Profile", default: "Employees" },
        href: { employee: "/employees/profile", default: "/employees" },
        icon: UsersIcon,
        employeeOnly: false,
        adminOnly: false,
        match: { employee: "/employees/profile", default: "/employees" },
    },
    {
        name: { employee: "My Department", default: "Departments" },
        href: { employee: "/departments", default: "/departments" },
        icon: UserGroupIcon,
        employeeOnly: false,
        adminOnly: false,
        match: { employee: "/departments", default: "/departments" },
    },
    {
        name: { employee: "My KPIs", default: "KPI Management" },
        href: "/kpis",
        icon: ChartBarIcon,
        employeeOnly: false,
        adminOnly: false,
        match: "/kpis",
    },
    {
        name: { employee: "My Payroll", default: "Payroll" },
        href: "/payroll",
        icon: CurrencyDollarIcon,
        employeeOnly: false,
        adminOnly: false,
        match: "/payroll",
    },
    {
        name: { employee: "Attendance", default: "Attendance" },
        href: "/attendance",
        icon: ClipboardCheckIcon,
        employeeOnly: false,
        adminOnly: false,
        match: "/attendance",
    },
    {
        name: { employee: "Leave Requests", default: "Leave Management" },
        href: "/leave",
        icon: CalendarIcon,
        employeeOnly: false,
        adminOnly: false,
        match: "/leave",
    },
    {
        name: { employee: "Settings", default: "Settings" },
        href: "/settings",
        icon: CogIcon,
        employeeOnly: false,
        adminOnly: false,
        match: "/settings",
    },
    // Admin only items
];

const navigationItems = computed(() => {
    const role = userRole.value;
    const isEmployee = role === "employee";
    const isAdmin = role === "admin";
    return sidebarItems
        .filter((item) => {
            if (item.employeeOnly && !isEmployee) return false;
            if (item.adminOnly && !isAdmin) return false;
            return true;
        })
        .map((item) => {
            const name = isEmployee
                ? item.name.employee ?? item.name.default
                : item.name.default;
            const href = isEmployee
                ? typeof item.href === "object"
                    ? item.href.employee
                    : item.href
                : typeof item.href === "object"
                ? item.href.default
                : item.href;
            const match = isEmployee
                ? typeof item.match === "object"
                    ? item.match.employee
                    : item.match
                : typeof item.match === "object"
                ? item.match.default
                : item.match;
            return {
                name,
                href,
                icon: item.icon,
                current: isCurrentPath(match),
            };
        });
});

const page = usePage();
const pendingLeaveRequestsCount = computed(
    () => page.props.pendingLeaveRequestsCount || 0
);
</script>
