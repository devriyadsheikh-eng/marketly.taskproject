import { ArrowRight, LogOut, ShoppingBag, Store } from 'lucide-react'
import { Link, Outlet } from 'react-router-dom'
import { useState } from 'react'
import { useAuth } from '../context/AuthContext.jsx'
import { apiErrorMessage } from '../services/api.js'

export default function CustomerLayout() {
  const { user, logout, working } = useAuth()
  const [logoutError, setLogoutError] = useState('')

  async function handleLogout() {
    setLogoutError('')
    try {
      await logout()
    } catch (error) {
      setLogoutError(apiErrorMessage(error, 'Unable to sign out. Please retry.'))
    }
  }

  return (
    <div className="min-h-screen bg-[#f7f8fc]">
      <header className="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-6 lg:px-10">
        <Link to="/" className="flex items-center gap-2.5 font-bold tracking-tight text-slate-900" aria-label="Marketly home">
          <span className="grid size-9 place-items-center rounded-xl bg-indigo-600 text-white"><ShoppingBag size={19} /></span>
          <span className="text-xl">marketly<span className="text-indigo-600">.</span></span>
        </Link>
        <nav className="flex items-center gap-2 sm:gap-4">
          <Link to="/shops" className="hidden items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-white sm:inline-flex"><Store size={16} /> Discover shops</Link>
          {user ? (
            <button onClick={handleLogout} disabled={working} className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white disabled:opacity-60">
              <span className="hidden sm:inline">{working ? 'Signing out…' : 'Sign out'}</span><LogOut className="sm:hidden" size={17} />
            </button>
          ) : (
            <Link to="/login" className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white">Sign in</Link>
          )}
          <Link to="/shop-owner" className="inline-flex items-center gap-1 rounded-lg bg-slate-900 px-3.5 py-2.5 text-xs font-semibold text-white transition hover:bg-indigo-700 sm:px-4 sm:text-sm">For shop owners <ArrowRight size={14} /></Link>
        </nav>
      </header>
      {logoutError && <div role="alert" className="mx-auto max-w-7xl px-6 pt-3 text-sm text-rose-700 lg:px-10">{logoutError}</div>}
      <Outlet />
      <footer className="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-7 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-10">
        <Link to="/" className="font-bold tracking-tight text-slate-700">marketly<span className="text-indigo-600">.</span></Link>
        <p>A simpler way to help small shops thrive.</p>
        <p>© {new Date().getFullYear()} Marketly</p>
      </footer>
    </div>
  )
}
