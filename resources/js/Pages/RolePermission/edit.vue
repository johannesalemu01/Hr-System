<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  role: Object,
  permissions: Array,
});

const form = useForm({
  name: props.role.name,
  permissions: props.role.permissions.map(p => p.name),
});

const user = usePage().props.auth.user;
</script>

<template>
  <div v-if="user.roles.includes('admin')">
    <h1>Edit Role</h1>

    <form @submit.prevent="form.put(`/permissions/${props.role.id}`)">
      <label>Name:</label>
      <input v-model="form.name" type="text" required />

      <label>Permissions:</label>
      <div v-for="perm in props.permissions" :key="perm.id">
        <input type="checkbox" v-model="form.permissions" :value="perm.name" /> {{ perm.name }}
      </div>

      <button type="submit">Update</button>
    </form>
  </div>
</template>
