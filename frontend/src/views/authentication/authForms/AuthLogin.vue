<!-- src/views/authentication/authForms/AuthLogin.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);

const login = async () => {
  error.value = null;
  loading.value = true;

  try {
    await authStore.login(email.value, password.value);
    // Your store usually redirects, fallback just in case
    router.push({ name: 'Dashboard' });
  } catch (err: any) {
    error.value = err?.message || 'Invalid email or password';
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
      prepend-inner-icon="mdi-email-outline"
      :rules="[
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email'
      ]"
      autocomplete="username"
      required
      class="mb-4"
    />

    <v-text-field
      v-model="password"
      label="Password"
      :type="showPassword ? 'text' : 'password'"
      variant="outlined"
      density="comfortable"
      prepend-inner-icon="mdi-lock-outline"
      :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
      @click:append-inner="showPassword = !showPassword"
      :rules="[(v) => !!v || 'Password is required']"
      autocomplete="current-password"
      required
      class="mb-4"
    />

    <div class="d-flex justify-space-between align-center mb-6">
      <v-checkbox v-model="rememberMe" label="Remember me" hide-details />
      <router-link
        to="/forgot-password"
        class="text-primary text-decoration-none font-weight-medium"
      >
        Forgot password?
      </router-link>
    </div>

    <v-alert v-if="error" type="error" variant="tonal" closable class="mb-6">
      {{ error }}
    </v-alert>

    <v-btn
      color="primary"
      block
      size="large"
      type="submit"
      :loading="loading"
      :disabled="loading"
      class="text-capitalize"
    >
      Sign In
    </v-btn>

    <v-divider class="my-8" />

    <div class="text-center">
      <span class="text-medium-emphasis">Don't have an account?</span>
      <v-btn variant="plain" to="/register" class="ml-2 text-primary font-weight-medium">
        Sign Up
      </v-btn>
    </div>
  </form>
</template>

<style lang="scss" scoped>
.loginForm {
  max-width: 420px;
  margin: 0 auto;
}
</style>