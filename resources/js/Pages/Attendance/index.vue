<template>

  <AuthenticatedLayout>
  <div class="attendance-page">
    <h1>Attendance</h1>
    <table class="attendance-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(record, index) in attendanceRecords" :key="index">
          <td>{{ index + 1 }}</td>
          <td>{{ record.name }}</td>
          <td>{{ record.date }}</td>
          <td>{{ record.status }}</td>
        </tr>
      </tbody>
    </table>
  </div>
      
</AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const attendanceRecords = [
  { name: "John Doe", date: "2023-10-01", status: "Present" },
  { name: "Jane Smith", date: "2023-10-01", status: "Absent" },
  { name: "Alice Johnson", date: "2023-10-01", status: "Present" },
];
</script>

<style scoped>
.attendance-page {
  padding: 20px;
}

.attendance-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.attendance-table th,
.attendance-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.attendance-table th {
  background-color: #f4f4f4;
}
</style>