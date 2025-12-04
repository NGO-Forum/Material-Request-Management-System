<!-- src/views/users/UsersView.vue -->
<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-6">
          <h1 class="text-h4 font-weight-bold">Users Management</h1>
          <v-btn color="primary" @click="openDialog()">
            <v-icon start>bx-plus</v-icon>
            Add User
          </v-btn>
        </div>

        <v-card elevation="4" class="rounded-lg">
          <v-card-title class="pa-4">
            <v-text-field
              v-model="search"
              append-inner-icon="bx-search"
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
                      <v-icon size="28">bx-user-circle</v-icon>
                    </div>
                  </template>
                </v-img>
              </v-avatar>
            </template>

            <!-- Role -->
            <template #item.role="{ item }">
              <v-chip
                :color="getRoleColor(item.role?.name)"
                size="small"
                label
              >
                {{ item.role?.name || 'N/A' }}
              </v-chip>
            </template>

            <!-- Department -->
            <template #item.department="{ item }">
              <span class="text-caption">
                {{ item.department?.name || '—' }}
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
                    <v-icon>bx-dots-vertical-rounded</v-icon>
                  </v-btn>
                </template>

                <v-list density="compact">
                  <v-list-item @click="viewUser(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <v-icon color="black">bx-show</v-icon>
                      <span>View</span>
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="openDialog(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <v-icon color="black">bx-edit-alt</v-icon>
                      <span>Edit</span>
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item @click="confirmDelete(item)" class="text-error">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <v-icon color="red">bx-trash</v-icon>
                      <span>Delete</span>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-card>

        <!-- Add/Edit User Dialog -->
        <v-dialog v-model="dialog" max-width="700" persistent @click:outside="confirmClose">
          <v-card class="pa-4">
            <v-card-title class="text-h5 font-weight-bold d-flex align-center gap-2">
              <v-icon>bx-user-plus</v-icon>
              {{ form.id ? 'Edit User' : 'Add New User' }}
            </v-card-title>

            <v-card-text>
              <v-form @submit.prevent="saveUser">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.name"
                      label="Full Name"
                      prepend-inner-icon="bx-user"
                      :rules="[v => !!v || 'Name is required']"
                      required
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.email"
                      label="Email"
                      type="email"
                      prepend-inner-icon="bx-envelope"
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
                      type="password"
                      prepend-inner-icon="bx-lock-alt"
                      :rules="form.id ? [] : [v => !!v || 'Password required', v => (v && v.length >= 6) || 'Min 6 characters']"
                      :hint="form.id ? 'Leave blank to keep current' : ''"
                      persistent-hint
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.phone_number"
                      label="Phone Number"
                      prepend-inner-icon="bx-phone"
                    />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.role_id"
                      :items="roles"
                      item-title="name"
                      item-value="id"
                      label="Role"
                      prepend-icon="bx-shield-alt"
                      clearable
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.department_id"
                      :items="departments"
                      item-title="name"
                      item-value="id"
                      label="Department"
                      prepend-icon="bx-building"
                      clearable
                    />
                  </v-col>

                  <v-col cols="12">
                    <v-file-input
                      v-model="form.image_profile"
                      label="Profile Image"
                      accept="image/*"
                      prepend-icon="bx-camera"
                      clearable
                      show-size
                    />
                    <div v-if="imagePreview" class="mt-4 text-center">
                      <v-img :src="imagePreview" max-height="160" max-width="160" class="rounded-lg mx-auto" />
                    </div>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmClose">Cancel</v-btn>
              <v-btn color="primary" :loading="saving" :disabled="saving" @click="saveUser">
                <v-icon start>{{ form.id ? 'bx-check' : 'bx-plus' }}</v-icon>
                {{ form.id ? 'Update' : 'Create' }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Unsaved Changes Confirmation Dialog -->
        <v-dialog v-model="confirmDiscardDialog" max-width="460" persistent>
          <v-card>
            <v-card-title class="text-h6 text-orange-darken-2">
              <v-icon color="orange-darken-2" start>bx-warning</v-icon>
              Unsaved Changes
            </v-card-title>
            <v-card-text class="pt-4 text-body-1">
              You have made changes that haven't been saved.<br>
              Do you really want to <strong>discard</strong> them?
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmDiscardDialog = false">
                Stay & Continue Editing
              </v-btn>
              <v-btn color="error" @click="forceCloseDialog">
                Discard Changes
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="420">
          <v-card>
            <v-card-title class="text-h6 text-error d-flex align-center gap-2">
              <v-icon>bx-trash</v-icon> Delete User?
            </v-card-title>
            <v-card-text>
              Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>?
              This action cannot be undone.
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
              <v-btn color="error" :loading="deleting" @click="deleteUser">
                <v-icon start>bx-check</v-icon> Delete
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- View User Details -->
        <v-dialog v-model="viewDialog" max-width="600">
          <v-card v-if="selectedUser">
            <v-card-title class="text-h5 d-flex align-center gap-2">
              <v-icon>bx-user-circle</v-icon> User Details
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
                <v-list-item>
                  <v-list-item-title>Phone</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedUser.phone_number || '—' }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Role</v-list-item-title>
                  <v-list-item-subtitle>
                    <v-chip small :color="getRoleColor(selectedUser.role?.name)">
                      {{ selectedUser.role?.name || '—' }}
                    </v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Department</v-list-item-title>
                  <v-list-item-subtitle>{{ selectedUser.department?.name || '—' }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item>
                  <v-list-item-title>Joined</v-list-item-title>
                  <v-list-item-subtitle>{{ formatDate(selectedUser.created_at) }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="viewDialog = false">Close</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Snackbar -->
        <v-snackbar
          v-model="snackbar.show"
          :color="snackbar.color"
          :timeout="4000"
          location="top"
        >
          <v-icon start>{{ snackbar.color === 'success' ? 'bx-check-circle' : 'bx-error' }}</v-icon>
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
import { ref, watch, onMounted, onUnmounted } from "vue";
import axiosClient from "@/plugins/axios";

interface Role { id: number; name: string }
interface Department { id: number; name: string }
interface User {
  id: number;
  name: string;
  email: string;
  image_profile: string | null;
  phone_number: string | null;
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

const snackbar = ref({
  show: false,
  message: "",
  color: "success" as "success" | "error"
});

const showMessage = (message: string, color: "success" | "error" = "success") => {
  snackbar.value = { show: true, message, color };
};

let currentImageUrl = "";

const form = ref({
  id: null as number | null,
  name: "",
  email: "",
  password: "",
  phone_number: "",
  image_profile: null as File | null,
  role_id: null as number | null,
  department_id: null as number | null,
});

const imagePreview = ref<string | null>(null);

const headers = [
  { title: "Avatar", key: "image_profile", sortable: false, width: 100, align: "center" },
  { title: "Name", key: "name" },
  { title: "Email", key: "email" },
  { title: "Phone", key: "phone_number" },
  { title: "Role", key: "role", sortable: false },
  { title: "Department", key: "department", sortable: false },
  { title: "Joined", key: "created_at" },
  { title: "Actions", key: "actions", sortable: false, align: "center" },
];

onMounted(async () => {
  try {
    const [u, r, d] = await Promise.all([
      axiosClient.get("/users"),
      axiosClient.get("/roles"),
      axiosClient.get("/departments")
    ]);
    users.value = u.data;
    roles.value = r.data;
    departments.value = d.data;
  } catch {
    showMessage("Failed to load data", "error");
  } finally {
    loading.value = false;
  }
});

const getImageUrl = (url: string | null, name: string = "User") => {
  if (!url) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random&bold=true&rounded=true`;
  }
  return url.startsWith("http") ? url : `${import.meta.env.VITE_APP_URL || ""}${url}`;
};

const getRoleColor = (role: string | undefined) => {
  const map: Record<string, string> = {
    admin: "red",
    manager: "orange",
    editor: "purple",
    user: "green",
  };
  return map[role?.toLowerCase() || ""] || "grey";
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
};

const openDialog = (user?: User) => {
  if (user) {
    form.value = {
      id: user.id,
      name: user.name,
      email: user.email,
      password: "",
      phone_number: user.phone_number || "",
      image_profile: null,
      role_id: user.role?.id || null,
      department_id: user.department?.id || null,
    };
    currentImageUrl = getImageUrl(user.image_profile, user.name);
    imagePreview.value = currentImageUrl;
  } else {
    form.value = {
      id: null,
      name: "",
      email: "",
      password: "",
      phone_number: "",
      image_profile: null,
      role_id: null,
      department_id: null,
    };
    currentImageUrl = "";
    imagePreview.value = null;
  }
  formDirty.value = false;
  dialog.value = true;
};

const confirmClose = () => {
  if (!formDirty.value) {
    closeDialog();
    return;
  }
  confirmDiscardDialog.value = true;
};

const forceCloseDialog = () => {
  confirmDiscardDialog.value = false;
  closeDialog();
};

const closeDialog = () => {
  dialog.value = false;
  formDirty.value = false;
  if (imagePreview.value?.startsWith("blob:")) {
    URL.revokeObjectURL(imagePreview.value);
    imagePreview.value = null;
  }
};

const saveUser = async () => {
  const data = new FormData();

  data.append("name", form.value.name);
  data.append("email", form.value.email);

  if (form.value.id) {
    if (form.value.password) {
      data.append("password", form.value.password);
    }
  } else {
    data.append("password", form.value.password);
  }

  if (form.value.phone_number) data.append("phone_number", form.value.phone_number);
  if (form.value.role_id !== null) data.append("role_id", form.value.role_id.toString());
  if (form.value.department_id !== null) data.append("department_id", form.value.department_id.toString());

  if (form.value.image_profile instanceof File) {
    data.append("image_profile", form.value.image_profile);
  }

  try {
    saving.value = true;
    let response;

    if (form.value.id) {
      response = await axiosClient.post(`/users/${form.value.id}?_method=PUT`, data); // Use POST with _method=PUT for FormData
      showMessage("User updated successfully");
      const index = users.value.findIndex(u => u.id === form.value.id);
      if (index !== -1) users.value[index] = response.data.data;
    } else {
      response = await axiosClient.post("/users", data);
      showMessage("User created successfully");
      users.value.unshift(response.data.data);
    }
    closeDialog();
  } catch (err: any) {
    let msg = "Operation failed";
    if (err.response?.data?.errors) {
      msg = Object.values(err.response.data.errors).flat().join(", ");
    } else if (err.response?.data?.message) {
      msg = err.response.data.message;
    }
    showMessage(msg, "error");
  } finally {
    saving.value = false;
  }
};

const viewUser = (user: User) => {
  selectedUser.value = user;
  viewDialog.value = true;
};

const confirmDelete = (user: User) => {
  userToDelete.value = user;
  deleteDialog.value = true;
};

const deleteUser = async () => {
  if (!userToDelete.value) return;
  try {
    deleting.value = true;
    await axiosClient.delete(`/users/${userToDelete.value.id}`);
    showMessage("User deleted successfully");
    users.value = users.value.filter(u => u.id !== userToDelete.value!.id);
  } catch {
    showMessage("Failed to delete user", "error");
  } finally {
    deleting.value = false;
    deleteDialog.value = false;
    userToDelete.value = null;
  }
};

watch(() => form.value.image_profile, (newFile) => {
  if (imagePreview.value?.startsWith("blob:")) {
    URL.revokeObjectURL(imagePreview.value);
  }
  imagePreview.value = newFile instanceof File ? URL.createObjectURL(newFile) : currentImageUrl;
});

watch(form, () => { formDirty.value = true; }, { deep: true });

onUnmounted(() => {
  if (imagePreview.value?.startsWith("blob:")) {
    URL.revokeObjectURL(imagePreview.value);
  }
});
</script>

<style scoped>
@import 'https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css';

.cursor-pointer { cursor: pointer; }
.gap-3 { gap: 12px; }
.bx { font-size: 20px !important; }
</style>