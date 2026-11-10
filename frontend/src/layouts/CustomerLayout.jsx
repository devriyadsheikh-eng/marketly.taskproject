
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
      setLogoutError(
        apiErrorMessage(error, 'Unable to sign out. Please retry.')
      )
    }
  }

  return (
    <div className="flex min-h-screen w-full flex-col bg-[#f7f8fc]">

      {/* Responsive Header */}
      <header className="sticky top-0 z-50 w-full border-b border-slate-200/70 bg-white">
        <div className="mx-auto flex min-h-[68px] max-w-7xl items-center justify-between gap-2 px-3 sm:min-h-[76px] sm:gap-4 sm:px-6 lg:px-10">

          {/* Logo */}
          <Link
            to="/"
            aria-label="Marketly home"
            className="flex min-w-0 shrink-0 items-center gap-2 sm:gap-2.5"
          >
            <span className="grid size-9 shrink-0 place-items-center rounded-xl bg-indigo-600 text-white shadow-sm sm:size-10">
              <ShoppingBag size={19} />
            </span>

            <span className="text-lg font-bold tracking-tight text-slate-900 sm:text-xl">
              marketly<span className="text-indigo-600">.</span>
            </span>
          </Link>

          {/* Navigation */}
          <nav className="flex shrink-0 items-center gap-1 sm:gap-3">

            {/* Discover shops: Tablet and Desktop */}
            <Link
              to="/shops"
              className="hidden items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-indigo-700 sm:inline-flex"
            >
              <Store size={16} />
              Discover shops
            </Link>

            {/* Sign in / Sign out */}
            {user ? (
              <button
                type="button"
                onClick={handleLogout}
                disabled={working}
                className="inline-flex shrink-0 items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:text-rose-600 disabled:opacity-60 sm:px-3 sm:text-sm"
              >
                <span className="whitespace-nowrap">
                  {working ? 'Signing out…' : 'Sign out'}
                </span>
                <LogOut size={16} />
              </button>
            ) : (
              <Link
                to="/login"
                className="shrink-0 whitespace-nowrap rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 sm:px-3 sm:text-sm"
              >
                Sign in
              </Link>
            )}

            {/* Shop Owner */}
            <Link
              to="/shop-owner"
              className="inline-flex shrink-0 items-center justify-center gap-1 rounded-lg bg-slate-900 px-2.5 py-2.5 text-[11px] font-semibold text-white transition hover:bg-indigo-700 sm:gap-2 sm:px-4 sm:text-sm"
            >
              <span className="sm:hidden">For owners</span>
              <span className="hidden sm:inline">For shop owners</span>
              <ArrowRight size={14} />
            </Link>

          </nav>
        </div>
      </header>

      {/* Logout Error */}
      {logoutError && (
        <div className="w-full px-4 pt-3 sm:px-6 lg:px-10">
          <div
            role="alert"
            className="mx-auto max-w-7xl rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"
          >
            {logoutError}
          </div>
        </div>
      )}

      {/* Page Content */}
      <main className="w-full min-w-0 flex-1">
        <Outlet />
      </main>

      {/* Footer */}
      <footer className="mt-auto w-full border-t border-slate-200/70 bg-white">
        <div className="mx-auto flex max-w-7xl flex-col items-center gap-3 px-4 py-6 text-center sm:flex-row sm:justify-between sm:gap-4 sm:px-6 sm:py-7 sm:text-left lg:px-10">

          <Link
            to="/"
            className="font-bold tracking-tight text-slate-700"
          >
            marketly<span className="text-indigo-600">.</span>
          </Link>

          <p className="text-xs leading-5 text-slate-500">
            A simpler way to help small shops thrive.
          </p>

          <p className="text-xs text-slate-500">
            © {new Date().getFullYear()} Marketly
          </p>

        </div>
      </footer>

    </div>
  )
}
