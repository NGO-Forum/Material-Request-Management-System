<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { ref } from 'vue';
import { LogoutIcon, UserIcon } from 'vue-tabler-icons';

const authStore = useAuthStore();
</script>

<template>
  <div class="pa-4">
    <!-- Scrollable Profile Dropdown -->
    <perfect-scrollbar style="max-height: 150px;">
      <v-list class="mt-3" dense>
        <!-- Profile Details -->
        <v-list-item color="secondary" rounded="md">
          <template v-slot:prepend>
            <UserIcon size="20" class="mr-2" />
          </template>
          <v-list-item-title class="text-subtitle-2">Profile Details</v-list-item-title>
        </v-list-item>

        <v-divider class="my-2"></v-divider>

        <!-- Logout -->
        <v-list-item @click="authStore.logout()" color="secondary" rounded="md">
          <template v-slot:prepend>
            <LogoutIcon size="20" class="mr-2" />
          </template>
          <v-list-item-title class="text-subtitle-2">Logout</v-list-item-title>
        </v-list-item>
      </v-list>
    </perfect-scrollbar>
  </div>
</template>

<style scoped>
.pa-4 {
  padding: 16px;
}

.v-list-item {
  cursor: pointer;
}

.v-list-item:hover {
  background-color: rgba(0, 0, 0, 0.04);
}

.mt-3 {
  margin-top: 12px;
}
</style>
