<template>
  <Link
    :href="href"
    :class="[
      active
        ? 'bg-primary-800 text-white'
        : 'text-primary-100 hover:bg-primary-700',
      'group flex items-center px-3 py-2 text-sm font-medium rounded-md'
    ]"
  >
    <slot />
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  }
});
</script>

