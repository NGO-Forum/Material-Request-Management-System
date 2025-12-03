<script setup lang="ts">
import { ref } from 'vue';
import { Form } from 'vee-validate';
import { useAuthStore } from '@/stores/auth';
import Google from '@/assets/images/auth/social-google.svg';

// Form fields
const username = ref('info@codedthemes.com');
const password = ref('admin123');
const showPassword = ref(false);
const rememberMe = ref(false);

// Validation rules
const emailRules = [
  (v: string) => !!v || 'Email is required',
  (v: string) => /.+@.+\..+/.test(v) || 'Email must be valid'
];

const passwordRules = [
  (v: string) => !!v || 'Password is required',
  (v: string) => (v && v.length <= 10) || 'Password must be less than 10 characters'
];

/* eslint-disable @typescript-eslint/no-explicit-any */
async function onSubmit(values: any, { setErrors }: any) {
  const authStore = useAuthStore();

  try {
    await authStore.login(username.value, password.value);
    // Redirect or handle login success here
  } catch (error: any) {
    setErrors({ apiError: error });
  }
}
</script>

<template>
  <Form @submit="onSubmit" class="loginForm" v-slot="{ errors, isSubmitting }">
    <!-- Username / Email -->
    <v-text-field
      v-model="username"
      :rules="emailRules"
      label="Email Address / Username"
      variant="outlined"
      density="comfortable"
      hide-details="auto"
      color="primary"
      class="mt-4 mb-6"
      required
    ></v-text-field>

    <!-- Password -->
    <v-text-field
      v-model="password"
      :rules="passwordRules"
      label="Password"
      :type="showPassword ? 'text' : 'password'"
      :append-icon="showPassword ? '$eye' : '$eyeOff'"
      @click:append="showPassword = !showPassword"
      variant="outlined"
      density="comfortable"
      hide-details="auto"
      color="primary"
      class="pwdInput mb-6"
      required
    ></v-text-field>

    <!-- Remember & Forgot Password -->
    <div class="d-sm-flex align-center mb-6">
      <v-checkbox
        v-model="rememberMe"
        :rules="[(v: any) => !!v || 'You must agree to continue!']"
        label="Remember me?"
        color="primary"
        hide-details
      ></v-checkbox>

      <div class="ml-auto">
        <a href="javascript:void(0)" class="text-primary text-decoration-none">Forgot password?</a>
      </div>
    </div>

    <!-- Submit Button -->
    <v-btn
      color="secondary"
      block
      variant="flat"
      size="large"
      type="submit"
      :loading="isSubmitting"
      class="mb-4"
    >
      Sign In
    </v-btn>

    <!-- API Error -->
    <div v-if="errors.apiError">
      <v-alert color="error">{{ errors.apiError }}</v-alert>
    </div>

    <!-- Divider / Social Login -->
    <v-divider class="my-6"></v-divider>
    <v-btn
      class="googleBtn"
      variant="outlined"
      block
    >
      <img :src="Google" alt="Google" width="20" class="me-2" /> Sign in with Google
    </v-btn>

    <!-- Register Link -->
    <div class="mt-5 text-center">
      <v-btn variant="plain" to="/register">Don't have an account?</v-btn>
    </div>
  </Form>
</template>

<style lang="scss" scoped>
.loginForm {
  max-width: 400px;
  margin: auto;

  .v-text-field .v-field--active input {
    font-weight: 500;
  }
}

.pwdInput {
  position: relative;

  .v-input__append {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
  }
}

.googleBtn {
  display: flex;
  align-items: center;
  justify-content: center;
  border-color: rgba(0, 0, 0, 0.08);
}
</style>
