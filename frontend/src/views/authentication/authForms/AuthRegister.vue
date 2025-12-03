<script setup lang="ts">
import { ref } from 'vue';

const Regform = ref();
const username = ref('');
const email = ref('');
const phoneNumber = ref('');
const address = ref('');
const role = ref('');
const department = ref('');
const password = ref('');
const imageProfile = ref<File | null>(null);
const imagePreview = ref<string | null>(null);
const agree = ref(false);
const showPassword = ref(false);

// Validation rules
const requiredRule = [(v: any) => !!v || 'This field is required'];
const emailRules = [
  (v: string) => !!v || 'E-mail is required',
  (v: string) => /.+@.+\..+/.test(v) || 'E-mail must be valid'
];
const passwordRules = [
  (v: string) => !!v || 'Password is required',
  (v: string) => (v && v.length >= 6) || 'Password must be at least 6 characters'
];
const imageRules = [(v: File) => !!v || 'Profile image is required'];

// Select options
const roles = ['Admin', 'Manager', 'Employee'];
const departments = [
  'MACOR Program Manager',
  'SACHAS Program Manager',
  'PILI Program Manager',
  'RITI Program Manager'
];

// File upload handler
function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    imageProfile.value = target.files[0];
    const reader = new FileReader();
    reader.onload = e => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(target.files[0]);
  }
}

// Drag & Drop support
function handleDrop(event: DragEvent) {
  event.preventDefault();
  if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
    imageProfile.value = event.dataTransfer.files[0];
    const reader = new FileReader();
    reader.onload = e => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(event.dataTransfer.files[0]);
  }
}

// Form submit
function validate() {
  if (Regform.value.validate()) {
    const formData = new FormData();
    formData.append('username', username.value);
    formData.append('email', email.value);
    formData.append('phone_number', phoneNumber.value);
    formData.append('address', address.value);
    formData.append('role', role.value);
    formData.append('department', department.value);
    formData.append('password', password.value);
    if (imageProfile.value) {
      formData.append('image_profile', imageProfile.value);
    }

    console.log('Form data ready to submit:', formData);
  }
}
</script>

<template>
  <v-form ref="Regform" lazy-validation class="mt-7 registerForm">
    <!-- Username -->
    <v-text-field
      v-model="username"
      :rules="requiredRule"
      label="Username"
      variant="outlined"
      density="comfortable"
      required
      class="mb-4"
    ></v-text-field>

    <!-- Email -->
    <v-text-field
      v-model="email"
      :rules="emailRules"
      label="Email Address"
      variant="outlined"
      density="comfortable"
      required
      class="mb-4"
    ></v-text-field>

    <!-- Phone Number -->
    <v-text-field
      v-model="phoneNumber"
      :rules="requiredRule"
      label="Phone Number"
      variant="outlined"
      density="comfortable"
      required
      class="mb-4"
    ></v-text-field>

    <!-- Address -->
    <v-textarea
      v-model="address"
      :rules="requiredRule"
      label="Address"
      variant="outlined"
      density="comfortable"
      rows="4"
      required
      class="mb-4"
    ></v-textarea>

    <!-- Role -->
    <v-select
      v-model="role"
      :items="roles"
      :rules="requiredRule"
      label="Select Role"
      required
      class="mb-4"
    ></v-select>

    <!-- Department -->
    <v-select
      v-model="department"
      :items="departments"
      :rules="requiredRule"
      label="Select Department"
      required
      class="mb-4"
    ></v-select>

    <!-- Password -->
    <v-text-field
      v-model="password"
      :rules="passwordRules"
      :type="showPassword ? 'text' : 'password'"
      :append-icon="showPassword ? '$eye' : '$eyeOff'"
      @click:append="showPassword = !showPassword"
      label="Password"
      variant="outlined"
      density="comfortable"
      required
      class="mb-4"
    ></v-text-field>

    <!-- Image Upload Box -->
    <div
      class="upload-box mb-4"
      @drop="handleDrop"
      @dragover.prevent
    >
      <input
        type="file"
        accept="image/*"
        class="file-input"
        @change="handleFileChange"
      />
      <template v-if="imagePreview">
        <img :src="imagePreview" alt="Profile Preview" class="preview-img" />
      </template>
      <template v-else>
        <div class="placeholder">
          <span>Click or Drag & Drop to upload image</span>
        </div>
      </template>
    </div>

    <!-- Agree to Terms -->
    <v-checkbox
      v-model="agree"
      :rules="[(v: any) => !!v || 'You must agree to continue!']"
      label="I agree to Terms and Conditions"
      required
      class="mb-4"
    ></v-checkbox>

    <!-- Submit Button -->
    <v-btn color="primary" block large @click="validate()">Sign Up</v-btn>

    <!-- Login Link -->
    <div class="mt-5 text-center">
      <v-divider></v-divider>
      <v-btn variant="plain" to="/login1" class="mt-2">Already have an account?</v-btn>
    </div>
  </v-form>
</template>

<style lang="scss" scoped>
.registerForm {
  max-width: 600px;
  margin: auto;

  .pwdInput {
    position: relative;
    .v-input__append {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
    }
  }

  .upload-box {
    position: relative;
    border: 2px dashed #bbb;
    border-radius: 12px;
    height: 180px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    overflow: hidden;
    transition: border 0.3s, background 0.3s;

    &:hover {
      border-color: #1976d2;
      background-color: rgba(25, 118, 210, 0.05);
    }

    .file-input {
      position: absolute;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
    }

    .placeholder {
      text-align: center;
      color: #888;
      font-size: 14px;
      pointer-events: none;
    }

    .preview-img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #1976d2;
    }
  }
}
</style>
