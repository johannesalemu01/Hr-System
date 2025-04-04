<template>
  <div class="flow-root">
    <ul role="list" class="-mb-8">
      <li v-for="(activity, activityIdx) in activities" :key="activity.id">
        <div class="relative pb-8">
          <span v-if="activityIdx !== activities.length - 1" class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
          <div class="relative flex items-start space-x-3">
            <div>
              <div :class="`relative px-1 ${activity.iconColor} rounded-full flex items-center justify-center h-10 w-10`">
                <component :is="getIconComponent(activity.icon)" class="h-5 w-5" aria-hidden="true" />
              </div>
            </div>
            <div class="min-w-0 flex-1">
              <div>
                <div class="text-sm">
                  <a href="#" class="font-medium text-gray-900">{{ activity.user.name }}</a>
                  {{ activity.action }}
                  <a href="#" class="font-medium text-gray-900">{{ activity.target }}</a>
                </div>
                <p class="mt-0.5 text-sm text-gray-500">{{ activity.date }}</p>
              </div>
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { 
  ChartBarIcon, CalendarIcon, CurrencyDollarIcon, 
  BadgeCheckIcon, UserAddIcon, ClipboardCheckIcon
} from '@heroicons/vue/outline';

const props = defineProps({
  activities: {
    type: Array,
    default: () => []
  }
});

const getIconComponent = (icon) => {
  const icons = {
    'chart-bar': ChartBarIcon,
    'calendar': CalendarIcon,
    'currency-dollar': CurrencyDollarIcon,
    'badge-check': BadgeCheckIcon,
    'user-add': UserAddIcon,
    'clipboard-check': ClipboardCheckIcon
  };
  return icons[icon] || ChartBarIcon;
};
</script>

