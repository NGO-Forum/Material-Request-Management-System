<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { MailIcon, LockIcon, EyeIcon, EyeOffIcon } from "vue-tabler-icons";

const authStore = useAuthStore();
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);
const loading = ref(false);
const error = ref<string | null>(null);

const togglePassword = () => showPassword.value = !showPassword.value;

const login = async () => {
  error.value = null;
  loading.value = true;
  try {
    await authStore.login(email.value, password.value);
  } catch (err: any) {
    error.value = err?.message || 'Invalid email or password';
  } finally {
    loading.value = false;
  }
};
</script>



<template>
  <form @submit.prevent="login" class="loginForm">
    <!-- Email -->
    <v-text-field
      v-model="email"
      label="Email Address"
      type="email"
      variant="outlined"
      density="comfortable"
      autocomplete="username"
      required
      class="mb-4"
      :rules="[
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email'
      ]"
    >
      <template #prepend-inner>
        <MailIcon size="20" class="text-gray-500" />
      </template>
    </v-text-field>

    <!-- Password -->
    <v-text-field
      v-model="password"
      label="Password"
      :type="showPassword ? 'text' : 'password'"
      variant="outlined"
      density="comfortable"
      autocomplete="current-password"
      required
      class="mb-4"
      :rules="[(v) => !!v || 'Password is required']"
    >
      <template #prepend-inner>
        <LockIcon size="20" class="text-gray-500" />
      </template>
      <template #append-inner>
        <div class="eye-icon" @click="togglePassword">
          <EyeIcon v-if="showPassword" size="22" />
          <EyeOffIcon v-else size="22" />
        </div>
      </template>
    </v-text-field>

    <div class="d-flex justify-space-between align-center mb-6">
      <v-checkbox v-model="rememberMe" label="Remember me" hide-details />
      <router-link to="/forgot-password" class="text-primary text-decoration-none font-weight-medium">
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
.eye-icon { 
  cursor: pointer; 
  &:hover { 
    color: #1976d2; 
    transform: scale(1.15); 
  } 
}
</style>
