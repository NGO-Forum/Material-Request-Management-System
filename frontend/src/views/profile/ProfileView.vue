<template>
  <v-container fluid class="pa-0">
    <!-- Hero Section -->
    <div class="profile-hero relative overflow-hidden">
      <div class="hero-bg"></div>
      <div class="hero-content pa-8 pa-md-12">
        <div class="d-flex flex-column flex-md-row align-center gap-6 max-w-1200 mx-auto">
          <v-avatar size="160" class="profile-avatar elevation-24">
            <v-img
              :src="userImage"
              cover
              class="rounded-circle border-4 border-white"
            >
              <template #placeholder>
                <div class="d-flex align-center justify-center fill-height bg-gradient-primary">
                  <UserCircleIcon size="90" class="text-white opacity-90" />
                </div>
              </template>
            </v-img>
          </v-avatar>

          <div class="text-center text-md-start">
            <h1 class="text-h3 font-weight-black text-white mb-2 drop-shadow">
              {{ authStore.user?.name || 'User' }}
            </h1>
            <p class="text-h6 text-white/90 mb-4 flex items-center justify-center justify-md-start gap-2">
              <MailIcon size="24" />
              {{ authStore.user?.email }}
            </p>
            <div class="d-flex gap-4 flex-wrap justify-center justify-md-start">
              <v-chip
                :color="getRoleColor(authStore.user?.role?.name)"
                size="x-large"
                class="px-5 font-weight-bold text-white shadow-lg"
                elevation="6"
              >
                <ShieldIcon size="20" class="mr-2" />
                {{ authStore.user?.role?.name || 'Member' }}
              </v-chip>
              <v-chip
                color="white"
                text-color="indigo-800"
                size="large"
                class="font-weight-medium"
                elevation="4"
              >
                <BuildingIcon size="20" class="mr-2" />
                {{ authStore.user?.department?.name || 'No Department' }}
              </v-chip>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Card -->
    <v-container class="mt-n10">
      <v-row justify="center">
        <v-col cols="12" lg="10" xl="9">
          <v-card class="pa-8 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700" elevation="20">
            <v-card-title class="text-h4 font-weight-bold text-primary mb-8 d-flex align-center gap-3">
              <UserCogIcon size="36" />
              My Profile
            </v-card-title>

            <v-row>
              <v-col cols="12" md="6">
                <v-list class="bg-transparent" density="comfortable">
                  <v-list-item>
                    <template #prepend><v-icon icon="mdi-account" class="text-primary" /></template>
                    <v-list-item-title class="font-weight-semibold">Full Name</v-list-item-title>
                    <v-list-item-subtitle class="text-h6 font-weight-medium">
                      {{ authStore.user?.name || '—' }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template #prepend><PhoneIcon size="22" class="text-green-600" /></template>
                    <v-list-item-title class="font-weight-semibold">Phone</v-list-item-title>
                    <v-list-item-subtitle>{{ authStore.user?.phone_number || 'Not set' }}</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template #prepend><MapPinIcon size="22" class="text-orange-600" /></template>
                    <v-list-item-title class="font-weight-semibold">Address</v-list-item-title>
                    <v-list-item-subtitle>{{ authStore.user?.address || 'Not set' }}</v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>

              <v-col cols="12" md="6">
                <v-list class="bg-transparent" density="comfortable">
                  <v-list-item>
                    <template #prepend><CalendarIcon size="22" class="text-indigo-600" /></template>
                    <v-list-item-title class="font-weight-semibold">Member Since</v-list-item-title>
                    <v-list-item-subtitle>{{ formatDate(authStore.user?.created_at) }}</v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item>
                    <template #prepend><ClockIcon size="22" class="text-purple-600" /></template>
                    <v-list-item-title class="font-weight-semibold">Last Login</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ authStore.user?.last_login_at ? formatDateTime(authStore.user.last_login_at) : 'Never' }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>

            <v-divider class="my-10"></v-divider>

            <!-- <div class="text-center">
              <v-btn
                size="x-large"
                color="primary"
                class="px-10 rounded-xl shadow-lg"
                prepend-icon="EditIcon"
                @click="openEditDialog"
              >
                <span class="font-weight-bold ">Edit Profile</span>
              </v-btn>
            </div> -->
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Edit Profile Dialog -->
    <v-dialog v-model="editDialog" max-width="900" persistent>
      <v-card class="pa-6 rounded-2xl">
        <v-card-title class="text-h5 font-weight-bold d-flex align-center gap-3 text-primary">
          <EditIcon size="32" />
          Edit My Profile
        </v-card-title>

        <v-card-text>
          <v-form @submit.prevent="saveProfile">
            <v-row>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.name"
                  label="Full Name"
                  prepend-inner-icon="UserIcon"
                  :rules="[v => !!v || 'Name is required']"
                  required
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.phone_number"
                  label="Phone Number"
                  prepend-inner-icon="PhoneIcon"
                />
              </v-col>

              <v-col cols="12">
                <v-textarea
                  v-model="form.address"
                  label="Address"
                  prepend-inner-icon="MapPinIcon"
                  rows="3"
                  auto-grow
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-select
                  v-model="form.department_id"
                  :items="departments"
                  item-title="name"
                  item-value="id"
                  label="Department"
                  prepend-inner-icon="BuildingIcon"
                />
              </v-col>

              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.password"
                  label="New Password (leave blank to keep current)"
                  :type="showPassword ? 'text' : 'password'"
                  prepend-inner-icon="LockIcon"
                  :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  @click:append-inner="showPassword = !showPassword"
                  :rules="form.password ? [v => v.length >= 6 || 'Min 6 characters'] : []"
                  hint="Only fill if you want to change password"
                  persistent-hint
                />
              </v-col>

              <!-- Image Upload -->
              <v-col cols="12">
                <div
                  class="upload-box text-center pa-8"
                  @drop.prevent="handleDrop"
                  @dragover.prevent="dragOver = true"
                  @dragleave.prevent="dragOver = false"
                  @click="openFilePicker"
                  :class="{ 'drag-over': dragOver }"
                >
                  <input ref="fileInput" type="file" accept="image/*" hidden @change="handleFileChange" />
                  <div v-if="imagePreview">
                    <img :src="imagePreview" class="preview-img rounded-circle" />
                    <p class="mt-4 text-button font-weight-medium">Drop new image or click to change</p>
                  </div>
                  <div v-else class="text-grey-darken-1">
                    <v-icon size="64" color="grey">mdi-camera-plus</v-icon>
                    <p class="mt-4 text-h6">Drop image here or click to upload</p>
                    <p class="text-caption">Supports JPG, PNG, GIF</p>
                  </div>
                </div>
              </v-col>
            </v-row>

            <v-alert v-if="errorMessage" type="error" class="mb-4" dismissible>
              {{ errorMessage }}
            </v-alert>
          </v-form>
        </v-card-text>

        <v-card-actions class="pa-6">
          <v-spacer />
          <v-btn variant="text" @click="closeEditDialog" :disabled="saving">Cancel</v-btn>
          <v-btn color="primary" :loading="saving" @click="saveProfile" size="large">
            Save Changes
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" location="top">
      <v-icon class="mr-3">{{ snackbar.color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import axiosClient from '@/plugins/axios'
import Swal from 'sweetalert2'

import {
  UserCircleIcon, MailIcon, ShieldIcon, BuildingIcon, PhoneIcon, MapPinIcon,
  CalendarIcon, ClockIcon, UserCogIcon, EditIcon, LockIcon, UserIcon
} from 'vue-tabler-icons'

const authStore = useAuthStore()
const editDialog = ref(false)
const saving = ref(false)
const showPassword = ref(false)
const dragOver = ref(false)
const errorMessage = ref('')
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string | null>(null)
const departments = ref<any[]>([])

const snackbar = ref({ show: false, message: '', color: 'success' })
const showMessage = (msg: string, color: 'success' | 'error' = 'success') => {
  snackbar.value = { show: true, message: msg, color }
}

const form = ref({
  name: '',
  phone_number: '',
  address: '',
  department_id: null as number | null,
  password: '',
  image_profile: null as File | null
})

const userImage = computed(() => {
  const img = authStore.user?.image_profile
  if (!img) return `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'U')}&background=6366f1&color=fff&bold=true&size=300`
  return img.startsWith('http') ? img : `${import.meta.env.VITE_APP_URL || ''}${img}`
})

onMounted(async () => {
  try {
    const [deptRes] = await Promise.all([
      axiosClient.get('/departments')
    ])
    departments.value = deptRes.data.data || deptRes.data
  } catch (err) {
    console.error('Failed to load departments')
  }
})

const openEditDialog = () => {
  form.value = {
    name: authStore.user?.name || '',
    phone_number: authStore.user?.phone_number || '',
    address: authStore.user?.address || '',
    department_id: authStore.user?.department?.id || null,
    password: '',
    image_profile: null
  }
  imagePreview.value = userImage.value
  errorMessage.value = ''
  editDialog.value = true
}

const closeEditDialog = () => {
  editDialog.value = false
  if (imagePreview.value?.startsWith('blob:')) URL.revokeObjectURL(imagePreview.value)
}

const openFilePicker = () => fileInput.value?.click()

const handleFileChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) {
    form.value.image_profile = file
    previewImage(file)
  }
}

const handleDrop = (e: DragEvent) => {
  dragOver.value = false
  const file = e.dataTransfer?.files[0]
  if (file && file.type.startsWith('image/')) {
    form.value.image_profile = file
    previewImage(file)
  }
}

const previewImage = (file: File) => {
  if (imagePreview.value?.startsWith('blob:')) URL.revokeObjectURL(imagePreview.value)
  imagePreview.value = URL.createObjectURL(file)
}

const saveProfile = async () => {
  errorMessage.value = ''
  const data = new FormData()
  data.append('name', form.value.name)
  if (form.value.phone_number) data.append('phone_number', form.value.phone_number)
  if (form.value.address) data.append('address', form.value.address)
  if (form.value.department_id) data.append('department_id', form.value.department_id.toString())
  if (form.value.password) data.append('password', form.value.password)
  if (form.value.image_profile) data.append('image_profile', form.value.image_profile)
  data.append('_method', 'PUT')

  try {
    saving.value = true
    const res = await axiosClient.post(`/users/${authStore.user?.id}`, data)

    // Update auth store
    authStore.user = { ...authStore.user, ...res.data.data }

    closeEditDialog()
    showMessage('Profile updated successfully!', 'success')

    Swal.fire({
      title: 'Success!',
      text: 'Your profile has been updated.',
      icon: 'success',
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: 'top-end',
      background: '#10b981',
      color: 'white'
    })
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Failed to update profile'
    showMessage(errorMessage.value, 'error')
  } finally {
    saving.value = false
  }
}

const getRoleColor = (role?: string) => {
  const map: Record<string, string> = {
    admin: 'red-accent-4',
    superadmin: 'purple-accent-4',
    manager: 'orange',
    employee: 'blue',
    developer: 'indigo'
  }
  return map[role?.toLowerCase() || ''] || 'grey-darken-2'
}

const formatDate = (d?: string) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '—'
const formatDateTime = (d?: string) => d ? new Date(d).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' }) : '—'
</script>

<style scoped>
.profile-hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 380px;
  position: relative;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff15" d="M0,160L48,176C96,192,192,224,288,213.3C384,203,480,149,576,133.3C672,117,768,139,864,170.7C960,203,1056,245,1152,245.3C1248,245,1344,203,1392,181.3L1440,160L1440,320L0,320Z"></path></svg>') bottom no-repeat;
  background-size: cover;
}

.upload-box {
  border: 3px dashed #ccc;
  border-radius: 24px;
  background: #fafafa;
  transition: all 0.3s ease;
  cursor: pointer;
}

.upload-box:hover, .drag-over {
  border-color: #6366f1 !important;
  background: #f0f4ff !important;
}

.preview-img {
  width: 140px;
  height: 140px;
  object-fit: cover;
  border: 6px solid #6366f1;
  box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
}

.bg-gradient-primary {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
}

.drop-shadow { text-shadow: 0 4px 10px rgba(0,0,0,0.4); }
</style>