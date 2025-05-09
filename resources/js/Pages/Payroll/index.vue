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


        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0"
            >
                <div class="flex items-center">

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
                                                        ? 'bg-gray-200'
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


                    <button
                        v-if="
                            payroll?.status === 'processing' &&
                            availableEmployees.length > 0
                        "
                        @click="openAddModal"
                        class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center justify-center text-sm"
                    >
                        <UserAddIcon class="h-5 w-5 mr-2" />
                        Add Employee

                    </button>
                    <span
                        v-if="
                            payroll?.status !== 'processing' &&
                            availableEmployees.length > 0
                        "
                        class="ml-4 text-sm text-gray-500 italic"
                    >
                        (Payroll must be 'processing' to add employees)
                    </span>
                    <span
                        v-if="
                            availableEmployees.length === 0 &&
                            payroll?.status === 'processing'
                        "
                        class="ml-4 text-sm text-gray-500 italic"
                    >
                        (No available employees to add)
                    </span>
                </div>


                <button
                    class="bg-[#1098ad] hover:bg-[#1097aa] text-white px-4 py-2 rounded-md flex items-center justify-center"
                    :disabled="payroll?.status === 'processing'"
                    @click="confirmGenerateAllPayslips"
                >
                    <DocumentDownloadIcon class="h-5 w-5 mr-2" />
                    PAYSLIP
                </button>
            </div>
        </div>

        <!-- Payroll Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">

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
                                <div class="flex items-center space-x-4">
                                    <Link
                                        :href="
                                            route('payroll.payslip', item.id)
                                        "
                                        class="text-primary-600 hover:text-primary-900"
                                        title="View Payslip"
                                    >
                                        Payslip
                                    </Link>
                                    <Link
                                        :href="
                                            route('payroll.items.edit', item.id)
                                        "
                                        class="text-indigo-600 hover:text-indigo-900"
                                        v-if="payroll?.status === 'processing'"
                                        title="Edit Adjustments"
                                    >
                                        Edit
                                        <!-- <<< Edit Link Added -->
                                    </Link>
                                    <button
                                    v-if="isAdmin"
                                        @click="
                                            confirmDeletePayrollItem(item.id)
                                        "
                                        class="text-red-600 hover:text-red-900"
                                        :disabled="
                                            payroll?.status !== 'processing'
                                        "
                                        title="Delete Item"
                                    >
                                        Delete
                                    </button>
                                </div>
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


            <div v-if="payrollItems?.data.length > 0" class="mt-6">
                <Pagination
                    :links="payrollItems.links"
                    :meta="payrollItems.meta"
                />
            </div>
        </div>


        <div class="mt-6 flex justify-end space-x-4">
            <button
                v-if="payroll?.status === 'processing'"
                @click="confirmProcessPayroll"
               
                class="px-4 py-2 border border-transparent rounded-md shadow-sm
                text-sm font-medium text-white bg-green-600 hover:bg-green-700
                focus:outline-none focus:ring-2 focus:ring-offset-2
                focus:ring-green-500" > Process Payroll
            </button>
            <button
                v-if="payroll?.status === 'approved'"
                @click="confirmRevertPayroll"
               
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm
                text-sm font-medium text-gray-700 bg-white hover:bg-gray-50
                focus:outline-none focus:ring-2 focus:ring-offset-2
                focus:ring-indigo-500" > Revert to Processing
            </button>
            <button
                v-if="payroll?.status === 'approved'"
                @click="confirmReleasePayroll"
                
                class="px-4 py-2 border border-transparent rounded-md shadow-sm
                text-sm font-medium text-white bg-blue-600 hover:bg-blue-700
                focus:outline-none focus:ring-2 focus:ring-offset-2
                focus:ring-blue-500" > Release Payroll
            </button>

            <span
                v-if="payroll?.status === 'paid'"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
            >
                Payroll Paid
            </span>
        </div>


        <Modal :show="showAddModal" @close="closeAddModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add Employee to Payroll
                </h2>

                <div class="mt-4">
                    <label
                        for="employeeToAdd"
                        class="block text-sm font-medium text-gray-700"
                        >Select Employee</label
                    >
                    <select
                        id="employeeToAdd"
                        v-model="selectedEmployeeToAdd"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                    >
                        <option :value="null" disabled>
                            -- Select an employee --
                        </option>
                        <option
                            v-for="employee in availableEmployees"
                            :key="employee.id"
                            :value="employee.id"
                        >
                            {{ employee.full_name }} ({{
                                employee.employee_id
                            }})
                        </option>
                    </select>
                    <InputError
                        class="mt-2"
                        :message="addEmployeeForm.errors.employee_id"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeAddModal">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': addEmployeeForm.processing }"
                        :disabled="
                            addEmployeeForm.processing || !selectedEmployeeToAdd
                        "
                        @click="submitAddEmployee"
                    >
                        Add Employee
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Confirmation Modal -->
        <ConfirmationModal
            :show="confirmingAction"
            @close="closeConfirmationModal"
        >
            <template #title>
                {{ confirmationTitle }}
            </template>
            <template #content>
                {{ confirmationMessage }}
            </template>
            <template #footer>
                <SecondaryButton @click="closeConfirmationModal">
                    Cancel
                </SecondaryButton>
                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': confirmationProcessing }"
                    :disabled="confirmationProcessing"
                    @click="executeConfirmedAction"
                >
                    Confirm
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue"; // ref added
import { Link, router, usePage, useForm } from "@inertiajs/vue3";
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
    UserAddIcon,
} from "@heroicons/vue/outline";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import InputError from "@/Components/InputError.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue"; // Added
import DangerButton from "@/Components/DangerButton.vue"; // Added

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
    availableEmployees: {
        type: Array,
        default: () => [],
    },
    payroll: {
        type: Object,
        required: true,
    },
});


const flash = computed(() => page.props.flash);



const page = usePage();

// Log the current signed in user for debugging
console.log("EmployeeKpis.vue signed in user:", page.props.auth?.user);

// Use roles array for admin check (supports string or object)
const isAdmin = computed(() => {
    const user = page.props.auth?.user;
    if (!user || !user.roles || !Array.isArray(user.roles)) return false;
    // If roles are array of strings
    if (typeof user.roles[0] === "string") {
        return user.roles.map((r) => r.trim().toLowerCase()).includes("admin");
    }
    // If roles are array of objects with .name
    if (typeof user.roles[0] === "object" && user.roles[0] !== null) {
        return user.roles
            .map((r) => r.name?.trim().toLowerCase())
            .includes("admin");
    }
    return false;
});


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


const confirmingAction = ref(false);
const confirmationTitle = ref("");
const confirmationMessage = ref("");
const confirmationAction = ref(null); 
const confirmationProcessing = ref(false);
const itemToDeleteId = ref(null);

const openConfirmationModal = (title, message, action) => {
    confirmationTitle.value = title;
    confirmationMessage.value = message;
    confirmationAction.value = action;
    confirmationProcessing.value = false;
    itemToDeleteId.value = null; 
    confirmingAction.value = true;
};

const closeConfirmationModal = () => {
    confirmingAction.value = false;
    confirmationTitle.value = "";
    confirmationMessage.value = "";
    confirmationAction.value = null;
    confirmationProcessing.value = false;
    itemToDeleteId.value = null;
};

const executeConfirmedAction = async () => {
    if (confirmationAction.value) {
        confirmationProcessing.value = true;
        try {
            await confirmationAction.value();
        } catch (error) {
            console.error("Error executing confirmed action:", error);

        } finally {
            confirmationProcessing.value = false;
            closeConfirmationModal();
        }
    }
};


// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-ET", {
        style: "currency",
        currency: "ETB",
        minimumFractionDigits: 2,
    }).format(value);
};

// Generate all payslips
const confirmGenerateAllPayslips = () => {
    // Renamed
    openConfirmationModal(
        "Download All Payslips",
        "Are you sure you want to download all payslips for the current period?",
        generateAllPayslips // Pass the actual function
    );
};

const generateAllPayslips = () => {
    // Actual action
    window.location.href = route("payroll.downloadAllPayslips", {
        start_date: selectedPeriod.value.start_date,
        end_date: selectedPeriod.value.end_date,
    });

};


const confirmProcessPayroll = () => {

    openConfirmationModal(
        "Process Payroll",
        "Are you sure you want to process this payroll? This will lock the payroll for editing.",
        processPayroll 
    );
};

const processPayroll = () => {

    return new Promise((resolve, reject) => {
        router.post(
            route("payroll.process", props.payroll.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => resolve(),
                onError: (errors) => {
                    console.error("Error processing payroll:", errors);
                    reject(errors);
                },
            }
        );
    });
};


const confirmRevertPayroll = () => {

    openConfirmationModal(
        "Revert Payroll",
        "Are you sure you want to revert this payroll back to 'processing'? Approval details will be cleared.",
        revertPayroll 
    );
};

const revertPayroll = () => {

    return new Promise((resolve, reject) => {
        router.post(
            route("payroll.revert", props.payroll.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => resolve(),
                onError: (errors) => {
                    console.error("Error reverting payroll:", errors);
                    reject(errors);
                },
            }
        );
    });
};


const confirmReleasePayroll = () => {

    openConfirmationModal(
        "Release Payroll",
        "Are you sure you want to release this payroll? This will mark the payroll as paid.",
        releasePayroll 
    );
};

const releasePayroll = () => {

    return new Promise((resolve, reject) => {
        router.post(
            route("payroll.release", props.payroll.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => resolve(),
                onError: (errors) => {
                    console.error("Error releasing payroll:", errors);
                    reject(errors);
                },
            }
        );
    });
};


const confirmDeletePayrollItem = (itemId) => {

    itemToDeleteId.value = itemId; 
    openConfirmationModal(
        "Delete Payroll Item",
        "Are you sure you want to delete this payroll item? This action cannot be undone.",
        deletePayrollItem 
    );
};

const deletePayrollItem = () => {

    if (!itemToDeleteId.value)
        return Promise.reject("No item ID specified for deletion.");

    return new Promise((resolve, reject) => {
        router.delete(route("payroll.items.destroy", itemToDeleteId.value), {
            preserveScroll: true,
            onSuccess: () => resolve(),
            onError: (errors) => {
                console.error("Error deleting payroll item:", errors);
                reject(errors);
            },
        });
    });
};


const showAddModal = ref(false);
const selectedEmployeeToAdd = ref(null);

const addEmployeeForm = useForm({
    employee_id: null,
});

const openAddModal = () => {
    selectedEmployeeToAdd.value = null; 
    addEmployeeForm.reset(); 
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const submitAddEmployee = () => {
    if (!selectedEmployeeToAdd.value) return;

    addEmployeeForm.employee_id = selectedEmployeeToAdd.value;

    addEmployeeForm.post(route("payroll.items.store", props.payroll.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddModal();

        },
        onError: () => {

        },
    });
};

</script>
