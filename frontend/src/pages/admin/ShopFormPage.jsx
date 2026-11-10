import { useCallback, useEffect, useMemo, useState } from 'react'
import { ArrowLeft, ImagePlus, Save, Store, Trash2 } from 'lucide-react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { adminApi } from '../../services/adminApi.js'
import { apiErrorMessage } from '../../services/api.js'
import { useToast } from '../../context/ToastContext.jsx'
import { Button, Card, ErrorState, Field, LoadingState, PageHeader, SelectField, TextAreaField } from '../../components/admin/AdminUi.jsx'

export default function ShopFormPage() {
  const { id } = useParams(); const editing = Boolean(id); const navigate = useNavigate(); const { notify } = useToast()
  const [owners, setOwners] = useState([]); const [form, setForm] = useState({ owner_id: '', name: '', slug: '', description: '', status: 'active' })
  const [currentLogo, setCurrentLogo] = useState(''); const [file, setFile] = useState(null); const [removeLogo, setRemoveLogo] = useState(false)
  const [loading, setLoading] = useState(editing); const [error, setError] = useState(''); const [saving, setSaving] = useState(false)
  const preview = useMemo(() => file ? URL.createObjectURL(file) : '', [file])
  useEffect(() => () => { if (preview) URL.revokeObjectURL(preview) }, [preview])
  const load = useCallback(async () => {
    setLoading(true); setError('')
    try { const [shopResponse, ownerResponse] = await Promise.all([editing ? adminApi.shop(id) : Promise.resolve(null), adminApi.owners({ per_page: 100 })]); setOwners(ownerResponse.data || []); if (shopResponse) { const shop = shopResponse.data; setForm({ owner_id: String(shop.owner_id), name: shop.name, slug: shop.slug, description: shop.description || '', status: shop.status }); setCurrentLogo(shop.logo_url || '') } }
    catch (e) { setError(apiErrorMessage(e, 'Unable to load shop form data.')) } finally { setLoading(false) }
  }, [editing, id])
  useEffect(() => { load() }, [load])
  function change(event) { const { name, value } = event.target; setForm((prev) => ({ ...prev, [name]: value })) }
  function chooseFile(event) { const selected = event.target.files?.[0]; if (!selected) return; setFile(selected); setRemoveLogo(false) }
  async function submit(event) {
    event.preventDefault(); setError('')
    if (!form.owner_id || !form.name.trim() || !form.slug.trim()) { setError('Owner, shop name, and URL slug are required.'); return }
    setSaving(true)
    try {
      let response
      if (file || removeLogo) { const body = new FormData(); Object.entries(form).forEach(([key, value]) => body.append(key, value)); if (file) body.append('logo', file); if (removeLogo) body.append('remove_logo', '1'); if (editing) body.append('_method', 'PUT'); response = editing ? await adminApi.updateShop(id, body, true) : await adminApi.createShop(body) }
      else response = editing ? await adminApi.updateShop(id, form) : await adminApi.createShop(form)
      notify(response.message || `Shop ${editing ? 'updated' : 'created'} successfully.`); navigate(`/admin/shops/${response.data.id}`)
    } catch (e) { setError(apiErrorMessage(e, 'Unable to save shop details.')) }
    finally { setSaving(false) }
  }
  if (loading) return <LoadingState label="Loading shop details" />
  if (error && editing && !form.name) return <ErrorState message={error} onRetry={load} />
  return <>
    <PageHeader title={editing ? 'Edit shop' : 'Create shop'} description={editing ? 'Update the shop profile and owner assignment.' : 'Add a shop to the marketplace.'} action={<Link to="/admin/shops" className="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-indigo-700"><ArrowLeft size={16} /> Back to shops</Link>} />
    <Card className="max-w-3xl p-5 sm:p-7">
      {error && <div role="alert" className="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{error}</div>}
      <form onSubmit={submit} className="space-y-5">
        <div className="grid gap-5 sm:grid-cols-2"><Field label="Shop name" name="name" value={form.name} onChange={change} required maxLength={255} placeholder="Sunday Supply" /><Field label="URL slug" name="slug" value={form.slug} onChange={change} required maxLength={255} pattern="[A-Za-z0-9_-]+" placeholder="sunday-supply" /><SelectField label="Shop owner" name="owner_id" value={form.owner_id} onChange={change} required><option value="">Select an owner</option>{owners.map((owner) => <option key={owner.id} value={owner.id}>{owner.name} — {owner.email}</option>)}</SelectField><SelectField label="Status" name="status" value={form.status} onChange={change}><option value="active">Active</option><option value="inactive">Inactive</option></SelectField></div>
        <TextAreaField label="Description" name="description" value={form.description} onChange={change} rows={4} maxLength={10000} placeholder="A short introduction to this shop" />
        <div><p className="mb-2 text-sm font-semibold text-slate-700">Shop logo <span className="font-normal text-slate-400"></span></p><div className="flex flex-wrap items-center gap-4"><div className="grid size-24 place-items-center overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 text-slate-400">{preview || currentLogo ? <img src={preview || currentLogo} alt="Shop logo preview" className="size-full object-cover" /> : <Store size={28} />}</div><div className="space-y-2"><label className="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"><ImagePlus size={16} />Choose image<input type="file" className="sr-only" onChange={chooseFile} /></label>{(currentLogo || file) && <button type="button" onClick={() => { setFile(null); setRemoveLogo(true); setCurrentLogo('') }} className="flex items-center gap-1 text-xs font-semibold text-rose-600"><Trash2 size={13} />Remove logo</button>}</div></div></div>
        <div className="flex justify-end gap-2 border-t border-slate-100 pt-5"><Button type="button" variant="secondary" onClick={() => navigate('/admin/shops')}>Cancel</Button><Button type="submit" disabled={saving}><Save size={16} />{saving ? 'Saving…' : editing ? 'Save changes' : 'Create shop'}</Button></div>
      </form>
    </Card>
  </>
}
