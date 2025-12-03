<template>
  <div class="logo">
    <RouterLink to="/">
      <img src="@/assets/images/ngof.png" alt="ngof" class="logo-img" :fill="primary" />
    </RouterLink>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';

const primary = ref('rgb(var(--v-theme-primary))');
const secondary = ref('rgb(var(--v-theme-secondary))');
</script>

<style scoped>
.logo-img {
  width: 160px;
  height: 100px;
  margin-right: 10px; 
  object-fit: contain; 
}
</style>
