<template>
  <div class="flow-root">
    <ul role="list" class="-my-5 divide-y divide-gray-200">
      <li v-for="event in events" :key="event.id" class="py-4">
        <div class="flex items-center space-x-4">
          <div :class="`flex-shrink-0 ${event.iconColor} rounded-md p-2`">
            <component :is="getIconComponent(event.icon)" class="h-5 w-5" aria-hidden="true" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ event.title }}</p>
            <div class="flex items-center text-sm text-gray-500">
              <CalendarIcon class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" aria-hidden="true" />
              <p>{{ event.date }} · {{ event.time }}</p>
            </div>
          </div>
          <div>
            <a href="#" class="inline-flex items-center shadow-sm px-2.5 py-0.5 border border-gray-300 text-sm leading-5 font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50">
              View
            </a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { 
  ChartBarIcon, CalendarIcon, CurrencyDollarIcon, 
  UserAddIcon, ClipboardCheckIcon
} from '@heroicons/vue/outline';

const props = defineProps({
  events: {
    type: Array,
    default: () => []
  }
});

const getIconComponent = (icon) => {
  const icons = {
    'chart-bar': ChartBarIcon,
    'calendar': CalendarIcon,
    'currency-dollar': CurrencyDollarIcon,
    'user-add': UserAddIcon,
    'clipboard-check': ClipboardCheckIcon
  };
  return icons[icon] || CalendarIcon;
};
</script>

