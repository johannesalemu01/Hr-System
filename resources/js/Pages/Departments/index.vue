<template>
  <AuthenticatedLayout >
      <div v-if="flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {{ flash.success }}
      </div>
      <div v-if="flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ flash.error }}
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
          <div class="bg-gradient-to-br from-purple-500 to-indigo-600 overflow-hidden shadow-lg rounded-lg text-white">
              <div class="px-5 py-6">
                  <div class="flex items-center">
                      <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-full p-3">
                          <OfficeBuildingIcon class="h-6 w-6 text-white" aria-hidden="true" />
                      </div>
                      <div class="ml-5 w-0 flex-1">
                          <dl>
                              <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                  Total Departments
                              </dt>
                              <dd class="flex items-baseline">
                                  <div class="text-2xl font-semibold text-white">
                                      {{ stats.totalDepartments }}
                                  </div>
                              </dd>
                          </dl>
                      </div>
                  </div>
              </div>
          </div>

          <div class="bg-gradient-to-br from-pink-500 to-rose-600 overflow-hidden shadow-lg rounded-lg text-white">
              <div class="px-5 py-6">
                  <div class="flex items-center">
                      <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-full p-3">
                          <UsersIcon class="h-6 w-6 text-white" aria-hidden="true" />
                      </div>
                      <div class="ml-5 w-0 flex-1">
                          <dl>
                              <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                  Total Employees
                              </dt>
                              <dd class="flex items-baseline">
                                  <div class="text-2xl font-semibold text-white">
                                      {{ stats.totalEmployees }}
                                  </div>
                              </dd>
                          </dl>
                      </div>
                  </div>
              </div>
          </div>

          <div class="bg-gradient-to-br from-cyan-500 to-blue-600 overflow-hidden shadow-lg rounded-lg text-white">
              <div class="px-5 py-6">
                  <div class="flex items-center">
                      <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-full p-3">
                          <ChartPieIcon class="h-6 w-6 text-white" aria-hidden="true" />
                      </div>
                      <div class="ml-5 w-0 flex-1">
                          <dl>
                              <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                  Avg. Employees Per Dept
                              </dt>
                              <dd class="flex items-baseline">
                                  <div class="text-2xl font-semibold text-white">
                                      {{ stats.avgEmployeesPerDepartment }}
                                  </div>
                              </dd>
                          </dl>
                      </div>
                  </div>
              </div>
          </div>

          <div class="bg-gradient-to-br from-amber-500 to-orange-600 overflow-hidden shadow-lg rounded-lg text-white">
              <div class="px-5 py-6">
                  <div class="flex items-center">
                      <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-full p-3">
                          <TrendingUpIcon class="h-6 w-6 text-white" aria-hidden="true" />
                      </div>
                      <div class="ml-5 w-0 flex-1">
                          <dl>
                              <dt class="text-sm font-medium text-white text-opacity-80 truncate">
                                  Largest Department
                              </dt>
                              <dd class="flex items-baseline">
                                  <div class="text-2xl font-semibold text-white">
                                      {{ stats.largestDepartment ? stats.largestDepartment.name : 'N/A' }}
                                  </div>
                              </dd>
                          </dl>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Search and Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
          <div class="w-full sm:w-auto relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <SearchIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
              </div>
              <input
                  type="text"
                  v-model="search"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  placeholder="Search departments"
                  @keyup.enter="applySearch"
              />
          </div>

          <Link
              v-if="isAdmin"
              :href="route('departments.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
              <PlusIcon class="h-5 w-5 mr-2" />
              Add Department
          </Link>
      </div>

      <!-- Departments Grid -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="department in departments" :key="department.id" 
              class="bg-white overflow-hidden shadow-lg rounded-lg border border-gray-200 hover:shadow-xl transition-shadow duration-300 ease-in-out">
              <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100">
                  <div class="flex items-center justify-between">
                      <div class="flex-1 min-w-0">
                          <h3 class="text-lg font-bold text-gray-900 truncate">
                              {{ department.name }}
                          </h3>
                          <p class="text-sm text-gray-500">
                              Code: {{ department.code }}
                          </p>
                      </div>
                      <div class="flex-shrink-0 flex items-center">
                          <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                              {{ department.employees_count }} Employees
                          </span>
                      </div>
                  </div>
              </div>
              <div class="px-6 py-4">
                  <p class="text-sm text-gray-600 line-clamp-2 mb-4">
                      {{ department.description || 'No description available' }}
                  </p>
                  
                  <div v-if="department.manager" class="flex items-center mb-4">
                      <div class="flex-shrink-0">
                          <img class="h-10 w-10 rounded-full" :src="department.manager.avatar" alt="Manager avatar" />
                      </div>
                      <div class="ml-3">
                          <p class="text-sm font-medium text-gray-900">
                              {{ department.manager.name }}
                          </p>
                          <p class="text-xs text-gray-500">
                              Department Manager
                          </p>
                      </div>
                  </div>
                  <div v-else class="flex items-center mb-4 text-gray-400 italic">
                      <UserIcon class="h-5 w-5 mr-2" />
                      <span class="text-sm">No manager assigned</span>
                  </div>
                  
                  <div class="mt-4 flex justify-between">
                      <Link
                          :href="route('departments.show', department.id)"
                          class="inline-flex items-center text-sm font-medium text-primary-600 hover:text-primary-900"
                      >
                          View details
                          <ChevronRightIcon class="ml-1 h-4 w-4" />
                      </Link>
                      
                      <div v-if="isAdmin" class="flex space-x-2">
                          <Link
                              :href="route('departments.edit', department.id)"
                              class="text-gray-400 hover:text-gray-500"
                          >
                              <PencilIcon class="h-5 w-5" />
                          </Link>
                          <button
                              @click="confirmDelete(department)"
                              class="text-gray-400 hover:text-red-500"
                          >
                              <TrashIcon class="h-5 w-5" />
                          </button>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Empty State -->
          <div v-if="departments.length === 0" class="col-span-full flex flex-col items-center justify-center py-12 bg-white rounded-lg shadow">
              <OfficeBuildingIcon class="h-16 w-16 text-gray-300" />
              <h3 class="mt-2 text-lg font-medium text-gray-900">No departments found</h3>
              <p class="mt-1 text-sm text-gray-500">
                  {{ search ? 'Try adjusting your search criteria.' : 'Get started by creating a new department.' }}
              </p>
              <div v-if="isAdmin" class="mt-6">
                  <Link
                      :href="route('departments.create')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                  >
                      <PlusIcon class="h-5 w-5 mr-2" />
                      Add Department
                  </Link>
              </div>
          </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6">
          <Pagination :links="pagination.links" :meta="pagination.meta" />
      </div>

      <!-- Delete Confirmation Modal -->
      <Modal :show="showDeleteModal" @close="showDeleteModal = false">
          <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900">Delete Department</h2>
              <p class="mt-1 text-sm text-gray-600">
                  Are you sure you want to delete this department? This action cannot be undone.
              </p>
              <div class="mt-6 flex justify-end space-x-3">
                  <button
                      type="button"
                      class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      @click="showDeleteModal = false"
                  >
                      Cancel
                  </button>
                  <button
                      type="button"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      @click="deleteDepartment"
                  >
                      Delete
                  </button>
              </div>
          </div>
      </Modal>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { 
  OfficeBuildingIcon, 
  UsersIcon, 
  ChartPieIcon, 
  TrendingUpIcon,
  SearchIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ChevronRightIcon,
  UserIcon
} from '@heroicons/vue/outline';

const props = defineProps({
  departments: {
      type: Array,
      required: true
  },
  stats: {
      type: Object,
      required: true
  },
  filters: {
      type: Object,
      default: () => ({
          search: ''
      })
  },
  isAdmin: {
      type: Boolean,
      default: false
  },
  pagination: {
      type: Object,
      required: true
  }
});

const page = usePage();
const flash = computed(() => page.props.flash);

// Search functionality
const search = ref(props.filters.search);

const applySearch = () => {
  router.get(route('departments.index'), {
      search: search.value
  }, {
      preserveState: true,
      replace: true
  });
};

// Delete functionality
const showDeleteModal = ref(false);
const departmentToDelete = ref(null);

const confirmDelete = (department) => {
  departmentToDelete.value = department;
  showDeleteModal.value = true;
};

const deleteDepartment = () => {
  router.delete(route('departments.destroy', departmentToDelete.value.id), {
      onSuccess: () => {
          showDeleteModal.value = false;
          departmentToDelete.value = null;
      }
  });
};
</script>

<style scoped>
/* Add any additional custom styles here */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>