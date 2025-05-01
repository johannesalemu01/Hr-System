<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue"; // Import computed

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

// Form for name and email
const profileForm = useForm({
    name: user.name,
    email: user.email,
});

// Form for profile picture
const pictureForm = useForm({
    profile_picture: null,
});

const handleFileChange = (event) => {
    pictureForm.profile_picture = event.target.files[0];
};

// Computed property to determine which picture to display (using user only)
const currentProfilePicture = computed(() => {
    const userPic = user?.profile_picture;
    if (userPic) {
        // This will still generate the wrong URL if userPic contains the prefix
        return `/storage/${userPic}`;
    }
    return null; // Return null if no user picture exists
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <!-- Form for name and email -->
        <form
            @submit.prevent="
                profileForm.patch(route('profile.update'), {
                    preserveScroll: true,
                })
            "
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="profileForm.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="profileForm.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="profileForm.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="profileForm.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="profileForm.processing"
                    >Save</PrimaryButton
                >

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="profileForm.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>

    <section class="mt-8">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Profile Picture</h2>

            <p class="mt-1 text-sm text-gray-600">
                Upload or update your profile picture.
            </p>
        </header>

        <!-- Separate form for profile picture -->
        <form
            @submit.prevent="
                pictureForm.post(route('profile.upload-profile-picture'), {
                    // preserveScroll: true, // Keep this commented or removed
                })
            "
            class="mt-6 space-y-6"
            enctype="multipart/form-data"
        >
            <div>
                <InputLabel for="profile_picture" value="Profile Picture" />

                <input
                    id="profile_picture"
                    type="file"
                    class="mt-1 block w-full"
                    @change="handleFileChange"
                />

                <InputError
                    class="mt-2"
                    :message="pictureForm.errors.profile_picture"
                />
            </div>

            <!-- Display current picture -->
            <div v-if="currentProfilePicture">
                <img
                    :src="currentProfilePicture"
                    alt="Profile Picture"
                    class="w-20 h-20 rounded-full mt-4"
                />
            </div>
            <!-- Show placeholder or message if no picture -->
            <div v-else>
                <p class="mt-4 text-sm text-gray-500">
                    No profile picture uploaded.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="pictureForm.processing"
                    >Upload</PrimaryButton
                >

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="pictureForm.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Uploaded successfully.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
