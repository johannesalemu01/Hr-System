<template>
    <div class="flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-200">
            <li v-for="event in events" :key="event.id" class="py-4">
                <div class="flex items-center space-x-4">
                    <div
                        :class="`flex-shrink-0 ${event.iconColor} rounded-md p-2`"
                    >
                        <component
                            :is="getIconComponent(event.icon)"
                            class="h-5 w-5"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ event.title }}
                        </p>
                        <div class="flex items-center text-sm text-gray-500">
                            <CalendarIcon
                                class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400"
                                aria-hidden="true"
                            />
                            <p>{{ event.date }} · {{ event.time }}</p>
                        </div>
                    </div>
                    <div>
                        <button
                            @click="openModal(event)"
                            class="inline-flex items-center shadow-sm px-2.5 py-0.5 border border-gray-300 text-sm leading-5 font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50"
                        >
                            View
                        </button>
                    </div>
                </div>
            </li>
        </ul>

        <!-- Event Details Modal -->
        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ selectedEvent?.title }}
                </h2>
                <div class="mt-4 space-y-2">
                    <p class="text-sm text-gray-500">
                        <strong>Date:</strong> {{ selectedEvent?.date }}
                    </p>
                    <p class="text-sm text-gray-500">
                        <strong>Time:</strong> {{ selectedEvent?.time }}
                    </p>
                    <p class="text-sm text-gray-500">
                        <strong>Type:</strong> {{ selectedEvent?.type }}
                    </p>
                    <p class="text-sm text-gray-500">
                        <strong>Description:</strong>
                        {{ selectedEvent?.description }}
                    </p>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                        @click="closeModal"
                    >
                        Close
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-blue-600 shadow-sm text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50"
                        @click="emitEditEvent"
                    >
                        Edit
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-red-600 shadow-sm text-sm font-medium rounded-md text-red-600 bg-white hover:bg-red-50"
                        @click="emitDeleteEvent"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref } from "vue";
import {
    ChartBarIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    UserAddIcon,
    ClipboardCheckIcon,
} from "@heroicons/vue/outline";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
});

const getIconComponent = (icon) => {
    const icons = {
        "chart-bar": ChartBarIcon,
        calendar: CalendarIcon,
        "currency-dollar": CurrencyDollarIcon,
        "user-add": UserAddIcon,
        "clipboard-check": ClipboardCheckIcon,
    };
    return icons[icon] || CalendarIcon;
};

// Define emitted events
const emit = defineEmits(["edit", "delete"]);

// Modal state
const showModal = ref(false);
const selectedEvent = ref(null);

// Open modal
const openModal = (event) => {
    if (!event || !event.id) {
        console.error("Invalid event object passed to openModal:", event);
        return;
    }
    selectedEvent.value = event;
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    selectedEvent.value = null;
    showModal.value = false;
};

// Emit edit event
const emitEditEvent = () => {
    if (selectedEvent.value && selectedEvent.value.id) {
        console.log("Emitting edit event with:", selectedEvent.value); // <-- Add this log
        const eventToEdit = { ...selectedEvent.value }; // Clone to avoid issues if modal closes too fast
        closeModal();
        emit("edit", eventToEdit);
    } else {
        console.error("Invalid event object:", selectedEvent.value);
    }
};

// Emit delete event
const emitDeleteEvent = () => {
    if (selectedEvent.value && selectedEvent.value.id) {
        const eventIdToDelete = selectedEvent.value.id; // Store the ID first
        closeModal(); // Then close the modal
        emit("delete", eventIdToDelete); // Emit the stored ID
    } else {
        console.error(
            "Cannot delete: Invalid or null selectedEvent:",
            selectedEvent.value
        );
    }
};
</script>
