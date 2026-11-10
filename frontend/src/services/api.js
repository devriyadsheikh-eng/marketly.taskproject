import axios from 'axios'

const configuredBaseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
const fallbackOrigin = typeof window === 'undefined' ? 'http://localhost' : window.location.origin
export const apiOrigin = new URL(configuredBaseURL, fallbackOrigin).origin

const sharedConfig = {
  headers: { Accept: 'application/json' },
  withCredentials: true,
  withXSRFToken: true,
}

const api = axios.create({
  ...sharedConfig,
  baseURL: configuredBaseURL,
  // Let Axios set the correct content type, including the boundary for FormData uploads.
  headers: { ...sharedConfig.headers },
})

export async function initializeCsrf() {
  return axios.get(`${apiOrigin}/sanctum/csrf-cookie`, sharedConfig)
}

export function apiErrorMessage(error, fallback = 'Something went wrong. Please try again.') {
  const response = error?.response
  const validationMessages = response?.data?.errors
    ? Object.values(response.data.errors).flat().filter(Boolean)
    : []

  return validationMessages[0] || response?.data?.message || error?.message || fallback
}

export default api
