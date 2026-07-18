<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Sidebar from '@/components/Sidebar.vue'

const router = useRouter()
const authStore = useAuthStore()

onMounted(async () => {
  if (authStore.token && !authStore.user) {
    await authStore.fetchMe()
  }
})
</script>

<template>
  <div class="app-layout">
    <Sidebar v-if="authStore.user" />
    <main class="main-content" :class="{ 'content-login': authStore.user }">
      <router-view />
    </main>
  </div>
</template>

<style>
@import "bootstrap/dist/css/bootstrap.min.css";

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.app-layout {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  min-height: 100vh;
}

.content-login {
  margin-left: 260px;
}
</style>
