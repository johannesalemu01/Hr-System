<template>
    <AuthenticatedLayout title="Edit Payroll Item Adjustments">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Adjustments for {{ payrollItem.employee_name }} ({{
                    payrollItem.employee_id_display
                }})
            </h2>
            <p class="text-sm text-gray-600">
                Payroll Period: {{ payrollItem.payroll_period }}
            </p>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

                <!-- Summary -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Summary
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="font-semibold">Basic Salary:</span>
                            {{ formatCurrency(payrollItem.basic_salary) }}
                        </div>
                        <div>
                            <span class="font-semibold">Allowances:</span>
                            {{ formatCurrency(payrollItem.total_allowances) }}
                        </div>
                        <div class="text-green-600">
                            <span class="font-semibold">Total Bonuses:</span>
                            {{ formatCurrency(payrollItem.total_bonuses) }}
                        </div>
                        <div class="text-red-600">
                            <span class="font-semibold">Total Deductions:</span>
                            {{ formatCurrency(payrollItem.total_deductions) }}
                        </div>
                        <div
                            class="col-span-2 md:col-span-4 text-lg font-bold"
                            :class="
                                payrollItem.net_salary < 0
                                    ? 'text-red-700'
                                    : 'text-green-700'
                            "
                        >
                            Net Salary:
                            {{ formatCurrency(payrollItem.net_salary) }}
                        </div>
                    </div>
                </div>

                <!-- Bonuses -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Bonuses
                            </h3>
                            <PrimaryButton @click="showAddBonusModal = true"
                                >Add Bonus</PrimaryButton
                            >
                        </header>
                        <ul
                            v-if="payrollItem.bonuses.length > 0"
                            class="space-y-2"
                        >
                            <li
                                v-for="bonus in payrollItem.bonuses"
                                :key="bonus.id"
                                class="flex justify-between items-center border-b pb-2"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ bonus.description }} ({{
                                            bonus.type
                                        }})
                                    </p>
                                    <p class="text-sm text-green-600">
                                        {{ formatCurrency(bonus.amount) }}
                                    </p>
                                </div>
                                <button
                                    @click="confirmDeleteBonus(bonus)"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                    title="Delete Bonus"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">
                            No bonuses recorded for this item.
                        </p>
                    </section>
                </div>

                <!-- Deductions -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Deductions
                            </h3>
                            <PrimaryButton @click="showAddDeductionModal = true"
                                >Add Deduction</PrimaryButton
                            >
                        </header>
                        <ul
                            v-if="payrollItem.deductions.length > 0"
                            class="space-y-2"
                        >
                            <li
                                v-for="deduction in payrollItem.deductions"
                                :key="deduction.id"
                                class="flex justify-between items-center border-b pb-2"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ deduction.description }} ({{
                                            deduction.type
                                        }})
                                    </p>
                                    <p class="text-sm text-red-600">
                                        {{ formatCurrency(deduction.amount) }}
                                    </p>
                                </div>
                                <button
                                    @click="confirmDeleteDeduction(deduction)"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                    title="Delete Deduction"
                                >
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500">
                            No deductions recorded for this item.
                        </p>
                    </section>
                </div>

                <div class="flex justify-start mt-6">
                    <Link
                        :href="
                            route('payroll.index', {
                                start_date: payrollItem.payroll?.start_date,
                                end_date: payrollItem.payroll?.end_date,
                            })
                        "
                        class="text-indigo-600 hover:text-indigo-900"
                    >
                        &larr; Back to Payroll List
                    </Link>
                </div>
            </div>
        </div>

        <!-- Add Bonus Modal -->
        <Modal :show="showAddBonusModal" @close="closeAddBonusModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add Manual Bonus
                </h2>
                <form @submit.prevent="submitAddBonus">
                    <div class="mt-4">
                        <InputLabel
                            for="bonus_description"
                            value="Description"
                        />
                        <TextInput
                            id="bonus_description"
                            v-model="addBonusForm.description"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="addBonusForm.errors.description"
                        />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="bonus_amount" value="Amount" />
                        <TextInput
                            id="bonus_amount"
                            v-model="addBonusForm.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="addBonusForm.errors.amount"
                        />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="bonus_type" value="Type" />
                        <select
                            id="bonus_type"
                            v-model="addBonusForm.bonus_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="manual_adjustment">
                                Manual Adjustment
                            </option>
                            <option value="other">Other</option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="addBonusForm.errors.bonus_type"
                        />
                    </div>
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeAddBonusModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            class="ml-3"
                            :class="{ 'opacity-25': addBonusForm.processing }"
                            :disabled="addBonusForm.processing"
                        >
                            Add Bonus
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Deduction Modal -->
        <Modal :show="showAddDeductionModal" @close="closeAddDeductionModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Add Manual Deduction
                </h2>
                <form @submit.prevent="submitAddDeduction">
                    <div class="mt-4">
                        <InputLabel
                            for="deduction_description"
                            value="Description"
                        />
                        <TextInput
                            id="deduction_description"
                            v-model="addDeductionForm.description"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="addDeductionForm.errors.description"
                        />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="deduction_amount" value="Amount" />
                        <TextInput
                            id="deduction_amount"
                            v-model="addDeductionForm.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="addDeductionForm.errors.amount"
                        />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="deduction_type" value="Type" />
                        <select
                            id="deduction_type"
                            v-model="addDeductionForm.deduction_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="manual_adjustment">
                                Manual Adjustment
                            </option>
                            <option value="loan_repayment">
                                Loan Repayment
                            </option>
                            <option value="advance">Cash Advance</option>
                            <option value="other">Other</option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="addDeductionForm.errors.deduction_type"
                        />
                    </div>
                    <div class="mt-6 flex justify-end">
                        <SecondaryButton @click="closeAddDeductionModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton
                            class="ml-3"
                            :class="{
                                'opacity-25': addDeductionForm.processing,
                            }"
                            :disabled="addDeductionForm.processing"
                        >
                            Add Deduction
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal
            :show="confirmingDeletion"
            @close="confirmingDeletion = false"
        >
            <template #title> Delete {{ deletionType }} </template>
            <template #content>
                Are you sure you want to delete this {{ deletionType }}? This
                action cannot be undone. <br />
                ({{ itemToDelete?.description }} -
                {{ formatCurrency(itemToDelete?.amount) }})
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancel
                </SecondaryButton>
                <DangerButton
                    class="ml-3"
                    @click="deleteItemConfirmed"
                    :class="{ 'opacity-25': deleteForm.processing }"
                    :disabled="deleteForm.processing"
                >
                    Delete
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Modal from "@/Components/Modal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import { TrashIcon } from "@heroicons/vue/outline";

const props = defineProps({
    payrollItem: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash);


const showAddBonusModal = ref(false);
const addBonusForm = useForm({
    description: "",
    amount: null,
    bonus_type: "manual_adjustment",
});

const closeAddBonusModal = () => {
    showAddBonusModal.value = false;
    addBonusForm.reset();
    addBonusForm.clearErrors();
};

const submitAddBonus = () => {
    addBonusForm.post(
        route("payroll.items.bonuses.store", props.payrollItem.id),
        {
            preserveScroll: true,
            onSuccess: () => closeAddBonusModal(),
        }
    );
};


const showAddDeductionModal = ref(false);
const addDeductionForm = useForm({
    description: "",
    amount: null,
    deduction_type: "manual_adjustment",
});

const closeAddDeductionModal = () => {
    showAddDeductionModal.value = false;
    addDeductionForm.reset();
    addDeductionForm.clearErrors();
};

const submitAddDeduction = () => {
    addDeductionForm.post(
        route("payroll.items.deductions.store", props.payrollItem.id),
        {
            preserveScroll: true,
            onSuccess: () => closeAddDeductionModal(),
        }
    );
};


const confirmingDeletion = ref(false);
const itemToDelete = ref(null);
const deletionType = ref(""); 
const deleteForm = useForm({});

const confirmDeleteBonus = (bonus) => {
    itemToDelete.value = bonus;
    deletionType.value = "Bonus";
    confirmingDeletion.value = true;
};

const confirmDeleteDeduction = (deduction) => {
    itemToDelete.value = deduction;
    deletionType.value = "Deduction";
    confirmingDeletion.value = true;
};

const deleteItemConfirmed = () => {
    const routeName =
        deletionType.value === "Bonus"
            ? "payroll.bonuses.destroy"
            : "payroll.deductions.destroy";
    deleteForm.delete(route(routeName, itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
        onError: () => {

        },
    });
};

// --- Formatting ---
const formatCurrency = (value) => {
    if (value === null || value === undefined) return "N/A";
    return new Intl.NumberFormat("en-ET", {
        style: "currency",
        currency: "ETB",
        minimumFractionDigits: 2,
    }).format(value);
};
</script>
