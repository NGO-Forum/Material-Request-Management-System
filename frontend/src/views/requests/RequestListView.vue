<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <div class="d-flex justify-space-between align-center mb-6">
          <h1 class="text-h4 font-weight-bold">Material Requests</h1>
          <v-btn color="primary" to="/main/requests/create" prepend-icon="mdi-plus">
            New Request
          </v-btn>
        </div>
        <v-card elevation="6" rounded="lg">
          <v-card-title class="pa-6">
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Search requests..."
              placeholder="Search by material, purpose, status..."
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
            />
          </v-card-title>
          <v-data-table
            :headers="visibleHeaders"
            :items="filteredRequests"
            :search="search"
            :loading="loading"
            items-per-page="15"
            density="comfortable"
            :sort-by="[{ key: 'created_at', order: 'desc' }]"
            class="elevation-2"
          >
            <!-- Status -->
            <template #item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                label
                class="font-weight-bold text-uppercase px-3"
              >
                {{ item.status.toUpperCase() }}
              </v-chip>
            </template>
            <!-- Material -->
            <template #item.material="{ item }">
              <div class="py-1">
                <div class="font-weight-bold">{{ item.material?.name || '—' }}</div>
                <div class="text-caption text-medium-emphasis">
                  {{ item.material?.model || 'No model' }}
                </div>
              </div>
            </template>
            <!-- Quantity -->
            <template #item.quantity="{ item }">
              <span class="font-weight-medium">{{ item.quantity }}</span>
            </template>
            <!-- Required By -->
            <template #item.receipt_date="{ item }">
              <div class="text-center">
                <v-chip
                  :color="isUrgent(item.receipt_date) ? 'red-darken-1' : 'primary'"
                  variant="tonal"
                  size="small"
                  class="font-weight-medium"
                >
                  <v-icon start size="16">mdi-calendar-clock</v-icon>
                  {{ formatDateOnly(item.receipt_date) }}
                </v-chip>
                <div v-if="isUrgent(item.receipt_date)" class="text-red text-caption mt-1 font-weight-bold">
                  URGENT
                </div>
              </div>
            </template>
            <!-- Purpose -->
            <template #item.purpose="{ item }">
              <div class="text-truncate" style="max-width: 200px;">
                {{ item.purpose || '—' }}
              </div>
            </template>
            <!-- Requester (Only visible to Admin/IT Assistant) -->
            <template #item.requester="{ item }">
              <div>
                <div class="font-weight-medium">{{ item.requester?.name || 'Unknown' }}</div>
                <div class="text-caption text-medium-emphasis">{{ item.requester?.email }}</div>
              </div>
            </template>
            <!-- Created At -->
            <template #item.created_at="{ item }">
              <span class="text-no-wrap">{{ formatDate(item.created_at) }}</span>
            </template>
            <!-- Actions -->
            <template #item.actions="{ item }">
              <v-menu location="bottom">
                <template #activator="{ props }">
                  <v-btn icon size="small" variant="text" v-bind="props">
                    <DotsVerticalIcon :size="20" />
                  </v-btn>
                </template>
                <v-list density="compact" class="py-1">
                  <v-list-item :to="`/main/requests/${item.id}`">
                    <v-list-item-title class="d-flex align-center gap-3">
                      <EyeIcon :size="18" />
                      <span>View Details</span>
                    </v-list-item-title>
                  </v-list-item>
                  <!-- Delete only for pending + own request -->
                  <v-list-item
                    v-if="item.status === 'pending' && item.requester.id === currentUserId"
                    @click="confirmDelete(item.id)"
                    class="text-error"
                  >
                    <v-list-item-title class="d-flex align-center gap-3">
                      <TrashIcon :size="18" class="text-error" />
                      <span>Delete Request</span>
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
    <!-- Delete Confirmation Dialog -->
    <v-dialog v-model="deleteDialog" max-width="420">
      <v-card>
        <v-card-title class="text-h6 text-error">
          <TrashIcon :size="24" class="mr-2" />
          Confirm Delete
        </v-card-title>
        <v-card-text class="pt-4">
          Are you sure you want to delete this request? This action cannot be undone.
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" @click="deleteRequest" :loading="deleting">
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axiosClient from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'
import { DotsVerticalIcon, EyeIcon, TrashIcon } from 'vue-tabler-icons'
const authStore = useAuthStore()
const currentUserId = computed(() => authStore.user?.id || 0)
// Fixed: Access role.name and normalize to lowercase
const userRole = computed(() => authStore.user?.role?.name?.toLowerCase() || 'employee')
interface MaterialRequest {
  id: number
  material: { id: number; name: string; model: string | null }
  quantity: number
  receipt_date: string
  purpose: string | null
  status: string
  requester: { id: number; name: string; email: string }
  created_at: string
}
const requests = ref<MaterialRequest[]>([])
const loading = ref(true)
const search = ref('')
const deleteDialog = ref(false)
const deleting = ref(false)
const requestToDelete = ref<number | null>(null)
// Base headers
const baseHeaders = [
  { title: '#', key: 'id', width: 70, align: 'center' as const },
  { title: 'Material', key: 'material', sortable: false },
  { title: 'Qty', key: 'quantity', align: 'center' as const, width: 80 },
  { title: 'Required By', key: 'receipt_date', align: 'center' as const, width: 150 },
  { title: 'Purpose', key: 'purpose', sortable: false, width: 200 },
  { title: 'Requester', key: 'requester', sortable: false },
  { title: 'Status', key: 'status', align: 'center' as const, width: 110 },
  { title: 'Requested On', key: 'created_at', width: 160 },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' as const, width: 80 }
]
// Show Requester column only for Admin/IT Assistant
const visibleHeaders = computed(() => {
  if (['admin', 'it assistant'].includes(userRole.value)) {
    return baseHeaders
  }
  return baseHeaders.filter(h => h.key !== 'requester')
})
// Filter requests: Admin/IT Assistant sees all, others see only own
const filteredRequests = computed(() => {
  if (['admin', 'it assistant'].includes(userRole.value)) {
    return requests.value
  }
  return requests.value.filter(r => r.requester.id === currentUserId.value)
})
const getStatusColor = (status: string): string => {
  const map: Record<string, string> = {
    pending: 'orange',
    approved: 'success',
    rejected: 'error',
    issued: 'info',
    returned: 'purple',
    cancelled: 'grey'
  }
  return map[status] || 'default'
}
const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
const formatDateOnly = (date: string): string => {
  return new Date(date).toLocaleDateString('en-GB', {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
const isUrgent = (receiptDate: string): boolean => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const target = new Date(receiptDate)
  const diffTime = target.getTime() - today.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays >= 0 && diffDays <= 3
}
const confirmDelete = (id: number) => {
  requestToDelete.value = id
  deleteDialog.value = true
}
const deleteRequest = async () => {
  if (!requestToDelete.value) return
  deleting.value = true
  try {
    await axiosClient.delete(`/material-requests/${requestToDelete.value}`)
    requests.value = requests.value.filter(r => r.id !== requestToDelete.value)
    deleteDialog.value = false
  } catch (err: any) {
    alert(err.response?.data?.message || 'Failed to delete request')
    console.error(err)
  } finally {
    deleting.value = false
    requestToDelete.value = null
  }
}
onMounted(async () => {
  loading.value = true
  try {
    const { data } = await axiosClient.get('/material-requests')
    requests.value = data.data || data || []
  } catch (err) {
    console.error('Failed to load requests:', err)
    alert('Failed to load material requests. Please try again.')
  } finally {
    loading.value = false
  }
})
</script>
<style scoped>
.v-data-table ::v-deep(th) {
  font-weight: 600 !important;
  background-color: #f8f9fa !important;
}
.text-error {
  color: #d32f2f !important;
}
.text-red {
  color: #d32f2f !important;
}
</style>