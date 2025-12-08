<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-6">
          <h1 class="text-h4 font-weight-bold">Materials Management</h1>
          <v-btn color="primary" @click="openDialog">
            <PlusIcon class="mr-2" :size="20" />
            Add Material
          </v-btn>
        </div>

        <v-card elevation="4" class="rounded-lg">
          <v-card-title class="pa-4">
            <v-text-field
              v-model="search"
              append-inner-icon="mdi-magnify"
              label="Search materials..."
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
            />
          </v-card-title>

          <v-data-table
            :headers="visibleHeaders"
            :items="materials"
            :search="search"
            :loading="loading"
            loading-text="Loading materials..."
            items-per-page="15"
            class="elevation-1"
            density="compact"
          >
            <!-- Image -->
            <template #item.image="{ item }">
              <v-avatar size="56" class="my-3 cursor-pointer" @click="viewMaterial(item)">
                <v-img
                  :src="item.image || 'https://via.placeholder.com/150?text=No+Image'"
                  cover
                  class="rounded-lg"
                >
                  <template #placeholder>
                    <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
                      <Package :size="28" />
                    </div>
                  </template>
                </v-img>
              </v-avatar>
            </template>

            <!-- Name (always visible) -->
            <template #item.name="{ item }">
              <div class="text-truncate font-weight-medium" style="max-width: 180px;">
                {{ item.name }}
              </div>
            </template>

            <!-- Category -->
            <template #item.category="{ item }">
              <v-chip size="small" color="primary" label>
                {{ item.category?.name ?? '—' }}
              </v-chip>
            </template>

            <!-- Stock -->
            <template #item.qty_remaining="{ item }">
              <v-chip
                :color="item.qty_remaining === 0 ? 'error' : item.qty_remaining <= 5 ? 'warning' : 'success'"
                size="small"
              >
                {{ item.qty_remaining }} left
              </v-chip>
            </template>

            <!-- Condition -->
            <template #item.condition="{ item }">
              <v-chip :color="getConditionColor(item.condition)" size="small">
                {{ item.condition ?? '—' }}
              </v-chip>
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
                  <v-list-item @click="viewMaterial(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <EyeIcon :size="18" /> View
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="openDialog(item)">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <EditIcon :size="18" /> Edit
                    </v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="confirmDelete(item)" class="text-error">
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
        <v-dialog v-model="dialog" max-width="900" persistent>
          <v-card class="pa-4">
            <v-card-title class="text-h5 font-weight-bold d-flex align-center gap-3">
              <Package :size="28" />
              {{ form.id ? 'Edit Material' : 'Add New Material' }}
            </v-card-title>

            <v-card-text>
              <v-form @submit.prevent="saveMaterial">
                <v-row>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.name"
                      label="Material Name *"
                      prepend-inner-icon="mdi-package-variant"
                      :rules="[v => !!v || 'Name is required']"
                      required
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.model"
                      label="Model"
                      prepend-inner-icon="mdi-tag"
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.serial_number"
                      label="Serial Number"
                      prepend-inner-icon="mdi-pound"
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.category_id"
                      :items="categories"
                      item-title="name"
                      item-value="id"
                      label="Category *"
                      prepend-inner-icon="mdi-folder-multiple"
                      :rules="[v => !!v || 'Category is required']"
                      required
                    />
                  </v-col>

                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model.number="form.qty_stock"
                      label="Total Stock *"
                      type="number"
                      prepend-inner-icon="mdi-cube"
                      :rules="[v => v >= 0 || 'Must be ≥ 0']"
                    />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      v-model.number="form.qty_issued"
                      label="Issued"
                      type="number"
                      prepend-inner-icon="mdi-arrow-up-bold"
                      :rules="[v => v >= 0 || 'Must be ≥ 0']"
                    />
                  </v-col>
                  <v-col cols="12" md="4">
                    <v-text-field
                      :model-value="remainingStock"
                      label="Remaining"
                      readonly
                      prepend-inner-icon="mdi-check-circle"
                      color="success"
                    />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.location"
                      label="Location"
                      prepend-inner-icon="mdi-map-marker"
                    />
                  </v-col>
                  <v-col cols="12" md="6">
                    <v-select
                      v-model="form.condition"
                      :items="conditions"
                      label="Condition"
                      prepend-inner-icon="mdi-heart"
                    />
                  </v-col>
                  <v-col cols="12">
                    <v-textarea
                      v-model="form.remarks"
                      label="Remarks"
                      prepend-inner-icon="mdi-message-text"
                      rows="2"
                      auto-grow
                    />
                  </v-col>

                  <!-- Image Upload -->
                  <v-col cols="12">
                    <div class="upload-box mb-6" @drop.prevent="handleDrop" @dragover.prevent @click="openFilePicker">
                      <input ref="fileInput" type="file" accept="image/*" class="file-input" @change="handleFileChange" hidden />
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

                <v-alert v-if="errorMessage" type="error" class="mb-4" dismissible @click="errorMessage = ''">
                  {{ errorMessage }}
                </v-alert>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="confirmClose">Cancel</v-btn>
              <v-btn color="primary" :loading="saving" @click="saveMaterial">
                {{ form.id ? 'Update' : 'Create' }} Material
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
              Delete Material?
            </v-card-title>
            <v-card-text>
              Are you sure you want to delete <strong>{{ materialToDelete?.name }}</strong>?<br>
              This action cannot be undone.
            </v-card-text>
            <v-card-actions>
              <v-spacer />
              <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
              <v-btn color="error" :loading="deleting" @click="deleteMaterial">Delete</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- View Dialog -->
        <v-dialog v-model="viewDialog" max-width="700">
          <v-card v-if="selectedMaterial">
            <v-card-title class="text-h5 d-flex align-center gap-3">
              <Package :size="28" />
              Material Details
            </v-card-title>
            <v-card-text>
              <div class="text-center mb-6">
                <v-avatar size="140" class="mb-4">
                  <v-img :src="selectedMaterial.image || 'https://via.placeholder.com/150?text=No+Image'" cover class="rounded-lg" />
                </v-avatar>
                <h3 class="text-h6">{{ selectedMaterial.name }}</h3>
                <p class="text-body-1 text-grey-darken-1">{{ selectedMaterial.model }}</p>
              </div>
              <v-divider />
              <v-list lines="two">
                <v-list-item><v-list-item-title>Category</v-list-item-title><v-list-item-subtitle><v-chip small color="primary">{{ selectedMaterial.category?.name ?? '—' }}</v-chip></v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Serial Number</v-list-item-title><v-list-item-subtitle>{{ selectedMaterial.serial_number ?? '—' }}</v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Location</v-list-item-title><v-list-item-subtitle>{{ selectedMaterial.location ?? '—' }}</v-list-item-subtitle></v-list-item>
                <v-list-item><v-list-item-title>Condition</v-list-item-title><v-list-item-subtitle><v-chip small :color="getConditionColor(selectedMaterial.condition)">{{ selectedMaterial.condition ?? '—' }}</v-chip></v-list-item-subtitle></v-list-item>
                <v-list-item>
                  <v-list-item-title>Stock</v-list-item-title>
                  <v-list-item-subtitle>
                    Total: <strong>{{ selectedMaterial.qty_stock }}</strong> |
                    Issued: <strong>{{ selectedMaterial.qty_issued }}</strong> |
                    Remaining: <v-chip small :color="selectedMaterial.qty_remaining <= 5 ? 'warning' : 'success'">{{ selectedMaterial.qty_remaining }}</v-chip>
                  </v-list-item-subtitle>
                </v-list-item>
                <v-list-item><v-list-item-title>Remarks</v-list-item-title><v-list-item-subtitle>{{ selectedMaterial.remarks ?? '—' }}</v-list-item-subtitle></v-list-item>
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
          <CheckCircleIcon class="mr-2" :size="22" v-if="snackbar.color === 'success'" />
          <AlertCircleIcon class="mr-2" :size="22" v-else />
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
import { ref, watch, onMounted, onUnmounted, computed } from 'vue'
import { useDisplay } from 'vuetify'
import axiosClient from '@/plugins/axios'
import {
  PlusIcon,
  DotsVerticalIcon,
  EyeIcon,
  EditIcon,
  TrashIcon,
  AlertTriangleIcon,
  AlertCircleIcon,
} from 'vue-tabler-icons'

interface Category { id: number; name: string }
interface Material {
  id: number
  name: string
  model: string | null
  serial_number: string | null
  qty_stock: number
  qty_issued: number
  qty_remaining: number
  location: string | null
  condition: string | null
  image: string | null
  remarks: string | null
  category_id: number
  category: Category | null
}

const materials = ref<Material[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const search = ref('')
const dialog = ref(false)
const viewDialog = ref(false)
const deleteDialog = ref(false)
const confirmDiscardDialog = ref(false)
const selectedMaterial = ref<Material | null>(null)
const materialToDelete = ref<Material | null>(null)
const formDirty = ref(false)
const errorMessage = ref('')
const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string | null>(null)

const snackbar = ref({ show: false, message: '', color: 'success' as 'success' | 'error' })
const showMessage = (msg: string, color: 'success' | 'error' = 'success') => {
  snackbar.value = { show: true, message: msg, color }
}

const conditions = ['New', 'Good', 'Fair', 'Poor', 'Damaged']

const form = ref({
  id: null as number | null,
  name: '',
  model: '',
  serial_number: '',
  category_id: null as number | null,
  qty_stock: 0,
  qty_issued: 0,
  location: '',
  condition: 'Good',
  remarks: '',
  image: null as File | null
})

const remainingStock = computed(() => form.value.qty_stock - form.value.qty_issued)

// Responsive headers
const { smAndDown, xs } = useDisplay()

const baseHeaders = [
  { title: 'Image', key: 'image', sortable: false, width: 100, align: 'center' as const },
  { title: 'Name', key: 'name', width: 200 },
  { title: 'Model', key: 'model' },
  { title: 'Serial', key: 'serial_number' },
  { title: 'Category', key: 'category', sortable: false },
  { title: 'Stock', key: 'qty_remaining', align: 'center' as const },
  { title: 'Condition', key: 'condition', align: 'center' as const },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' as const }
]

const visibleHeaders = computed(() => {
  let headers = [...baseHeaders]

  if (smAndDown.value) {
    headers = headers.filter(h => 
      !['model', 'serial_number', 'category', 'qty_remaining', 'condition'].includes(h.key)
    )
  }

  // Optional: even more compact on phones
  if (xs.value) {
    // Already very clean — only Image, Name, Actions remain
  }

  return headers
})

onMounted(async () => {
  try {
    const [mRes, cRes] = await Promise.all([
      axiosClient.get('/materials'),
      axiosClient.get('/categories')
    ])
    materials.value = mRes.data.data || mRes.data
    categories.value = cRes.data.data || cRes.data
  } catch {
    showMessage('Failed to load data', 'error')
  } finally {
    loading.value = false
  }
})

const getConditionColor = (c: string | null) => {
  const map: Record<string, string> = { New: 'success', Good: 'primary', Fair: 'warning', Poor: 'orange', Damaged: 'error' }
  return map[c ?? ''] || 'grey'
}

const openDialog = (material?: Material) => {
  if (material) {
    Object.assign(form.value, {
      id: material.id,
      name: material.name,
      model: material.model ?? '',
      serial_number: material.serial_number ?? '',
      category_id: material.category_id,
      qty_stock: material.qty_stock,
      qty_issued: material.qty_issued,
      location: material.location ?? '',
      condition: material.condition ?? 'Good',
      remarks: material.remarks ?? '',
      image: null
    })
    imagePreview.value = material.image || null
  } else {
    form.value = {
      id: null, name: '', model: '', serial_number: '', category_id: null,
      qty_stock: 0, qty_issued: 0, location: '', condition: 'Good', remarks: '', image: null
    }
    imagePreview.value = null
  }
  errorMessage.value = ''
  formDirty.value = false
  dialog.value = true
}

const confirmClose = () => formDirty.value ? (confirmDiscardDialog.value = true) : closeDialog()
const forceCloseDialog = () => { confirmDiscardDialog.value = false; closeDialog() }
const closeDialog = () => {
  dialog.value = false
  formDirty.value = false
  if (imagePreview.value?.startsWith('blob:')) URL.revokeObjectURL(imagePreview.value)
  imagePreview.value = null
}

const handleFileChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) { form.value.image = file; previewImage(file) }
}
const handleDrop = (e: DragEvent) => {
  const file = e.dataTransfer?.files[0]
  if (file?.type.startsWith('image/')) { form.value.image = file; previewImage(file) }
}
const previewImage = (file: File) => {
  if (imagePreview.value?.startsWith('blob:')) URL.revokeObjectURL(imagePreview.value)
  imagePreview.value = URL.createObjectURL(file)
}
const openFilePicker = () => fileInput.value?.click()

const saveMaterial = async () => {
  errorMessage.value = ''
  const data = new FormData()
  data.append('name', form.value.name)
  data.append('model', form.value.model ?? '')
  if (form.value.serial_number) data.append('serial_number', form.value.serial_number)
  data.append('category_id', String(form.value.category_id!))
  data.append('qty_stock', String(form.value.qty_stock))
  data.append('qty_issued', String(form.value.qty_issued))
  if (form.value.location) data.append('location', form.value.location)
  data.append('condition', form.value.condition)
  if (form.value.remarks) data.append('remarks', form.value.remarks)
  if (form.value.image) data.append('image', form.value.image)

  try {
    saving.value = true
    let res
    if (form.value.id) {
      data.append('_method', 'PUT')
      res = await axiosClient.post(`/materials/${form.value.id}`, data)
      const idx = materials.value.findIndex(m => m.id === form.value.id)
      if (idx > -1) materials.value.splice(idx, 1, res.data.data ?? res.data)
      showMessage('Material updated successfully')
    } else {
      res = await axiosClient.post('/materials', data)
      materials.value.unshift(res.data.data ?? res.data)
      showMessage('Material created successfully')
    }
    closeDialog()
  } catch (err: any) {
    const msg = err.response?.data?.message ||
      Object.values(err.response?.data?.errors ?? {}).flat().join(', ') ||
      'Operation failed'
    errorMessage.value = msg
    showMessage(msg, 'error')
  } finally {
    saving.value = false
  }
}

const viewMaterial = (m: Material) => { selectedMaterial.value = m; viewDialog.value = true }
const confirmDelete = (m: Material) => { materialToDelete.value = m; deleteDialog.value = true }

const deleteMaterial = async () => {
  if (!materialToDelete.value) return
  try {
    deleting.value = true
    await axiosClient.delete(`/materials/${materialToDelete.value.id}`)
    materials.value = materials.value.filter(m => m.id !== materialToDelete.value!.id)
    showMessage('Material deleted successfully')
  } catch {
    showMessage('Failed to delete material', 'error')
  } finally {
    deleting.value = false
    deleteDialog.value = false
    materialToDelete.value = null
  }
}

watch(form, () => { formDirty.value = true }, { deep: true })
onUnmounted(() => {
  if (imagePreview.value?.startsWith('blob:')) URL.revokeObjectURL(imagePreview.value)
})
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
.preview-img {
  width: 140px;
  height: 140px;
  border-radius: 12px;
  object-fit: cover;
  border: 4px solid #1976d2;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.file-input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
</style>