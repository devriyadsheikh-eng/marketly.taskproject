import { AlertCircle, RotateCw } from 'lucide-react'

export default function AuthErrorState({ message, onRetry }) {
  return (
    <main className="grid min-h-[55vh] place-items-center px-6">
      <section className="max-w-md rounded-2xl border border-rose-100 bg-white p-7 text-center shadow-sm">
        <AlertCircle className="mx-auto text-rose-500" size={28} />
        <h1 className="mt-4 text-lg font-semibold text-slate-900">We couldn’t verify your session</h1>
        <p className="mt-2 text-sm leading-6 text-slate-600">{message}</p>
        <button onClick={onRetry} className="mt-5 inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          <RotateCw size={15} /> Try again
        </button>
      </section>
    </main>
  )
}
