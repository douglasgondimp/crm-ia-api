import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/services/api'

interface User {
    id: number
    uuid: string
    name: string
    email: string
    avatar?: string
    phone?: string
    role: string
    status: boolean
    last_login_at?: string
    email_verified_at?: string
}

interface LoginResponse {
    message: string
    data: {
        user: User
        token: string
    }
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const token = ref<string | null>(localStorage.getItem('token'))
    const loading = ref(false)
    const error = ref<string | null>(null)

    const isAuthenticated = computed(() => !!token.value && !!user.value)

    async function login(email: string, password: string) {
        loading.value = true
        error.value = null

        try {
            const response = await api.post<LoginResponse>('/login', { email, password })

            user.value = response.data.user
            token.value = response.data.token
            api.setToken(response.data.token)

            return response.data
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Erro ao fazer login'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function register(name: string, email: string, password: string, phone?: string) {
        loading.value = true
        error.value = null

        try {
            const response = await api.post<LoginResponse>('/register', {
                name,
                email,
                password,
                password_confirmation: password,
                phone,
            })

            user.value = response.data.user
            token.value = response.data.token
            api.setToken(response.data.token)

            return response.data
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Erro ao cadastrar'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function logout() {
        loading.value = true

        try {
            await api.post('/logout')
        } catch (e) {
            // Ignora erros de logout
        } finally {
            user.value = null
            token.value = null
            api.removeToken()
            loading.value = false
        }
    }

    async function fetchMe() {
        if (!token.value) return

        loading.value = true
        error.value = null

        try {
            const response = await api.get<{ data: User }>('/me')
            user.value = response.data
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Erro ao buscar usuário'
            await logout()
            throw e
        } finally {
            loading.value = false
        }
    }

    async function updateProfile(data: { name?: string; email?: string; phone?: string; avatar?: File }) {
        loading.value = true
        error.value = null

        try {
            const formData = new FormData()
            if (data.name) formData.append('name', data.name)
            if (data.email) formData.append('email', data.email)
            if (data.phone) formData.append('phone', data.phone)
            if (data.avatar) formData.append('avatar', data.avatar)

            const response = await api.put<{ data: User }>('/me', formData)
            user.value = response.data

            return response.data
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Erro ao atualizar perfil'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function updatePassword(currentPassword: string, newPassword: string) {
        loading.value = true
        error.value = null

        try {
            await api.put('/me/password', {
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: newPassword,
            })
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Erro ao atualizar senha'
            throw e
        } finally {
            loading.value = false
        }
    }

    function clearError() {
        error.value = null
    }

    return {
        user,
        token,
        loading,
        error,
        isAuthenticated,
        login,
        register,
        logout,
        fetchMe,
        updateProfile,
        updatePassword,
        clearError,
    }
})