<template>
    <form @submit.prevent="uploadImage">
        <input type="file" @change="onFileChange" />
        <button type="submit">Upload</button>
    </form>

    <div v-if="uploadedImage">
        <h3>Preview:</h3>
        <img :src="uploadedImage" class="w-32 h-32 object-cover rounded-full" />
    </div>

    <div>
        <img
            v-if="employee.profile_picture"
            :src="employee.profile_picture"
            class="w-32 h-32 object-cover rounded-full"
        />
        <p v-else>No profile picture available.</p>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        employee: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selectedFile: null,
            employeeId: 1, // dynamic if needed
            uploadedImage: null,
        };
    },
    methods: {
        onFileChange(e) {
            this.selectedFile = e.target.files[0];
        },
        async uploadImage() {
            const formData = new FormData();
            formData.append("profile_picture", this.selectedFile);

            try {
                const response = await axios.post(
                    `/api/employees/${this.employeeId}/upload-profile`, // Updated endpoint
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );

                console.log(response.data);
                this.uploadedImage = response.data.url;
            } catch (error) {
                console.error("Upload failed", error.response?.data || error);
            }
        },
    },
};
</script>
