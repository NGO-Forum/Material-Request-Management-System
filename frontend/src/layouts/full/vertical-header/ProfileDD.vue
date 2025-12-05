<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
import { LogoutIcon, UserIcon, MailIcon, ShieldIcon } from 'vue-tabler-icons';
import Swal from 'sweetalert2';

const authStore = useAuthStore();

const confirmLogout = () => {
  Swal.fire({
    title: '<span style="font-size: 1.8rem; font-weight: 700;">Logout?</span>',
    html: `<p style="font-size: 1.1rem; color: #666; margin: 16px 0;">
            See you soon, <strong>${authStore.user?.name || 'friend'}</strong>!
           </p>`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Logout',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    buttonsStyling: false,
    width: '420px',
    padding: '2rem',
    customClass: {
      popup: 'swal-popup',
      confirmButton: 'swal-confirm-btn',
      cancelButton: 'swal-cancel-btn',
      actions: 'swal-actions',
    },
    // Add beautiful icon animation
    iconHtml: '<div class="swal-icon-animate">?</div>',
  }).then((result) => {
    if (result.isConfirmed) {
      authStore.logout();
      Swal.fire({
        title: 'Logged Out!',
        text: 'You have been safely logged out.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        background: '#10b981',
        color: 'white',
        customClass: {
          popup: 'animate__animated animate__fadeInRight',
        },
      });
    }
  });
};
</script>

<template>
  <div class="pa-5 bg-surface rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700">
    <!-- User Info -->
    <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
      <v-avatar size="70" class="ring-4 ring-primary/30 shadow-lg">
        <img
          :src="authStore.user?.image_profile || `https://ui-avatars.com/api/?name=${encodeURIComponent(authStore.user?.name || 'User')}&background=6366f1&color=fff&bold=true&size=128`"
          :alt="authStore.user?.name"
          class="rounded-full object-cover"
        />
      </v-avatar>
      <div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
          {{ authStore.user?.name || 'Guest User' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-1">
          <MailIcon size="15" />
          {{ authStore.user?.email || 'user@example.com' }}
        </p>
        <div class="flex items-center gap-1 text-sm font-semibold text-primary mt-2">
          <ShieldIcon size="16" />
          {{ authStore.user?.role?.name || 'Member' }}
        </div>
      </div>
    </div>

    <!-- Menu -->
    <v-list density="compact" class="bg-transparent">
      <v-list-item
        to="/main/profile"
        class="rounded-xl hover:bg-primary/5 dark:hover:bg-primary/10 mb-3 transition-all duration-200"
      >
        <template v-slot:prepend>
          <UserIcon size="22" class="text-primary" />
        </template>
        <v-list-item-title class="font-medium text-gray-700 dark:text-gray-200">
          My Profile
        </v-list-item-title>
      </v-list-item>

      <v-list-item
        @click="confirmLogout"
        class="rounded-xl hover:bg-red-50 dark:hover:bg-red-900/30 text-error cursor-pointer transition-all duration-200"
      >
        <template v-slot:prepend>
          <LogoutIcon size="22" />
        </template>
        <v-list-item-title class="font-semibold">Logout</v-list-item-title>
      </v-list-item>
    </v-list>
  </div>
</template>

<style scoped>
/* Beautiful SweetAlert2 Popup */
.swal-popup {
  border-radius: 20px !important;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2) !important;
  background: white !important;
}

.swal-actions {
  gap: 16px !important;
  margin-top: 28px !important;
  justify-content: center !important;
}

/* Confirm Button (Red) */
.swal-confirm-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626) !important;
  color: white !important;
  padding: 14px 32px !important;
  border-radius: 16px !important;
  font-weight: 600 !important;
  font-size: 1rem !important;
  min-width: 130px;
  box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4) !important;
  transition: all 0.3s ease !important;
}

.swal-confirm-btn:hover {
  transform: translateY(-3px) scale(1.05) !important;
  box-shadow: 0 12px 28px rgba(239, 68, 68, 0.5) !important;
}

/* Cancel Button (Gray) */
.swal-cancel-btn {
  background: linear-gradient(135deg, #6b7280, #4b5563) !important;
  color: white !important;
  padding: 14px 32px !important;
  border-radius: 16px !important;
  font-weight: 600 !important;
  font-size: 1rem !important;
  min-width: 130px;
  box-shadow: 0 8px 20px rgba(107, 114, 128, 0.4) !important;
  transition: all 0.3s ease !important;
}

.swal-cancel-btn:hover {
  transform: translateY(-3px) scale(1.05) !important;
  box-shadow: 0 12px 28px rgba(107, 114, 128, 0.5) !important;
}

/* Animated Question Icon */
.swal-icon-animate {
  font-size: 4rem;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}
</style>