<template>
  <AdminLayout :title="kpi.name" description="KPI details and performance">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- KPI Details -->
      <div class="lg:col-span-1">
        <DashboardCard title="KPI Information">
          <div class="space-y-4">
            <div>
              <h3 class="text-sm font-medium text-gray-500">Description</h3>
              <p class="mt-1 text-sm text-gray-900">{{ kpi.description }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Measurement Unit</h3>
                <p class="mt-1 text-sm text-gray-900">{{ kpi.measurement_unit }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Frequency</h3>
                <p class="mt-1 text-sm text-gray-900">{{ kpi.frequency }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Department</h3>
                <p class="mt-1 text-sm text-gray-900">{{ kpi.department }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Position</h3>
                <p class="mt-1 text-sm text-gray-900">{{ kpi.position || 'All Positions' }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-gray-500">Points Value</h3>
                <p class="mt-1 text-sm text-gray-900">{{ kpi.points_value }}</p>
              </div>
              <div>
                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                <p class="mt-1">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="kpi.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                    {{ kpi.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </p>
              </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200">
              <div class="flex justify-between">
                <Link :href="route('kpis.edit', kpi.id)" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                  <PencilIcon class="h-4 w-4 mr-1" />
                  Edit KPI
                </Link>
                <Link :href="route('kpis.assign')" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                  <UserPlusIcon class="h-4 w-4 mr-1" />
                  Assign to Employee
                </Link>
              </div>
            </div>
          </div>
        </DashboardCard>
        
        <!-- KPI Stats -->
        <DashboardCard title="KPI Statistics" class="mt-6">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-md">
              <div class="text-sm font-medium text-gray-500">Avg. Achievement</div>
              <div class="mt-1 text-2xl font-semibold" :class="getAchievementClass(stats.avgAchievement)">
                {{ stats.avgAchievement }}%
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-md">
              <div class="text-sm font-medium text-gray-500">Assigned To</div>
              <div class="mt-1 text-2xl font-semibold text-gray-900">
                {{ stats.employeeCount }}
              </div>
            </div>
          </div>
          
          <div class="mt-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Performance Trend</h3>
            <div class="h-40">
              <LineChart 
                :labels="trendData.labels" 
                :datasets="[
                  {
                    label: 'Achievement %',
                    data: trendData.data,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                  }
                ]" 
              />
            </div>
          </div>
        </DashboardCard>
      </div>
      
      <!-- Employee KPIs and Records -->
      <div class="lg:col-span-2">
        <!-- Employee Assignments -->
        <DashboardCard title="Assigned Employees">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Employee
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Target
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Period
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="employeeKpis.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    No employees assigned to this KPI.
                  </td>
                </tr>
                <tr v-for="empKpi in employeeKpis" :key="empKpi.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ empKpi.employee.name }}</div>
                    <div class="text-sm text-gray-500">{{ empKpi.employee.department }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ empKpi.target_value }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(empKpi.start_date) }} - {{ formatDate(empKpi.end_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusClass(empKpi.status)">
                      {{ empKpi.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <Link 
                      :href="route('kpis.record', empKpi.id)" 
                      class="text-primary-600 hover:text-primary-900"
                      v-if="empKpi.status === 'active'"
                    >
                      Record
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </DashboardCard>
        
        <!-- Recent Records -->
        <DashboardCard title="Recent KPI Records" class="mt-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Employee
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actual
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Target
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
                <tr v-if="kpiRecords.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    No records found for this KPI.
                  </td>
                </tr>
                <tr v-for="record in kpiRecords" :key="record.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.employee }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(record.record_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ record.actual_value }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ record.target_value }}
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
import { Link } from '@inertiajs/vue3';
import { 
  PencilIcon, UserPlusIcon
} from '@heroicons/vue/outline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DashboardCard from '@/Components/Dashboard/DashboardCard.vue';
import LineChart from '@/Components/Dashboard/Charts/LineChart.vue';

const props = defineProps({
  kpi: {
    type: Object,
    required: true
  },
  employeeKpis: {
    type: Array,
    required: true
  },
  kpiRecords: {
    type: Array,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  trendData: {
    type: Object,
    required: true
  }
});

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

// Get achievement class
const getAchievementClass = (percentage) => {
  if (percentage >= 90) return 'text-green-600';
  if (percentage >= 70) return 'text-blue-600';
  if (percentage >= 50) return 'text-yellow-600';
  return 'text-red-600';
};
</script>