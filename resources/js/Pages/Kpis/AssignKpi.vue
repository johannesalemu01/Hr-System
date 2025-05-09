<template>
    <AdminLayout title="Assign KPI">
        <div class="max-w-2xl mx-auto mt-10 bg-white shadow rounded-lg p-8">
            <h2
                class="text-2xl font-bold mb-2 text-[#1098ad] flex items-center gap-2"
            >
                <span
                    class="inline-block bg-primary-100 text-[#1098ad] px-2 py-1 rounded text-sm font-semibold"
                    >Assign KPI</span
                >
                <span>{{ kpi.name }}</span>
            </h2>
            <div class="mb-4 text-gray-600">
                <div><strong>Department:</strong> {{ kpi.department }}</div>
                <div>
                    <strong>Measurement Unit:</strong>
                    {{ kpi.measurement_unit }}
                </div>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Employee Search & Select -->
                <div>
                    <label class="block mb-1 font-semibold"
                        >Select Employee</label
                    >
                    <!-- <input
                        v-model="employeeSearch"
                        type="text"
                        placeholder="Search employee by name or ID"
                        class="w-full border rounded px-3 py-2 mb-2"
                    /> -->
                    <select
                        v-model="form.employee_id"
                        class="w-full border rounded px-3 py-2"
                        required
                    >
                        <option value="">-- Select Employee --</option>
                        <option
                            v-for="emp in filteredEmployees"
                            :key="emp.id"
                            :value="emp.id"
                        >
                            {{ emp.name }} ({{ emp.employee_id }}) -
                            {{ emp.department }}
                        </option>
                    </select>
                    <div
                        v-if="errors.employee_id"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ errors.employee_id }}
                    </div>
                </div>
                <!-- Target Value -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-1 font-semibold"
                            >Target Value</label
                        >
                        <input
                            v-model="form.target_value"
                            type="number"
                            step="any"
                            class="w-full border rounded px-3 py-2"
                            required
                        />
                        <div
                            v-if="errors.target_value"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.target_value }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold"
                            >Minimum Value</label
                        >
                        <input
                            v-model="form.minimum_value"
                            type="number"
                            step="any"
                            class="w-full border rounded px-3 py-2"
                            required
                        />
                        <div
                            v-if="errors.minimum_value"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.minimum_value }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold"
                            >Maximum Value</label
                        >
                        <input
                            v-model="form.maximum_value"
                            type="number"
                            step="any"
                            class="w-full border rounded px-3 py-2"
                            required
                        />
                        <div
                            v-if="errors.maximum_value"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.maximum_value }}
                        </div>
                    </div>
                </div>
                <!-- Weight -->
                <div>
                    <label class="block mb-1 font-semibold"
                        >Weight (0.1 - 5)</label
                    >
                    <input
                        v-model="form.weight"
                        type="number"
                        step="0.1"
                        min="0.1"
                        max="5"
                        class="w-full border rounded px-3 py-2"
                        required
                    />
                    <div v-if="errors.weight" class="text-red-500 text-xs mt-1">
                        {{ errors.weight }}
                    </div>
                </div>
                <!-- Period -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-semibold"
                            >Start Date</label
                        >
                        <input
                            v-model="form.start_date"
                            type="date"
                            class="w-full border rounded px-3 py-2"
                            required
                        />
                        <div
                            v-if="errors.start_date"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.start_date }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 font-semibold">End Date</label>
                        <input
                            v-model="form.end_date"
                            type="date"
                            class="w-full border rounded px-3 py-2"
                            required
                        />
                        <div
                            v-if="errors.end_date"
                            class="text-red-500 text-xs mt-1"
                        >
                            {{ errors.end_date }}
                        </div>
                    </div>
                </div>
                <!-- Notes -->
                <div>
                    <label class="block mb-1 font-semibold"
                        >Notes (optional)</label
                    >
                    <textarea
                        v-model="form.notes"
                        class="w-full border rounded px-3 py-2"
                        rows="2"
                        placeholder="Any additional notes..."
                    ></textarea>
                    <div v-if="errors.notes" class="text-red-500 text-xs mt-1">
                        {{ errors.notes }}
                    </div>
                </div>
                <!-- Submit -->
                <div class="flex items-center gap-4">
                    <button
                        type="submit"
                        class="bg-[#1098ad] hover:bg-primary-700 text-white font-semibold px-6 py-2 rounded shadow"
                        :disabled="processing"
                    >
                        <span v-if="processing">Assigning...</span>
                        <span v-else>Assign KPI</span>
                    </button>
                    <span v-if="success" class="text-green-600 font-semibold"
                        >KPI assigned successfully!</span
                    >
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const props = defineProps({
    employees: Array,
    kpi: Object,
});

const today = new Date().toISOString().slice(0, 10);
const nextMonth = new Date();
nextMonth.setMonth(nextMonth.getMonth() + 1);
const nextMonthStr = nextMonth.toISOString().slice(0, 10);

const form = useForm({
    employee_id: "",
    kpi_id: props.kpi.id,
    target_value: "",
    minimum_value: "",
    maximum_value: "",
    weight: 1,
    start_date: today,
    end_date: nextMonthStr,
    notes: "",
});

const errors = ref({});
const processing = ref(false);
const success = ref(false);

// Employee search/filter
const employeeSearch = ref("");
const filteredEmployees = computed(() => {
    if (!employeeSearch.value) return props.employees;
    const term = employeeSearch.value.toLowerCase();
    return props.employees.filter(
        (e) =>
            e.name.toLowerCase().includes(term) ||
            (e.employee_id && e.employee_id.toLowerCase().includes(term)) ||
            (e.department && e.department.toLowerCase().includes(term))
    );
});

// Auto-fill min/max when target changes
watch(
    () => form.target_value,
    (val) => {
        if (val !== "" && !form.minimum_value) form.minimum_value = val;
        if (val !== "" && !form.maximum_value) form.maximum_value = val;
    }
);

const submit = () => {
    processing.value = true;
    success.value = false;
    errors.value = {};
    form.post(route("kpis.store-employee-kpi"), {
        onSuccess: () => {
            success.value = true;
            form.reset(
                "employee_id",
                "target_value",
                "minimum_value",
                "maximum_value",
                "weight",
                "notes"
            );
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<style scoped>
.bg-primary-100 {
    background-color: #e0e7ff;
}
.text-primary-700 {
    color: #3730a3;
}
.bg-primary-600 {
    background-color: #4f46e5;
}
.bg-primary-700 {
    background-color: #3730a3;
}
</style>
