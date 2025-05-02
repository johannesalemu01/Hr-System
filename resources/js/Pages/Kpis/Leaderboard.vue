<template>
    <AdminLayout
        title="KPI Leaderboard"
        description="Top performing employees based on KPI points"
    >
        <!-- Employee View (Updated) -->
        <div v-if="isEmployeeView" class="container mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">
                My KPI Performance & Badges
            </h1>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- My Rank & Points (Takes more space now) -->
                <div
                    class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md border border-gray-200"
                >
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        My Performance Summary
                    </h2>
                    <div
                        v-if="myPointsData"
                        class="flex flex-col items-center text-center space-y-4"
                    >
                        <img
                            :src="myPointsData.profile_picture"
                            alt="My Profile"
                            class="h-24 w-24 rounded-full object-cover border-4 border-primary-500 shadow-lg"
                        />
                        <div>
                            <p class="text-xl font-medium text-gray-900">
                                {{ myPointsData.name }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ myPointsData.position }}
                            </p>
                            <p class="mt-3 text-3xl font-bold text-primary-600">
                                {{ myPointsData.total_points }} Points
                            </p>
                            <p v-if="myRank" class="text-lg text-gray-600 mt-1">
                                Overall Rank:
                                <span class="font-semibold text-gray-800"
                                    >#{{ myRank }}</span
                                >
                            </p>
                            <div
                                v-if="
                                    myPointsData.earned_badges &&
                                    myPointsData.earned_badges.length
                                "
                                class="mt-4 flex flex-wrap justify-center gap-2"
                            >
                                <span
                                    v-for="badge in myPointsData.earned_badges"
                                    :key="badge.id"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium shadow-sm"
                                    :style="{
                                        backgroundColor: badge.color,
                                        color: getContrastColor(badge.color),
                                    }"
                                    :title="`${badge.name} (${badge.points_required} points)`"
                                >
                                    <component
                                        :is="resolveIconComponent(badge.icon)"
                                        class="h-4 w-4 mr-1.5"
                                    />
                                    {{ badge.name }}
                                </span>
                            </div>
                            <p v-else class="mt-4 text-sm text-gray-500">
                                No badges earned yet.
                            </p>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-4">
                        <p>Your performance data is not yet available.</p>
                    </div>
                </div>

                <!-- Available Badges (Takes more space now) -->
                <div
                    class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md border border-gray-200"
                >
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">
                        Available Badges
                    </h2>
                    <div
                        v-if="availableBadges && availableBadges.length"
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"
                    >
                        <div
                            v-for="badge in availableBadges"
                            :key="badge.id"
                            class="text-center p-4 border rounded-lg transition-all duration-200 ease-in-out flex flex-col items-center justify-between"
                            :class="{
                                'border-green-400 bg-green-50 shadow-md scale-105':
                                    myPointsData &&
                                    myPointsData.earned_badges &&
                                    myPointsData.earned_badges.some(
                                        (eb) => eb.id === badge.id
                                    ),
                                'opacity-60 border-gray-200':
                                    myPointsData &&
                                    myPointsData.total_points <
                                        badge.points_required &&
                                    !(
                                        myPointsData.earned_badges &&
                                        myPointsData.earned_badges.some(
                                            (eb) => eb.id === badge.id
                                        )
                                    ),
                                'border-gray-300 hover:shadow-lg hover:border-primary-300':
                                    !(
                                        myPointsData &&
                                        myPointsData.earned_badges &&
                                        myPointsData.earned_badges.some(
                                            (eb) => eb.id === badge.id
                                        )
                                    ),
                            }"
                        >
                            <div class="flex justify-center mb-2">
                                <span
                                    class="inline-flex items-center justify-center h-16 w-16 rounded-full shadow-inner"
                                    :style="{ backgroundColor: badge.color }"
                                >
                                    <component
                                        :is="resolveIconComponent(badge.icon)"
                                        class="h-8 w-8"
                                        :style="{
                                            color: getContrastColor(
                                                badge.color
                                            ),
                                        }"
                                    />
                                </span>
                            </div>
                            <p class="text-base font-medium text-gray-800 mt-2">
                                {{ badge.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ badge.points_required }} pts
                            </p>
                            <p
                                v-if="
                                    myPointsData &&
                                    myPointsData.earned_badges &&
                                    myPointsData.earned_badges.some(
                                        (eb) => eb.id === badge.id
                                    )
                                "
                                class="mt-1 text-xs text-green-600 font-semibold"
                            >
                                Earned
                            </p>
                            <p
                                v-else-if="
                                    myPointsData &&
                                    myPointsData.total_points <
                                        badge.points_required
                                "
                                class="mt-1 text-xs text-gray-500"
                            >
                                {{
                                    badge.points_required -
                                    myPointsData.total_points
                                }}
                                more points
                            </p>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">No badges available.</p>
                </div>
            </div>
        </div>

        <!-- Admin/Manager View (Render AdminLeaderboard component - Unchanged) -->
        <div v-else class="container  mx-auto px-4 py-8">
            <AdminLeaderboard
                :topOverall="topOverall"
                :topByDepartment="topByDepartment"
                :departments="departments"
                :availableBadges="availableBadges"
            />
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import AdminLeaderboard from "@/Pages/Kpis/AdminLeaderboard.vue"; // Import the AdminLeaderboard component
// Import icons needed for Employee view
import {
    StarIcon,
    LightBulbIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ChartBarIcon,
    CalendarIcon,
    BadgeCheckIcon,
    UserIcon,
    ChartPieIcon,
    AcademicCapIcon,
    FireIcon,
} from "@heroicons/vue/outline";

const props = defineProps({
    topOverall: {
        type: Array,
        required: true,
    },
    topByDepartment: {
        type: Object, // Dept ID => Array of employees
        required: true,
    },
    departments: {
        type: Object, // Dept ID => Dept Name
        required: true,
    },
    availableBadges: {
        type: Array,
        required: true,
    },
    // Props specific to employee view
    isEmployeeView: {
        type: Boolean,
        default: false,
    },
    myRank: {
        type: Number,
        default: null,
    },
    myPointsData: {
        type: Object,
        default: null,
    },
});

// Icon map needed for Employee view
const iconMap = {
    lightbulb: LightBulbIcon,
    star: StarIcon,
    calendar: CalendarIcon,
    "check-circle": CheckCircleIcon,
    "badge-check": BadgeCheckIcon,
    "chart-bar": ChartBarIcon,
    "academic-cap": AcademicCapIcon,
    fire: FireIcon,
    users: UserGroupIcon,
    // Add other icons used by your badges here
};

// Function needed for Employee view
const resolveIconComponent = (iconName) => {
    const iconComponent = iconMap[iconName];
    if (!iconComponent) {
        console.warn(
            `Icon mapping missing for: ${iconName}. Defaulting to StarIcon.`
        );
        return StarIcon; // Default icon
    }
    return iconComponent;
};

// Function needed for Employee view
const getContrastColor = (bgColor) => {
    if (!bgColor || typeof bgColor !== "string") return "#000000"; // Default to black if invalid

    let color = bgColor.trim();
    if (color.startsWith("#")) {
        color = color.substring(1);
    }

    // Handle shorthand hex (e.g., #FFF) -> #FFFFFF
    if (color.length === 3) {
        color = color
            .split("")
            .map((char) => char + char)
            .join("");
    }

    if (color.length !== 6) {
        console.warn(
            `Invalid hex color format: ${bgColor}. Defaulting text to black.`
        );
        return "#000000";
    }

    try {
        const r = parseInt(color.substring(0, 2), 16);
        const g = parseInt(color.substring(2, 4), 16);
        const b = parseInt(color.substring(4, 6), 16);
        // Calculate brightness (using standard formula)
        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
        // Return black for light backgrounds, white for dark
        return brightness > 155 ? "#000000" : "#FFFFFF";
    } catch (e) {
        console.error(`Error processing hex color ${bgColor}:`, e);
        return "#000000"; // Default to black on error
    }
};

// If using route() helper in the template links (might be needed by AdminLeaderboard if not passed down)
const page = usePage();
const route = (name, params) => page.props.ziggy.route(name, params);
</script>

<style scoped>
/* Add any specific styles if needed */
</style>
