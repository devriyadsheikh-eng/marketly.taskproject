import AppRoutes from './routes/AppRoutes.jsx'
import { AuthProvider } from './context/AuthContext.jsx'
import { ToastProvider } from './context/ToastContext.jsx'

export default function App() {
  return (
    <AuthProvider><ToastProvider><AppRoutes /></ToastProvider></AuthProvider>
  )
}
