<template>
<AuthenticatedLayout>


  <div class="payroll-page">
    <h1 class="text-2xl font-bold mb-4">Payroll Management</h1>
    <div class="mb-6">
      <button
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        @click="openAddPayrollModal"
      >
        Add Payroll
      </button>
    </div>
    <table class="table-auto w-full border-collapse border border-gray-300">
      <thead>
        <tr class="bg-gray-100">
          <th class="border border-gray-300 px-4 py-2">Employee Name</th>
          <th class="border border-gray-300 px-4 py-2">Position</th>
          <th class="border border-gray-300 px-4 py-2">Salary</th>
          <th class="border border-gray-300 px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(payroll, index) in payrolls" :key="index">
          <td class="border border-gray-300 px-4 py-2">{{ payroll.name }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ payroll.position }}</td>
          <td class="border border-gray-300 px-4 py-2">{{ payroll.salary }}</td>
          <td class="border border-gray-300 px-4 py-2">
            <button
              class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
              @click="editPayroll(index)"
            >
              Edit
            </button>
            <button
              class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 ml-2"
              @click="deletePayroll(index)"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Add/Edit Payroll Modal -->
    <div v-if="showModal" class="modal fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded shadow-lg w-1/3">
        <h2 class="text-xl font-bold mb-4">{{ isEditing ? 'Edit Payroll' : 'Add Payroll' }}</h2>
        <form @submit.prevent="savePayroll">
          <div class="mb-4">
            <label class="block text-gray-700">Employee Name</label>
            <input
              type="text"
              v-model="form.name"
              class="w-full border border-gray-300 px-4 py-2 rounded"
              required
            />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700">Position</label>
            <input
              type="text"
              v-model="form.position"
              class="w-full border border-gray-300 px-4 py-2 rounded"
              required
            />
          </div>
          <div class="mb-4">
            <label class="block text-gray-700">Salary</label>
            <input
              type="number"
              v-model="form.salary"
              class="w-full border border-gray-300 px-4 py-2 rounded"
              required
            />
          </div>
          <div class="flex justify-end">
            <button
              type="button"
              class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 mr-2"
              @click="closeModal"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</AuthenticatedLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const payrolls = ref([]);
const showModal = ref(false);
const isEditing = ref(false);
const form = reactive({
  name: '',
  position: '',
  salary: '',
});
const editIndex = ref(null);

function openAddPayrollModal() {
  resetForm();
  isEditing.value = false;
  showModal.value = true;
}

function editPayroll(index) {
  Object.assign(form, payrolls.value[index]);
  isEditing.value = true;
  editIndex.value = index;
  showModal.value = true;
}

function deletePayroll(index) {
  payrolls.value.splice(index, 1);
}

function savePayroll() {
  if (isEditing.value) {
    payrolls.value[editIndex.value] = { ...form };
  } else {
    payrolls.value.push({ ...form });
  }
  closeModal();
}

function closeModal() {
  showModal.value = false;
}

function resetForm() {
  form.name = '';
  form.position = '';
  form.salary = '';
}
</script>

<style scoped>
.modal {
  z-index: 1000;
}
</style>