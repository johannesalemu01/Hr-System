<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  permissions: [],
});

const props = defineProps({
  permissions: Array,
});

const user = usePage().props.auth.user;
</script>

<template>
  <div v-if="user.roles.includes('admin')">
    <h1>Create Role</h1>

    <form @submit.prevent="form.post('/permissions')">
      <label>Name:</label>
      <input v-model="form.name" type="text" required />

      <label>Permissions:</label>
      <div v-for="perm in props.permissions" :key="perm.id">
        <input type="checkbox" v-model="form.permissions" :value="perm.name" /> {{ perm.name }}
      </div>

      <button type="submit">Create</button>
    </form>
  </div>
</template>
