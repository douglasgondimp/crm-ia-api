<template>
    <div class="min-vh-100 bg-light">
        <div class="container py-5">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="fw-bold">Bem-vindo ao CRM IA</h1>
                        <p class="text-muted">Você está autenticado!</p>
                    </div>

                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h2 class="h5 mb-3">Informações do Usuário</h2>
                            <div class="small">
                                <p class="mb-2"><strong>Nome:</strong> {{ authStore.user?.name }}</p>
                                <p class="mb-2"><strong>E-mail:</strong> {{ authStore.user?.email }}</p>
                                <p class="mb-0"><strong>Função:</strong> {{ authStore.user?.role }}</p>
                            </div>
                        </div>
                    </div>

                    <Button label="Sair" icon="pi pi-sign-out" class="btn btn-danger w-100" severity="danger"
                        @click="handleLogout" :loading="authStore.loading" />
                </div>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Button from 'primevue/button'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>