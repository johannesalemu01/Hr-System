<template>
    <div class="h-screen flex overflow-hidden bg-gray-50">
        <!-- Sidebar -->
        <!-- <Sidebar
            :is-sidebar-open="isSidebarOpen"
            @close-sidebar="isSidebarOpen = false"
        /> -->

        <!-- Main content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <!-- <Header
                @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
                :user="user"
            /> -->

            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6">
                <button
                            @click="goBack"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        >
<ArrowLeftIcon class="h-5 w-5 inline-block mr-2 text-[#2c6a74] hover:text-[white]"/>
                            Go Back
                        </button>

                <div class="max-w-full mx-0 mt-6">
                    <!-- Page header -->
                    <div
                        v-if="title"
                        class="mb-6 flex items-center justify-between"
                    >
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">
                                {{ title }}
                            </h1>
                            <p
                                v-if="description"
                                class="mt-1 text-sm text-gray-500"
                            >
                                {{ description }}
                            </p>
                        </div>
                        <!-- Go Back Button -->
                      
                    </div>

                    <!-- Page content -->
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { usePage } from "@inertiajs/vue3";
// import Sidebar from "@/Components/Dashboard/Sidebar.vue";
import Header from "@/Components/Dashboard/Header.vue";
import { BellIcon,ArrowLeftIcon } from "@heroicons/vue/outline";

const props = defineProps({
    title: {
        type: String,
        default: "",
    },
    description: {
        type: String,
        default: "",
    },
});

const isSidebarOpen = ref(false);
const { props: pageProps } = usePage();
const user = pageProps.auth.user;

// Define the goBack method
const goBack = () => {
    window.history.back();
};
</script>
