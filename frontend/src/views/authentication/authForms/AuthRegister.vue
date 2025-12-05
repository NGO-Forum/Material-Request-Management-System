<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axiosClient from '@/plugins/axios';

const fileInput = ref<HTMLInputElement | null>(null);
const authStore = useAuthStore();
const form = ref<any>(null);

const name = ref('');
const email = ref('');
const password = ref('');
const phoneNumber = ref('');
const address = ref('');
const role_id = ref<number | null>(null);
const department_id = ref<number | null>(null);
const imageProfile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const showPassword = ref(false);
const agree = ref(false);
const loading = ref(false);
const errorMessage = ref<string | null>(null);

const roles = ref<Array<{ id: number; name: string }>>([]);
const departments = ref<Array<{ id: number; name: string }>>([]);

// Fetch Roles & Departments
const fetchRolesAndDepartments = async () => {
  try {
    const [rolesRes, deptsRes] = await Promise.all([
      axiosClient.get('/roles'),
      axiosClient.get('/departments')
    ]);
    roles.value = rolesRes.data.data || rolesRes.data;
    departments.value = deptsRes.data.data || deptsRes.data;
  } catch (err: any) {
    errorMessage.value = 'Failed to load roles or departments.';
    console.error(err);
  }
};

onMounted(() => {
  fetchRolesAndDepartments();
});

// Image handlers
const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (input.files?.[0]) {
    const file = input.files[0];
    imageProfile.value = file;
    previewImage(file);
  }
};

const handleDrop = (e: DragEvent) => {
  e.preventDefault();
  const file = e.dataTransfer?.files[0];
  if (file && file.type.startsWith('image/')) {
    imageProfile.value = file;
    previewImage(file);
  }
};

const previewImage = (file: File) => {
  const reader = new FileReader();
  reader.onload = () => {
    imagePreview.value = reader.result as string;
  };
  reader.readAsDataURL(file);
};

const openFilePicker = () => {
  fileInput.value?.click();
};

// Submit Registration
const submitRegister = async () => {
  if (!form.value?.validate()) {
    errorMessage.value = 'Please fill in all required fields correctly.';
    return;
  }

  if (!agree.value) {
    errorMessage.value = 'You must agree to the terms and conditions.';
    return;
  }

  if (!role_id.value || !department_id.value) {
    errorMessage.value = 'Please select a role and department.';
    return;
  }

  loading.value = true;
  errorMessage.value = null;

  const formData = new FormData();
  formData.append('name', name.value);
  formData.append('email', email.value);
  formData.append('password', password.value);
  if (phoneNumber.value) formData.append('phone_number', phoneNumber.value);
  if (address.value) formData.append('address', address.value);
  formData.append('role_id', role_id.value.toString());
  formData.append('department_id', department_id.value.toString());
  if (imageProfile.value) formData.append('image_profile', imageProfile.value);

  try {
    await authStore.register(formData);
    // Optional: redirect after success
    // router.push('/dashboard');
  } catch (err: any) {
    // Improved error handling from Laravel validation
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      if (errors.role_id) {
        errorMessage.value = errors.role_id[0];
      } else if (errors.email) {
        errorMessage.value = errors.email[0];
      } else {
        errorMessage.value = 'Registration failed. Please check your input.';
      }
    } else {
      errorMessage.value = err.message || 'Registration failed. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <v-form ref="form" @submit.prevent="submitRegister" class="mt-7 registerForm">
    <!-- Full Name -->
    <v-text-field
      v-model="name"
      label="Full Name"
      variant="outlined"
      density="comfortable"
      :rules="[(v) => !!v || 'Name is required']"
      required
      class="mb-4"
    />

    <!-- Email -->
    <v-text-field
      v-model="email"
      label="Email Address"
      type="email"
      variant="outlined"
      density="comfortable"
      :rules="[
        (v) => !!v || 'Email is required',
        (v) => /.+@.+\..+/.test(v) || 'Please enter a valid email'
      ]"
      required
      class="mb-4"
    />

    <!-- Phone & Address -->
    <v-text-field
      v-model="phoneNumber"
      label="Phone Number"
      variant="outlined"
      density="comfortable"
      class="mb-4"
    />

    <v-textarea
      v-model="address"
      label="Address"
      variant="outlined"
      density="comfortable"
      rows="3"
      class="mb-4"
    />

    <!-- Role -->
    <v-select
      v-model="role_id"
      :items="roles"
      item-title="name"
      item-value="id"
      label="Select Role"
      variant="outlined"
      :rules="[(v) => !!v || 'Please select a role']"
      required
      class="mb-4"
      :loading="roles.length === 0"
      :disabled="roles.length === 0"
    />

    <!-- Department -->
    <v-select
      v-model="department_id"
      :items="departments"
      item-title="name"
      item-value="id"
      label="Select Department"
      variant="outlined"
      :rules="[(v) => !!v || 'Please select a department']"
      required
      class="mb-4"
      :loading="departments.length === 0"
      :disabled="departments.length === 0"
    />

    <!-- Password -->
    <v-text-field
      v-model="password"
      :type="showPassword ? 'text' : 'password'"
      label="Password"
      variant="outlined"
      density="comfortable"
      :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
      @click:append-inner="showPassword = !showPassword"
      :rules="[
        (v) => !!v || 'Password is required',
        (v) => (v && v.length >= 6) || 'Password must be at least 6 characters'
      ]"
      required
      class="mb-4"
    />

    <!-- Image Upload -->
    <div
      class="upload-box mb-4"
      @drop.prevent="handleDrop"
      @dragover.prevent
      @click="openFilePicker"
    >
      <input
        ref="fileInput"
        type="file"
        accept="image/*"
        class="file-input"
        @change="handleFileChange"
        hidden
      />
      <div v-if="imagePreview" class="text-center">
        <img :src="imagePreview" alt="Preview" class="preview-img" />
        <p class="text-caption mt-2">Click or drop to change</p>
      </div>
      <div v-else class="placeholder text-center">
        <v-icon size="48" color="grey">mdi-cloud-upload</v-icon>
        <p class="mt-2">Click or Drag & Drop to upload image</p>
      </div>
    </div>

    <!-- Terms -->
    <v-checkbox
      v-model="agree"
      label="I agree to Terms and Conditions"
      required
      class="mb-4"
    />

    <!-- Error Alert -->
    <v-alert v-if="errorMessage" type="error" class="mb-4" dismissible>
      {{ errorMessage }}
    </v-alert>

    <!-- Submit -->
    <v-btn
      color="primary"
      block
      size="large"
      type="submit"
      :loading="loading"
      :disabled="loading || !agree"
    >
      Create Account
    </v-btn>

    <!-- Login Link -->
    <div class="text-center mt-6">
      <v-divider class="mb-4" />
      <span class="text-grey-darken-1">Already have an account?</span>
      <v-btn variant="plain" to="/login" class="ml-2">Sign In</v-btn>
    </div>
  </v-form>
</template>

<style lang="scss" scoped>
.registerForm {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

.upload-box {
  position: relative;
  border: 2px dashed #bbb;
  border-radius: 16px;
  height: 220px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  background-color: #fafafa;
  transition: all 0.3s ease;

  &:hover {
    border-color: #1976d2;
    background-color: #f0f7ff;
  }

  .file-input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
  }

  .preview-img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #1976d2;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .placeholder {
    color: #666;
    p {
      margin: 8px 0 0;
      font-size: 14px;
    }
  }
}
</style>