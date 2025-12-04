<script setup lang="ts">
import { shallowRef } from 'vue';
import { RouterLink } from 'vue-router';

const footerLink = shallowRef([
  {
    title: 'Home',
    url: '/'
  },
  {
    title: 'Documentation',
    url: '#!'
  },
  {
    title: 'Support',
    url: '#!'
  }
]);
</script>
<template>
  <v-footer class="px-4 py-3 footer mt-2" color="grey lighten-4">
  <v-row justify="space-between" align="center" no-gutters>

    <v-col cols="12" sm="6" class="text-sm-left text-center">
      <p class="text-body-2 mb-0 font-weight-medium">
        🌏 The NGO Forum on Cambodia — Working for sustainable development & social justice
      </p>
    </v-col>

    <v-col cols="12" sm="6" class="text-sm-right text-center mt-sm-0 mt-2">
      <p class="text-body-2 mb-0">
        © {{ new Date().getFullYear() }} | All Rights Reserved
      </p>
    </v-col>

  </v-row>
</v-footer>

</template>

<style lang="scss">
.v-footer {
  position: unset;
}
footer {
  a {
    &:hover {
      color: rgb(var(--v-theme-primary)) !important;
    }
  }
}
</style>
