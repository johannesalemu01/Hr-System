<template>
  <div class="constainer">
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h4>Permissions</h4>
<Link href="{{ url('permissions/create') }}" class="btn ">Add Permissions</Link>
      </div>
      <div class="card-body">

      </div>
    </div>
  </div>
</div>
  </div>
</template>

<script setup>

</script>

<style scoped>

</style><script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  roles: Array,
  permissions: Array,
});

const user = usePage().props.auth.user;
</script>

<template>
  <div>
    <h1>Roles & Permissions</h1>

    <div v-if="user.roles.includes('admin')">
      <button @click="$inertia.get('/permissions/create')">Create Role</button>
    </div>

    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Permissions</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="role in props.roles" :key="role.id">
          <td>{{ role.name }}</td>
          <td>{{ role.permissions.map(p => p.name).join(', ') }}</td>
          <td v-if="user.roles.includes('admin')">
            <button @click="$inertia.get(`/permissions/${role.id}/edit`)">Edit</button>
            <button @click="$inertia.delete(`/permissions/${role.id}`)">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
