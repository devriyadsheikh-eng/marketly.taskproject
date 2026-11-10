import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import api, { apiErrorMessage, initializeCsrf } from '../services/api.js'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)
  const [working, setWorking] = useState(false)
  const [error, setError] = useState('')

  const refreshUser = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const response = await api.get('/user')
      setUser(response.data.user)
      return response.data.user
    } catch (requestError) {
      if (requestError.response?.status === 401) {
        setUser(null)
        return null
      }

      setUser(null)
      setError(apiErrorMessage(requestError, 'Unable to verify your session.'))
      throw requestError
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    let mounted = true

    api.get('/user')
      .then((response) => {
        if (mounted) setUser(response.data.user)
      })
      .catch((requestError) => {
        if (!mounted) return
        setUser(null)
        if (requestError.response?.status !== 401) {
          setError(apiErrorMessage(requestError, 'Unable to verify your session.'))
        }
      })
      .finally(() => {
        if (mounted) setLoading(false)
      })

    return () => { mounted = false }
  }, [])

  const login = useCallback(async (credentials) => {
    setWorking(true)
    setError('')
    try {
      await initializeCsrf()
      const response = await api.post('/login', credentials)

      // Browser sessions are the configured Sanctum strategy; bearer tokens are not persisted.
      if (response.data.token) {
        throw new Error('The API returned a bearer token instead of a browser session. Check Sanctum stateful domains and CORS configuration.')
      }

      const authenticatedResponse = await api.get('/user')
      setUser(authenticatedResponse.data.user)
      return authenticatedResponse.data.user
    } catch (requestError) {
      setUser(null)
      setError(apiErrorMessage(requestError, 'Unable to sign in.'))
      throw requestError
    } finally {
      setWorking(false)
    }
  }, [])

  const logout = useCallback(async () => {
    setWorking(true)
    setError('')
    try {
      await api.post('/logout')
      setUser(null)
    } catch (requestError) {
      setError(apiErrorMessage(requestError, 'Unable to sign out. Please retry.'))
      throw requestError
    } finally {
      setWorking(false)
    }
  }, [])

  const value = useMemo(() => ({
    user,
    loading,
    working,
    error,
    login,
    logout,
    refreshUser,
    clearError: () => setError(''),
  }), [user, loading, working, error, login, logout, refreshUser])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth must be used within AuthProvider')
  return context
}
