<template>
    <AuthenticatedLayout title="Create Department" description="Add a new department to your organization">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100">
                    <h3 class="text-lg font-bold text-gray-900">
                        Create New Department
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Fill in the details below to create a new department
                    </p>
                </div>
                
                <form @submit.prevent="submitForm" class="p-6 space-y-6">
                    <!-- Department Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            Department Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            placeholder="e.g. Human Resources"
                            required
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>
                    

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700">
                            Department Code <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="code"
                            v-model="form.code"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            placeholder="e.g. HR"
                            required
                        />
                        <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                            {{ form.errors.code }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            A short, unique code for the department (max 10 characters)
                        </p>
                    </div>
                    
                    <!-- Department Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                            placeholder="Describe the department's purpose and responsibilities"
                        ></textarea>
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                            {{ form.errors.description }}
                        </div>
                    </div>
                    
                    <!-- Department Manager -->
                    <div>
                        <label for="manager" class="block text-sm font-medium text-gray-700">
                            Department Manager
                        </label>
                        <select
                            id="manager"
                            v-model="form.manager_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                        >
                            <option value="">Select a manager</option>
                            <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                                {{ manager.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.manager_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.manager_id }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Optional: Assign a manager to this department
                        </p>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <Link
                            :href="route('departments.index')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 "
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :href="route('departments.index')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create Department</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    managers: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    name: '',
    code: '',
    description: '',
    manager_id: '',
});

const submitForm = () => {
    form.post(route('departments.store'));
};
</script>

