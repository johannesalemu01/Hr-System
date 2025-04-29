<template>
  <AdminLayout title="Record KPI" :description="`Record KPI value for ${employeeKpi.employee.name}`">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- KPI Information -->
      <div class="lg:col-span-1">
        <DashboardCard title="KPI Details">
          <div class="space-y-4">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Employee</h3>
              <p class="mt-1 text-sm text-gray-900">{{ employeeKpi.employee.name }}</p>
              <p class="text-xs text-gray-500">{{ employeeKpi.employee.employee_id }}</p>
            </div>
            
            <div>
              <h3 class="text-sm font-medium text-gray-500">KPI</h3>
              <p class="mt-1 text-sm text-gray-900">{{ employeeKpi.kpi.name }}</p>
              <p class="text-xs text-gray-500">Measurement: {{ employeeKpi.kpi.measurement_unit }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Target Value</h3>
                <p class="mt-1 text-sm text-gray-900">{{ employeeKpi.target_value }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Weight</h3>
                <p class="mt-1 text-sm text-gray-900">{{ employeeKpi.weight }}x</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Minimum</h3>
                <p class="mt-1 text-sm text-gray-900">{{ employeeKpi.minimum_value }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Maximum</h3>
                <p class="mt-1 text-sm text-gray-900">{{ employeeKpi.maximum_value }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Start Date</h3>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(employeeKpi.start_date) }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">End Date</h3>
                <p class="mt-1 text-sm text-gray-900">{{ formatDate(employeeKpi.end_date) }}</p>
              </div>
            </div>
            
            <div>
              <h3 class="text-sm font-medium text-gray-500">Status</h3>
              <p class="mt-1">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(employeeKpi.status)">
                  {{ employeeKpi.status }}
                </span>
              </p>
            </div>
          </div>
        </DashboardCard>
      </div>
      
      <!-- Record Form -->
      <div class="lg:col-span-2">
        <DashboardCard title="Record KPI Value">
          <form @submit.prevent="submitRecord">
            <div class="space-y-4">
              <div>
                <label for="actual_value" class="block text-sm font-medium text-gray-700">
                  Actual Value ({{ employeeKpi.kpi.measurement_unit }})
                </label>
                <input
                  type="number"
                  id="actual_value"
                  v-model="form.actual_value"
                  step="0.01"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  :min="employeeKpi.minimum_value"
                  :max="employeeKpi.maximum_value"
                />
                <div v-if="form.errors.actual_value" class="text-red-500 text-sm mt-1">
                  {{ form.errors.actual_value }}
                </div>
              </div>
              
              <div>
                <label for="record_date" class="block text-sm font-medium text-gray-700">
                  Record Date
                </label>
                <input
                  type="date"
                  id="record_date"
                  v-model="form.record_date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  :min="employeeKpi.start_date"
                  :max="today"
                />
                <div v-if="form.errors.record_date" class="text-red-500 text-sm mt-1">
                  {{ form.errors.record_date }}
                </div>
              </div>
              
              <div>
                <label for="comments" class="block text-sm font-medium text-gray-700">
                  Comments
                </label>
                <textarea
                  id="comments"
                  v-model="form.comments"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                  placeholder="Add any comments or notes about this KPI record"
                ></textarea>
                <div v-if="form.errors.comments" class="text-red-500 text-sm mt-1">
                  {{ form.errors.comments }}
                </div>
              </div>
              
              <!-- Achievement Preview -->
              <div v-if="form.actual_value" class="p-4 bg-gray-50 rounded-md">
                <h3 class="text-sm font-medium text-gray-700">Achievement Preview</h3>
                <div class="mt-2 flex items-center">
                  <div class="mr-2 text-sm font-medium" :class="getAchievementClass(achievementPercentage)">
                    {{ achievementPercentage }}%
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div 
                      class="h-2.5 rounded-full" 
                      :class="getProgressColorClass(achievementPercentage)"
                      :style="{ width: `${achievementPercentage}%` }"
                    ></div>
                  </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  Estimated points: {{ estimatedPoints }} ({{ employeeKpi.kpi.points_value }} × {{ employeeKpi.weight }} × {{ achievementPercentage / 100 }})
                </div>
              </div>
              
              <div class="flex justify-end space-x-3">
                <Link
                  :href="route('kpis.employee-kpis')"
                  class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 "
                  :disabled="form.processing"
                >
                  <span v-if="form.processing">Processing...</span>
                  <span v-else>Record KPI</span>
                </button>
              </div>
            </div>
          </form>
        </DashboardCard>
        
        <!-- Previous Records -->
        <DashboardCard title="Previous Records" class="mt-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Value
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Achievement
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Points
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="previousRecords.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                    No previous records found.
                  </td>
                </tr>
                <tr v-for="record in previousRecords" :key="record.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(record.record_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.actual_value }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getAchievementClass(record.achievement_percentage)">
                      {{ record.achievement_percentage }}%
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ record.points_earned }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </DashboardCard>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DashboardCard from '@/Components/Dashboard/DashboardCard.vue';

const props = defineProps({
  employeeKpi: {
    type: Object,
    required: true
  },
  previousRecords: {
    type: Array,
    required: true
  }
});

// Form handling
const form = useForm({
  employee_kpi_id: props.employeeKpi.id,
  actual_value: '',
  record_date: new Date().toISOString().split('T')[0],
  comments: ''
});

// Today's date for max date input
const today = new Date().toISOString().split('T')[0];

// Format date
const formatDate = (dateString) => {
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Get status class
const getStatusClass = (status) => {
  switch (status) {
    case 'active':
      return 'bg-green-100 text-green-800';
    case 'completed':
      return 'bg-blue-100 text-blue-800';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

// Calculate achievement percentage
const achievementPercentage = computed(() => {
  if (!form.actual_value) return 0;
  
  const percentage = (form.actual_value / props.employeeKpi.target_value) * 100;
  return Math.min(Math.round(percentage), 100); // Cap at 100%
});

// Calculate estimated points
const estimatedPoints = computed(() => {
  return Math.round((achievementPercentage.value / 100) * props.employeeKpi.kpi.points_value * props.employeeKpi.weight);
});

// Get achievement class
const getAchievementClass = (percentage) => {
  if (percentage >= 90) return 'text-green-600';
  if (percentage >= 70) return 'text-blue-600';
  if (percentage >= 50) return 'text-yellow-600';
  return 'text-red-600';
};

// Get progress color class
const getProgressColorClass = (percentage) => {
  if (percentage >= 90) return 'bg-green-600';
  if (percentage >= 70) return 'bg-blue-600';
  if (percentage >= 50) return 'bg-yellow-500';
  return 'bg-red-600';
};

// Submit record
const submitRecord = () => {
  form.post(route('kpis.store-record'));
};
</script>