import { createContext, useCallback, useContext, useMemo, useState } from 'react'
import { CheckCircle2, X, XCircle } from 'lucide-react'

const ToastContext = createContext(null)
export function ToastProvider({ children }) {
  const [toast, setToast] = useState(null)
  const notify = useCallback((message, type = 'success') => {
    setToast({ message, type, id: Date.now() })
    window.setTimeout(() => setToast(null), 4200)
  }, [])
  const value = useMemo(() => ({ notify }), [notify])
  return <ToastContext.Provider value={value}>{children}{toast && <div role="status" className={`fixed right-4 top-20 z-[70] flex max-w-sm items-start gap-3 rounded-xl border bg-white px-4 py-3 shadow-xl ${toast.type === 'error' ? 'border-rose-200' : 'border-emerald-200'}`}><span className={toast.type === 'error' ? 'text-rose-600' : 'text-emerald-600'}>{toast.type === 'error' ? <XCircle size={19} /> : <CheckCircle2 size={19} />}</span><p className="flex-1 text-sm font-medium text-slate-800">{toast.message}</p><button onClick={() => setToast(null)} aria-label="Dismiss notification" className="text-slate-400 hover:text-slate-700"><X size={17} /></button></div>}</ToastContext.Provider>
}
export function useToast() { const context = useContext(ToastContext); if (!context) throw new Error('useToast must be used inside ToastProvider'); return context }
