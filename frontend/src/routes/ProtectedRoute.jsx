import { Navigate, Outlet, useLocation } from 'react-router-dom'
import { useAuth } from '../context/AuthContext.jsx'
import AuthErrorState from '../components/AuthErrorState.jsx'
import AuthLoading from '../components/AuthLoading.jsx'

export default function ProtectedRoute({ roles }) {
  const { user, loading, error, refreshUser } = useAuth()
  const location = useLocation()

  if (loading) return <AuthLoading />
  if (error) return <AuthErrorState message={error} onRetry={() => refreshUser().catch(() => {})} />
  if (!user) return <Navigate to="/login" replace state={{ from: location }} />

  if (roles && !roles.includes(user.role)) {
    return <Navigate to={user.role === 'admin' ? '/admin' : user.role === 'shop_owner' ? '/shop-owner' : '/'} replace />
  }

  return <Outlet />
}
