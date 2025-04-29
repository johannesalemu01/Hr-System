<template>
    <div v-if="links.length > 3" class="flex items-center justify-between py-4">
        <!-- Pagination Info -->
        <div class="text-sm text-gray-500">
            Showing
            <span class="font-medium">{{ meta.from }}</span>
            to
            <span class="font-medium">{{ meta.to }}</span>
            of
            <span class="font-medium">{{ meta.total }}</span>
            results
        </div>

        <!-- Pagination Links -->
        <nav class="flex items-center space-x-4" aria-label="Pagination">
            <!-- Previous Page Link -->
            <Link
                v-if="links[0].url"
                :href="links[0].url"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700"
            >
                Previous
            </Link>
            <span
                v-else
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-md"
            >
                Previous
            </span>

            <!-- Next Page Link -->
            <Link
                v-if="links[links.length - 1].url"
                :href="links[links.length - 1].url"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700"
            >
                Next
            </Link>
            <span
                v-else
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-md"
            >
                Next
            </span>
        </nav>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { ChevronLeftIcon, ChevronRightIcon } from "@heroicons/vue/solid";

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    meta: {
        type: Object,
        default: () => ({
            from: 1,
            to: 1,
            total: 0,
        }),
    },
});

// Filter out the "Previous" and "Next" links for the middle section
const paginationLinks = computed(() => {
    return props.links.slice(1, -1);
});
</script>
