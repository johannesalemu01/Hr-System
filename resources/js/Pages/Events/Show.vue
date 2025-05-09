<template>
    <AdminLayout title="Event Details">
        <div class="max-w-2xl mx-auto mt-12">
            <div
                class="bg-gradient-to-br from-blue-50 to-white rounded-2xl shadow-lg p-8 border border-blue-100"
            >
                <div class="flex items-center mb-6">
                    <div
                        class="flex-shrink-0 bg-blue-100 rounded-full p-3 mr-4"
                    >
                        <svg
                            class="h-8 w-8 text-blue-500"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-extrabold text-blue-800">
                        {{ event.title }}
                    </h1>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center text-gray-700">
                        <svg
                            class="h-5 w-5 text-blue-400 mr-2"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <span class="font-semibold mr-1">Date & Time:</span>
                        <span>{{ event.event_date }}</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <svg
                            class="h-5 w-5 text-green-400 mr-2"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 17v-2a4 4 0 018 0v2M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <span class="font-semibold mr-1">Type:</span>
                        <span class="capitalize">{{ event.type }}</span>
                    </div>
                    <div
                        v-if="event.description"
                        class="flex items-start text-gray-700"
                    >
                        <svg
                            class="h-5 w-5 text-purple-400 mr-2 mt-1"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8 10h.01M12 10h.01M16 10h.01M21 16V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2z"
                            />
                        </svg>
                        <div>
                            <span class="font-semibold">Description:</span>
                            <div class="mt-1 text-gray-600 whitespace-pre-line">
                                {{ event.description }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                   
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";

defineProps({
    event: {
        type: Object,
        required: true,
    },
});
</script>
