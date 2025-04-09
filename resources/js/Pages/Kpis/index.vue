<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        KPI Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
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
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Search KPIs"
                  type="search"
                />
              </div>
            </div>
            
            <div>
              <Listbox v-model="departmentFilter">
                <div class="relative">
                  <ListboxButton class="relative w-48 bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <span class="block truncate">{{ departmentFilter.name }}</span>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                      <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </span>
                  </ListboxButton>

                  <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <ListboxOptions class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                      <ListboxOption v-slot="{ active, selected }" :value="{ id: 0, name: 'All Departments', code: '' }">
                        <div :class="[active ? 'text-white bg-indigo-600' : 'text-gray-900', 'cursor-default select-none relative py-2 pl-3 pr-9']">
                          <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">All Departments</span>
                          <span v-if="selected" :class="[active ? 'text-white' : 'text-indigo-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
                            <CheckIcon class="h-5 w-5" aria-hidden="true" />
                          </span>
                        </div>
                      </ListboxOption>
                      <ListboxOption v-for="department in departments" :key="department.id" v-slot="{ active, selected }" :value="department">
                        <div :class="[active ? 'text-white bg-indigo-600' : 'text-gray-900', 'cursor-default select-none relative py-2 pl-3 pr-9']">
                          <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">{{ department.name }}</span>
                          <span v-if="selected" :class="[active ? 'text-white' : 'text-indigo-600', 'absolute inset-y-0 right-0 flex items-center pr-4']">
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
          
          <Link href="/kpis/create" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <PlusIcon class="h-5 w-5 mr-2" />
            Create KPI
          </Link>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="kpi in filteredKpis" :key="kpi.id" class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0" :class="getKpiIconClass(kpi.measurement_unit)">
                  <component :is="getKpiIcon(kpi.measurement_unit)" class="h-6 w-6" aria-hidden="true" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">{{ kpi.name }}</dt>
                    <dd class="flex items-baseline">
                      <div class="text-2xl font-semibold text-gray-900">
                        {{ formatKpiValue(kpi.current_value, kpi.measurement_unit) }}
                      </div>
                      <div class="ml-2 flex items-baseline text-sm font-semibold">
                        <span :class="getKpiTrendClass(kpi.trend)">
                          {{ formatKpiTrend(kpi.trend) }}
                        </span>
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
              
              <div class="mt-4">
                <div class="relative pt-1">
                  <div class="flex mb-2 items-center justify-between">
                    <div>
                      <span class="text-xs font-semibold inline-block text-gray-600">
                        Progress
                      </span>
                    </div>
                    <div class="text-right">
                      <span class="text-xs font-semibold inline-block text-gray-600">
                        {{ kpi.progress }}%
                      </span>
                    </div>
                  </div>
                  <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                    <div
                      :style="{ width: `${kpi.progress}%` }"
                      class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 px-4 py-4 sm:px-6">
              <div class="text-sm">
                <div class="flex justify-between items-center">
                  <span class="font-medium text-indigo-600">{{ kpi.frequency }}</span>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="kpi.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                    {{ kpi.points_value }} points
                  </span>
                </div>
                <p class="mt-2 text-gray-500 line-clamp-2">{{ kpi.description }}</p>
                <div class="mt-3 flex justify-between">
                  <Link :href="route('kpis.show', kpi.id)" class="text-indigo-600 hover:text-indigo-900">
                    View details
                  </Link>
                  <div class="flex space-x-2">
                    <Link :href="route('kpis.edit', kpi.id)" class="text-gray-400 hover:text-gray-500">
                      <PencilIcon class="h-5 w-5" />
                    </Link>
                    <button @click="confirmDelete(kpi)" class="text-gray-400 hover:text-red-500">
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
          <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Delete KPI</h2>
            <p class="mt-1 text-sm text-gray-600">
              Are you sure you want to delete this KPI? This action cannot be undone.
            </p>
            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                @click="showDeleteModal = false"
              >
                Cancel
              </button>
              <button
                type="button"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                @click="deleteKpi"
              >
                Delete
              </button>
            </div>
          </div>
        </Modal>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Listbox, ListboxButton, ListboxOptions, ListboxOption } from '@headlessui/vue';
import {
  SearchIcon, 
  PlusIcon,
  ChartBarIcon,
  ClockIcon,
  AdjustmentsIcon, // Replace PercentageIcon with AdjustmentsIcon
  HashtagIcon,
  PencilIcon,
  TrashIcon,
  ChevronDownIcon, 
  CheckIcon,
  ArrowUpIcon,
  ArrowDownIcon
} from "@heroicons/vue/outline";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  kpis: {
    type: Array,
    required: true
  },
  departments: {
    type: Array,
    required: true
  }
});

const search = ref('');
const departmentFilter = ref({ id: 0, name: 'All Departments', code: '' });
const showDeleteModal = ref(false);
const kpiToDelete = ref(null);

const filteredKpis = computed(() => {
  let filtered = props.kpis;
  
  if (search.value) {
    const searchLower = search.value.toLowerCase();
    filtered = filtered.filter(kpi => 
      kpi.name.toLowerCase().includes(searchLower) ||
      kpi.description.toLowerCase().includes(searchLower)
    );
  }
  
  if (departmentFilter.value.id !== 0) {
    filtered = filtered.filter(kpi => 
      kpi.department_id === departmentFilter.value.id
    );
  }
  
  return filtered;
});

const getKpiIconClass = (unit) => {
  const classes = {
    percentage: 'bg-blue-100 text-blue-600',
    number: 'bg-green-100 text-green-600',
    time: 'bg-purple-100 text-purple-600',
    rating: 'bg-yellow-100 text-yellow-600',
    boolean: 'bg-pink-100 text-pink-600'
  };
  return `rounded-md p-3 ${classes[unit] || 'bg-gray-100 text-gray-600'}`;
};

const getKpiIcon = (unit) => {
  const icons = {
    percentage: AdjustmentsIcon, // Replace PercentageIcon with AdjustmentsIcon
    number: HashtagIcon,
    time: ClockIcon,
    rating: ChartBarIcon,
    boolean: CheckIcon
  };
  return icons[unit] || ChartBarIcon;
};

const formatKpiValue = (value, unit) => {
  switch (unit) {
    case 'percentage':
      return `${value}%`;
    case 'time':
      return `${value} days`;
    case 'rating':
      return value.toFixed(1);
    case 'boolean':
      return value ? 'Yes' : 'No';
    default:
      return value;
  }
};

const getKpiTrendClass = (trend) => {
  return trend > 0 
    ? 'text-green-600' 
    : trend < 0 
      ? 'text-red-600' 
      : 'text-gray-600';
};

const formatKpiTrend = (trend) => {
  if (!trend) return '';
  const icon = trend > 0 ? ArrowUpIcon : ArrowDownIcon;
  return `${trend > 0 ? '+' : ''}${trend}%`;
};

const confirmDelete = (kpi) => {
  kpiToDelete.value = kpi;
  showDeleteModal.value = true;
};

const deleteKpi = () => {
  router.delete(route('kpis.destroy', kpiToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      kpiToDelete.value = null;
    }
  });
};
</script>
