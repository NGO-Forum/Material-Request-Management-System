<!-- src/views/roles/RolesView.vue -->
<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-6">
          <h1 class="text-h4 font-weight-bold">Roles Management</h1>
          <v-btn color="primary" @click="openDialog()">
            <PlusIcon class="mr-2" :size="20" />
            Add Role
          </v-btn>
        </div>

        <v-card elevation="4" class="rounded-lg">
          <v-card-title class="pa-4">
            <v-text-field
              v-model="search"
              append-inner-icon="SearchIcon"
              label="Search roles..."
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
            />
          </v-card-title>

          <v-data-table
            :headers="visibleHeaders"
            :items="roles"
            :search="search"
            :loading="loading"
            loading-text="Loading roles..."
            items-per-page="15"
            class="elevation-1"
            density="compact"
            :sort-by="[{ key: 'created_at', order: 'desc' }]"
          >
            <!-- Role Name -->
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

            <!-- Description (only visible when header exists) -->
            <template #item.description="{ item }">
              <span class="text-caption">{{ item.description || '—' }}</span>
            </template>

            <template #item.created_at="{ item }">
              {{ safeDate(item.created_at) }}
            </template>

            <template #item.actions="{ item }">
              <v-menu location="bottom">
                <template #activator="{ props }">
                  <v-btn icon size="small" v-bind="props">
                    <DotsVerticalIcon :size="20" />
                  </v-btn>
                </template>
                <v-list density="compact">
                  <v-list-item @click="openDialog(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <EditIcon :size="18" />
                      Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="confirmDelete(item)" class="text-error">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <TrashIcon :size="18" class="text-error" />
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-card>

        <!-- Add/Edit Dialog -->
        <v-dialog v-model="dialog" max-width="600" persistent>
          <v-card class="pa-4">
            <v-card-title class="text-h5 font-weight-bold d-flex align-center gap-3">
              <ShieldIcon :size="28" />
              {{ form.id ? 'Edit Role' : 'Add New Role' }}
            </v-card-title>

            <v-card-text>
              <v-form @submit.prevent="saveRole">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.name"
                      label="Role Name"
                      prepend-inner-icon="TagIcon"
                      :rules="nameRules"
                      required
                      autofocus
                    />
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Description (Optional)"
                      prepend-inner-icon="FileDescriptionIcon"
                      rows="3"
                      auto-grow
                      clearable
                    />
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmClose">Cancel</v-btn>
              <v-btn
                color="primary"
                :loading="saving"
                :disabled="saving || !form.name.trim()"
                @click="saveRole"
              >
                <CheckIcon class="mr-2" :size="20" v-if="form.id" />
                <PlusIcon class="mr-2" :size="20" v-else />
                {{ form.id ? 'Update' : 'Create' }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Unsaved Changes Dialog -->
        <v-dialog v-model="confirmDiscardDialog" max-width="460" persistent>
          <v-card>
            <v-card-title class="text-h6 text-orange-darken-2">
              <AlertTriangleIcon :size="24" class="mr-2" />
              Unsaved Changes
            </v-card-title>
            <v-card-text class="pt-4">
              You have unsaved changes. Do you really want to <strong>discard</strong> them?
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
              Delete Role?
            </v-card-title>
            <v-card-text>
              Delete <strong>{{ roleToDelete?.name }}</strong>?<br>
              <small class="text-medium-emphasis">This cannot be undone.</small>
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
              <v-btn color="error" :loading="deleting" @click="deleteRole">
                Delete
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Snackbar -->
        <v-snackbar
          v-model="snackbar.show"
          :color="snackbar.color"
          timeout="4000"
          location="top"
        >
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
import { ref, watch, onMounted, computed } from "vue";
import { useDisplay } from "vuetify"; // ← This is the correct import
import axiosClient from "@/plugins/axios";

import {
  ShieldIcon,
  PlusIcon,
  SearchIcon,
  DotsVerticalIcon,
  EditIcon,
  TrashIcon,
  TagIcon,
  FileDescriptionIcon,
  CheckIcon,
  AlertTriangleIcon,
  CircleCheckIcon,
  CircleXIcon
} from "vue-tabler-icons";

interface Role {
  id: number;
  name: string;
  description: string | null;
  created_at: string | null;
}

const roles = ref<Role[]>([]);
const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const search = ref("");
const dialog = ref(false);
const deleteDialog = ref(false);
const confirmDiscardDialog = ref(false);
const roleToDelete = ref<Role | null>(null);
const formDirty = ref(false);

const snackbar = ref({
  show: false,
  message: "",
  color: "success" as "success" | "error"
});

const showMessage = (msg: string, color: "success" | "error" = "success") => {
  snackbar.value = { show: true, message: msg, color };
};

const form = ref({
  id: null as number | null,
  name: "",
  description: ""
});

const nameRules = [
  (v: string) => !!v || "Role name is required",
  (v: string) => (v && v.length >= 2) || "Minimum 2 characters"
];

// Base headers
const baseHeaders = [
  { title: "Role", key: "name", width: 180 },
  { title: "Description", key: "description" },
  { title: "Created", key: "created_at", width: 150 },
  { title: "Actions", key: "actions", sortable: false, align: "center", width: 100 }
];

// Responsive headers using Vuetify's useDisplay (recommended & reactive)
const { mdAndUp } = useDisplay();

const visibleHeaders = computed(() => {
  return mdAndUp.value
    ? baseHeaders
    : baseHeaders.filter(h => h.key !== "description");
});

onMounted(() => {
  loadRoles();
  // No need for manual resize listener — useDisplay() handles it automatically
});

const loadRoles = async () => {
  try {
    loading.value = true;
    const res = await axiosClient.get("/roles");
    roles.value = (res.data.data || res.data || []).map((r: any) => ({
      ...r,
      created_at: r.created_at || new Date().toISOString()
    }));
  } catch {
    showMessage("Failed to load roles", "error");
  } finally {
    loading.value = false;
  }
};

const getRoleColor = (name: string) => {
  const lower = name.toLowerCase();
  const map: Record<string, string> = {
    admin: "red",
    superadmin: "deep-purple",
    manager: "orange",
    employee: "blue",
    editor: "purple",
    user: "green",
    developer: "indigo",
    moderator: "teal"
  };
  for (const key in map) {
    if (lower.includes(key)) return map[key];
  }
  return "grey";
};

const safeDate = (date: string | null) => {
  if (!date) return "—";
  const d = new Date(date);
  return isNaN(d.getTime())
    ? "—"
    : d.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
};

const openDialog = (role?: Role) => {
  form.value = role
    ? { id: role.id, name: role.name, description: role.description || "" }
    : { id: null, name: "", description: "" };
  formDirty.value = false;
  dialog.value = true;
};

const confirmClose = () => {
  formDirty.value ? (confirmDiscardDialog.value = true) : closeDialog();
};

const forceCloseDialog = () => {
  confirmDiscardDialog.value = false;
  closeDialog();
};

const closeDialog = () => {
  dialog.value = false;
  formDirty.value = false;
  form.value = { id: null, name: "", description: "" };
};

const saveRole = async () => {
  if (!form.value.name.trim()) return;

  try {
    saving.value = true;
    const payload = { name: form.value.name.trim(), description: form.value.description || null };

    if (form.value.id) {
      const res = await axiosClient.put(`/roles/${form.value.id}`, payload);
      const updated = res.data.data || res.data;
      const idx = roles.value.findIndex(r => r.id === form.value.id);
      if (idx !== -1) roles.value[idx] = updated;
      showMessage("Role updated successfully");
    } else {
      const res = await axiosClient.post("/roles", payload);
      const newRole = res.data.data || res.data;
      newRole.created_at = newRole.created_at || new Date().toISOString();
      roles.value.unshift(newRole);
      showMessage("Role created successfully");
    }
    closeDialog();
  } catch (err: any) {
    const msg =
      err.response?.data?.message ||
      (err.response?.data?.errors
        ? Object.values(err.response.data.errors).flat().join(", ")
        : "Operation failed");
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
    roles.value = roles.value.filter(r => r.id !== roleToDelete.value!.id);
    showMessage("Role deleted successfully");
  } catch (err: any) {
    showMessage(err.response?.data?.message || "Cannot delete role (in use)", "error");
  } finally {
    deleting.value = false;
    deleteDialog.value = false;
    roleToDelete.value = null;
  }
};

watch(form, () => { formDirty.value = true; }, { deep: true });
</script>

<style scoped>
.v-icon { font-size: 20px !important; }
</style>