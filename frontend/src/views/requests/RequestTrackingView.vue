<template>
  <v-container fluid class="pa-4 pa-md-8">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-space-between align-center mb-8">
      <div>
        <h1 class="text-h4 font-weight-bold mb-1">Request Tracking Dashboard</h1>
        <p class="text-body-1 text-medium-emphasis">
          Real-time monitoring of material requests and workflow
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
          <div v-else class="text-center py-8 text-medium-emphasis">
            No requests to display
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card elevation="10" class="pa-6 h-100 bubble-shape">
          <v-card-title class="text-h6 font-weight-bold">
            Last 7 Days Trend
          </v-card-title>
          <VueApexCharts
            v-if="trendChart.series[0].data.some(d => d > 0)"
            type="bar"
            height="340"
            :options="trendChart.options"
            :series="trendChart.series"
          />
          <div v-else class="text-center py-8 text-medium-emphasis">
            No activity in the last 7 days
          </div>
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
        :headers="visibleHeaders"
        :items="recentRequests"
        items-per-page="10"
        class="elevation-0"
        density="comfortable"
        :loading="loading"
      >
        <!-- Requester (Admin/IT Assistant only) -->
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
import { useAuthStore } from '@/stores/auth'
import { DownloadIcon } from 'vue-tabler-icons'
import VueApexCharts from 'vue3-apexcharts'
const authStore = useAuthStore()
const currentUserId = computed(() => authStore.user?.id || 0)
// Fixed: Access role.name and normalize to lowercase
const userRole = computed(() => authStore.user?.role?.name?.toLowerCase() || 'employee')
// State
const requests = ref<any[]>([])
const usersCache = ref<Map<number, any>>(new Map())
const loading = ref(true)
const exporting = ref(false)
// Filtered requests based on role: Admin/IT Assistant see all, others own
const filteredRequests = computed(() => {
  if (['admin', 'it assistant'].includes(userRole.value)) {
    return requests.value
  }
  return requests.value.filter(r => r.requester_id === currentUserId.value)
})
const recentRequests = computed(() => filteredRequests.value.slice(0, 10))
// Helper: Get user from cache
const getRequester = (id: number) => {
  return usersCache.value.get(id) || { name: 'Loading...', department: { name: '' } }
}
// Fetch single user if not cached (only for admin/it assistant)
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
    requests.value = list.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    // Only fetch user details if admin or it assistant
    if (['admin', 'it assistant'].includes(userRole.value)) {
      const requesterIds = [...new Set(list.map(r => r.requester_id).filter(Boolean))]
      await Promise.all(requesterIds.map(id => fetchUser(id)))
    }
  } catch (err) {
    console.error('Failed to load requests', err)
  } finally {
    loading.value = false
  }
})
// Table headers - hide Requester column for non-admin/it assistant
const baseHeaders = [
  { title: 'Requester', key: 'requester', sortable: false, width: '300' },
  { title: 'Material', key: 'material', sortable: false },
  { title: 'Qty', key: 'quantity', align: 'center' as const, width: '80' },
  { title: 'Status', key: 'status', align: 'center' as const, width: '120' },
  { title: 'Date', key: 'created_at', width: '160' },
  { title: '', key: 'actions', sortable: false, align: 'end' as const, width: '60' }
]
const visibleHeaders = computed(() => {
  if (['admin', 'it assistant'].includes(userRole.value)) {
    return baseHeaders
  }
  return baseHeaders.filter(h => h.key !== 'requester')
})
// Status Donut Chart
const statusChart = computed(() => {
  const list = filteredRequests.value
  const counts = {
    pending: list.filter(r => r.status === 'pending').length,
    approved: list.filter(r => r.status === 'approved').length,
    issued: list.filter(r => r.status === 'issued').length,
    returned: list.filter(r => r.status === 'returned').length,
    rejected: list.filter(r => r.status === 'rejected').length,
    cancelled: list.filter(r => r.status === 'cancelled').length
  }
  const series = Object.values(counts).filter(v => v > 0)
  const labels = Object.keys(counts)
    .map(k => k.charAt(0).toUpperCase() + k.slice(1))
    .filter((_, i) => Object.values(counts)[i] > 0)
  return {
    series,
    options: {
      chart: { type: 'donut' as const },
      labels,
      colors: ['#FF9800', '#4CAF50', '#2196F3', '#9C27B0', '#F44336', '#9E9E9E'],
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
                formatter: () => list.length.toString()
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
// Weekly Trend (Last 7 days real data)
const trendChart = computed(() => {
  const days = []
  const data = []
  const now = new Date()
  now.setHours(0, 0, 0, 0)
  for (let i = 6; i >= 0; i--) {
    const date = new Date(now)
    date.setDate(date.getDate() - i)
    days.push(date.toLocaleDateString('en-US', { weekday: 'short' }))
   
    const count = filteredRequests.value.filter(r => {
      const reqDate = new Date(r.created_at)
      reqDate.setHours(0, 0, 0, 0)
      return reqDate.getTime() === date.getTime()
    }).length
    data.push(count)
  }
  return {
    series: [{ name: 'Requests', data }],
    options: {
      chart: { toolbar: { show: false } },
      plotOptions: { bar: { borderRadius: 8, distributed: true } },
      xaxis: { categories: days },
      colors: ['#2196F3'],
      grid: { show: false },
      dataLabels: { enabled: false },
      tooltip: { y: { formatter: (val: number) => `${val} request${val !== 1 ? 's' : ''}` } }
    }
  }
})
// Status chip color
const getStatusColor = (status: string) => {
  const map: Record<string, string> = {
    pending: 'orange',
    approved: 'success',
    issued: 'info',
    returned: 'purple',
    rejected: 'error',
    cancelled: 'grey'
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
// CSV Export (filtered by role)
const exportToCSV = () => {
  exporting.value = true
  const escapeCSV = (val: any) => `"${String(val ?? '').replace(/"/g, '""')}"`
  const rows = [
    ['Requester', 'Email', 'Department', 'Material', 'Quantity', 'Purpose', 'Status', 'Required By', 'Date Created'],
    ...filteredRequests.value.map(r => {
      const user = usersCache.value.get(r.requester_id) || {}
      return [
        user.name || '—',
        user.email || '—',
        user.department?.name || '—',
        r.material?.name || '—',
        r.quantity,
        r.purpose || '—',
        r.status.toUpperCase(),
        r.receipt_date || '—',
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
</style>