<template>
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
        <p class="text-3xl font-bold mt-2">{{ value }}</p>
        <div class="mt-2 flex items-center text-sm">
            <span :class="trend === 'up' ? 'text-green-600' : 'text-red-600'">
                {{ percentage }}%
            </span>
            <span class="text-gray-500 ml-2">vs last month</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import {
    UsersIcon,
    ChartBarIcon,
    CalendarIcon,
    ClipboardCheckIcon,
    CurrencyDollarIcon,
    UserAddIcon,
    BadgeCheckIcon,
} from "@heroicons/vue/outline";
import { ArrowSmUpIcon, ArrowSmDownIcon } from "@heroicons/vue/solid";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        required: true,
    },
    trend: {
        type: String,
        default: "up",
        validator: (value) => ["up", "down"].includes(value),
    },
    percentage: {
        type: Number,
        default: 0,
    },
    color: {
        type: String,
        default: "blue",
        validator: (value) =>
            ["blue", "green", "amber", "indigo", "pink", "red"].includes(value),
    },
});

const iconComponent = computed(() => {
    const icons = {
        users: UsersIcon,
        "chart-bar": ChartBarIcon,
        calendar: CalendarIcon,
        "clipboard-check": ClipboardCheckIcon,
        "currency-dollar": CurrencyDollarIcon,
        "user-add": UserAddIcon,
        "badge-check": BadgeCheckIcon,
    };
    return icons[props.icon] || UsersIcon;
});

const colorClasses = computed(() => {
    const colors = {
        blue: { bgLight: "bg-blue-100", text: "text-blue-600" },
        green: { bgLight: "bg-green-100", text: "text-green-600" },
        amber: { bgLight: "bg-amber-100", text: "text-amber-600" },
        indigo: { bgLight: "bg-indigo-100", text: "text-indigo-600" },
        pink: { bgLight: "bg-pink-100", text: "text-pink-600" },
        red: { bgLight: "bg-red-100", text: "text-red-600" },
    };
    return colors[props.color] || colors.blue;
});
</script>
