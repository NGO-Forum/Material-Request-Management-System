<script setup lang="ts">
import { ref } from 'vue';
import { BuildingStoreIcon } from 'vue-tabler-icons';

const notificationDD = ref(['All Notifications', 'New', 'Unread', 'Other']);
const selectNotify = ref('All Notifications');
</script>

<template>
  <div class="pa-4">
    <!-- Header with title and count -->
    <div class="d-flex align-center justify-space-between mb-3">
      <h6 class="text-subtitle-1">
        All Notifications
        <v-chip color="warning" variant="flat" size="small" class="ml-2 text-white">01</v-chip>
      </h6>
    </div>

    <!-- Notifications filter -->
    <v-select
      :items="notificationDD"
      v-model="selectNotify"
      color="primary"
      variant="outlined"
      density="default"
      hide-details
      class="mb-3"
    ></v-select>
  </div>

  <v-divider></v-divider>

  <!-- Notifications List -->
  <perfect-scrollbar style="height: calc(100vh - 300px); max-height: 650px;">
    <v-list class="py-0" lines="three">

      <!-- Notification Item 1 -->
      <v-list-item color="secondary" class="no-spacer">
        <template v-slot:prepend>
          <v-avatar size="40" class="mr-3 py-2">
            <img src="@/assets/images/profile/chhea.jpg" width="40" alt="John Deo" />
          </v-avatar>
        </template>

        <div class="d-inline-flex align-center justify-space-between w-100">
          <h6 class="text-subtitle-1 font-weight-regular">John Deo</h6>
          <span class="text-subtitle-2 text-medium-emphasis">2 mins ago</span>
        </div>

        <p class="text-subtitle-2 text-medium-emphasis mt-1">
          It is a long established fact that a reader will be distracted
        </p>

        <div class="mt-3">
          <v-chip size="small" color="error" text="Unread" variant="tonal" class="mr-2" />
          <v-chip size="small" color="warning" text="New" variant="tonal" />
        </div>
      </v-list-item>

      <v-divider></v-divider>

      <!-- Notification Item 2 -->
      <v-list-item color="secondary" class="no-spacer">
        <template v-slot:prepend>
          <v-avatar size="40" variant="flat" color="lightsuccess" class="mr-3 py-2 text-success">
            <BuildingStoreIcon size="20" />
          </v-avatar>
        </template>

        <div class="d-inline-flex align-center justify-space-between w-100">
          <h6 class="text-subtitle-1">Store Verification Done</h6>
          <span class="text-subtitle-2 text-medium-emphasis">2 mins ago</span>
        </div>

        <p class="text-subtitle-2 text-medium-emphasis mt-1">
          We have successfully received your request.
        </p>

        <div class="mt-3">
          <v-chip size="small" color="error" text="Unread" variant="tonal" />
        </div>
      </v-list-item>

    </v-list>
  </perfect-scrollbar>

  <v-divider></v-divider>

  <!-- Footer -->
  <div class="pa-2 text-center">
    <v-btn color="primary" variant="text">View All</v-btn>
  </div>
</template>

<style scoped>
/* Optional spacing fixes */
.no-spacer {
  padding: 12px 16px;
}
</style>
