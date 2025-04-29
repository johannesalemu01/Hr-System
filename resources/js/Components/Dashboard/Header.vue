<template>
  <header class="bg-white shadow-sm z-10">
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <!-- Mobile menu button -->
          <button
            type="button"
            class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
            @click="$emit('toggleSidebar')"
          >
            <span class="sr-only">Open sidebar</span>
            <MenuIcon class="h-6 w-6" aria-hidden="true" />
          </button>
          
          <!-- Search -->
         
        </div>
        
        
      </div>
    </div>
  </header>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { BellIcon, SearchIcon } from '@heroicons/vue/outline';
import { Link } from '@inertiajs/vue3';

defineProps({
  user: {
    type: Object,
    default: () => ({})
  }
});

defineEmits(['toggleSidebar']);
</script>

