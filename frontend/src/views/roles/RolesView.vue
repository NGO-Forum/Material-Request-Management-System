<!-- src/views/roles/RolesView.vue -->
<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-6">
          <h1 class="text-h4 font-weight-bold">Roles Management</h1>
          <v-btn color="primary" @click="openDialog()">
            <v-icon start>bx-plus</v-icon>
            Add Role
          </v-btn>
        </div>

        <v-card elevation="4" class="rounded-lg">
          <v-card-title class="pa-4">
            <v-text-field
              v-model="search"
              append-inner-icon="bx-search"
              label="Search roles..."
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
            />
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="roles"
            :search="search"
            :loading="loading"
            loading-text="Loading roles..."
            items-per-page="15"
            class="elevation-1"
            density="compact"
          >
            <!-- Role Name with Color -->
            <template #item.name="{ item }">
              <v-chip
                :color="getRoleColor(item.name)"
                size="small"
                label
                class="font-weight-medium"
              >
                {{ item.name }}
              </v-chip>
            </template>

            <!-- Description -->
            <template #item.description="{ item }">
              <span class="text-caption">{{ item.description || '—' }}</span>
            </template>

            <!-- Created At -->
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

        <!-- Add/Edit Dialog -->
        <v-dialog v-model="dialog" max-width="600" persistent @click:outside="confirmClose">
          <v-card class="pa-4">
            <v-card-title class="text-h5 font-weight-bold d-flex align-center gap-2">
              <v-icon>bx-shield-alt</v-icon>
              {{ form.id ? 'Edit Role' : 'Add New Role' }}
            </v-card-title>

            <v-card-text>
              <v-form @submit.prevent="saveRole">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.name"
                      label="Role Name"
                      prepend-inner-icon="bx-tag"
                      :rules="[v => !!v || 'Role name is required', v => (v && v.length >= 2) || 'Min 2 characters']"
                      required
                    />
                  </v-col>

                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Description"
                      prepend-inner-icon="bx-detail"
                      rows="3"
                      auto-grow
                    />
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmClose">Cancel</v-btn>
              <v-btn color="primary" :loading="saving" :disabled="saving" @click="saveRole">
                <v-icon start>{{ form.id ? 'bx-check' : 'bx-plus' }}</v-icon>
                {{ form.id ? 'Update' : 'Create' }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="420">
          <v-card>
            <v-card-title class="text-h6 text-error d-flex align-center gap-2">
              <v-icon>bx-trash</v-icon> Delete Role?
            </v-card-title>
            <v-card-text>
              Are you sure you want to delete <strong>{{ roleToDelete?.name }}</strong>?
              <br /><small>This may affect users assigned to this role.</small>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
              <v-btn color="error" :loading="deleting" @click="deleteRole">
                <v-icon start>bx-check</v-icon> Delete
              </v-btn>
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
import { ref, onMounted, watch } from "vue";
import axiosClient from "@/plugins/axios";

interface Role {
  id: number;
  name: string;
  description: string | null;
  created_at: string;
  updated_at: string | null;
}

const roles = ref<Role[]>([]);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const search = ref("");
const dialog = ref(false);
const deleteDialog = ref(false);
const roleToDelete = ref<Role | null>(null);
const formDirty = ref(false);

const snackbar = ref({
  show: false,
  message: "",
  color: "success" as "success" | "error"
});

const showMessage = (message: string, color: "success" | "error" = "success") => {
  snackbar.value = { show: true, message, color };
};

const form = ref({
  id: null as number | null,
  name: "",
  description: ""
});

const headers = [
  { title: "Role", key: "name", width: 180 },
  { title: "Description", key: "description" },
  { title: "Created", key: "created_at", width: 150 },
  { title: "Actions", key: "actions", sortable: false, align: "center", width: 100 },
];

onMounted(async () => {
  await loadRoles();
});

const loadRoles = async () => {
  try {
    loading.value = true;
    const res = await axiosClient.get("/roles");
    roles.value = res.data;
  } catch {
    showMessage("Failed to load roles", "error");
  } finally {
    loading.value = false;
  }
};

const getRoleColor = (roleName: string) => {
  const map: Record<string, string> = {
    admin: "red",
    manager: "orange",
    employee: "blue",
    user: "green"
  };
  return map[roleName?.toLowerCase()] || "grey";
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric"
  });
};

const openDialog = (role?: Role) => {
  if (role) {
    form.value = {
      id: role.id,
      name: role.name,
      description: role.description || ""
    };
  } else {
    form.value = { id: null, name: "", description: "" };
  }
  formDirty.value = false;
  dialog.value = true;
};

const confirmClose = () => {
  if (formDirty.value && !confirm("You have unsaved changes. Close anyway?")) return;
  closeDialog();
};

const closeDialog = () => {
  dialog.value = false;
  formDirty.value = false;
};

const saveRole = async () => {
  try {
    saving.value = true;
    let response;
    if (form.value.id) {
      response = await axiosClient.put(`/roles/${form.value.id}`, form.value);
      showMessage("Role updated successfully");
      const index = roles.value.findIndex(r => r.id === form.value.id);
      if (index !== -1) roles.value[index] = response.data;
    } else {
      response = await axiosClient.post("/roles", form.value);
      showMessage("Role created successfully");
      roles.value.unshift(response.data);
    }
    closeDialog();
  } catch (err: any) {
    const msg = err.response?.data?.message || "Operation failed";
    showMessage(msg, "error");
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (role: Role) => {
  roleToDelete.value = role;
  deleteDialog.value = true;
};

const deleteRole = async () => {
  if (!roleToDelete.value) return;
  try {
    deleting.value = true;
    await axiosClient.delete(`/roles/${roleToDelete.value.id}`);
    showMessage("Role deleted successfully");
    roles.value = roles.value.filter(r => r.id !== roleToDelete.value!.id);
  } catch (err: any) {
    showMessage(err.response?.data?.message || "Cannot delete role (in use?)", "error");
  } finally {
    deleting.value = false;
    deleteDialog.value = false;
    roleToDelete.value = null;
  }
};

// Track form changes
watch(() => form.value, () => { formDirty.value = true; }, { deep: true });
</script>

<style scoped>
@import 'https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css';

.cursor-pointer { cursor: pointer; }
.gap-3 { gap: 12px; }
.bx { font-size: 20px !important; }
</style>