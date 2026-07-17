const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

console.log('API_BASE_URL', API_BASE_URL)

class ApiService {
    private baseURL: string
    private token: string | null = null

    constructor(baseURL: string) {
        this.baseURL = baseURL
        this.token = localStorage.getItem('token')
    }

    setToken(token: string) {
        this.token = token
        localStorage.setItem('token', token)
    }

    removeToken() {
        this.token = null
        localStorage.removeItem('token')
    }

    private async request<T>(
        endpoint: string,
        options: RequestInit = {}
    ): Promise<T> {
        const url = `${this.baseURL}${endpoint}`

        const headers: HeadersInit = {
            'Content-Type': 'application/json',
            ...options.headers,
        }

        if (this.token) {
            (headers as Record<string, string>)['Authorization'] = `Bearer ${this.token}`
        }

        const response = await fetch(url, {
            ...options,
            headers,
        })

        if (!response.ok) {
            const error = await response.json().catch(() => ({ message: 'Erro na requisição' }))
            throw new Error(error.message || `HTTP ${response.status}`)
        }

        return response.json()
    }

    async get<T>(endpoint: string): Promise<T> {
        return this.request<T>(endpoint)
    }

    async post<T>(endpoint: string, data?: unknown): Promise<T> {
        return this.request<T>(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
        })
    }

    async put<T>(endpoint: string, data?: unknown): Promise<T> {
        return this.request<T>(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data),
        })
    }

    async delete<T>(endpoint: string): Promise<T> {
        return this.request<T>(endpoint, {
            method: 'DELETE',
        })
    }
}

export const api = new ApiService(API_BASE_URL)