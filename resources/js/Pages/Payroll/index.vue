<template>
    <AuthenticatedLayout
        title="Payroll"
        description="You can show and manage Payroll from here."
    >
        <!-- Flash Messages -->
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

        <!-- Payroll Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center">
                <div class="bg-[#1098ad] text-white p-3 rounded-lg mr-4">
                    <CurrencyDollarIcon class="h-6 w-6" />
                </div>
                <div>
                    <h2 class="text-xl font-semibold">Payroll</h2>
                    <p class="text-gray-600">
                        You can show and manage Payroll from here.
                    </p>
                </div>
            </div>
        </div>

        <!-- Payroll Controls -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0"
            >
                <div class="flex items-center">
                    <!-- Date Range Picker -->
                    <div class="ml-4 relative">
                        <Listbox v-model="selectedPeriod">
                            <div class="relative">
                                <ListboxButton
                                    class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                >
                                    <div class="flex items-center">
                                        <CalendarIcon
                                            class="h-5 w-5 text-gray-400 mr-2"
                                        />
                                        <span class="block truncate">{{
                                            currentPeriod?.formatted
                                        }}</span>
                                    </div>
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
                                            v-for="period in payrollPeriods"
                                            :key="period.id"
                                            v-slot="{ active, selected }"
                                            :value="period"
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
                                                    >{{ period.label }}</span
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
                </div>

                <!-- Payslip Button -->
                <button
                    class="bg-[#1098ad] hover:bg-[#1097aa] text-white px-4 py-2 rounded-md flex items-center justify-center"
                    :disabled="payroll?.status === 'processing'"
                    @click="generateAllPayslips"
                >
                    <DocumentDownloadIcon class="h-5 w-5 mr-2" />
                    PAYSLIP
                </button>
            </div>
        </div>

        <!-- Payroll Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Employee Details
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Gross
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Deductions
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Cash Advance
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Overtime
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Net Pay
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
                        <tr v-for="item in payrollItems?.data" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img
                                            class="h-10 w-10 rounded-full"
                                            :src="item.employee.profile_picture"
                                            alt=""
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ item.employee.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ item.employee.employee_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatCurrency(item.gross) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatCurrency(item.deductions) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatCurrency(item.cash_advance) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                {{ formatCurrency(item.overtime) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                :class="
                                    item.net_pay < 0
                                        ? 'text-red-600'
                                        : 'text-green-600'
                                "
                            >
                                {{ formatCurrency(item.net_pay) }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                            >
                                <Link
                                    :href="route('payroll.payslip', item.id)"
                                    class="text-primary-600 hover:text-primary-900"
                                >
                                    View Payslip
                                </Link>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="payrollItems?.data.length === 0">
                            <td
                                colspan="7"
                                class="px-6 py-4 text-center text-gray-500"
                            >
                                No payroll items found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="payrollItems?.data.length > 0" class="mt-6">
                <Pagination
                    :links="payrollItems.links"
                    :meta="payrollItems.meta"
                />
            </div>
        </div>

        <!-- Payroll Actions -->
        <div class="mt-6 flex justify-end space-x-4">
            <button
                v-if="payroll?.status === 'processing'"
                @click="processPayroll"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
                Process Payroll
            </button>
            <button
                v-if="payroll?.status === 'approved'"
                @click="releasePayroll"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Release Payroll
            </button>
        </div>
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
    DocumentTextIcon,
    CalendarIcon,
    SelectorIcon,
    CheckIcon,
    SearchIcon,
    DocumentDownloadIcon,
    CurrencyDollarIcon,
} from "@heroicons/vue/outline";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    payrollItems: {
        type: Object,
        required: true,
    },
    payrollPeriods: {
        type: Array,
        required: true,
    },
    currentPeriod: {
        type: Object,
        required: true,
    },
    payroll: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const flash = computed(() => page.props.flash);

// Pagination
const perPage = ref(25);
const currentPage = ref(1);
const search = ref("");

// Selected period
const selectedPeriod = ref(props.currentPeriod);

// Watch for period changes
watch(selectedPeriod, (newPeriod) => {
    router.get(
        route("payroll.index"),
        {
            start_date: newPeriod.start_date,
            end_date: newPeriod.end_date,
            start_date: newPeriod.start_date,
            end_date: newPeriod.end_date,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
});

// Filtered and paginated payroll items
const filteredPayrollItems = computed(() => {
    let filtered = props.payrollItems?.data;

    // Apply search filter
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(
            (item) =>
                item.employee.name.toLowerCase().includes(searchLower) ||
                item.employee.employee_id.toLowerCase().includes(searchLower)
        );
    }

    return filtered;
});

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-ET", {
        style: "currency",
        currency: "ETB",
        minimumFractionDigits: 2,
    }).format(value);
};

// Generate all payslips
const generateAllPayslips = () => {
    if (
        confirm(
            "Are you sure you want to download all payslips for the current period?"
        )
    ) {
        window.location.href = route("payroll.downloadAllPayslips", {
            start_date: selectedPeriod.value.start_date,
            end_date: selectedPeriod.value.end_date,
        });
    }
};

// Process payroll
const processPayroll = () => {
    if (
        confirm(
            "Are you sure you want to process this payroll? This will lock the payroll for editing."
        )
    ) {
        router.post(route("payroll.process", props.payroll.id));
    }
};

// Release payroll
const releasePayroll = () => {
    if (
        confirm(
            "Are you sure you want to release this payroll? This will mark the payroll as paid."
        )
    ) {
        router.post(route("payroll.release", props.payroll.id));
    }
};
</script>
