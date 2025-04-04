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
          <div class="flex-1 flex items-center ml-4 md:ml-0">
            <div class="max-w-lg w-full lg:max-w-xs">
              <label for="search" class="sr-only">Search</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <SearchIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                </div>
                <input
                  id="search"
                  name="search"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  placeholder="Search"
                  type="search"
                />
              </div>
            </div>
          </div>
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

