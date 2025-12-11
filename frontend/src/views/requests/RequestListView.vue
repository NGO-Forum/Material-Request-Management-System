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
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
            />
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="requests"
            :search="search"
            :loading="loading"
            items-per-page="15"
            class="elevation-2"
            density="comfortable"
          >
            <template #item.status="{ item }">
              <v-chip
                :color="getStatusColor(item.status)"
                size="small"
                label
                class="font-weight-medium"
              >
                {{ item.status.toUpperCase() }}
              </v-chip>
            </template>

            <template #item.material="{ item }">
              <div class="py-2">
                <div class="font-weight-bold">{{ item.material?.name || '—' }}</div>
                <div class="text-caption text-grey-darken-1">
                  Qty: {{ item.quantity }} | {{ item.material?.model || '' }}
                </div>
              </div>
            </template>

            <template #item.requester="{ item }">
              <div>
                <div>{{ item.requester?.name || 'Unknown' }}</div>
                <div class="text-caption text-grey">{{ item.requester?.email }}</div>
              </div>
            </template>

            <template #item.created_at="{ item }">
              {{ formatDate(item.created_at) }}
            </template>

            <template #item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                :to="`/main/requests/${item.id}`"
              />
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axiosClient from '@/plugins/axios'

interface MaterialRequest {
  id: number
  material: { id: number; name: string; model: string | null }
  quantity: number
  purpose: string | null
  status: string
  requester: { name: string; email: string }
  created_at: string
}

const requests = ref<MaterialRequest[]>([])
const loading = ref(true)
const search = ref('')

const headers = [
  { title: '#', key: 'id', width: 80 },
  { title: 'Material', key: 'material', sortable: false },
  { title: 'Qty', key: 'quantity', align: 'center' },
  { title: 'Purpose', key: 'purpose' },
  { title: 'Requester', key: 'requester', sortable: false },
  { title: 'Status', key: 'status', align: 'center' },
  { title: 'Date', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'center' }
]

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    pending: 'orange',
    approved: 'success',
    rejected: 'error',
    issued: 'info',
    returned: 'purple',
    cancelled: 'grey'
  }
  return colors[status] || 'default'
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(async () => {
  try {
    const res = await axiosClient.get('/material-requests')
    requests.value = res.data.data || res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
})
</script>