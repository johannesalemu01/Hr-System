<template>
  <AuthenticatedLayout>
    <!-- Action buttons -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex-1 flex items-center space-x-4">
        <div class="w-64">
          <label for="search" class="sr-only">Search</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <SearchIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </div>
            <input
              id="search"
              v-model="search"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="Search employees"
              type="search"
            />
          </div>
        </div>
        
        <div>
          <Listbox v-model="departmentFilter">
            <div class="relative">
              <ListboxButton class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                <span class="block truncate">{{ departmentFilter.name }}</span>
                <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                  <SelectorIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                </span>
              </ListboxButton>

              <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <ListboxOptions class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                  <ListboxOption v-for="department in departments" :key="department.id" v-slot="{ active, selected }" :value="department">
                    <div :class="[active ? 'text-white bg-primary-600' : 'text-gray-900', 'cursor-default select-none relative py-2 pl-3 pr-9']">
                      <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ department.name }}</span>
                      <span v-if="selected" :class="[active ? 'text-white' : 'text-primary-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                      </span>
                    </div>
                  </ListboxOption>
                </ListboxOptions>
              </transition>
            </div>
          </Listbox>
        </div>
      </div>
      
      <Link :href="'/employees/create'" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        <UserAddIcon class="h-5 w-5 mr-2" />
        Add Employee
      </Link>
    </div>

    <!-- Employee table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <ul role="list" class="divide-y divide-gray-200">
        <li v-for="employee in filteredEmployees" :key="employee.id">
          <Link :href="route('employees.show', employee.id)" class="block hover:bg-gray-50">
            <div class="px-4 py-4 sm:px-6">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img class="h-10 w-10 rounded-full" :src="employee.profile_picture || '/placeholder.svg?height=40&width=40'" alt="" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-primary-600">{{ employee.full_name }}</div>
                    <div class="text-sm text-gray-500">{{ employee.position }}</div>
                  </div>
                </div>
                <div class="flex items-center">
                  <div class="mr-6">
                    <div class="text-sm text-gray-900">{{ employee.department }}</div>
                    <div class="text-sm text-gray-500">{{ employee.employee_id }}</div>
                  </div>
                  <div class="flex items-center">
                    <MailIcon class="h-5 w-5 text-gray-400 mr-1" />
                    <div class="text-sm text-gray-500">{{ employee.email }}</div>
                  </div>
                </div>
              </div>
            </div>
          </Link>
        </li>
      </ul>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
      <Pagination :links="employees.links" />
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import { SearchIcon, UserAddIcon, MailIcon, SelectorIcon, CheckIcon } from '@heroicons/vue/outline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  employees: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  departments: {
    type: Array,
    default: () => []
  }
});

const search = ref('');
const departmentFilter = ref({ id: 0, name: 'All Departments' });

const filteredEmployees = computed(() => {
  let filtered = props.employees.data;
  
  // Apply search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(employee => 
      employee.full_name.toLowerCase().includes(searchLower) ||
      employee.email.toLowerCase().includes(searchLower) ||
      employee.employee_id.toLowerCase().includes(searchLower)
    );
  }
  
  // Apply department filter
  if (departmentFilter.value.id !== 0) {
    filtered = filtered.filter(employee => 
      employee.department_id === departmentFilter.value.id
    );
  }
  
  return filtered;
});
</script>

