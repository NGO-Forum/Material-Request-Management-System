<template>
  <v-container fluid class="fill-height">
    <v-row justify="center" align="center">
      <v-col cols="12" md="8" lg="6">
        <v-card elevation="12" rounded="xl">
          <v-card-title class="text-h5 font-weight-bold text-center pa-8 bg-primary text-white">
            <v-icon size="48" class="mb-3">mdi-file-document-plus</v-icon>
            <div class="mt-3">Create New Material Request</div>
          </v-card-title>

          <v-card-text class="pa-8">
            <v-form @submit.prevent="submitRequest" ref="formRef">
              <!-- Material Selection -->
              <v-select
                v-model="form.material_id"
                :items="materials"
                item-title="name"
                item-value="id"
                label="Select Material *"
                prepend-inner-icon="mdi-package-variant"
                variant="outlined"
                :loading="loadingMaterials"
                :disabled="loadingMaterials"
                :rules="[v => !!v || 'Please select a material']"
                required
                clearable
              >
                <template #item="{ props, item }">
                  <v-list-item v-bind="props">
                    <v-list-item-title class="font-weight-medium">
                      {{ item.raw.name }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      <v-chip
                        size="x-small"
                        :color="item.raw.qty_remaining > 5 ? 'success' : item.raw.qty_remaining > 0 ? 'warning' : 'error'"
                      >
                        {{ item.raw.qty_remaining }} left
                      </v-chip>
                      • {{ item.raw.category?.name || 'Uncategorized' }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </template>
              </v-select>

              <!-- Quantity -->
              <v-text-field
                v-model.number="form.quantity"
                label="Quantity Required *"
                type="number"
                min="1"
                prepend-inner-icon="mdi-counter"
                variant="outlined"
                :rules="[
                  v => !!v || 'Quantity is required',
                  v => v >= 1 || 'Quantity must be at least 1',
                  v => v <= selectedMaterialStock || `Only ${selectedMaterialStock} available`
                ]"
                required
              />

              <!-- Purpose -->
              <v-textarea
                v-model="form.purpose"
                label="Purpose / Reason (Optional)"
                prepend-inner-icon="mdi-text"
                variant="outlined"
                rows="3"
                auto-grow
              />

              <!-- Actions -->
              <div class="d-flex justify-space-between mt-8">
                <v-btn variant="text" @click="router.back()">
                  Cancel
                </v-btn>

                <v-btn
                  color="primary"
                  type="submit"
                  size="large"
                  :loading="saving"
                  :disabled="!form.material_id || form.quantity < 1"
                >
                  <v-icon start>mdi-send</v-icon>
                  Submit Request
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Success Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" location="top">
      {{ snackbar.message }}
      <template #actions>
        <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axiosClient from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// Only allow if logged in
if (!authStore.isLoggedIn) {
  router.push('/login')
}

const formRef = ref<any>(null)
const saving = ref(false)
const loadingMaterials = ref(true)
const materials = ref<any[]>([])

const form = ref({
  material_id: null as number | null,
  quantity: 1,
  purpose: ''
})

// Computed stock
const selectedMaterialStock = computed(() => {
  const mat = materials.value.find(m => m.id === form.value.material_id)
  return mat?.qty_remaining || 0
})

const quantityRules = [
  (v: any) => !!v || 'Quantity is required',
  (v: any) => v >= 1 || 'Minimum 1',
  (v: any) => v <= selectedMaterialStock || `Only ${selectedMaterialStock} available`
]

const snackbar = ref({
  show: false,
  message: '',
  color: 'success' as 'success' | 'error'
})

const showMessage = (msg: string, color: 'success' | 'error' = 'success') => {
  snackbar.value = { show: true, message: msg, color }
}

onMounted(async () => {
  try {
    const res = await axiosClient.get('/materials')
    materials.value = res.data.data || res.data || []
  } catch (err) {
    showMessage('Failed to load materials', 'error')
  } finally {
    loadingMaterials.value = false
  }
})

const submitRequest = async () => {
  const { valid } = await formRef.value?.validate()
  if (!valid) return

  saving.value = true

  try {
    await axiosClient.post('/material-requests', {
      material_id: form.value.material_id,
      quantity: form.value.quantity,
      purpose: form.value.purpose || null
      // DO NOT SEND requester_id → Laravel uses auth()->id()
    })

    showMessage('Request submitted successfully!', 'success')
    setTimeout(() => router.push('/main/requests/list'), 1500)
  } catch (err: any) {
    const msg = err.response?.data?.message ||
                (err.response?.data?.errors ? Object.values(err.response.data.errors).flat().join(', ') : 'Request failed')

    showMessage(msg, 'error')
  } finally {
    saving.value = false
  }
}
</script>