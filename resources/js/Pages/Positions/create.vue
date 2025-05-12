<template>
    <AuthenticatedLayout>
        <div class="max-w-xl mx-auto bg-white shadow rounded p-6">
            <h2 class="text-2xl font-bold mb-4">Add Position</h2>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block mb-1">Title</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full border rounded px-3 py-2"
                    />
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Department</label>
                    <select
                        v-model="form.department_id"
                        class="w-full border rounded px-3 py-2"
                    >
                        <option value="">Select department</option>
                        <option
                            v-for="dept in departments"
                            :key="dept.id"
                            :value="dept.id"
                        >
                            {{ dept.name }}
                        </option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Min Salary</label>
                    <input
                        v-model="form.min_salary"
                        type="number"
                        class="w-full border rounded px-3 py-2"
                    />
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Max Salary</label>
                    <input
                        v-model="form.max_salary"
                        type="number"
                        class="w-full border rounded px-3 py-2"
                    />
                </div>
                <div class="mb-4">
                    <label class="block mb-1">Description</label>
                    <textarea
                        v-model="form.description"
                        class="w-full border rounded px-3 py-2"
                    ></textarea>
                </div>
                <button
                    type="submit"
                    class="bg-primary-600 text-black px-4 py-2 rounded border border-gray-300"
                >
                    Create
                </button>
                  <button
                  type="button"
                  class="bg-primary-600 text-black px-4 py-2 rounded border border-gray-300 ml-2"
                  @click="$inertia.visit(route('positions.index'))"
                >
                  Cancel
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({ departments: Array });

const form = useForm({
    title: "",
    department_id: "",
    min_salary: "",
    max_salary: "",
    description: "",
});

const submit = () => {
    form.post(route("positions.store"));
};
</script>
