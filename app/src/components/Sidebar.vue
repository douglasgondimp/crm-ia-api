<template>
    <div class="sidebar d-flex flex-column h-100">
        <!-- System Name -->
        <div class="sidebar-header p-3 border-bottom">
            <h5 class="mb-0 fw-bold text-primary">CRM IA</h5>
        </div>

        <!-- Menu Items -->
        <nav class="sidebar-nav flex-grow-1 p-3">
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <router-link to="/pipeline" class="nav-link d-flex align-items-center">
                        <i class="pi pi-chart-line me-2"></i>
                        <span>Pipeline</span>
                    </router-link>
                </li>
                <li class="nav-item mb-2">
                    <router-link to="/leads" class="nav-link d-flex align-items-center">
                        <i class="pi pi-users me-2"></i>
                        <span>Leads</span>
                    </router-link>
                </li>
                <li class="nav-item mb-2">
                    <router-link to="/empresas" class="nav-link d-flex align-items-center">
                        <i class="pi pi-building me-2"></i>
                        <span>Empresas</span>
                    </router-link>
                </li>
                <li class="nav-item mb-2">
                    <router-link to="/usuarios" class="nav-link d-flex align-items-center">
                        <i class="pi pi-user me-2"></i>
                        <span>Usuários</span>
                    </router-link>
                </li>
                <li class="nav-item mb-2">
                    <router-link to="/metricas" class="nav-link d-flex align-items-center">
                        <i class="pi pi-chart-bar me-2"></i>
                        <span>Métricas</span>
                    </router-link>
                </li>
                <li class="nav-item mb-2">
                    <router-link to="/sugestoes-ia" class="nav-link d-flex align-items-center">
                        <i class="pi pi-sparkles me-2"></i>
                        <span>Sugestões de IA</span>
                    </router-link>
                </li>
            </ul>
        </nav>

        <!-- User Footer -->
        <div class="sidebar-footer p-3 border-top">
            <div class="dropdown">
                <button class="btn btn-link text-decoration-none w-100 d-flex align-items-center p-0" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <Avatar :label="userInitials" shape="circle" size="normal" class="me-2"
                        style="background-color: #0d6efd; color: white;" />
                    <div class="text-start flex-grow-1">
                        <div class="fw-semibold small">{{ authStore.user?.name }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ authStore.user?.email }}</div>
                    </div>
                    <i class="pi pi-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                </button>
                <ul class="dropdown-menu w-100">
                    <li>
                        <router-link to="/perfil" class="dropdown-item">
                            <i class="pi pi-user me-2"></i>
                            Visualizar Perfil
                        </router-link>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <button class="dropdown-item text-danger" @click="handleLogout">
                            <i class="pi pi-sign-out me-2"></i>
                            Sair
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Avatar from 'primevue/avatar'


const router = useRouter()
const authStore = useAuthStore()

const userInitials = computed(() => {
    if (!authStore.user?.name) return 'U'
    const names = authStore.user.name.split(' ')
    if (names.length >= 2 && names[0] && names[1]) {
        return `${names[0][0]}${names[1][0]}`.toUpperCase()
    }
    if (names[0]) {
        return names[0].substring(0, 2).toUpperCase()
    }
    return 'U'
})

async function handleLogout() {
    await authStore.logout()
    router.push('/login')
}
</script>

<style scoped>
.sidebar {
    width: 260px;
    min-height: 100vh;
    background-color: #f8f9fa;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
}

.sidebar-header {
    background-color: #fff;
}

.sidebar-nav {
    overflow-y: auto;
}

.sidebar-nav .nav-link {
    color: #333;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    text-decoration: none;
}

.sidebar-nav .nav-link:hover {
    background-color: #e9ecef;
    color: #0d6efd;
}

.sidebar-nav .nav-link.router-link-active {
    background-color: #0d6efd;
    color: white;
}

.sidebar-nav .nav-link i {
    font-size: 1.1rem;
    width: 1.5rem;
    text-align: center;
}

.sidebar-footer {
    background-color: #fff;
}

.sidebar-footer .btn-link {
    color: #333;
}

.sidebar-footer .btn-link:hover {
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.dropdown-item {
    cursor: pointer;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Main content adjustment */
@media (min-width: 768px) {
    .main-content {
        margin-left: 260px;
    }
}
</style>