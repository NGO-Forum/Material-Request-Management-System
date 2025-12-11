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
        <v-card elevation="16" class="mb-8 bubble-shape overflow-hidden">
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
                      {{ request.material?.name }}
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
                        {{ request.status.toUpperCase() }}
                      </v-chip>
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item v-if="issueRecord">
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Issued On
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatFullDate(issueRecord.issued_date) }}
                    </v-list-item-subtitle>
                  </v-list-item>

                  <v-list-item v-if="returnRecord">
                    <v-list-item-title class="font-weight-medium text-grey-darken-2">
                      Returned On
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatFullDate(returnRecord.created_at) }}
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-col>
            </v-row>

            <!-- Action Buttons -->
            <v-divider class="my-10" />
            <div class="d-flex flex-wrap gap-4">
              <v-btn
                v-if="request.status === 'pending'"
                color="success"
                size="large"
                @click="takeAction('approved')"
                prepend-icon="mdi-check-bold"
                :loading="processing"
                elevation="6"
              >
                Approve
              </v-btn>

              <v-btn
                v-if="request.status === 'pending'"
                color="error"
                size="large"
                @click="takeAction('rejected')"
                prepend-icon="mdi-close-thick"
                :loading="processing"
                elevation="6"
              >
                Reject
              </v-btn>

              <v-btn
                v-if="['pending', 'approved'].includes(request.status)"
                color="grey-darken-2"
                size="large"
                @click="takeAction('cancelled')"
                prepend-icon="mdi-cancel"
                :loading="processing"
                elevation="6"
              >
                Cancel
              </v-btn>

              <v-btn
                v-if="request.status === 'approved' && !issueRecord"
                color="info"
                size="large"
                @click="issueDialog = true"
                prepend-icon="mdi-package-down"
                elevation="6"
              >
                Issue Material
              </v-btn>

              <v-btn
                v-if="request.status === 'issued' && !returnRecord"
                color="purple"
                size="large"
                @click="returnDialog = true"
                prepend-icon="mdi-package-up"
                elevation="6"
              >
                Return Material
              </v-btn>
            </div>
          </v-card-text>
        </v-card>

        <!-- Timeline -->
        <v-card elevation="12" class="bubble-shape">
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
        <v-card v-if="issueRecord" elevation="12" class="mb-6 bubble-shape">
          <v-card-title class="bg-info text-white pa-6">
            <v-icon class="mr-3" size="28">mdi-package-down</v-icon>
            Material Issued
          </v-card-title>
          <v-card-text class="pt-8">
            <p><strong>Issued by:</strong> {{ issueRecord.issuedBy?.name || '—' }}</p>
            <p><strong>Date:</strong> {{ formatFullDate(issueRecord.issued_date) }}</p>
            <p><strong>Expected Return:</strong> {{ issueRecord.expected_return_date ? formatFullDate(issueRecord.expected_return_date) : 'Not set' }}</p>
          </v-card-text>
        </v-card>

        <v-card v-if="returnRecord" elevation="12" class="bubble-shape">
          <v-card-title class="bg-purple text-white pa-6">
            <v-icon class="mr-3" size="28">mdi-package-up</v-icon>
            Material Returned
          </v-card-title>
          <v-card-text class="pt-8">
            <p><strong>Returned by:</strong> {{ returnRecord.returnedBy?.name || '—' }}</p>
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

    <!-- Issue Dialog -->
    <v-dialog v-model="issueDialog" max-width="600">
      <v-card>
        <v-card-title class="text-h6 bg-info text-white">
          <v-icon class="mr-3">mdi-package-down</v-icon>
          Issue Material
        </v-card-title>
        <v-card-text class="pt-8">
          <v-row>
            <v-col cols="12" sm="6">
              <v-text-field v-model="issueForm.issued_date" label="Issue Date" type="date" required />
            </v-col>
            <v-col cols="12" sm="6">
              <v-text-field v-model="issueForm.expected_return_date" label="Expected Return" type="date" />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="issueDialog = false">Cancel</v-btn>
          <v-btn color="info" :loading="processing" @click="issueMaterial">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Return Dialog -->
    <v-dialog v-model="returnDialog" max-width="600">
      <v-card>
        <v-card-title class="text-h6 bg-purple text-white">
          <v-icon class="mr-3">mdi-package-up</v-icon>
          Return Material
        </v-card-title>
        <v-card-text class="pt-8">
          <v-select
            v-model="returnForm.it_condition_status"
            :items="['Good', 'Damaged', 'Lost']"
            label="Condition"
          />
          <v-textarea v-model="returnForm.it_remarks" label="Remarks" rows="3" />
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="returnDialog = false">Cancel</v-btn>
          <v-btn color="purple" :loading="processing" @click="returnMaterial">Confirm</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" location="top">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axiosClient from '@/plugins/axios'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const request = ref<any>(null)
const actions = ref<any[]>([])
const issueRecord = ref<any>(null)
const returnRecord = ref<any>(null)
const issueDialog = ref(false)
const returnDialog = ref(false)
const loading = ref(true)
const processing = ref(false)

const issueForm = ref({
  issued_date: new Date().toISOString().substr(0, 10),
  expected_return_date: ''
})

const returnForm = ref({
  it_condition_status: 'Good' as const,
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

const getStatusColor = (status: string) => {
  const map: Record<string, string> = {
    pending: 'orange',
    approved: 'success',
    rejected: 'error',
    issued: 'info',
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
    returned: 'purple'
  }
  return map[type] || 'grey'
}

const getTimelineIcon = (type: string) => {
  const icons: Record<string, string> = {
    created: 'mdi-plus-circle',
    approved: 'mdi-check-bold',
    rejected: 'mdi-close-thick',
    issued: 'mdi-package-down',
    returned: 'mdi-package-up'
  }
  return icons[type] || 'mdi-circle-outline'
}

const timeline = ref<any[]>([])

const loadData = async () => {
  loading.value = true
  try {
    const id = Number(route.params.id)

    const [reqRes, actRes, issueRes, returnRes] = await Promise.all([
      axiosClient.get(`/material-requests/${id}`),
      axiosClient.get('/material-request-actions'),
      axiosClient.get('/material-issue-records'),
      axiosClient.get('/material-returns')
    ])

    const reqData = reqRes.data.data || reqRes.data
    if (!reqData) throw new Error('Not found')

    request.value = reqData

    const acts = actRes.data.data || actRes.data || []
    const issues = issueRes.data.data || issueRes.data || []
    const returns = returnRes.data.data || returnRes.data || []

    actions.value = acts.filter((a: any) => a.request_id === id)
    issueRecord.value = issues.find((i: any) => i.request_id === id) || null
    returnRecord.value = returns.find((r: any) => r.request_id === id) || null

    timeline.value = [
      { type: 'created', title: 'Request Created', by: request.value.requester?.name || 'Unknown', date: request.value.created_at },
      ...actions.value.map((a: any) => ({
        type: a.action_type,
        title: a.action_type.charAt(0).toUpperCase() + a.action_type.slice(1),
        by: a.actor?.name || 'System',
        date: a.created_at,
        remarks: a.remarks
      })),
      issueRecord.value && { type: 'issued', title: 'Material Issued', by: issueRecord.value.issuedBy?.name || 'Unknown', date: issueRecord.value.issued_date },
      returnRecord.value && { type: 'returned', title: 'Material Returned', by: returnRecord.value.returnedBy?.name || 'Unknown', date: returnRecord.value.created_at, remarks: returnRecord.value.it_remarks }
    ].filter(Boolean)

  } catch (err) {
    showMessage('Failed to load data', 'error')
    router.push('/main/requests/list')
  } finally {
    loading.value = false
  }
}

const takeAction = async (status: 'approved' | 'rejected' | 'cancelled') => {
  if (!confirm(`Confirm ${status} this request?`)) return

  processing.value = true
  try {
    await axiosClient.put(`/material-requests/${request.value.id}`, { status })
    await axiosClient.post('/material-request-actions', {
      request_id: request.value.id,
      action_by: authStore.currentUser?.id || 1,
      action_type: status
    })
    showMessage(`Request ${status} successfully!`, 'success')
    loadData()
  } catch {
    showMessage('Action failed', 'error')
  } finally {
    processing.value = false
  }
}

const issueMaterial = async () => {
  processing.value = true
  try {
    await Promise.all([
      axiosClient.post('/material-issue-records', {
        request_id: request.value.id,
        issued_by: authStore.currentUser?.id || 1,
        ...issueForm.value
      }),
      axiosClient.put(`/material-requests/${request.value.id}`, { status: 'issued' }),
      axiosClient.post('/material-stock-movements', {
        material_id: request.value.material_id,
        request_id: request.value.id,
        movement_type: 'issue',
        quantity: request.value.quantity
      })
    ])
    showMessage('Material issued!', 'success')
    issueDialog.value = false
    loadData()
  } catch {
    showMessage('Issue failed', 'error')
  } finally {
    processing.value = false
  }
}

const returnMaterial = async () => {
  processing.value = true
  try {
    await Promise.all([
      axiosClient.post('/material-returns', {
        request_id: request.value.id,
        returned_by: authStore.currentUser?.id || 1,
        it_inspected_by: authStore.currentUser?.id || 1,
        ...returnForm.value
      }),
      axiosClient.put(`/material-requests/${request.value.id}`, { status: 'returned' }),
      axiosClient.post('/material-stock-movements', {
        material_id: request.value.material_id,
        request_id: request.value.id,
        movement_type: 'return',
        quantity: request.value.quantity
      })
    ])
    showMessage('Material returned!', 'success')
    returnDialog.value = false
    loadData()
  } catch {
    showMessage('Return failed', 'error')
  } finally {
    processing.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.bubble-shape {
  border-radius: 28px !important;
}
</style>