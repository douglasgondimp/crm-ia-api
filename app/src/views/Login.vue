<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card shadow-lg p-5" style="width: 100%; max-width: 420px;">
            <div class="text-center mb-4">
                <h1 class="fw-bold mb-2">CRM IA</h1>
                <p class="text-muted">Faça login para continuar</p>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <InputText id="email" v-model="form.email" type="email" class="form-control border rounded-2 p-2"
                        placeholder="seu@email.com" :class="{ 'is-invalid': errors.email }" @blur="validateEmail" />
                    <div v-if="errors.email" class="invalid-feedback">
                        {{ errors.email }}
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>

                    <IconField>
                        <InputPassword id="password" v-model="form.password" class="form-control border rounded-2 p-2"
                            placeholder="••••••••" :class="{ 'is-invalid': errors.password }" @blur="validatePassword"
                            :mask="mask" />
                        <InputIcon class="cursor-pointer" @click="mask = !mask">
                            <Eye v-if="mask" />
                            <EyeSlash v-else />
                        </InputIcon>
                        <div v-if="errors.password" class="invalid-feedback">
                            {{ errors.password }}
                        </div>
                    </IconField>

                </div>

                <div v-if="authStore.error" class="alert alert-danger" role="alert">
                    {{ authStore.error }}
                </div>

                <button type="submit" class="btn btn-primary w-100 rounded-3"
                    :disabled="authStore.loading">Entrar</button>
                <!-- <Button type="submit" label="Entrar" :loading="authStore.loading" severity="success" /> -->
            </form>

            <div class="mt-4 text-center">
                <p class="text-muted mb-0">
                    Não tem uma conta? <a href="#" @click.prevent="showRegister = true"
                        class="text-decoration-none">Cadastre-se</a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import InputPassword from 'primevue/inputpassword'
import Button from 'primevue/button'
import { IconField, InputIcon } from 'primevue'
import Eye from '@primeicons/vue/eye'
import EyeSlash from '@primeicons/vue/eye-slash'

const router = useRouter()
const authStore = useAuthStore()
const showRegister = ref(false)
const mask = ref(true)

const form = reactive({
    email: '',
    password: '',
})

const errors = reactive({
    email: '',
    password: '',
})

function validateEmail() {
    if (!form.email) {
        errors.email = 'O e-mail é obrigatório'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Informe um e-mail válido'
    } else {
        errors.email = ''
    }
}

function validatePassword() {
    if (!form.password) {
        errors.password = 'A senha é obrigatória'
    } else if (form.password.length < 6) {
        errors.password = 'A senha deve ter no mínimo 6 caracteres'
    } else {
        errors.password = ''
    }
}

async function handleSubmit() {
    validateEmail()
    validatePassword()

    if (errors.email || errors.password) {
        return
    }

    try {
        await authStore.login(form.email, form.password)
        router.push('/')
    } catch (e) {
        // Erro já tratado na store
    }
}

onMounted(() => {
    if (authStore.isAuthenticated) {
        router.push('/')
    }
})
</script>

<style scoped>
.bg-gradient {
    min-height: 100vh;
}

.cursor-pointer {
    position: absolute;
    inset-inline-end: 0 !important;
    right: -20px !important;
    top: 11px;
}
</style>