<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div
        class="min-h-screen bg-gradient-to-br from-[#1098ad]/10 to-white dark:from-gray-900 dark:to-gray-800 dark:text-white"
    >
        <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
            <!-- Header/Nav -->
            <header class="absolute left-8 top-8">
                <img
                    src="/logo.png"
                    alt="Logo"
                    class="h-12 w-auto mx-auto mb-8"
                />
            </header>
            <nav v-if="canLogin" class="absolute right-8 top-8 flex gap-4">
                <template v-if="!$page.props.auth.user">
                    <Link
                        :href="route('login')"
                        class="rounded-full bg-white/80 backdrop-blur-sm px-6 py-2 text-sm font-medium text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800/50 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-700/70 transition"
                    >
                        Log in
                    </Link>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-full bg-[#1098ad] px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#1098ad]/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1098ad] transition"
                    >
                        Register
                    </Link>
                </template>
                <Link
                    v-else
                    :href="route('dashboard')"
                    class="rounded-full bg-[#1098ad] px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#1098ad]/90 transition"
                >
                    Dashboard
                </Link>
            </nav>

            <!-- Hero Section -->
            <div class="mx-auto max-w-3xl text-center">
                <h1
                    class="text-5xl font-bold tracking-tight text-gray-900 sm:text-7xl dark:text-white"
                >
                    Omishtu HR
                    <span class="text-[#1098ad]">Management</span>
                </h1>
                <p
                    class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300"
                >
                    Omishtu's HR system is designed to simplify and enhance our
                    human resource management processes, providing powerful
                    tools for payroll, performance tracking, and employee
                    management.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="mx-auto mt-20 max-w-6xl">
                <dl
                    class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        class="relative rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-8 shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <dt
                            class="flex items-center gap-4 text-lg font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1098ad]/10 text-[#1098ad]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.171-.879-1.172-2.303 0-3.182C10.536 7.719 11.768 7.5 12 7.5c.725 0 1.45.22 2.003.659"
                                    />
                                </svg>
                            </div>
                            Payroll Management
                        </dt>
                        <dd
                            class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400"
                        >
                            Automated payroll processing, tax calculations, and
                            direct deposits with complete compliance.
                        </dd>
                    </div>

                    <div
                        class="relative rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-8 shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <dt
                            class="flex items-center gap-4 text-lg font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1098ad]/10 text-[#1098ad]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"
                                    />
                                </svg>
                            </div>
                            KPI Tracking
                        </dt>
                        <dd
                            class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400"
                        >
                            Monitor and optimize employee performance with
                            customizable KPIs and real-time analytics.
                        </dd>
                    </div>

                    <div
                        class="relative rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-8 shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <dt
                            class="flex items-center gap-4 text-lg font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1098ad]/10 text-[#1098ad]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"
                                    />
                                </svg>
                            </div>
                            Employee Management
                        </dt>
                        <dd
                            class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400"
                        >
                            Comprehensive employee data management, onboarding,
                            and document handling.
                        </dd>
                    </div>

                    <div
                        class="relative rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-8 shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <dt
                            class="flex items-center gap-4 text-lg font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1098ad]/10 text-[#1098ad]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"
                                    />
                                </svg>
                            </div>
                            Leave Management
                        </dt>
                        <dd
                            class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400"
                        >
                            Streamlined leave requests, approvals, and tracking
                            with automated balance calculations.
                        </dd>
                    </div>

                    <div
                        class="relative rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-8 shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <dt
                            class="flex items-center gap-4 text-lg font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1098ad]/10 text-[#1098ad]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"
                                    />
                                </svg>
                            </div>
                            Training & Development
                        </dt>
                        <dd
                            class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400"
                        >
                            Track employee training progress and manage
                            development programs effectively.
                        </dd>
                    </div>

                    <div
                        class="relative rounded-2xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm p-8 shadow-lg ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition hover:shadow-xl hover:-translate-y-1"
                    >
                        <dt
                            class="flex items-center gap-4 text-lg font-semibold leading-7 text-gray-900 dark:text-white"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#1098ad]/10 text-[#1098ad]"
                            >
                                <svg
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"
                                    />
                                </svg>
                            </div>
                            Analytics & Reporting
                        </dt>
                        <dd
                            class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-400"
                        >
                            Generate comprehensive reports and gain insights
                            with advanced analytics tools.
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- CTA Section -->
            <div class="mt-20 flex justify-center">
                <Link
                    v-if="canRegister && !$page.props.auth.user"
                    :href="route('register')"
                    class="group relative rounded-full bg-[#1098ad] px-8 py-4 text-base font-medium text-white shadow-lg hover:bg-[#1098ad]/90 hover:shadow-[#1098ad]/30 hover:shadow-xl transition"
                >
                    <span class="flex items-center gap-2">
                        Explore the System
                        <svg
                            class="h-5 w-5 transition group-hover:translate-x-1"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
                            />
                        </svg>
                    </span>
                </Link>
            </div>
        </div>
    </div>
</template>
