<template>
  <v-container fluid class="fill-height pa-4 pa-md-8">
    <v-row justify="center">
      <v-col cols="12" md="8" lg="6">
        <v-card elevation="16" rounded="xl" class="overflow-hidden">
          <v-card-title class="text-h5 font-weight-bold text-center pa-8 bg-primary text-white">
            <v-icon size="48" class="mb-3">mdi-file-document-plus</v-icon>
            <div class="mt-3">
              {{ isPreview ? 'Preview Your Request' : 'Create New Material Request' }}
            </div>
          </v-card-title>

          <!-- Loading Materials -->
          <div v-if="loadingMaterials" class="text-center py-16">
            <v-progress-circular indeterminate size="64" color="primary" />
          </div>

          <!-- Step 1: Form -->
          <v-card-text v-else-if="!isPreview" class="pa-8">
            <v-form @submit.prevent="goToPreview" ref="formRef">
              <!-- Material Select -->
              <v-select
                v-model="form.material_id"
                :items="materials"
                item-title="name"
                item-value="id"
                label="Select Material *"
                prepend-inner-icon="mdi-package-variant"
                variant="outlined"
                :rules="[v => !!v || 'Please select a material']"
                clearable
                @update:model-value="resetQuantityIfInvalid"
              >
                <template #item="{ props, item }">
                  <v-list-item v-bind="props">
                    <v-list-item-title>{{ item.raw.name }}</v-list-item-title>
                    <v-list-item-subtitle>
                      <v-chip
                        size="x-small"
                        :color="item.raw.qty_remaining > 5 ? 'success' : item.raw.qty_remaining > 0 ? 'warning' : 'error'"
                      >
                        {{ item.raw.qty_remaining }} left
                      </v-chip>
                      • {{ item.raw.category?.name || 'Uncategorized' }}
                      <span v-if="item.raw.model" class="ml-2 text-grey">• {{ item.raw.model }}</span>
                    </v-list-item-subtitle>
                  </v-list-item>
                </template>
              </v-select>

              <!-- Quantity -->
              <v-text-field
                v-model.number="form.quantity"
                label="Quantity Required *"
                type="number"
                :min="1"
                :max="selectedStock"
                prepend-inner-icon="mdi-counter"
                variant="outlined"
                :rules="quantityRules"
                :disabled="!form.material_id"
              />

              <!-- Required By Date (Receipt Date) -->
              <v-text-field
                v-model="form.receipt_date"
                label="Required By Date *"
                type="date"
                prepend-inner-icon="mdi-calendar-clock"
                variant="outlined"
                :min="today"
                :rules="[
                  v => !!v || 'Please select a date',
                  v => new Date(v) >= new Date(today) || 'Date cannot be in the past'
                ]"
              />

              <!-- Purpose -->
              <v-textarea
                v-model="form.purpose"
                label="Purpose / Reason (Optional)"
                prepend-inner-icon="mdi-text-box-outline"
                variant="outlined"
                rows="3"
                auto-grow
                counter="1000"
              />

              <div class="d-flex justify-end gap-4 mt-8">
                <v-btn variant="text" @click="router.back()" size="large">
                  Cancel
                </v-btn>
                <v-btn
                  color="primary"
                  type="submit"
                  size="large"
                  :disabled="!isFormValid"
                  :loading="false"
                >
                  <v-icon start>mdi-eye</v-icon>
                  Preview Request
                </v-btn>
              </div>
            </v-form>
          </v-card-text>

          <!-- Step 2: Preview -->
          <v-card-text v-else class="pa-8">
            <div class="text-h6 font-weight-medium text-center mb-8 text-grey-darken-3">
              Please confirm your request details
            </div>

            <v-card variant="outlined" class="mb-8 pa-6">
              <v-table density="comfortable">
                <tbody>
                  <tr>
                    <td class="font-weight-bold text-grey-darken-2 py-3">Material</td>
                    <td class="py-3">{{ selectedMaterial?.name || '-' }}</td>
                  </tr>
                  <tr v-if="selectedMaterial?.model">
                    <td class="font-weight-bold text-grey-darken-2">Model</td>
                    <td>{{ selectedMaterial.model }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-grey-darken-2">Quantity</td>
                    <td>
                      <v-chip color="primary" size="small" class="font-weight-bold">
                        {{ form.quantity }} unit(s)
                      </v-chip>
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-grey-darken-2">Required By</td>
                    <td>
                      <v-chip
                        :color="isUrgent(form.receipt_date) ? 'red-darken-1' : 'success'"
                        class="font-weight-bold"
                        variant="flat"
                      >
                        <v-icon start>mdi-calendar-alert</v-icon>
                        {{ formatDateOnly(form.receipt_date) }}
                        <span v-if="isUrgent(form.receipt_date)" class="ml-2">(URGENT)</span>
                      </v-chip>
                    </td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-grey-darken-2">Purpose</td>
                    <td>{{ form.purpose || '— Not specified' }}</td>
                  </tr>
                  <tr>
                    <td class="font-weight-bold text-grey-darken-2">Requested By</td>
                    <td>
                      <strong>{{ authStore.user?.name }}</strong>
                      <span class="text-grey ml-2">({{ authStore.user?.email }})</span>
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card>

            <div class="d-flex justify-space-between">
              <v-btn variant="outlined" size="large" @click="isPreview = false">
                <v-icon start>mdi-arrow-left</v-icon>
                Back to Edit
              </v-btn>

              <v-btn
                color="success"
                size="large"
                :loading="saving"
                @click="submitRequest"
              >
                <v-icon start>mdi-send</v-icon>
                Submit Request
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Snackbar -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="6000"
      location="top"
      multi-line
    >
      {{ snackbar.message }}
      <template #actions>
        <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axiosClient from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

if (!authStore.isLoggedIn) {
  router.push('/login')
}

const isPreview = ref(false)
const saving = ref(false)
const loadingMaterials = ref(true)
const materials = ref<any[]>([])
const formRef = ref<any>(null)

const today = new Date().toISOString().slice(0, 10)

const form = ref({
  material_id: null as number | null,
  quantity: 1,
  receipt_date: today,
  purpose: ''
})

const selectedMaterial = computed(() =>
  materials.value.find(m => m.id === form.value.material_id)
)

const selectedStock = computed(() => selectedMaterial.value?.qty_remaining || 0)

const quantityRules = [
  (v: any) => !!v || 'Quantity is required',
  (v: any) => v >= 1 || 'Minimum quantity is 1',
  (v: any) => v <= selectedStock.value || `Only ${selectedStock.value} unit(s) available`
]

const isFormValid = computed(() => {
  return (
    form.value.material_id &&
    form.value.quantity >= 1 &&
    form.value.quantity <= selectedStock.value &&
    form.value.receipt_date &&
    new Date(form.value.receipt_date) >= new Date(today)
  )
})

const isUrgent = (date: string) => {
  const diffDays = Math.ceil((new Date(date).getTime() - new Date().getTime()) / (1000 * 3600 * 24))
  return diffDays >= 0 && diffDays <= 3
}

const formatDateOnly = (date: string) => {
  return new Date(date).toLocaleDateString('en-GB', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const snackbar = ref({
  show: false,
  message: '',
  color: 'success' as 'success' | 'error'
})

const showMsg = (msg: string, color: 'success' | 'error' = 'success') => {
  snackbar.value = { show: true, message: msg, color }
}

// Reset quantity if current value exceeds new stock
const resetQuantityIfInvalid = async () => {
  await nextTick()
  if (form.value.quantity > selectedStock.value) {
    form.value.quantity = selectedStock.value || 1
  }
}

onMounted(async () => {
  try {
    const { data } = await axiosClient.get('/materials')
    materials.value = data.data || data || []
  } catch (err) {
    showMsg('Failed to load materials list', 'error')
  } finally {
    loadingMaterials.value = false
  }
})

const goToPreview = async () => {
  const { valid } = await formRef.value.validate()
  if (valid && isFormValid.value) {
    isPreview.value = true
  }
}

const submitRequest = async () => {
  saving.value = true
  try {
    await axiosClient.post('/material-requests', {
      material_id: form.value.material_id,
      quantity: form.value.quantity,
      receipt_date: form.value.receipt_date,
      purpose: form.value.purpose || null
    })

    showMsg('Your material request has been submitted successfully!', 'success')
    setTimeout(() => router.push('/main/requests/list'), 2000)
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Failed to submit request. Please try again.'
    showMsg(msg, 'error')
    isPreview.value = false
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.gap-4 { gap: 16px; }
.v-table tbody tr:hover {
  background: rgba(0, 0, 0, 0.02) !important;
}
</style>