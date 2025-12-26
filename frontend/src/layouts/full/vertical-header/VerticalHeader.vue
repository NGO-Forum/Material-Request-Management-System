<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useCustomizerStore } from '@/stores/customizer';

import { BellIcon, SettingsIcon, SearchIcon, Menu2Icon } from 'vue-tabler-icons';

import NotificationDD from './NotificationDD.vue';
import ProfileDD from './ProfileDD.vue';
import Searchbar from './SearchBarPanel.vue';

const authStore = useAuthStore();
const customizer = useCustomizerStore();

const showSearch = ref(false);
const searchbox = () => (showSearch.value = !showSearch.value);

/* ─────────────────────────────
   Safe inline placeholder (no network)
   ───────────────────────────── */
const avatarPlaceholder =
  'data:image/svg+xml;base64,' +
  btoa(`
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40">
      <rect width="40" height="40" rx="20" fill="#e5e7eb"/>
      <text x="50%" y="55%" text-anchor="middle" font-size="18" fill="#6b7280">?</text>
    </svg>
  `);

/* ─────────────────────────────
   Computed avatar values
   ───────────────────────────── */
const userName = computed(() => authStore.user?.name || 'User');

const uiAvatar = computed(
  () =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(
      userName.value
    )}&background=6366f1&color=fff&bold=true`
);

// The image we try first
const avatarUrl = computed(
  () => authStore.user?.image_profile || uiAvatar.value
);

// The src currently displayed (swaps if error)
const currentAvatar = ref(avatarUrl.value);

// Reset avatar if store updates
watch(avatarUrl, newVal => (currentAvatar.value = newVal));

const onAvatarError = () => {
  // fallback to UI avatar if custom image fails
  currentAvatar.value = uiAvatar.value;
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
      size="small"
      @click.stop="customizer.SET_MINI_SIDEBAR(!customizer.mini_sidebar)"
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
      size="small"
      @click.stop="customizer.SET_SIDEBAR_DRAWER"
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
    <v-sheet
      v-if="showSearch"
      class="search-sheet v-col-12 pa-4 bg-surface"
    >
      <Searchbar :closesearch="searchbox" />
    </v-sheet>

    <!-- Desktop Search -->
    <v-sheet
      class="mx-3 v-col-3 v-col-xl-2 v-col-lg-4 d-none d-lg-block"
    >
      <Searchbar />
    </v-sheet>

    <v-spacer />

    <!-- Notifications -->
    <v-menu :close-on-content-click="false">
      <template #activator="{ props }">
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

    <!-- User Profile -->
    <v-menu :close-on-content-click="false">
      <template #activator="{ props }">
        <v-btn
          class="profileBtn text-primary d-flex align-center"
          color="lightprimary"
          rounded="pill"
          variant="flat"
          v-bind="props"
        >
          <v-avatar
            size="40"
            class="mr-3"
            style="border:2px solid #6366f1; box-shadow:0 2px 6px rgba(0,0,0,0.1);"
          >
            <v-img
              :src="currentAvatar"
              :lazy-src="avatarPlaceholder"
              :alt="userName"
              class="object-cover"
              cover
              @error="onAvatarError"
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

.profileBtn:hover {
  background-color: #f3f4f6;
  transition: background-color 0.2s ease;
}

.v-avatar img {
  border-radius: 50%;
}
</style>
