import { useCallback, useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Edit2, Eye, Plus, Search, Trash2, Store, Users, Package, Boxes } from 'lucide-react'
import { adminApi } from '../../services/adminApi.js'
import { apiErrorMessage } from '../../services/api.js'
import { useToast } from '../../context/ToastContext.jsx'
import { Button, Card, ConfirmDialog, EmptyState, ErrorState, LoadingState, PageHeader, Pagination, StatusBadge } from '../../components/admin/AdminUi.jsx'

const configs = {
  shops: { title: 'Shop management', description: 'Create and maintain marketplace shops.', endpoint: adminApi.shops, create: '/admin/shops/new', icon: Store, columns: ['Shop', 'Owner', 'Listings', 'Status', 'Actions'], empty: 'No shops yet', emptyText: 'Create the first shop to begin building your marketplace.', render: (item) => [<div key="shop" className="flex items-center gap-3">{item.logo_url ? <img src={item.logo_url} alt="" className="size-10 rounded-lg object-cover" /> : <span className="grid size-10 place-items-center rounded-lg bg-indigo-50 text-indigo-600"><Store size={17} /></span>}<div><p className="font-semibold text-slate-900">{item.name}</p><p className="text-xs text-slate-500">/{item.slug}</p></div></div>, <div key="owner"><p className="font-medium text-slate-800">{item.owner?.name || '—'}</p><p className="text-xs text-slate-500">{item.owner?.email || ''}</p></div>, item.listings_count ?? 0, <StatusBadge key="status" active={item.status === 'active'} />] },
  users: { title: 'Shop owners', description: 'Manage shop-owner accounts and access.', endpoint: adminApi.owners, create: '/admin/users/new', icon: Users, columns: ['Owner', 'Email', 'Shops', 'Account', 'Actions'], empty: 'No shop owners found', emptyText: 'Add a shop owner to assign them to shops.', render: (item) => [<div key="name" className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-full bg-violet-50 font-bold text-violet-700">{item.name?.[0]?.toUpperCase()}</span><span className="font-semibold text-slate-900">{item.name}</span></div>, item.email, item.shops_count ?? '—', <StatusBadge key="status" active={item.is_active} />] },
  products: { title: 'Global products', description: 'Manage products in the shared catalog.', endpoint: adminApi.products, create: '/admin/products/new', icon: Package, columns: ['Product', 'Creator', 'Listings', 'Actions'], empty: 'No global products found', emptyText: 'Create products in the shared catalog for shop owners to list.', render: (item) => [<div key="product" className="flex items-center gap-3">{item.thumbnail_url ? <img src={item.thumbnail_url} alt="" className="size-10 rounded-lg object-cover" /> : <span className="grid size-10 place-items-center rounded-lg bg-amber-50 text-amber-600"><Package size={17} /></span>}<div><p className="font-semibold text-slate-900">{item.name}</p><p className="text-xs text-slate-500">{item.slug}</p></div></div>, <div key="creator"><p className="font-medium text-slate-800">{item.creator?.name || '—'}</p><p className="text-xs text-slate-500">{item.creator?.email || ''}</p></div>, item.listings_count ?? 0] },
  listings: { title: 'Product listings', description: 'Review shop-specific product assignments and availability.', endpoint: adminApi.listings, create: null, icon: Boxes, columns: ['Listing', 'Shop', 'Price', 'Status', 'Actions'], empty: 'No product listings found', emptyText: 'Shop-specific product assignments will appear here.', render: (item) => [<div key="listing" className="flex items-center gap-3">{item.thumbnail_url ? <img src={item.thumbnail_url} alt="" className="size-10 rounded-lg object-cover" /> : <span className="grid size-10 place-items-center rounded-lg bg-slate-100"><Package size={17} /></span>}<div><p className="font-semibold text-slate-900">{item.name || item.product?.name}</p><p className="text-xs text-slate-500">{item.product?.slug}</p></div></div>, item.shop?.name || '—', `$${Number(item.price || 0).toFixed(2)}`, <StatusBadge key="status" active={item.status === 'active'} />] },
}

export default function EntityListPage({ type }) {
  const config = configs[type]
  const Icon = config.icon
  const navigate = useNavigate()
  const { notify } = useToast()
  const [items, setItems] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [query, setQuery] = useState('')
  const [searchText, setSearchText] = useState('')
  const [page, setPage] = useState(1)
  const [record, setRecord] = useState(null)
  const [busy, setBusy] = useState(false)
  const [deleteListings, setDeleteListings] = useState(false)

  const load = useCallback(async (p = page, q = query) => {
    setLoading(true); setError('')
    try {
      const response = await config.endpoint({ page: p, per_page: 10, q: q || undefined })
      setItems(response.data || [])
      setMeta(response.meta || null)
    } catch (requestError) { setError(apiErrorMessage(requestError, `Unable to load ${config.title.toLowerCase()}.`)) }
    finally { setLoading(false) }
  }, [config, page, query])
  useEffect(() => { load(page, query) }, [load, page, query])

  async function submitSearch(event) { event.preventDefault(); setPage(1); setQuery(searchText.trim()) }
  function editPath(item) { return `/admin/${type}/${item.id}/edit` }
  function detailPath(item) { return `/admin/shops/${item.id}` }

  async function deleteRecord() {
    setBusy(true)
    try {
      if (type === 'shops') await adminApi.deleteShop(record.id, deleteListings)
      else if (type === 'users') await adminApi.deleteOwner(record.id)
      else await adminApi.deleteProduct(record.id)
      notify(`${type === 'users' ? 'Shop owner' : type.slice(0, -1)} deleted successfully.`)
      setRecord(null); setDeleteListings(false); await load(page, query)
    } catch (requestError) {
      if (type === 'shops' && requestError.response?.status === 409 && requestError.response?.data?.code === 'SHOP_HAS_LISTINGS' && !deleteListings) {
        setDeleteListings(true)
        notify('This shop has listings. Confirm again to delete the shop and all its listings.', 'error')
      } else notify(apiErrorMessage(requestError, `Unable to delete this ${type === 'users' ? 'owner' : type.slice(0, -1)}.`), 'error')
    } finally { setBusy(false) }
  }

  async function toggleOwner(item) {
    try { await adminApi.updateOwner(item.id, { is_active: !item.is_active }); notify(`Account ${item.is_active ? 'deactivated' : 'activated'}.`); await load(page, query) }
    catch (requestError) { notify(apiErrorMessage(requestError, 'Unable to update account status.'), 'error') }
  }
  async function toggleShop(item) {
    try { await apiShopStatus(item.id, item.status === 'active' ? 'inactive' : 'active'); notify(`Shop ${item.status === 'active' ? 'deactivated' : 'activated'}.`); await load(page, query) }
    catch (requestError) { notify(apiErrorMessage(requestError, 'Unable to update shop status.'), 'error') }
  }

  return <>
    <PageHeader title={config.title} description={config.description} action={config.create && <Button onClick={() => navigate(config.create)}><Plus size={16} />{type === 'shops' ? 'Create shop' : type === 'users' ? 'Add shop owner' : 'Create product'}</Button>} />
    <Card>
      <form onSubmit={submitSearch} className="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row"><div className="relative flex-1"><Search size={17} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" /><input value={searchText} onChange={(event) => setSearchText(event.target.value)} placeholder={`Search ${config.title.toLowerCase()}…`} className="w-full rounded-lg border border-slate-200 py-2.5 pl-9 pr-3 text-sm outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100" /></div><Button type="submit" variant="secondary">Search</Button></form>
      {loading ? <LoadingState /> : error ? <div className="p-5"><ErrorState message={error} onRetry={() => load(page, query)} /></div> : items.length === 0 ? <EmptyState title={config.empty} message={query ? 'Try a different search term.' : config.emptyText} /> : <>
        <div className="overflow-x-auto"><table className="w-full min-w-[760px] text-left text-sm"><thead className="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr>{config.columns.map((column) => <th key={column} className="px-5 py-3 font-semibold">{column}</th>)}</tr></thead><tbody className="divide-y divide-slate-100">{items.map((item) => { const cells = config.render(item); return <tr key={item.id} className="hover:bg-slate-50/70">{cells.map((cell, index) => <td key={index} className="px-5 py-4 text-slate-600">{cell}</td>)}<td className="px-5 py-4"><div className="flex items-center justify-end gap-1">{type === 'shops' && <IconButton title="View details" onClick={() => navigate(detailPath(item))}><Eye size={16} /></IconButton>}{type !== 'listings' && <IconButton title="Edit" onClick={() => navigate(editPath(item))}><Edit2 size={16} /></IconButton>}{type === 'shops' && <button onClick={() => toggleShop(item)} className="rounded-md px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">{item.status === 'active' ? 'Deactivate' : 'Activate'}</button>}{type === 'users' && <button onClick={() => toggleOwner(item)} className="rounded-md px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">{item.is_active ? 'Deactivate' : 'Activate'}</button>}{type !== 'listings' && <IconButton title="Delete" onClick={() => { setRecord(item); setDeleteListings(false) }}><Trash2 size={16} className="text-rose-600" /></IconButton>}</div></td></tr> })}</tbody></table></div>
        <Pagination meta={meta} onPage={setPage} />
      </>}
    </Card>
    <ConfirmDialog open={Boolean(record)} title={deleteListings ? 'Delete shop and its listings?' : `Delete ${type === 'users' ? 'shop owner' : type.slice(0, -1)}?`} message={deleteListings ? 'This shop contains product listings. Deleting it will permanently remove the shop and every shop-specific listing. Global products will remain.' : type === 'users' ? 'This account can only be deleted if it has no shops or products. This action cannot be undone.' : type === 'products' ? 'This global product can only be deleted when no shops list it.' : 'This action cannot be undone.'} confirmLabel={deleteListings ? 'Delete shop and listings' : undefined} busy={busy} onCancel={() => { if (!busy) { setRecord(null); setDeleteListings(false) } }} onConfirm={deleteRecord} />
  </>
}

function IconButton({ children, title, onClick }) { return <button type="button" title={title} aria-label={title} onClick={onClick} className="grid size-8 place-items-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900">{children}</button> }
async function apiShopStatus(id, status) { return adminApi.updateShop(id, { status }) }
