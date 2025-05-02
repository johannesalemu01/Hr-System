<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3"; // Import usePage
import { ref, computed } from "vue"; 

const activeTab = ref("profile"); 

const props = defineProps({
    user: Object,
    company: Object,
    isEmployee: Boolean,
});


const availableTabs = computed(() => {
    const baseTabs = [{ id: "profile", name: "Profile Settings" }];
    if (!props.isEmployee) {

        baseTabs.push({ id: "company", name: "Company Settings" });
    }
    return baseTabs;
});


const page = usePage();
const successMessage = page.props.flash?.success || "";


const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
});


const companyForm = useForm({
    name: props.company?.name || "Omishtu-joy",
    address: props.company?.address || "megenagna",
    phone: props.company?.phone || "",
    email: props.company?.email || "omishtu@gmail.com",
});

const updateProfile = () => {
    console.log("Profile data being sent:", profileForm);
    profileForm.post(route("settings.update-profile"), {
        onSuccess: () => {
            page.props.flash.success =
                "Profile information updated successfully!";
           
        },
      
    });
};

const updateCompany = () => {

    if (props.isEmployee) {
        console.error("Attempted to update company settings as an employee.");
        return;
    }
    console.log("Company data being sent:", companyForm);
    companyForm.post(route("settings.update-company"), {
        onSuccess: () => {
            page.props.flash.success =
                "Company information updated successfully!";
        },
    });
};
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Success Message -->
                    <div
                        v-if="successMessage"
                        class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg"
                        role="alert" 
                    >
                        {{ successMessage }}
                    </div>

                    <!-- Settings Header -->
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-gray-800">
                            Settings
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage your account settings and preferences.
                        </p>
                    </div>


                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button
                                v-for="tab in availableTabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    activeTab === tab.id
                                        ? 'border-primary-500 text-primary-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm',
                                ]"
                            >
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>


                    <div class="p-6">

                        <div v-if="activeTab === 'profile'" class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Profile Information
                            </h3>
                            <form
                                @submit.prevent="updateProfile"
                                class="space-y-4"
                            >
                                <div>
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Name</label
                                    >
                                    <input
                                        id="name"
                                        type="text"
                                        v-model="profileForm.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p
                                        v-if="profileForm.errors.name"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ profileForm.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="email"
                                        class="block text-sm font-medium text-gray-700"
                                        >Email</label
                                    >
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="profileForm.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p
                                        v-if="profileForm.errors.email"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ profileForm.errors.email }}
                                    </p>
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50" 
                                    :disabled="profileForm.processing"
                                >
                                    Save
                                </button>
                            </form>
                        </div>


                        <div
                            v-if="!isEmployee && activeTab === 'company'"
                            class="space-y-6"
                        >
                            <h3 class="text-lg font-medium text-gray-900">
                                Company Information
                            </h3>
                            <form
                                @submit.prevent="updateCompany"
                                class="space-y-4"
                            >
                                <div>
                                    <label
                                        for="company_name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Company Name</label
                                    >
                                    <input
                                        id="company_name"
                                        type="text"
                                        v-model="companyForm.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p
                                        v-if="companyForm.errors.name"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ companyForm.errors.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="address"
                                        class="block text-sm font-medium text-gray-700"
                                        >Address</label
                                    >
                                    <input
                                        id="address"
                                        type="text"
                                        v-model="companyForm.address"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p
                                        v-if="companyForm.errors.address"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ companyForm.errors.address }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="phone"
                                        class="block text-sm font-medium text-gray-700"
                                        >Phone</label
                                    >
                                    <input
                                        id="phone"
                                        type="text"
                                        v-model="companyForm.phone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p
                                        v-if="companyForm.errors.phone"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ companyForm.errors.phone }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        for="email"
                                        class="block text-sm font-medium text-gray-700"
                                        >Email</label
                                    >
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="companyForm.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p
                                        v-if="companyForm.errors.email"
                                        class="text-sm text-red-600 mt-1"
                                    >
                                        {{ companyForm.errors.email }}
                                    </p>
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50" 
                                    :disabled="companyForm.processing"
                                >
                                    Save
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
