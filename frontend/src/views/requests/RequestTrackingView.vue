<template>
  <v-container fluid class="pa-4 pa-md-8">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-space-between align-center mb-8">
      <div>
        <h1 class="text-h4 font-weight-bold mb-1">Request Tracking Dashboard</h1>
        <p class="text-body-1 text-medium-emphasis">
          Real-time monitoring of all material requests and workflow
        </p>
      </div>
      <v-btn
        color="primary"
        size="large"
        @click="exportToCSV"
        :loading="exporting"
        elevation="8"
      >
        <DownloadIcon class="mr-2" :size="20" />
        Export CSV
      </v-btn>
    </div>

    <!-- Summary Cards -->
    <v-row class="mb-8">
      <v-col v-for="card in summaryCards" :key="card.title" cols="12" sm="6" md="3">
        <v-card
          elevation="12"
          class="pa-6 summary-card bubble-shape text-white"
          :color="card.color"
          rounded="xl"
        >
          <div class="d-flex align-center justify-space-between">
            <div>
              <div class="text-h3 font-weight-bold">{{ card.value }}</div>
              <div class="text-h6 mt-2 opacity-90">{{ card.title }}</div>
            </div>
            <v-icon size="56">{{ card.icon }}</v-icon>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts -->
    <v-row class="mb-8">
      <v-col cols="12" md="6">
        <v-card elevation="10" class="pa-6 h-100 bubble-shape">
          <v-card-title class="text-h6 font-weight-bold">
            Request Status Distribution
          </v-card-title>
          <VueApexCharts
            v-if="statusChart.options && statusChart.series.length"
            type="donut"
            height="340"
            :options="statusChart.options"
            :series="statusChart.series"
          />
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card elevation="10" class="pa-6 h-100 bubble-shape">
          <v-card-title class="text-h6 font-weight-bold">
            Weekly Request Trend
          </v-card-title>
          <VueApexCharts
            type="bar"
            height="340"
            :options="trendChart.options"
            :series="trendChart.series"
          />
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Requests Table -->
    <v-card elevation="10" class="bubble-shape overflow-hidden">
      <v-card-title class="pa-6 bg-grey-lighten-5 d-flex align-center justify-space-between">
        <div class="text-h6 font-weight-bold">Recent Requests</div>
        <v-spacer />
        <v-btn variant="text" color="primary" to="/main/requests/list">
          View All
        </v-btn>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="recentRequests"
        items-per-page="10"
        class="elevation-0"
        density="comfortable"
        :loading="loading"
      >
        <!-- Requester -->
        <template #item.requester="{ item }">
          <div class="d-flex align-center gap-3 py-3">
            <v-avatar size="40">
              <v-img
                v-if="getRequester(item.requester_id)?.image_profile"
                :src="getRequester(item.requester_id).image_profile"
                alt="Avatar"
                cover
              >
                <template #placeholder>
                  <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
                    <v-progress-circular indeterminate size="20" />
                  </div>
                </template>
              </v-img>
              <span v-else class="text-h6 font-weight-bold text-grey-darken-1">
                {{ (getRequester(item.requester_id)?.name || '?').charAt(0).toUpperCase() }}
              </span>
            </v-avatar>

            <div class="d-flex flex-column">
              <span class="font-weight-medium">
                {{ getRequester(item.requester_id)?.name || 'Loading...' }}
              </span>
              <span class="text-caption text-medium-emphasis">
                {{ getRequester(item.requester_id)?.department?.name || '—' }}
              </span>
            </div>
          </div>
        </template>

        <!-- Material -->
        <template #item.material="{ item }">
          <div>
            <div class="font-weight-medium">{{ item.material?.name || '—' }}</div>
            <div class="text-caption text-medium-emphasis">Qty: {{ item.quantity }}</div>
          </div>
        </template>

        <!-- Status -->
        <template #item.status="{ item }">
          <v-chip
            :color="getStatusColor(item.status)"
            size="small"
            class="font-weight-bold px-4 text-uppercase"
            label
          >
            {{ item.status }}
          </v-chip>
        </template>

        <!-- Date -->
        <template #item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <v-btn
            icon="mdi-eye"
            size="small"
            variant="text"
            color="primary"
            :to="`/main/requests/${item.id}`"
          />
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axiosClient from '@/plugins/axios'
import { DownloadIcon } from 'vue-tabler-icons'
import VueApexCharts from 'vue3-apexcharts'

// State
const requests = ref<any[]>([])
const recentRequests = ref<any[]>([])
const usersCache = ref<Map<number, any>>(new Map())
const loading = ref(true)
const exporting = ref(false)

// Helper: Get user from cache
const getRequester = (id: number) => {
  return usersCache.value.get(id) || { name: 'Loading...', department: { name: '' } }
}

// Fetch single user if not cached
const fetchUser = async (userId: number) => {
  if (usersCache.value.has(userId)) return

  try {
    const { data } = await axiosClient.get(`/users/${userId}`)
    const user = data.data || data
    usersCache.value.set(userId, user)
  } catch (err) {
    console.error(`Failed to load user ${userId}`, err)
    usersCache.value.set(userId, { name: 'Unknown User', department: { name: '—' } })
  }
}

// Load requests and users
onMounted(async () => {
  try {
    loading.value = true
    const { data } = await axiosClient.get('/material-requests')
    const list = (data.data || data || []) as any[]

    requests.value = list
    recentRequests.value = list.slice(0, 10)

    // Collect unique requester IDs
    const requesterIds = [...new Set(list.map(r => r.requester_id).filter(Boolean))]

    // Fetch all missing users in parallel
    await Promise.all(requesterIds.map(id => fetchUser(id)))
  } catch (err) {
    console.error('Failed to load requests', err)
  } finally {
    loading.value = false
  }
})

// Summary Cards
const summaryCards = computed(() => {
  const total = requests.value.length
  const pending = requests.value.filter(r => r.status === 'pending').length
  const approved = requests.value.filter(r => r.status === 'approved').length
  const processed = requests.value.filter(r => ['issued', 'returned'].includes(r.status)).length

  return [
    { title: 'Total Requests', value: total, color: 'blue-grey-darken-1', icon: 'mdi-file-document-multiple-outline' },
    { title: 'Pending Approval', value: pending, color: 'orange-darken-2', icon: 'mdi-clock-outline' },
    { title: 'Approved', value: approved, color: 'success', icon: 'mdi-check-circle' },
    { title: 'Processed', value: processed, color: 'purple', icon: 'mdi-package-up' }
  ]
})

// Status Donut Chart
const statusChart = computed(() => {
  const counts = {
    pending: requests.value.filter(r => r.status === 'pending').length,
    approved: requests.value.filter(r => r.status === 'approved').length,
    issued: requests.value.filter(r => r.status === 'issued').length,
    returned: requests.value.filter(r => r.status === 'returned').length,
    rejected: requests.value.filter(r => r.status === 'rejected').length
  }

  const series = Object.values(counts).filter(v => v > 0)
  const labels = ['Pending', 'Approved', 'Issued', 'Returned', 'Rejected'].filter((_, i) => Object.values(counts)[i] > 0)

  return {
    series,
    options: {
      chart: { type: 'donut' as const },
      labels,
      colors: ['#FF9800', '#4CAF50', '#2196F3', '#9C27B0', '#F44336'],
      legend: { position: 'bottom' as const },
      dataLabels: { enabled: true },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                formatter: () => requests.value.length.toString()
              }
            }
          }
        }
      },
      responsive: [{
        breakpoint: 480,
        options: { legend: { position: 'bottom' } }
      }]
    }
  }
})

// Weekly Trend (you can replace with real data later)
const trendChart = {
  series: [{ name: 'Requests', data: [12, 19, 15, 25, 22, 30, 28] }],
  options: {
    chart: { toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 8, distributed: true } },
    xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] },
    colors: ['#2196F3'],
    grid: { show: false },
    dataLabels: { enabled: false }
  }
}

// Table headers
const headers = [
  { title: 'Requester', key: 'requester', sortable: false, width: '300' },
  { title: 'Material', key: 'material', sortable: false },
  { title: 'Qty', key: 'quantity', align: 'center' as const, width: '80' },
  { title: 'Status', key: 'status', align: 'center' as const, width: '120' },
  { title: 'Date', key: 'created_at', width: '160' },
  { title: '', key: 'actions', sortable: false, align: 'end' as const, width: '60' }
]

// Status chip color
const getStatusColor = (status: string) => {
  const map: Record<string, string> = {
    pending: 'orange',
    approved: 'success',
    issued: 'info',
    returned: 'purple',
    rejected: 'error'
  }
  return map[status] || 'grey'
}

// Date formatting
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// CSV Export with proper escaping
const exportToCSV = () => {
  exporting.value = true

  const escapeCSV = (val: any) => `"${String(val ?? '').replace(/"/g, '""')}"`

  const rows = [
    ['Requester', 'Email', 'Department', 'Material', 'Quantity', 'Purpose', 'Status', 'Date'],
    ...requests.value.map(r => {
      const user = usersCache.value.get(r.requester_id) || {}
      return [
        user.name || '',
        user.email || '',
        user.department?.name || '—',
        r.material?.name || '',
        r.quantity,
        r.purpose || '—',
        r.status.toUpperCase(),
        new Date(r.created_at).toLocaleDateString()
      ].map(escapeCSV)
    })
  ]

  const csvContent = rows.map(row => row.join(',')).join('\n')
  const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `material-requests-${new Date().toISOString().slice(0,10)}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)

  exporting.value = false
}
</script>

<style scoped>
.bubble-shape {
  border-radius: 28px !important;
}

.summary-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.summary-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 35px -5px rgba(0,0,0,0.15) !important;
}
</style>