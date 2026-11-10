import ProtectedRoute from './ProtectedRoute.jsx'

export default function AdminRoute() {
  return <ProtectedRoute roles={['admin']} />
}
