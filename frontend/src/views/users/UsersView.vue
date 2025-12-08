<!-- src/views/users/UsersView.vue -->
<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-6">
          <h1 class="text-h4 font-weight-bold">Users Management</h1>
          <v-btn color="primary" @click="openDialog">
            <PlusIcon class="mr-2" :size="20" />
            Add User
          </v-btn>
        </div>

        <v-card elevation="4" class="rounded-lg">
          <v-card-title class="pa-4">
            <v-text-field
              v-model="search"
              append-inner-icon="SearchIcon"
              label="Search users..."
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
            />
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="users"
            :search="search"
            :loading="loading"
            loading-text="Loading users..."
            items-per-page="15"
            class="elevation-1"
            density="compact"
          >
            <!-- Avatar -->
            <template #item.image_profile="{ item }">
              <v-avatar size="48" class="my-3 cursor-pointer" @click="viewUser(item)">
                <v-img
                  :src="getImageUrl(item.image_profile, item.name)"
                  cover
                  class="rounded-circle"
                >
                  <template #placeholder>
                    <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
                      <UserCircleIcon :size="28" />
                    </div>
                  </template>
                </v-img>
              </v-avatar>
            </template>

            <!-- Role -->
            <template #item.role="{ item }">
              <v-chip :color="getRoleColor(item.role?.name)" size="small" label>
                {{ item.role?.name || 'N/A' }}
              </v-chip>
            </template>

            <!-- Department -->
            <template #item.department="{ item }">
              <span class="text-caption">{{ item.department?.name || '—' }}</span>
            </template>

            <!-- Address -->
            <template #item.address="{ item }">
              <span class="text-caption">
                {{ item.address ? item.address.substring(0, 40) + (item.address.length > 40 ? '...' : '') : '—' }}
              </span>
            </template>

            <!-- Joined Date -->
            <template #item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>

            <!-- Actions -->
            <template #item.actions="{ item }">
              <v-menu location="bottom">
                <template #activator="{ props }">
                  <v-btn icon size="small" v-bind="props">
                    <DotsVerticalIcon :size="20" />
                  </v-btn>
                </template>
                <v-list density="compact">
                  <v-list-item @click="viewUser(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <EyeIcon :size="18" /> View
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="openDialog(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <EditIcon :size="18" /> Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item
                    @click="confirmDelete(item)"
                    class="text-error"
                    :disabled="isLastAdmin(item)"
                  >
                    <v-list-item-title class="d-flex align-center gap-3">
                      <TrashIcon :size="18" class="text-error" /> Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-card>

        <!-- Add/Edit Dialog -->
        <v-dialog v-model="dialog" max-width="800" persistent>
          <v-card class="pa-4">
            <v-card-title class="text-h5 font-weight-bold d-flex align-center gap-3">
              <UserPlusIcon :size="28" />
              {{ form.id ? 'Edit User' : 'Add New User' }}
            </v-card-title>

            <v-card-text>
              <v-form @submit.prevent="saveUser">
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
                      v-model="form.email"
                      label="Email"
                      type="email"
                      prepend-inner-icon="MailIcon"
                      :disabled="!!form.id"
                      :rules="form.id ? [] : [v => !!v || 'Email required', v => /.+@.+\..+/.test(v) || 'Valid email required']"
                      :hint="form.id ? 'Email cannot be changed' : ''"
                      persistent-hint
                    />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.password"
                      label="Password"
                      :type="showPassword ? 'text' : 'password'"
                      prepend-inner-icon="LockIcon"
                      :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                      @click:append-inner="showPassword = !showPassword"
                      :rules="form.id ? [] : [v => !!v || 'Password required', v => v.length >= 6 || 'Min 6 characters']"
                      :hint="form.id ? 'Leave blank to keep current password' : ''"
                      persistent-hint
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
                      rows="2"
                      auto-grow
                    />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.role_id"
                      :items="availableRoles"
                      item-title="name"
                      item-value="id"
                      label="Role"
                      prepend-inner-icon="ShieldIcon"
                      :rules="[v => !!v || 'Role is required']"
                      required
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
                      :rules="[v => !!v || 'Department is required']"
                      required
                    />
                  </v-col>

                  <!-- Beautiful Drag & Drop Image Upload -->
                  <v-col cols="12">
                    <div
                      class="upload-box mb-6"
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
                        <p class="text-caption mt-2">Click or drop to change image</p>
                      </div>
                      <div v-else class="placeholder text-center">
                        <v-icon size="48" color="grey">mdi-cloud-upload</v-icon>
                        <p class="mt-2">Click or Drag & Drop to upload image</p>
                      </div>
                    </div>
                  </v-col>
                </v-row>

                <!-- Error Alert -->
                <v-alert
                  v-if="errorMessage"
                  type="error"
                  class="mb-4"
                  dismissible
                  @click="errorMessage = ''"
                >
                  {{ errorMessage }}
                </v-alert>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmClose">Cancel</v-btn>
              <v-btn color="primary" :loading="saving" @click="saveUser">
                {{ form.id ? 'Update User' : 'Create User' }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Confirm Discard -->
        <v-dialog v-model="confirmDiscardDialog" max-width="460">
          <v-card>
            <v-card-title class="text-h6 text-orange-darken-2">
              <AlertTriangleIcon :size="24" class="mr-2" />
              Unsaved Changes
            </v-card-title>
            <v-card-text class="pt-4">
              You have unsaved changes. Do you want to discard them?
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmDiscardDialog = false">Stay</v-btn>
              <v-btn color="error" @click="forceCloseDialog">Discard</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="420">
          <v-card>
            <v-card-title class="text-h6 text-error">
              <TrashIcon :size="24" class="mr-2" />
              Delete User?
            </v-card-title>
            <v-card-text>
              Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>?<br>
              This action cannot be undone.
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
              <v-btn color="error" :loading="deleting" @click="deleteUser">Delete</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- View User -->
        <v-dialog v-model="viewDialog" max-width="600">
          <v-card v-if="selectedUser">
            <v-card-title class="text-h5 d-flex align-center gap-3">
              <UserCircleIcon :size="28" />
              User Details
            </v-card-title>
            <v-card-text>
              <div class="text-center mb-6">
                <v-avatar size="120">
                  <v-img :src="getImageUrl(selectedUser.image_profile, selectedUser.name)" cover class="rounded-circle" />
                </v-avatar>
                <div class="mt-4">
                  <h3 class="text-h6">{{ selectedUser.name }}</h3>
                  <p class="text-body-1">{{ selectedUser.email }}</p>
                </div>
              </div>
              <v-divider />
              <v-list lines="two">
                <v-list-item><v-list-item-title>Phone</v-list-item-title><v-list-item-subtitle>{{ selectedUser.phone_number || '—' }}</v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Address</v-list-item-title><v-list-item-subtitle>{{ selectedUser.address || '—' }}</v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Role</v-list-item-title><v-list-item-subtitle><v-chip small :color="getRoleColor(selectedUser.role?.name)">{{ selectedUser.role?.name || '—' }}</v-chip></v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Department</v-list-item-title><v-list-item-subtitle>{{ selectedUser.department?.name || '—' }}</v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Joined</v-list-item-title><v-list-item-subtitle>{{ formatDate(selectedUser.created_at) }}</v-list-item-subtitle></v-list-item>
              </v-list>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="viewDialog = false">Close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Snackbar -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" location="top">
          <CircleCheckIcon class="mr-2" :size="22" v-if="snackbar.color === 'success'" />
          <CircleXIcon class="mr-2" :size="22" v-else />
          {{ snackbar.message }}
          <template #actions>
            <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
          </template>
        </v-snackbar>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import axiosClient from "@/plugins/axios";

import {
  PlusIcon, SearchIcon, DotsVerticalIcon, EyeIcon, EditIcon, TrashIcon,
  UserPlusIcon, UserIcon, MailIcon, LockIcon, PhoneIcon, ShieldIcon,
  BuildingIcon, CheckIcon, AlertTriangleIcon, CircleCheckIcon,
  CircleXIcon, UserCircleIcon, MapPinIcon
} from "vue-tabler-icons";

interface Role { id: number; name: string }
interface Department { id: number; name: string }
interface User {
  id: number;
  name: string;
  email: string;
  image_profile: string | null;
  phone_number: string | null;
  address: string | null;
  role: { id: number; name: string } | null;
  department: { id: number; name: string } | null;
  created_at: string;
}

const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const departments = ref<Department[]>([]);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const search = ref("");
const dialog = ref(false);
const viewDialog = ref(false);
const deleteDialog = ref(false);
const confirmDiscardDialog = ref(false);
const selectedUser = ref<User | null>(null);
const userToDelete = ref<User | null>(null);
const formDirty = ref(false);
const showPassword = ref(false);
const errorMessage = ref<string>("");
const fileInput = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(null);

const snackbar = ref({ show: false, message: "", color: "success" as "success" | "error" });
const showMessage = (message: string, color: "success" | "error" = "success") => {
  snackbar.value = { show: true, message, color };
};

const form = ref({
  id: null as number | null,
  name: "",
  email: "",
  password: "",
  phone_number: "",
  address: "",
  image_profile: null as File | null,
  role_id: null as number | null,
  department_id: null as number | null,
});

let currentImageUrl = "";

// Hide "Admin" role from dropdown if one already exists (except when editing the admin)
const availableRoles = computed(() => {
  const hasAdmin = users.value.some(u => u.role?.name.toLowerCase() === 'admin');
  const adminRole = roles.value.find(r => r.name.toLowerCase() === 'admin');
  if (hasAdmin && adminRole && !form.value.id) {
    return roles.value.filter(r => r.id !== adminRole.id);
  }
  return roles.value;
});

const isLastAdmin = (user: User) => {
  return user.role?.name.toLowerCase() === 'admin' &&
    users.value.filter(u => u.role?.name.toLowerCase() === 'admin').length === 1;
};

type UserTableHeader = {
  readonly title: string;
  readonly key: string;
  readonly sortable?: boolean;
  readonly width?: number | string;
  readonly align?: "start" | "center" | "end";
};

const headers: readonly UserTableHeader[] = [
  { title: "Avatar", key: "image_profile", sortable: false, width: 100, align: "center" },
  { title: "Name", key: "name", align: "start" },
  { title: "Email", key: "email", align: "start" },
  { title: "Phone", key: "phone_number", align: "start" },
  { title: "Address", key: "address", sortable: false, align: "start" },
  { title: "Role", key: "role", sortable: false, align: "center" },
  { title: "Department", key: "department", sortable: false, align: "center" },
  { title: "Joined", key: "created_at", align: "end" },
  { title: "Actions", key: "actions", sortable: false, align: "center" },
];


onMounted(async () => {
  try {
    const [u, r, d] = await Promise.all([
      axiosClient.get("/users"),
      axiosClient.get("/roles"),
      axiosClient.get("/departments")
    ]);
    users.value = u.data.data || u.data;
    roles.value = r.data.data || r.data;
    departments.value = d.data.data || d.data;
  } catch {
    showMessage("Failed to load data", "error");
  } finally {
    loading.value = false;
  }
});

const getImageUrl = (url: string | null, name: string = "User") => {
  if (!url) return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&bold=true&rounded=true`;
  return url.startsWith("http") ? url : `${import.meta.env.VITE_APP_URL || ""}${url}`;
};

const getRoleColor = (role: string | undefined) => {
  const map: Record<string, string> = {
    admin: "red", superadmin: "deep-purple", manager: "orange",
    employee: "blue", editor: "purple", user: "green", developer: "indigo"
  };
  return map[role?.toLowerCase() || ""] || "grey";
};

const formatDate = (date: string) => new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });

const openDialog = (user?: User) => {
  if (user) {
    form.value = {
      id: user.id,
      name: user.name,
      email: user.email,
      password: "",
      phone_number: user.phone_number || "",
      address: user.address || "",
      image_profile: null,
      role_id: user.role?.id || null,
      department_id: user.department?.id || null,
    };
    currentImageUrl = getImageUrl(user.image_profile, user.name);
    imagePreview.value = currentImageUrl;
  } else {
    form.value = { id: null, name: "", email: "", password: "", phone_number: "", address: "", image_profile: null, role_id: null, department_id: null };
    imagePreview.value = null;
    currentImageUrl = "";
  }
  errorMessage.value = "";
  formDirty.value = false;
  dialog.value = true;
};

const confirmClose = () => formDirty.value ? (confirmDiscardDialog.value = true) : closeDialog();
const forceCloseDialog = () => { confirmDiscardDialog.value = false; closeDialog(); };
const closeDialog = () => {
  dialog.value = false;
  formDirty.value = false;
  if (imagePreview.value?.startsWith("blob:")) URL.revokeObjectURL(imagePreview.value);
  imagePreview.value = null;
};

// Image Upload Handlers
const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  if (input.files?.[0]) {
    form.value.image_profile = input.files[0];
    previewImage(input.files[0]);
  }
};

const handleDrop = (e: DragEvent) => {
  e.preventDefault();
  const file = e.dataTransfer?.files[0];
  if (file && file.type.startsWith('image/')) {
    form.value.image_profile = file;
    previewImage(file);
  }
};

const previewImage = (file: File) => {
  if (imagePreview.value?.startsWith("blob:")) URL.revokeObjectURL(imagePreview.value);
  imagePreview.value = URL.createObjectURL(file);
};

const openFilePicker = () => fileInput.value?.click();

const saveUser = async () => {
  errorMessage.value = "";

  const data = new FormData();
  data.append("name", form.value.name);
  data.append("email", form.value.email);
  if (form.value.password) data.append("password", form.value.password);
  if (form.value.phone_number) data.append("phone_number", form.value.phone_number);
  if (form.value.address) data.append("address", form.value.address);
  data.append("role_id", form.value.role_id!.toString());
  data.append("department_id", form.value.department_id!.toString());
  if (form.value.image_profile) data.append("image_profile", form.value.image_profile);

  try {
    saving.value = true;
    let response;
    if (form.value.id) {
      response = await axiosClient.post(`/users/${form.value.id}?_method=PUT`, data);
      const updated = response.data.data || response.data;
      const idx = users.value.findIndex(u => u.id === form.value.id);
      if (idx !== -1) users.value.splice(idx, 1, updated);
      showMessage("User updated successfully");
    } else {
      response = await axiosClient.post("/users", data);
      users.value.unshift(response.data.data || response.data);
      showMessage("User created successfully");
    }
    closeDialog();
  } catch (err: any) {
    const validationErrors = err.response?.data?.errors;
    const backendMessage = err.response?.data?.message;
    const message = validationErrors
      ? Object.values(validationErrors as Record<string, string[]>).flat().join(", ")
      : backendMessage ?? "Operation failed";

    errorMessage.value = message;
    showMessage(message, "error");
  } finally {
    saving.value = false;
  }
};

const viewUser = (user: User) => { selectedUser.value = user; viewDialog.value = true; };
const confirmDelete = (user: User) => { userToDelete.value = user; deleteDialog.value = true; };

const deleteUser = async () => {
  if (!userToDelete.value) return;
  try {
    deleting.value = true;
    await axiosClient.delete(`/users/${userToDelete.value.id}`);
    users.value = users.value.filter(u => u.id !== userToDelete.value!.id);
    showMessage("User deleted successfully");
  } catch {
    showMessage("Cannot delete the last Admin user", "error");
  } finally {
    deleting.value = false;
    deleteDialog.value = false;
    userToDelete.value = null;
  }
};

watch(() => form.value.image_profile, (file) => {
  if (file instanceof File) previewImage(file);
});
watch(form, () => { formDirty.value = true; }, { deep: true });

onUnmounted(() => {
  if (imagePreview.value?.startsWith("blob:")) URL.revokeObjectURL(imagePreview.value);
});
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }
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
}
.upload-box:hover {
  border-color: #1976d2;
  background-color: #f0f7ff;
}
.file-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.preview-img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #1976d2;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.placeholder { color: #666; }
.placeholder p { margin: 8px 0 0; font-size: 14px; }
</style>