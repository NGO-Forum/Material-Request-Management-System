<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-space-between align-center mb-8">
          <div>
            <h1 class="text-h4 font-weight-bold mb-1">Inventory Reports</h1>
            <p class="text-body-1 text-medium-emphasis">
              Comprehensive overview of material stock levels and categories
            </p>
          </div>
          <v-btn color="primary" @click="exportToCSV" :loading="exporting" class="mb-3">
            <DownloadIcon class="mr-2" :size="20" />
            Export CSV
          </v-btn>
        </div>

        <!-- Summary Cards -->
        <v-row class="mb-8">
          <v-col cols="12" sm="6" md="3" v-for="card in summaryCards" :key="card.title">
            <v-card elevation="6" class="pa-6 text-center" :class="card.bg">
              <v-icon size="48" color="white" class="mb-3">{{ card.icon }}</v-icon>
              <div class="text-h4 font-weight-bold text-white">{{ card.value }}</div>
              <div class="text-caption text-white-opacity-90">{{ card.title }}</div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Charts Row -->
        <v-row class="mb-8">
          <v-col cols="12" md="6">
            <v-card elevation="4" class="pa-4 h-100 bubble-shape-sm bubble-primary">
              <v-card-title class="text-h6 font-weight-bold">Materials by Category</v-card-title>
              <apexchart type="pie" height="320" :options="categoryChart.options" :series="categoryChart.series"/>
            </v-card>
          </v-col>
          <v-col cols="12" md="6">
            <v-card elevation="4" class="pa-4 h-100 bubble-shape-sm bubble-success">
              <v-card-title class="text-h6 font-weight-bold">Stock Status Distribution</v-card-title>
              <apexchart type="bar" height="320" :options="stockStatusChart.options" :series="stockStatusChart.series"/>
            </v-card>
          </v-col>
        </v-row>

        <!-- Materials Table -->
        <v-card elevation="4" class="rounded-lg">
          <v-card-title class="pa-4 d-flex align-center justify-space-between">
            <v-text-field
              v-model="search"
              append-inner-icon="mdi-magnify"
              label="Search materials..."
              single-line
              hide-details
              clearable
              variant="outlined"
              density="comfortable"
              class="w-75"
            />
            <v-btn color="primary" @click="exportToCSV" :loading="exporting">
              <DownloadIcon class="mr-2" :size="20"/> Export CSV
            </v-btn>
          </v-card-title>

          <PerfectScrollbar :style="{ height: '480px' }">
            <v-data-table
              :headers="headers"
              :items="materials"
              :search="search"
              :loading="loading"
              loading-text="Loading inventory..."
              items-per-page="10"
              class="elevation-1"
              density="comfortable"
            >
              <template #item.image="{ item }">
                <v-avatar size="48" class="my-2">
                  <v-img :src="item.image || 'https://via.placeholder.com/80?text=No+Img'" cover class="rounded"/>
                </v-avatar>
              </template>

              <template #item.name="{ item }">
                <div class="font-weight-medium">{{ item.name }}</div>
                <div class="text-caption text-medium-emphasis">{{ item.model || '—' }}</div>
              </template>

              <template #item.category="{ item }">
                <v-chip size="small" color="primary" label>{{ item.category?.name || 'Uncategorized' }}</v-chip>
              </template>

              <template #item.qty_remaining="{ item }">
                <v-chip
                  :color="item.qty_remaining === 0 ? 'error' : item.qty_remaining <= 5 ? 'warning' : 'success'"
                  size="small"
                  class="font-weight-bold"
                >
                  {{ item.qty_remaining }} left
                </v-chip>
              </template>

              <template #item.status="{ item }">
                <v-chip
                  :color="item.qty_remaining === 0 ? 'error' : item.qty_remaining <= 5 ? 'warning' : 'success'"
                  size="small"
                >
                  {{ item.qty_remaining === 0 ? 'Out of Stock' : item.qty_remaining <= 5 ? 'Low Stock' : 'Available' }}
                </v-chip>
              </template>
            </v-data-table>
          </PerfectScrollbar>
        </v-card>

        <!-- Snackbar -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" location="top">
          <CircleCheckIcon v-if="snackbar.color === 'success'" class="mr-2" />
          <AlertCircleIcon v-else class="mr-2" />
          {{ snackbar.message }}
        </v-snackbar>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axiosClient from '@/plugins/axios';
import { CircleCheckIcon, AlertCircleIcon, DownloadIcon } from 'vue-tabler-icons';
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';

interface Material {
  id: number;
  name: string;
  model: string | null;
  image: string | null;
  qty_remaining: number;
  category: { name: string } | null;
}

const materials = ref<Material[]>([]);
const loading = ref(true);
const search = ref('');
const exporting = ref(false);
const snackbar = ref({ show: false, message: '', color: 'success' as 'success' | 'error' });

const showMessage = (msg: string, color: 'success' | 'error' = 'success') => {
  snackbar.value = { show: true, message: msg, color };
};

const stats = computed(() => {
  const total = materials.value.length;
  const outOfStock = materials.value.filter(m => m.qty_remaining === 0).length;
  const lowStock = materials.value.filter(m => m.qty_remaining > 0 && m.qty_remaining <= 5).length;
  const inStock = total - outOfStock;
  return { total, inStock, lowStock, outOfStock };
});

const summaryCards = computed(() => [
  { title: 'Total Materials', value: stats.value.total, icon: 'mdi-package-variant', bg: 'bg-primary' },
  { title: 'In Stock', value: stats.value.inStock, icon: 'mdi-check-circle', bg: 'bg-success' },
  { title: 'Low Stock (≤5)', value: stats.value.lowStock, icon: 'mdi-alert', bg: 'bg-warning' },
  { title: 'Out of Stock', value: stats.value.outOfStock, icon: 'mdi-close-circle', bg: 'bg-error' }
]);

const categoryChart = computed(() => {
  const categories = materials.value.reduce((acc: any, m) => {
    const name = m.category?.name || 'Uncategorized';
    acc[name] = (acc[name] || 0) + 1;
    return acc;
  }, {});
  return {
    series: Object.values(categories),
    options: {
      chart: { type: 'pie' },
      labels: Object.keys(categories),
      legend: { position: 'bottom' },
      colors: ['#1976d2', '#388e3c', '#f57c00', '#d32f2f', '#7b1fa2', '#689f38', '#5d4037'],
      dataLabels: { enabled: true },
      tooltip: { y: { formatter: (val: number) => `${val} items` } }
    }
  };
});

const stockStatusChart = computed(() => ({
  series: [{ name: 'Materials', data: [stats.value.inStock, stats.value.lowStock, stats.value.outOfStock] }],
  options: {
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
    colors: ['#4caf50', '#ff9800', '#f44336'],
    xaxis: { categories: ['In Stock', 'Low Stock (≤5)', 'Out of Stock'] },
    dataLabels: { enabled: true, style: { fontSize: '14px', fontWeight: 'bold' } },
    tooltip: { y: { formatter: (val: number) => `${val} materials` } }
  }
}));

const headers = [
  { title: 'Image', key: 'image', sortable: false, width: 90, align: 'center' as const },
  { title: 'Material', key: 'name', width: 250 },
  { title: 'Category', key: 'category', sortable: false },
  { title: 'Remaining', key: 'qty_remaining', align: 'center' as const },
  { title: 'Status', key: 'status', align: 'center' as const }
];

onMounted(async () => {
  try {
    const res = await axiosClient.get('/materials');
    materials.value = res.data.data || res.data;
  } catch {
    showMessage('Failed to load inventory data', 'error');
  } finally {
    loading.value = false;
  }
});

const exportToCSV = () => {
  exporting.value = true;
  const csvContent = [
    ['Name', 'Model', 'Category', 'Remaining Stock', 'Status'],
    ...materials.value.map(m => [
      m.name,
      m.model || '',
      m.category?.name || 'Uncategorized',
      m.qty_remaining,
      m.qty_remaining === 0 ? 'Out of Stock' : m.qty_remaining <= 5 ? 'Low Stock' : 'Available'
    ])
  ].map(row => row.join(',')).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `inventory-report-${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
  exporting.value = false;
  showMessage('Report exported successfully!');
};
</script>

<style scoped>
.h-100 { height: 100%; }
.bg-primary { background: linear-gradient(135deg, #1976d2, #42a5f5); }
.bg-success { background: linear-gradient(135deg, #388e3c, #66bb6a); }
.bg-warning { background: linear-gradient(135deg, #f57c00, #ffb74d); }
.bg-error { background: linear-gradient(135deg, #d32f2f, #ef5350); }
.bubble-shape-sm { border-radius: 20px; }
.bubble-primary { background: linear-gradient(135deg, #42a5f5, #1976d2); }
.bubble-success { background: linear-gradient(135deg, #66bb6a, #388e3c); }
</style>
