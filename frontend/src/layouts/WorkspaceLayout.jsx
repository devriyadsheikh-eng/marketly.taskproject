import { useState } from 'react'
import { ArrowUpRight, LogOut, Menu, Store, X } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { useAuth } from '../context/AuthContext.jsx'
import { apiErrorMessage } from '../services/api.js'

export default function WorkspaceLayout({ title, subtitle, links, children }) {
  const { user, logout, working } = useAuth()
  const [menuOpen, setMenuOpen] = useState(false)
  const [logoutError, setLogoutError] = useState('')

  async function handleLogout() {
    setLogoutError('')
    try {
      await logout()
    } catch (error) {
      setLogoutError(apiErrorMessage(error, 'Unable to sign out.'))
    }
  }

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900">
      <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div className="mx-auto flex h-16 max-w-[1440px] items-center justify-between px-4 sm:px-6 lg:px-8">
          <div className="flex min-w-0 items-center gap-3">
            <button onClick={() => setMenuOpen((open) => !open)} className="grid size-9 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden" aria-label={menuOpen ? 'Close navigation' : 'Open navigation'}>
              {menuOpen ? <X size={19} /> : <Menu size={19} />}
            </button>
            <NavLink to={links[0].to} className="flex items-center gap-2.5">
              <span className="grid size-9 place-items-center rounded-xl bg-indigo-600 text-white"><Store size={18} /></span>
              <span className="hidden font-bold tracking-tight sm:block">Marketly<span className="text-indigo-600">.</span></span>
            </NavLink>
            <span className="hidden h-6 w-px bg-slate-200 sm:block" />
            <div className="min-w-0"><p className="truncate text-sm font-semibold">{title}</p><p className="hidden truncate text-xs text-slate-500 sm:block">{subtitle}</p></div>
          </div>

          <div className="flex items-center gap-3">
            <div className="hidden text-right sm:block"><p className="text-sm font-semibold">{user?.name}</p><p className="text-xs capitalize text-slate-500">{user?.role?.replace('_', ' ')}</p></div>
            <span className="grid size-9 place-items-center rounded-full bg-indigo-50 text-sm font-bold text-indigo-700" aria-hidden="true">{user?.name?.charAt(0)?.toUpperCase() || 'U'}</span>
            <button onClick={handleLogout} disabled={working} className="hidden items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 disabled:opacity-60 sm:inline-flex">
              <LogOut size={15} /> {working ? 'Signing out…' : 'Sign out'}
            </button>
            <button onClick={handleLogout} disabled={working} className="grid size-9 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 disabled:opacity-60 sm:hidden" aria-label="Sign out"><LogOut size={17} /></button>
          </div>
        </div>
      </header>

      <div className="mx-auto flex max-w-[1440px]">
        <aside className={`${menuOpen ? 'block' : 'hidden'} fixed inset-x-0 top-16 z-20 border-b border-slate-200 bg-white p-4 shadow-lg lg:static lg:block lg:min-h-[calc(100vh-4rem)] lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:bg-white lg:p-5 lg:shadow-none`}>
          <p className="mb-3 px-3 text-[11px] font-bold uppercase tracking-[.16em] text-slate-400">Workspace</p>
          <nav className="space-y-1">
            {links.map(({ to, label, icon: Icon }) => (
              <NavLink key={to} end={to === '/admin' || to === '/shop-owner'} to={to} onClick={() => setMenuOpen(false)} className={({ isActive }) => `flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition ${isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'}`}>
                <Icon size={17} /> {label}
              </NavLink>
            ))}
          </nav>
          <a href="https://laravel.com/docs" target="_blank" rel="noreferrer" className="mt-8 flex items-center gap-2 px-3 text-xs font-medium text-slate-400 hover:text-indigo-600">Help center <ArrowUpRight size={13} /></a>
        </aside>

        <main className="min-w-0 flex-1 px-4 py-6 sm:px-6 sm:py-8 lg:px-9">
          {logoutError && <div role="alert" className="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{logoutError}</div>}
          {children}
        </main>
      </div>
    </div>
  )
}
