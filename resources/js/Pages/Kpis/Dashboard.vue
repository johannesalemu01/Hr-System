<template>
  <AdminLayout title="KPI Dashboard" description="Overview of KPI performance across the organization">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
      <StatCard 
        title="Total KPIs" 
        :value="overallStats.total_kpis" 
        icon="chart-bar" 
        color="blue"
      />
      <StatCard 
        title="Active KPIs" 
        :value="overallStats.active_kpis" 
        icon="check-circle" 
        color="green"
      />
      <StatCard 
        title="Active Assignments" 
        :value="overallStats.active_employee_kpis" 
        icon="user-group" 
        color="purple"
      />
      <StatCard 
        title="Avg. Achievement" 
        :value="overallStats.avg_achievement + '%'" 
        icon="chart-pie" 
        color="amber"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Performance Trend Chart -->
      <DashboardCard title="Overall KPI Performance Trend" subtitle="Last 6 months">
        <div class="h-80">
          <LineChart 
            :labels="performanceTrend.labels" 
            :datasets="[
              {
                label: 'Achievement %',
                data: performanceTrend.data,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
              }
            ]" 
          />
        </div>
      </DashboardCard>

      <!-- Department Performance -->
      <DashboardCard title="Department Performance" subtitle="Average achievement by department">
        <div class="h-80">
          <BarChart 
            :labels="departmentPerformance.map(d => d.name)" 
            :datasets="[
              {
                label: 'Achievement %',
                data: departmentPerformance.map(d => d.achievement),
                backgroundColor: '#6366f1',
              }
            ]" 
          />
        </div>
      </DashboardCard>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Top Performing Employees -->
      <DashboardCard title="Top Performing Employees" class="lg:col-span-1">
        <ul class="divide-y divide-gray-200">
          <li v-for="(employee, index) in topEmployees" :key="employee.id" class="py-4 flex items-center">
            <div class="flex-shrink-0 mr-3">
              <div class="relative">
                <img class="h-10 w-10 rounded-full" :src="employee.profile_picture" alt="" />
                <span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-primary-600 flex items-center justify-center text-xs text-white font-medium">
                  {{ index + 1 }}
                </span>
              </div>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ employee.name }}
              </p>
              <p class="text-sm text-gray-500 truncate">
                {{ employee.department }}
              </p>
            </div>
            <div class="ml-3">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                {{ employee.achievement }}%
              </span>
            </div>
          </li>
          <li v-if="topEmployees.length === 0" class="py-4 text-center text-gray-500">
            No data available
          </li>
        </ul>
      </DashboardCard>

      <!-- KPI Performance -->
      <DashboardCard title="KPI Performance" subtitle="Average achievement by KPI type" class="lg:col-span-1">
        <ul class="divide-y divide-gray-200">
          <li v-for="kpi in kpiPerformance" :key="kpi.id" class="py-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ kpi.name }}
              </p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getAchievementClass(kpi.achievement)">
                {{ kpi.achievement }}%
              </span>
            </div>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
              <div class="bg-primary-600 h-2.5 rounded-full" :style="{ width: `${kpi.achievement}%` }"></div>
            </div>
          </li>
          <li v-if="kpiPerformance.length === 0" class="py-4 text-center text-gray-500">
            No data available
          </li>
        </ul>
      </DashboardCard>

      <!-- Recent KPI Records -->
      <DashboardCard title="Recent KPI Records" class="lg:col-span-1">
        <ul class="divide-y divide-gray-200">
          <li v-for="record in recentRecords" :key="record.id" class="py-3">
            <div class="flex items-center justify-between">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ record.employee }}
              </p>
              <span class="text-xs text-gray-500">
                {{ formatDate(record.record_date) }}
              </span>
            </div>
            <p class="text-sm text-gray-500 truncate">
              {{ record.kpi }}
            </p>
            <div class="mt-1 flex items-center justify-between">
              <p class="text-xs text-gray-500">
                {{ record.actual_value }} / {{ record.target_value }}
              </p>
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="getAchievementClass(record.achievement_percentage)">
                {{ record.achievement_percentage }}%
              </span>
            </div>
          </li>
          <li v-if="recentRecords.length === 0" class="py-4 text-center text-gray-500">
            No recent records
          </li>
        </ul>
      </DashboardCard>
    </div>

    <!-- Action buttons -->
    <div class="mt-6 flex justify-end space-x-3">
      <Link :href="route('kpis.index')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        <ChartBarIcon class="h-5 w-5 mr-2 text-gray-500" />
        Manage KPIs
      </Link>
      <Link :href="route('kpis.employee-kpis')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        <UserIcon class="h-5 w-5 mr-2 text-gray-500" />
        Employee KPIs
      </Link>
      <Link :href="route('kpis.assign')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        <PlusIcon class="h-5 w-5 mr-2" />
        Assign KPI
      </Link>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { 
  ChartBarIcon, UserIcon, PlusIcon, ChartPieIcon, 
  CheckCircleIcon, UserGroupIcon 
} from '@heroicons/vue/outline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import DashboardCard from '@/Components/Dashboard/DashboardCard.vue';
import LineChart from '@/Components/Dashboard/Charts/LineChart.vue';
import BarChart from '@/Components/Dashboard/Charts/BarChart.vue';

const props = defineProps({
  topEmployees: {
    type: Array,
    required: true
  },
  departmentPerformance: {
    type: Array,
    required: true
  },
  kpiPerformance: {
    type: Array,
    required: true
  },
  recentRecords: {
    type: Array,
    required: true
  },
  overallStats: {
    type: Object,
    required: true
  },
  performanceTrend: {
    type: Object,
    required: true
  }
});

// Format date
const formatDate = (dateString) => {
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

// Get achievement class based on percentage
const getAchievementClass = (percentage) => {
  if (percentage >= 90) return 'bg-green-100 text-green-800';
  if (percentage >= 70) return 'bg-blue-100 text-blue-800';
  if (percentage >= 50) return 'bg-yellow-100 text-yellow-800';
  return 'bg-red-100 text-red-800';
};
</script>