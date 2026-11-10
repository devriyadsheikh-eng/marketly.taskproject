import { useState } from 'react'
import { ArrowLeft, ArrowRight, LoaderCircle, LockKeyhole, Mail, ShieldCheck, Store } from 'lucide-react'
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext.jsx'
import AuthLoading from '../../components/AuthLoading.jsx'
import { apiErrorMessage } from '../../services/api.js'

function workspacePath(role) {
  return role === 'admin' ? '/admin' : role === 'shop_owner' ? '/shop-owner' : '/'
}

export default function LoginPage() {
  const { user, loading, working, login } = useAuth()
  const location = useLocation()
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')

  if (loading) return <AuthLoading label="Preparing secure sign in" />
  if (user) return <Navigate to={workspacePath(user.role)} replace />

  async function handleSubmit(event) {
    event.preventDefault()
    setError('')

    try {
      const authenticatedUser = await login({ email, password })
      const requestedPath = location.state?.from?.pathname
      navigate(requestedPath || workspacePath(authenticatedUser.role), { replace: true })
    } catch (requestError) {
      setError(apiErrorMessage(requestError, 'Sign in failed. Check your details and try again.'))
    }
  }

  return (
    <main className="grid min-h-screen bg-white lg:grid-cols-[1fr_.9fr]">
      <section className="relative hidden overflow-hidden bg-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
        <div className="absolute -right-40 top-20 size-[28rem] rounded-full bg-indigo-500/20 blur-3xl" />
        <div className="absolute -bottom-48 -left-28 size-[32rem] rounded-full bg-violet-600/20 blur-3xl" />
        <Link to="/" className="relative z-10 inline-flex items-center gap-3 font-bold tracking-tight">
          <span className="grid size-10 place-items-center rounded-xl bg-indigo-500"><Store size={19} /></span>
          <span className="text-xl">marketly<span className="text-indigo-300">.</span></span>
        </Link>
        <div className="relative z-10 max-w-xl pb-14">
          <div className="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3.5 py-2 text-xs font-semibold text-indigo-100"><ShieldCheck size={15} /> Secure workspace access</div>
          <h1 className="text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">Welcome back to your marketplace.</h1>
          <p className="mt-5 max-w-lg text-base leading-7 text-slate-300">Sign in to manage your shops, products, and the details that make your business yours.</p>
        </div>
        <p className="relative z-10 text-xs text-slate-400">Private access for administrators and shop owners.</p>
      </section>

      <section className="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
        <div className="w-full max-w-md">
          <Link to="/" className="mb-10 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900 lg:hidden"><ArrowLeft size={16} /> Back to home</Link>
          <div className="mb-8 lg:mb-10">
            <span className="grid size-11 place-items-center rounded-xl bg-indigo-50 text-indigo-600"><LockKeyhole size={19} /></span>
            <p className="mt-6 text-xs font-bold uppercase tracking-[.16em] text-indigo-600">Your workspace</p>
            <h2 className="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Sign in to Marketly</h2>
            <p className="mt-3 text-sm leading-6 text-slate-600">Use the account details provided by your administrator.</p>
          </div>

          {error && <div role="alert" className="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-5 text-rose-700">{error}</div>}

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label htmlFor="email" className="mb-2 block text-sm font-semibold text-slate-700">Email address</label>
              <div className="relative">
                <Mail className="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" size={17} />
                <input id="email" name="email" type="email" autoComplete="username" required value={email} onChange={(event) => setEmail(event.target.value)} placeholder="you@company.com" className="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100" />
              </div>
            </div>
            <div>
              <label htmlFor="password" className="mb-2 block text-sm font-semibold text-slate-700">Password</label>
              <input id="password" name="password" type="password" autoComplete="current-password" required value={password} onChange={(event) => setPassword(event.target.value)} placeholder="Enter your password" className="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100" />
            </div>
            <button type="submit" disabled={working} className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/15 transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-70">
              {working ? <><LoaderCircle className="animate-spin" size={17} /> Signing in…</> : <>Sign in <ArrowRight size={17} /></>}
            </button>
          </form>

          <p className="mt-6 text-center text-xs leading-5 text-slate-500">Your account role and access are verified securely by the server.</p>
        </div>
      </section>
    </main>
  )
}
