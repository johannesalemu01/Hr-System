<template>
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
            <p v-if="subtitle" class="mt-1 text-sm text-gray-500">
                {{ subtitle }}
            </p>
            <div class="mt-4">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    subtitle: String,
});
</script>
