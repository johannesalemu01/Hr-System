<template>
  <AuthenticatedLayout>

  
  <div class="departments-page">
    <h1 class="text-2xl font-bold mb-4">Departments</h1>
    <table class="table-auto w-full border-collapse border border-gray-300">
      <thead>
        <tr class="bg-gray-100">
          <th class="border border-gray-300 px-4 py-2">#</th>
          <th class="border border-gray-300 px-4 py-2">Name</th>
          <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(department, index) in departments" :key="department.id">
          <td class="border border-gray-300 px-4 py-2">{{ index + 1 }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ department.name }}</td>
          <td class="border border-gray-300 px-4 py-2">
            <button
              class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
              @click="editDepartment(department.id)"
            >
              Edit
            </button>
            <button
              class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 ml-2"
              @click="deleteDepartment(department.id)"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const departments = ref([
  { id: 1, name: "Human Resources" },
  { id: 2, name: "Finance" }, 
  { id: 3, name: "Engineering" },
])

const editDepartment = (id) => {
  alert(`Edit department with ID: ${id}`)
}

const deleteDepartment = (id) => {
  if (confirm("Are you sure you want to delete this department?")) {
    departments.value = departments.value.filter(
      (department) => department.id !== id
    )
  }
}
</script>

<style scoped>
.departments-page {
  padding: 20px;
}
</style>