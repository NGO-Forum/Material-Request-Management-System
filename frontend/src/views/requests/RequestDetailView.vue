<template>
  <v-container fluid class="pa-4 pa-md-8">
    <!-- Back Button -->
    <v-btn
      variant="text"
      size="small"
      class="mb-6 text-primary"
      @click="router.back()"
      prepend-icon="mdi-arrow-left"
    >
      Back to Requests
    </v-btn>
    <!-- Loading -->
    <div v-if="loading" class="text-center py-16">
      <v-progress-circular indeterminate size="64" color="primary" />
    </div>
    <!-- Main Content -->
    <v-row v-else-if="request">
      <!-- Left Column -->
      <v-col cols="12" lg="8">
        <!-- Header Card -->
        <v-card elevation="16" class="mb-8 rounded-xl overflow-hidden">
          <div class="bg-primary pa-8 text-white">
            <h1 class="text-h4 font-weight-bold d-flex align-center gap-4">
              <v-icon size="48">mdi-file-document-outline</v-icon>
              Request #{{ request.id }}
            </h1>
            <p class="text-h6 mt-3 opacity-90">
              {{ request.material?.name || 'Unknown Material' }}
            </p>
            <p class="mt-2 opacity-80">
              Requested by <strong>{{ request.requester?.name || '—' }}</strong>
              • {{ formatFullDate(request.created_at) }}
            </p>
          </div>
          <v-card-text class="pa-8">
            <v-row>
              <v-col cols="12" md="6">
                <v-list bg-color="transparent" density="comfortable">
                  <v-list-item>
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Material Details
                    </v-list-item-title>
                    <v-list-item-subtitle class="text-h6 font-weight-bold mt-2">
                      {{ request.material?.name || '—' }}
                      <span class="text-body-1 text-medium-emphasis">
                        ({{ request.material?.model || 'No model' }})
                      </span>
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Quantity
                    </v-list-item-title>
                    <v-list-item-subtitle class="text-h5 font-weight-bold">
                      {{ request.quantity }} unit(s)
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Purpose
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ request.purpose || 'Not specified' }}
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item v-if="request.receipt_date">
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Required By
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      <v-chip color="orange-darken-1" variant="flat">
                        <v-icon start>mdi-calendar-clock</v-icon>
                        {{ formatDateOnly(request.receipt_date) }}
                      </v-chip>
                      <span v-if="isUrgent(request.receipt_date)" class="ml-3 text-red font-weight-bold">
                        URGENT
                      </span>
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
              <v-col cols="12" md="6">
                <v-list bg-color="transparent" density="comfortable">
                  <v-list-item>
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Current Status
                    </v-list-item-title>
                    <v-list-item-subtitle class="mt-3">
                      <v-chip
                        :color="getStatusColor(request.status)"
                        size="large"
                        class="font-weight-bold px-8 py-4 text-h6"
                        label
                      >
                        {{ request.status.toUpperCase().replace('_', ' ') }}
                      </v-chip>
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item v-if="issueRecord">
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Issued On
                    </v-list-item-title>
                    <v-list-item-subtitle class="font-weight-bold">
                      {{ formatFullDate(issueRecord.issued_date) }}
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item v-if="returnRecord">
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Returned On
                    </v-list-item-title>
                    <v-list-item-subtitle class="font-weight-bold">
                      {{ formatFullDate(returnRecord.return_date) }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>
            <!-- Action Buttons - ROLE BASED -->
            <v-divider class="my-10" />
            <div class="d-flex flex-wrap gap-4">
              <template v-if="isAdmin && request.status === 'pending'">
                <v-btn
                  color="success"
                  size="large"
                  prepend-icon="mdi-check-bold"
                  :loading="processing"
                  elevation="6"
                  @click="openConfirmDialog('approved', 'Approve Request', 'This request will be approved.', 'success', CheckIcon)"
                >
                  Approve
                </v-btn>
                <v-btn
                  color="error"
                  size="large"
                  prepend-icon="mdi-close-thick"
                  :loading="processing"
                  elevation="6"
                  @click="openConfirmDialog('rejected', 'Reject Request', 'This request will be rejected.', 'error', XIcon)"
                >
                  Reject
                </v-btn>
              </template>
              <v-btn
                v-if="['pending', 'approved'].includes(request.status) && (isRequester || isAdmin)"
                color="grey-darken-2"
                size="large"
                prepend-icon="mdi-cancel"
                :loading="processing"
                elevation="6"
                @click="openConfirmDialog('cancelled', 'Cancel Request', 'This request will be cancelled.', 'warning', AlertTriangleIcon)"
              >
                Cancel
              </v-btn>
              <v-btn
                v-if="request.status === 'approved' && !issueRecord && isItAssistant"
                color="info"
                size="large"
                prepend-icon="mdi-package-down"
                elevation="6"
                :loading="processing"
                @click="issueDialog = true"
              >
                Issue Material
              </v-btn>
              <v-btn
                v-if="request.status === 'issued' && !returnRecord && isRequester"
                color="purple"
                size="large"
                prepend-icon="mdi-package-up"
                elevation="6"
                :loading="processing"
                @click="openConfirmDialog('pending_return', 'Initiate Return', 'Initiate return of the material?', 'warning', AlertTriangleIcon)"
              >
                Return Material
              </v-btn>
              <v-btn
                v-if="request.status === 'pending_return' && isItAssistant"
                color="blue"
                size="large"
                prepend-icon="mdi-magnify"
                elevation="6"
                :loading="processing"
                @click="inspectDialog = true"
              >
                Inspect Return
              </v-btn>
              <v-btn
                v-if="request.status === 'inspected_return' && isAdmin"
                color="success"
                size="large"
                prepend-icon="mdi-check-bold"
                elevation="6"
                :loading="processing"
                @click="openConfirmDialog('returned', 'Confirm Return', 'Confirm the return of the material?', 'success', CheckIcon)"
              >
                Confirm Return
              </v-btn>
              <div v-if="!isAdmin && !isItAssistant && !isRequester" class="text-medium-emphasis">
                No actions available at this time.
              </div>
            </div>
          </v-card-text>
        </v-card>
        <!-- Activity Timeline -->
        <v-card elevation="12" class="rounded-xl">
          <v-card-title class="text-h6 font-weight-bold pa-6 bg-grey-lighten-4">
            Activity Timeline
          </v-card-title>
          <v-card-text class="pt-6">
            <v-timeline density="compact" align="start">
              <v-timeline-item
                v-for="(log, i) in timeline"
                :key="i"
                size="small"
                :dot-color="getTimelineColor(log.type)"
              >
                <template #icon>
                  <v-icon color="white" size="20">{{ getTimelineIcon(log.type) }}</v-icon>
                </template>
                <div class="font-weight-bold text-body-1">{{ log.title }}</div>
                <div class="text-caption text-medium-emphasis">
                  by <strong>{{ log.by }}</strong> • {{ formatFullDate(log.date) }}
                </div>
                <div v-if="log.remarks" class="text-body-2 mt-2 text-grey-darken-2">
                  "{{ log.remarks }}"
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-card-text>
        </v-card>
      </v-col>
      <!-- Right Column -->
      <v-col cols="12" lg="4">
        <v-card v-if="issueRecord" elevation="12" class="mb-6 rounded-xl">
          <v-card-title class="pa-6" style="background-color: #2196f3; color: white;">
            Material Issued
          </v-card-title>
          <v-card-text class="pt-8">
            <p><strong>Issued by:</strong> {{ getIssuerName }}</p>
            <p><strong>Date:</strong> {{ formatFullDate(issueRecord.issued_date) }}</p>
            <p><strong>Expected Return:</strong>
              {{ issueRecord.expected_return_date ? formatFullDate(issueRecord.expected_return_date) : 'Not set' }}
            </p>
          </v-card-text>
        </v-card>
        <v-card v-if="returnRecord" elevation="12" class="rounded-xl">
          <v-card-title class="pa-6" style="background-color: purple; color: white;">
            Material Returned
          </v-card-title>
          <v-card-text class="pt-8">
            <p><strong>Returned by:</strong> {{ getReturnerName }}</p>
            <p><strong>Condition:</strong>
              <v-chip :color="returnRecord.it_condition_status === 'Good' ? 'success' : 'error'" class="ml-2">
                {{ returnRecord.it_condition_status || '—' }}
              </v-chip>
            </p>
            <p><strong>Remarks:</strong> {{ returnRecord.it_remarks || 'No remarks' }}</p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    <!-- Confirmation Dialog -->
    <v-dialog v-model="confirmDialog" max-width="480" persistent>
      <v-card>
        <v-card-title class="text-h6 d-flex align-center gap-3" :class="confirmColor">
          <component :is="confirmIcon" :size="28" />
          {{ confirmTitle }}
        </v-card-title>
        <v-card-text class="pt-4 text-body-1">
          {{ confirmMessage }}
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="confirmDialog = false">Cancel</v-btn>
          <v-btn :color="confirmColor" @click="executeAction" :loading="processing">
            Confirm
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- Issue Dialog -->
    <v-dialog v-model="issueDialog" max-width="600">
      <v-card>
        <v-card-title class="text-h6 bg-info text-white">Issue Material</v-card-title>
        <v-card-text class="pt-8">
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="issueForm.issued_date"
                label="Issue Date *"
                type="date"
                :max="today"
                required
              />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field
                v-model="issueForm.expected_return_date"
                label="Expected Return Date"
                type="date"
                :min="issueForm.issued_date || today"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="issueDialog = false">Cancel</v-btn>
          <v-btn color="info" :loading="processing" @click="issueMaterial">
            Confirm Issue
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- Inspect Return Dialog -->
    <v-dialog v-model="inspectDialog" max-width="600">
      <v-card>
        <v-card-title class="text-h6 bg-blue text-white">Inspect Returned Material</v-card-title>
        <v-card-text class="pt-8">
          <v-select
            v-model="returnForm.it_condition_status"
            :items="['Good', 'Damaged', 'Lost']"
            label="Condition *"
            required
          />
          <v-textarea
            v-model="returnForm.it_remarks"
            label="Remarks (Optional)"
            rows="3"
          />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="inspectDialog = false">Cancel</v-btn>
          <v-btn color="blue" :loading="processing" @click="inspectReturn">
            Confirm Inspection
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- Snackbar -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      timeout="5000"
      location="top"
    >
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>
<script setup lang="ts">
import { ref, onMounted, computed, type Component } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axiosClient from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'
import { CheckIcon, XIcon, AlertTriangleIcon } from 'vue-tabler-icons'
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const request = ref<any>(null)
const issueRecord = ref<any>(null)
const returnRecord = ref<any>(null)
const timeline = ref<any[]>([])
const loading = ref(true)
const processing = ref(false)
const issueDialog = ref(false)
const inspectDialog = ref(false)
const confirmDialog = ref(false)
const confirmAction = ref<string | null>(null)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmColor = ref('primary')
const confirmIcon = ref<Component>(AlertTriangleIcon)
const today = new Date().toISOString().slice(0, 10)
const issueForm = ref({
  issued_date: today,
  expected_return_date: ''
})
const returnForm = ref({
  it_condition_status: 'Good' as 'Good' | 'Damaged' | 'Lost',
  it_remarks: ''
})
const snackbar = ref({
  show: false,
  message: '',
  color: 'success' as 'success' | 'error'
})
const showMessage = (msg: string, color: 'success' | 'error' = 'success') => {
  snackbar.value = { show: true, message: msg, color }
}
// Role & Ownership Helpers
const userRole = computed(() => authStore.user?.role?.name?.toLowerCase() || 'employee')
const currentUserId = computed(() => authStore.user?.id || 0)
const isAdmin = computed(() => userRole.value === 'admin')
const isItAssistant = computed(() => userRole.value === 'it assistant')
const isRequester = computed(() => request.value?.requester?.id === currentUserId.value)
// Date & Status Helpers
const formatFullDate = (date: string | null) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
const formatDateOnly = (date: string) => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}
const isUrgent = (date: string) => {
  const diffDays = Math.ceil((new Date(date).getTime() - new Date().getTime()) / (1000 * 3600 * 24))
  return diffDays >= 0 && diffDays <= 3
}
const getStatusColor = (status: string) => {
  const map: Record<string, string> = {
    pending: 'orange',
    approved: 'success',
    rejected: 'error',
    issued: 'info',
    pending_return: 'warning',
    inspected_return: 'blue',
    returned: 'purple',
    cancelled: 'grey-darken-1'
  }
  return map[status] || 'grey'
}
const getTimelineColor = (type: string) => {
  const map: Record<string, string> = {
    created: 'primary',
    approved: 'success',
    rejected: 'error',
    issued: 'info',
    pending_return: 'warning',
    inspected_return: 'blue',
    returned: 'purple',
    cancelled: 'grey-darken-2'
  }
  return map[type] || 'grey'
}
const getTimelineIcon = (type: string) => {
  const icons: Record<string, string> = {
    created: 'mdi-plus-circle',
    approved: 'mdi-check-bold',
    rejected: 'mdi-close-thick',
    issued: 'mdi-package-down',
    pending_return: 'mdi-package-up',
    inspected_return: 'mdi-magnify',
    returned: 'mdi-check-circle',
    cancelled: 'mdi-cancel'
  }
  return icons[type] || 'mdi-circle-outline'
}
const openConfirmDialog = (
  action: string,
  title: string,
  message: string,
  color: string,
  icon: Component
) => {
  confirmAction.value = action
  confirmTitle.value = title
  confirmMessage.value = message
  confirmColor.value = color
  confirmIcon.value = icon
  confirmDialog.value = true
}
const executeAction = async () => {
  if (!confirmAction.value || !request.value?.id) return
  processing.value = true
  try {
    if (['approved', 'rejected', 'cancelled'].includes(confirmAction.value)) {
      await axiosClient.patch(`/material-requests/${request.value.id}/status`, {
        status: confirmAction.value
      })
      showMessage(`Request ${confirmAction.value} successfully!`, 'success')
    } else if (confirmAction.value === 'pending_return') {
      await axiosClient.post(`/material-requests/${request.value.id}/initiate-return`)
      showMessage('Return initiated successfully!', 'success')
    } else if (confirmAction.value === 'returned') {
      await axiosClient.post(`/material-requests/${request.value.id}/confirm-return`)
      showMessage('Return confirmed successfully!', 'success')
    }
    confirmDialog.value = false
    await loadData()
  } catch (err: any) {
    showMessage(err.response?.data?.message || 'Action failed', 'error')
  } finally {
    processing.value = false
    confirmAction.value = null
  }
}
const issueMaterial = async () => {
  if (!request.value?.id) return
  processing.value = true
  try {
    await axiosClient.post(`/material-requests/${request.value.id}/issue`, {
      issued_date: issueForm.value.issued_date,
      expected_return_date: issueForm.value.expected_return_date || null
    })
    showMessage('Material issued successfully!', 'success')
    issueDialog.value = false
    issueForm.value = { issued_date: today, expected_return_date: '' }
    await loadData()
  } catch (err: any) {
    showMessage(err.response?.data?.message || 'Failed to issue material', 'error')
  } finally {
    processing.value = false
  }
}
const inspectReturn = async () => {
  if (!request.value?.id) return
  processing.value = true
  try {
    await axiosClient.post(`/material-requests/${request.value.id}/inspect-return`, {
      it_condition_status: returnForm.value.it_condition_status,
      it_remarks: returnForm.value.it_remarks
    })
    showMessage('Return inspected successfully!', 'success')
    inspectDialog.value = false
    returnForm.value = { it_condition_status: 'Good', it_remarks: '' }
    await loadData()
  } catch (err: any) {
    showMessage(err.response?.data?.message || 'Failed to inspect return', 'error')
  } finally {
    processing.value = false
  }
}
// Load all data
const loadData = async () => {
  loading.value = true
  try {
    const id = Number(route.params.id)
    const [
      reqRes,
      actionsRes,
      issuesRes,
      returnsRes
    ] = await Promise.all([
      axiosClient.get(`/material-requests/${id}`),
      axiosClient.get('/material-request-actions'),
      axiosClient.get('/material-issue-records'),
      axiosClient.get('/material-returns')
    ])
    const req = reqRes.data.data || reqRes.data
    if (!req) throw new Error('Request not found')
    request.value = req
    const actions = (actionsRes.data.data || actionsRes.data || []).filter((a: any) => a.request_id === id)
    issueRecord.value = (issuesRes.data.data || issuesRes.data || []).find((i: any) => i.request_id === id) || null
    returnRecord.value = (returnsRes.data.data || returnsRes.data || []).find((r: any) => r.request_id === id) || null
    // Build timeline - this is the source of truth for names
    timeline.value = [
      {
        type: 'created',
        title: 'Request Created',
        by: req.requester?.name || 'System',
        date: req.created_at
      },
      ...actions.map((a: any) => ({
        type: a.action_type,
        title: a.action_type.charAt(0).toUpperCase() + a.action_type.slice(1).replace('_', ' '),
        by: a.actor?.name || 'System',
        date: a.created_at,
        remarks: a.remarks
      })),
      issueRecord.value && {
        type: 'issued',
        title: 'Material Issued',
        by: issueRecord.value.issuedBy?.name || 'System',
        date: issueRecord.value.issued_date
      },
      returnRecord.value && {
        type: 'returned',
        title: 'Material Returned',
        by: returnRecord.value.returnedBy?.name || 'System',
        date: returnRecord.value.return_date,
        remarks: returnRecord.value.it_remarks
      }
    ].filter(Boolean) as any[]
  } catch (err: any) {
    if (err.response?.status === 403) {
      showMessage('You are not authorized to view this request.', 'error')
    } else {
      showMessage('Failed to load request details', 'error')
    }
    router.push('/main/requests')
  } finally {
    loading.value = false
  }
}
// Get issuer name from timeline (same as log.by)
const getIssuerName = computed(() => {
  const issuedLog = timeline.value.find(log => log.type === 'issued')
  return issuedLog?.by || '—'
})
// Get returner name from timeline (same as log.by)
const getReturnerName = computed(() => {
  const returnedLog = timeline.value.find(log => log.type === 'returned')
  return returnedLog?.by || '—'
})
onMounted(loadData)
</script>
<style scoped>
.rounded-xl { border-radius: 20px !important; }
.gap-4 { gap: 1rem; }
.text-red { color: #d32f2f !important; }
</style>