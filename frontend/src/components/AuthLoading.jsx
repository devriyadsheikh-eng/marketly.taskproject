import { LoaderCircle } from 'lucide-react'

export default function AuthLoading({ label = 'Checking your session' }) {
  return (
    <main className="grid min-h-[55vh] place-items-center px-6">
      <div className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 text-sm font-medium text-slate-600 shadow-sm" role="status">
        <LoaderCircle className="animate-spin text-indigo-600" size={19} /> {label}
      </div>
    </main>
  )
}
