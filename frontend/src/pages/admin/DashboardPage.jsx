import { useCallback, useEffect, useState } from 'react'
import { Activity, ArrowRight, Boxes, Package, Store, Users } from 'lucide-react'
import { Link } from 'react-router-dom'
import { adminApi } from '../../services/adminApi.js'
import { apiErrorMessage } from '../../services/api.js'
import { Card, ErrorState, LoadingState, PageHeader } from '../../components/admin/AdminUi.jsx'

const tiles = [
  { key: 'shops', label: 'Total shops', icon: Store, color: 'bg-indigo-50 text-indigo-600', href: '/admin/shops' },
  { key: 'shop_owners', label: 'Total shop owners', icon: Users, color: 'bg-violet-50 text-violet-600', href: '/admin/users' },
  { key: 'products', label: 'Global products', icon: Package, color: 'bg-amber-50 text-amber-600', href: '/admin/products' },
  { key: 'shop_listings', label: 'Product listings', icon: Boxes, color: 'bg-emerald-50 text-emerald-600', href: '/admin/listings' },
]

export default function DashboardPage() {
  const [data, setData] = useState(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(true)
  const load = useCallback(async () => {
    setLoading(true); setError('')
    try { const response = await adminApi.dashboard(); setData(response.data) }
    catch (requestError) { setError(apiErrorMessage(requestError, 'Unable to load dashboard statistics.')) }
    finally { setLoading(false) }
  }, [])
  useEffect(() => { load() }, [load])

  return <>
    <PageHeader title="Dashboard" description="A live overview of your e-commerce marketplace." />
    {loading ? <LoadingState label="Loading dashboard" /> : error ? <ErrorState message={error} onRetry={load} /> : <>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">{tiles.map(({ key, label, icon: Icon, color, href }) => <Card key={key} className="p-5"><div className="flex items-start justify-between"><span className={`grid size-10 place-items-center rounded-xl ${color}`}><Icon size={19} /></span><Link to={href} className="text-slate-400 hover:text-indigo-600" aria-label={`View ${label}`}><ArrowRight size={17} /></Link></div><p className="mt-5 text-sm font-medium text-slate-500">{label}</p><p className="mt-1 text-3xl font-bold tracking-tight text-slate-950">{data?.[key]?.total ?? 0}</p></Card>)}</div>
      <div className="mt-6 grid gap-5 lg:grid-cols-2">
        <Card className="p-5"><div className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-xl bg-sky-50 text-sky-600"><Activity size={19} /></span><div><h2 className="font-semibold text-slate-900">Marketplace status</h2><p className="text-xs text-slate-500">Active records reported by the API</p></div></div><div className="mt-5 grid grid-cols-2 gap-3"><Stat label="Active shops" value={data?.shops?.active} /><Stat label="Active owners" value={data?.shop_owners?.active} /><Stat label="Active listings" value={data?.shop_listings?.active} /><Stat label="Inactive listings" value={data?.shop_listings?.inactive} /></div></Card>
        <Card className="p-5"><h2 className="font-semibold text-slate-900">Quick actions</h2><p className="mt-1 text-sm text-slate-500">Create and manage marketplace records.</p><div className="mt-5 grid gap-2 sm:grid-cols-2"><QuickLink to="/admin/shops/new" icon={Store} label="Create a shop" /><QuickLink to="/admin/users/new" icon={Users} label="Add shop owner" /><QuickLink to="/admin/products/new" icon={Package} label="Create global product" /><QuickLink to="/admin/listings" icon={Boxes} label="Browse listings" /></div></Card>
      </div>
    </>}
  </>
}
function Stat({ label, value }) { return <div className="rounded-lg bg-slate-50 px-4 py-3"><p className="text-xs text-slate-500">{label}</p><p className="mt-1 text-xl font-bold text-slate-900">{value ?? 0}</p></div> }
function QuickLink({ to, icon: Icon, label }) { return <Link to={to} className="flex items-center gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"><Icon size={17} />{label}<ArrowRight size={15} className="ml-auto" /></Link> }
