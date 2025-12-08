<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { useCustomizerStore } from '@/stores/customizer';
import { ref } from 'vue';
import { BellIcon, SettingsIcon, SearchIcon, Menu2Icon } from 'vue-tabler-icons';

// Dropdown imports
import NotificationDD from './NotificationDD.vue';
import ProfileDD from './ProfileDD.vue';
import Searchbar from './SearchBarPanel.vue';

const authStore = useAuthStore();
const customizer = useCustomizerStore();
const showSearch = ref(false);

const searchbox = () => {
  showSearch.value = !showSearch.value;
};
</script>

<template>
  <v-app-bar elevation="0" height="80">
    <!-- Sidebar Toggle (Desktop) -->
    <v-btn
      class="hidden-md-and-down text-secondary"
      color="lightsecondary"
      icon
      rounded="sm"
      variant="flat"
      @click.stop="customizer.SET_MINI_SIDEBAR(!customizer.mini_sidebar)"
      size="small"
    >
      <Menu2Icon size="20" stroke-width="1.5" />
    </v-btn>

    <!-- Sidebar Toggle (Mobile) -->
    <v-btn
      class="hidden-lg-and-up text-secondary ms-3"
      color="lightsecondary"
      icon
      rounded="sm"
      variant="flat"
      @click.stop="customizer.SET_SIDEBAR_DRAWER"
      size="small"
    >
      <Menu2Icon size="20" stroke-width="1.5" />
    </v-btn>

    <!-- Search Toggle (Mobile) -->
    <v-btn
      class="hidden-lg-and-up text-secondary ml-3"
      color="lightsecondary"
      icon
      rounded="sm"
      variant="flat"
      size="small"
      @click="searchbox"
    >
      <SearchIcon size="17" stroke-width="1.5" />
    </v-btn>

    <!-- Mobile Search Panel -->
    <v-sheet v-if="showSearch" class="search-sheet v-col-12 pa-4 bg-surface">
      <Searchbar :closesearch="searchbox" />
    </v-sheet>

    <!-- Desktop Search -->
    <v-sheet class="mx-3 v-col-3 v-col-xl-2 v-col-lg-4 d-none d-lg-block">
      <Searchbar />
    </v-sheet>

    <v-spacer />

    <!-- Notification Dropdown -->
    <v-menu :close-on-content-click="false">
      <template v-slot:activator="{ props }">
        <v-btn
          icon
          class="text-secondary mx-3"
          color="lightsecondary"
          rounded="sm"
          size="small"
          variant="flat"
          v-bind="props"
        >
          <BellIcon stroke-width="1.5" size="22" />
          <v-badge v-if="false" color="error" dot offset-x="8" offset-y="8" />
        </v-btn>
      </template>
      <v-sheet rounded="md" width="360" elevation="12">
        <NotificationDD />
      </v-sheet>
    </v-menu>

    <!-- User Profile Dropdown -->
    <v-menu :close-on-content-click="false">
      <template v-slot:activator="{ props }">
        <v-btn
          class="profileBtn text-primary d-flex align-center"
          color="lightprimary"
          variant="flat"
          rounded="pill"
          v-bind="props"
        >
          <v-avatar
            size="40"
            class="mr-3"
            style="border: 2px solid #6366f1; box-shadow: 0 2px 6px rgba(0,0,0,0.1);"
          >
            <v-img
              :src="authStore.user?.image_profile || `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=6366f1&color=fff&bold=true`"
              :alt="authStore.user?.name || 'User'"
              class="object-cover"
              lazy-src="https://via.placeholder.com/40?text=..."
              cover
            />
          </v-avatar>
          <SettingsIcon stroke-width="1.5" />
        </v-btn>
      </template>
      <v-sheet rounded="md" width="340" elevation="12">
        <ProfileDD />
      </v-sheet>
    </v-menu>
  </v-app-bar>
</template>

<style scoped>
.search-sheet {
  position: absolute;
  top: 70px;
  left: 0;
  right: 0;
  z-index: 999;
  border-radius: 0 0 12px 12px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

/* Profile button hover effect */
.profileBtn:hover {
  background-color: #f3f4f6; /* Light surface hover */
  transition: background-color 0.2s ease;
}

/* Avatar image styling */
.v-avatar img {
  border-radius: 50%;
}
</style>
