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
  <v-footer class="px-0 footer mt-2">
    <v-row justify="center" align="center" no-gutters>
      <v-col cols="12" sm="6">
        <p class="text-body-1 mb-0 text-sm-left text-center">
        The NGO Forum on Cambodia
        </p>
      </v-col>
      <v-col class="text-sm-right text-center" cols="12" sm="6">
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
