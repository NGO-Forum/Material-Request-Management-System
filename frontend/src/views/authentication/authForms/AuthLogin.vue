<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);

const login = async () => {
  loading.value = true;
  error.value = null;

  try {
    await authStore.login(email.value, password.value);
    // Redirected automatically by store
  } catch (err: any) {
    error.value = err || 'Invalid email or password';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <form @submit.prevent="login" class="loginForm">
    <v-text-field
      v-model="email"
      label="Email Address"
      type="email"
      variant="outlined"
      density="comfortable"
      :rules="[(v) => !!v || 'Email is required']"
      required
      class="mb-4"
    />

    <v-text-field
      v-model="password"
      label="Password"
      :type="showPassword ? 'text' : 'password'"
      variant="outlined"
      density="comfortable"
      :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
      @click:append-inner="showPassword = !showPassword"
      :rules="[(v) => !!v || 'Password is required']"
      required
      class="mb-4"
    />

    <div class="d-flex justify-space-between mb-4">
      <v-checkbox label="Remember me" />
      <a href="#" class="text-primary text-decoration-none">Forgot password?</a>
    </div>

    <v-alert v-if="error" type="error" class="mb-4">{{ error }}</v-alert>

    <v-btn
      color="primary"
      block
      size="large"
      type="submit"
      :loading="loading"
    >
      Sign In
    </v-btn>

    <v-divider class="my-6" />

    <div class="text-center">
      <span>Don't have an account?</span>
      <v-btn variant="plain" to="/register" class="ml-2">Sign Up</v-btn>
    </div>
  </form>
</template>

<style lang="scss" scoped>
.loginForm {
  max-width: 420px;
  margin: 0 auto;
}
</style>