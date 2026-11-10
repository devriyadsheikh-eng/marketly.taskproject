import { ArrowLeft, SearchX } from 'lucide-react'
import { Link } from 'react-router-dom'

export default function NotFoundPage() {
  return (
    <main className="grid min-h-screen place-items-center bg-slate-50 px-6">
      <section className="max-w-md text-center">
        <span className="mx-auto grid size-14 place-items-center rounded-2xl bg-indigo-50 text-indigo-600"><SearchX size={25} /></span>
        <p className="mt-6 text-xs font-bold uppercase tracking-[.18em] text-indigo-600">404 · Page not found</p>
        <h1 className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">We couldn’t find that page.</h1>
        <p className="mt-3 text-sm leading-6 text-slate-600">The link may be old, or the page may have moved.</p>
        <Link to="/" className="mt-7 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"><ArrowLeft size={16} /> Back to Marketly</Link>
      </section>
    </main>
  )
}
