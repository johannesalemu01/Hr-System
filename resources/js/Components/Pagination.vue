<template>
    <div v-if="links.length > 3" class="flex justify-end items-center py-4">
        <!-- Pagination Links -->
        <nav class="flex items-center space-x-4" aria-label="Pagination">
            <!-- Previous Page Link -->
            <Link
                v-if="links[0].url"
                :href="links[0].url"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-black bg-primary-600 rounded-md hover:bg-primary-700 border border-gray-300" 
            >
                Prev
            </Link>
            <span
                v-else
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-md"
            >
                Prev
            </span>

            <!-- Next Page Link -->
            <Link
                v-if="links[links.length - 1].url"
                :href="links[links.length - 1].url"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-black bg-primary-600 rounded-md hover:bg-primary-700 border border-gray-300"
            >
                Next
            </Link>
            <span
                v-else
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-md "
            >
                Next
            </span>
        </nav>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>
