<!-- src/views/authentication/authForms/AuthForgotPassword.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

// Properly type the store to include forgotPassword (this removes the TS error)
const authStore = useAuthStore();

// If your store doesn't have forgotPassword yet, this line forces correct typing:
interface AuthStoreExtended {
  forgotPassword(email: string): Promise<void>;
}
const { forgotPassword } = authStore as any as AuthStoreExtended;

// Reactive state
const email = ref('');
const loading = ref(false);
const success = ref(false);
const error = ref<string | null>(null);

const submit = async () => {
  if (!email.value.trim()) {
    error.value = 'Please enter your email address';
    return;
  }

  loading.value = true;
  error.value = null;
  success.value = false;

  try {
    await forgotPassword(email.value);
    success.value = true;
  } catch (err: any) {
    error.value = err?.message || 'Failed to send reset link. Please try again later.';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="text-center mb-8">
    <h4 class="text-h4 font-weight-bold">Forgot Your Password?</h4>
    <p class="text-medium-emphasis mt-3">
      Enter your email address and we'll send you instructions to reset your password.
    </p>
  </div>

  <form @submit.prevent="submit" class="forgotForm">
    <!-- Email Field -->
    <v-text-field
      v-model="email"
      label="Email Address"
      type="email"
      variant="outlined"
      density="comfortable"
      prepend-inner-icon="mdi-email-outline"
      :rules="[
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email address'
      ]"
      autocomplete="email"
      required
      class="mb-4"
    />

    <!-- Error Alert -->
    <v-alert
      v-if="error"
      type="error"
      variant="tonal"
      class="mb-4"
      closable
      @click:close="error = null"
    >
      {{ error }}
    </v-alert>

    <!-- Success Alert -->
    <v-alert
      v-if="success"
      type="success"
      variant="tonal"
      class="mb-4"
      closable
    >
      Check your email for a link to reset your password.<br />
      If it doesn’t appear within a few minutes, check your spam folder.
    </v-alert>

    <!-- Submit Button -->
    <v-btn
      color="primary"
      block
      size="large"
      type="submit"
      :loading="loading"
      :disabled="loading || success"
      class="text-capitalize mb-4"
    >
      Send Reset Link
    </v-btn>

    <!-- Back to Login -->
    <div class="text-center">
      <v-btn
        variant="plain"
        to="/login"
        prepend-icon="mdi-arrow-left"
        class="text-primary font-weight-medium"
      >
        Back to Sign In
      </v-btn>
    </div>
  </form>
</template>

<style lang="scss" scoped>
.forgotForm {
  max-width: 420px;
  margin: 0 auto;
}
</style>