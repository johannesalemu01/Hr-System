<script setup>
import { useForm, Link } from "@inertiajs/vue3"; 
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";


import PrimaryButton from "@/Components/PrimaryButton.vue"; 


const props = defineProps({
    departments: {
        type: Array,
        default: () => [],
    },
    positions: {
        type: Array,
        default: () => [],
    },
    roles: {

        type: Array,
        default: () => [],
    },
    errors: Object, 
});


const form = useForm({

    first_name: "",
    last_name: "",
    middle_name: "",
    email: "",
    password: "",
    password_confirmation: "",
    employee_id: "",
    date_of_birth: null,
    gender: null,
    marital_status: null,
    address: "",
    phone_number: "",
    emergency_contact_name: "",
    emergency_contact_phone: "",
    emergency_contact_relationship: "",
    department_id: "",
    position_id: "",
    hire_date: "",
    employment_status: "full_time",
    bank_name: "",
    bank_account_number: "",
    profile_picture: null,
    role: null, 
});


const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_picture = file;
    } else {
        form.profile_picture = null;
    }
};

const submit = () => {
    form.post(route("employees.store"), {
        onSuccess: () => {
            form.reset();
            const fileInput = document.getElementById("profile_picture");
            if (fileInput) {
                fileInput.value = "";
            }
        },
        onError: (errors) => {
            console.error("Form errors:", errors);
        },
    });
};


</script>

<template>
    <AuthenticatedLayout
        title="Add Employee"
        description="Fill in the details to add a new employee"
    >
     
        <form
            @submit.prevent="submit"
            class="space-y-6 bg-white p-6 shadow sm:rounded-lg max-w-4xl mx-auto"
        >
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-4">
                Personal Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label
                        for="first_name"
                        class="block text-sm font-medium text-gray-700"
                    >
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.first_name }"
                    />
                    <span
                        v-if="form.errors.first_name"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.first_name }}</span
                    >
                </div>
                <div>
                    <label
                        for="middle_name"
                        class="block text-sm font-medium text-gray-700"
                        >Middle Name</label
                    >
                    <input
                        id="middle_name"
                        v-model="form.middle_name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.middle_name }"
                    />
                    <span
                        v-if="form.errors.middle_name"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.middle_name }}</span
                    >
                </div>
                <div>
                    <label
                        for="last_name"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.last_name }"
                    />
                    <span
                        v-if="form.errors.last_name"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.last_name }}</span
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.email }"
                    />
                    <span
                        v-if="form.errors.email"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.email }}</span
                    >
                </div>
                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.password }"
                    />
                    <span
                        v-if="form.errors.password"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.password }}</span
                    >
                </div>
                <div>
                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500': form.errors.password_confirmation,
                        }"
                    />
                    <span
                        v-if="form.errors.password_confirmation"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.password_confirmation }}</span
                    >
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label
                        for="date_of_birth"
                        class="block text-sm font-medium text-gray-700"
                        >Date of Birth</label
                    >
                    <input
                        id="date_of_birth"
                        v-model="form.date_of_birth"
                        type="date"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.date_of_birth }"
                    />
                    <span
                        v-if="form.errors.date_of_birth"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.date_of_birth }}</span
                    >
                </div>
                <div>
                    <label
                        for="gender"
                        class="block text-sm font-medium text-gray-700"
                        >Gender</label
                    >
                    <select
                        id="gender"
                        v-model="form.gender"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.gender }"
                    >
                        <option :value="null">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <span
                        v-if="form.errors.gender"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.gender }}</span
                    >
                </div>
                <div>
                    <label
                        for="marital_status"
                        class="block text-sm font-medium text-gray-700"
                        >Marital Status</label
                    >
                    <select
                        id="marital_status"
                        v-model="form.marital_status"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500': form.errors.marital_status,
                        }"
                    >
                        <option :value="null">Select Status</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="divorced">Divorced</option>
                        <option value="widowed">Widowed</option>
                        <option value="other">Other</option>
                    </select>
                    <span
                        v-if="form.errors.marital_status"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.marital_status }}</span
                    >
                </div>
            </div>

            <div>
                <label
                    for="address"
                    class="block text-sm font-medium text-gray-700"
                    >Address</label
                >
                <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    :class="{ 'border-red-500': form.errors.address }"
                ></textarea>
                <span
                    v-if="form.errors.address"
                    class="text-sm text-red-600 mt-1"
                    >{{ form.errors.address }}</span
                >
            </div>

            <div>
                <label
                    for="phone_number"
                    class="block text-sm font-medium text-gray-700"
                    >Phone Number</label
                >
                <input
                    id="phone_number"
                    v-model="form.phone_number"
                    type="tel"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                    :class="{ 'border-red-500': form.errors.phone_number }"
                />
                <span
                    v-if="form.errors.phone_number"
                    class="text-sm text-red-600 mt-1"
                    >{{ form.errors.phone_number }}</span
                >
            </div>

            <h2 class="text-xl font-semibold text-gray-800 border-b pb-4 pt-6">
                Employment Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label
                        for="employee_id"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Employee ID <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="employee_id"
                        v-model="form.employee_id"
                        type="text"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.employee_id }"
                    />
                    <span
                        v-if="form.errors.employee_id"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.employee_id }}</span
                    >
                </div>
                <div>
                    <label
                        for="department_id"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Department <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="department_id"
                        v-model="form.department_id"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.department_id }"
                    >
                        <option value="">Select a department</option>
                        <option
                            v-for="department in props.departments"
                            :key="department.id"
                            :value="department.id"
                        >
                            {{ department.name }}
                        </option>
                    </select>
                    <span
                        v-if="form.errors.department_id"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.department_id }}</span
                    >
                </div>
                <div>
                    <label
                        for="position_id"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Position <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="position_id"
                        v-model="form.position_id"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.position_id }"
                    >
                        <option value="">Select a position</option>
                        <option
                            v-for="position in props.positions"
                            :key="position.id"
                            :value="position.id"
                        >
                            {{ position.title }}
                        </option>
                    </select>
                    <span
                        v-if="form.errors.position_id"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.position_id }}</span
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label
                        for="hire_date"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Hire Date <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="hire_date"
                        v-model="form.hire_date"
                        type="date"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.hire_date }"
                    />
                    <span
                        v-if="form.errors.hire_date"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.hire_date }}</span
                    >
                </div>
                <div>
                    <label
                        for="employment_status"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Employment Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="employment_status"
                        v-model="form.employment_status"
                        required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500': form.errors.employment_status,
                        }"
                    >
                        <option value="full_time">Full-Time</option>
                        <option value="part_time">Part-Time</option>
                        <option value="contract">Contract</option>
                        <option value="intern">Intern</option>
                        <option value="probation">Probation</option>
                    </select>
                    <span
                        v-if="form.errors.employment_status"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.employment_status }}</span
                    >
                </div>
                <div>
                    <label
                        for="role"
                        class="block text-sm font-medium text-gray-700"
                        >Role</label
                    >
                    <select
                        id="role"
                        v-model="form.role"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.role }"
                    >
                        <option :value="null">
                            -- Select Role (Optional) --
                        </option>

                        <option
                            v-for="role in props.roles"
                            :key="role.id"
                            :value="role.name"
                        >
                            {{ role.name }}
                        </option>
                    </select>
                    <span
                        v-if="form.errors.role"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.role }}</span
                    >
                </div>
            </div>


            <h2 class="text-xl font-semibold text-gray-800 border-b pb-4 pt-6">
                Emergency Contact
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label
                        for="emergency_contact_name"
                        class="block text-sm font-medium text-gray-700"
                        >Contact Name</label
                    >
                    <input
                        id="emergency_contact_name"
                        v-model="form.emergency_contact_name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500':
                                form.errors.emergency_contact_name,
                        }"
                    />
                    <span
                        v-if="form.errors.emergency_contact_name"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.emergency_contact_name }}</span
                    >
                </div>
                <div>
                    <label
                        for="emergency_contact_phone"
                        class="block text-sm font-medium text-gray-700"
                        >Contact Phone</label
                    >
                    <input
                        id="emergency_contact_phone"
                        v-model="form.emergency_contact_phone"
                        type="tel"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500':
                                form.errors.emergency_contact_phone,
                        }"
                    />
                    <span
                        v-if="form.errors.emergency_contact_phone"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.emergency_contact_phone }}</span
                    >
                </div>
                <div>
                    <label
                        for="emergency_contact_relationship"
                        class="block text-sm font-medium text-gray-700"
                        >Relationship</label
                    >
                    <input
                        id="emergency_contact_relationship"
                        v-model="form.emergency_contact_relationship"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500':
                                form.errors.emergency_contact_relationship,
                        }"
                    />
                    <span
                        v-if="form.errors.emergency_contact_relationship"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.emergency_contact_relationship }}</span
                    >
                </div>
            </div>

            <h2 class="text-xl font-semibold text-gray-800 border-b pb-4 pt-6">
                Financial Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label
                        for="bank_name"
                        class="block text-sm font-medium text-gray-700"
                        >Bank Name</label
                    >
                    <input
                        id="bank_name"
                        v-model="form.bank_name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.bank_name }"
                    />
                    <span
                        v-if="form.errors.bank_name"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.bank_name }}</span
                    >
                </div>
                <div>
                    <label
                        for="bank_account_number"
                        class="block text-sm font-medium text-gray-700"
                        >Bank Account Number</label
                    >
                    <input
                        id="bank_account_number"
                        v-model="form.bank_account_number"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        :class="{
                            'border-red-500': form.errors.bank_account_number,
                        }"
                    />
                    <span
                        v-if="form.errors.bank_account_number"
                        class="text-sm text-red-600 mt-1"
                        >{{ form.errors.bank_account_number }}</span
                    >
                </div>
            </div>

            <h2 class="text-xl font-semibold text-gray-800 border-b pb-4 pt-6">
                Profile Picture
            </h2>
            <div>
                <label
                    for="profile_picture"
                    class="block text-sm font-medium text-gray-700"
                    >Upload Picture</label
                >
                <input
                    id="profile_picture"
                    type="file"
                    @input="handleFileChange"
                    accept="image/*"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                    :class="{ 'border-red-500': form.errors.profile_picture }"
                />
                <p class="mt-1 text-xs text-gray-500">
                    PNG, JPG, GIF up to 2MB.
                </p>
                <span
                    v-if="form.errors.profile_picture"
                    class="text-sm text-red-600 mt-1"
                    >{{ form.errors.profile_picture }}</span
                >
                <div
                    v-if="
                        form.profile_picture &&
                        typeof form.profile_picture !== 'string'
                    "
                    class="mt-4"
                >
                    <img
                        :src="URL.createObjectURL(form.profile_picture)"
                        alt="Preview"
                        class="h-20 w-20 rounded-full object-cover"
                    />
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <Link
                    :href="route('employees.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? "Saving..." : "Add Employee" }}
                </PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
