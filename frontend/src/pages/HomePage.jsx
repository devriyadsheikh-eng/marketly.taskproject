import { useEffect, useState } from 'react'
import { ArrowDownRight, ArrowRight, ArrowUpRight, BarChart3, Check, Compass, Package, Sparkles, Store } from 'lucide-react'
import { Link } from 'react-router-dom'
import { publicApi } from '../services/publicApi.js'

const features = [
  { icon: Store, title: 'Independent storefronts', text: 'Explore distinctive shops and the collections they choose to offer.' },
  { icon: Package, title: 'A shared product catalog', text: 'Discover products that shop owners can tailor for their customers.' },
  { icon: BarChart3, title: 'Simple shop management', text: 'Keep product details and shop listings organized in one place.' },
]

export default function HomePage() {
  const [products, setProducts] = useState([])
  const [productsLoading, setProductsLoading] = useState(true)

  useEffect(() => {
    publicApi.catalogProducts()
      .then((response) => setProducts(response.data || []))
      .catch(() => setProducts([]))
      .finally(() => setProductsLoading(false))
  }, [])

  const featured = products.slice(0, 2)

  return (
    <main className="overflow-hidden bg-[#f7f8fc]">
      <section className="mx-auto max-w-7xl px-4 pb-12 pt-4 sm:px-6 lg:px-10 lg:pb-16">
        <div className="relative isolate overflow-hidden rounded-[2rem] bg-[#11162b] px-6 py-12 text-white shadow-2xl shadow-indigo-950/10 sm:px-10 sm:py-16 lg:px-16 lg:py-20">
          <div className="pointer-events-none absolute -right-32 -top-40 -z-10 size-[32rem] rounded-full bg-indigo-500/25 blur-3xl" />
          <div className="pointer-events-none absolute -bottom-56 left-1/3 -z-10 size-[30rem] rounded-full bg-emerald-400/10 blur-3xl" />
          <div className="grid items-center gap-12 lg:grid-cols-[1.05fr_.95fr] lg:gap-10">
            <div className="max-w-2xl">
              <div className="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[.06] px-3.5 py-2 text-xs font-semibold text-indigo-100">
                <Sparkles size={14} className="text-emerald-300" /> A marketplace for independent shops
              </div>
              <h1 className="text-5xl font-semibold leading-[1.04] tracking-[-.05em] sm:text-6xl lg:text-[4.4rem]">Good finds.<br /><span className="text-emerald-300">Good people.</span></h1>
              <p className="mt-6 max-w-xl text-base leading-7 text-slate-300 sm:text-lg sm:leading-8">Meet independent shops, explore their collections, and find products with a story behind them.</p>
              <div className="mt-8 flex flex-wrap items-center gap-3">
                <Link to="/shops" className="inline-flex items-center gap-2 rounded-xl bg-emerald-300 px-5 py-3.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-950/20 transition hover:-translate-y-0.5 hover:bg-emerald-200">Explore shops <ArrowRight size={17} /></Link>
                <Link to="/shop-owner" className="inline-flex items-center gap-2 rounded-xl border border-white/15 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">For shop owners <ArrowUpRight size={16} /></Link>
              </div>
              <div className="mt-9 flex items-center gap-2.5 text-sm text-slate-300"><span className="grid size-6 place-items-center rounded-full bg-emerald-300/15 text-emerald-300"><Check size={14} /></span>Every shop brings its own point of view.</div>
            </div>

            <div className="relative mx-auto w-full max-w-[30rem] lg:ml-auto">
              <div className="absolute -inset-4 rotate-3 rounded-[2rem] bg-gradient-to-br from-indigo-400/30 to-emerald-300/20 blur-sm" />
              <div className="relative rounded-[1.6rem] border border-white/15 bg-white/[.08] p-4 shadow-2xl backdrop-blur-xl sm:p-5">
                <div className="flex items-center justify-between px-1 pb-4">
                  <div><p className="text-[11px] font-bold uppercase tracking-[.18em] text-emerald-300">The marketplace</p><p className="mt-1 text-sm font-semibold text-white">A little something for everyone</p></div>
                  <span className="grid size-10 place-items-center rounded-xl bg-white/10 text-emerald-200"><Compass size={19} /></span>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  {featured.length ? featured.map((product, index) => (
                    <article key={product.id} className={`overflow-hidden rounded-2xl bg-white text-slate-900 ${index === 0 ? 'translate-y-3' : ''}`}>
                      <div className="relative grid h-36 place-items-center overflow-hidden bg-slate-100 sm:h-44">{product.thumbnail_url ? <img src={product.thumbnail_url} alt={product.name} className="size-full object-cover" /> : <Package size={34} className="text-slate-400" />}<span className="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold text-indigo-700">Shared catalog</span></div>
                      <div className="p-3.5"><p className="truncate text-sm font-bold">{product.name}</p><p className="mt-1 truncate text-xs text-slate-500">{product.description || 'Ready to discover'}</p></div>
                    </article>
                  )) : <>
                    <div className="grid h-52 place-items-center rounded-2xl bg-gradient-to-br from-indigo-200 to-violet-300 text-indigo-800"><Store size={40} /></div>
                    <div className="grid h-52 place-items-center rounded-2xl bg-gradient-to-br from-emerald-100 to-amber-100 text-emerald-800"><Package size={40} /></div>
                  </>}
                </div>
                <Link to="/shops" className="mt-5 flex items-center justify-between rounded-xl border border-white/10 bg-black/10 px-4 py-3 text-sm font-semibold text-white transition hover:bg-black/20"><span>Find your next favorite</span><ArrowDownRight size={17} className="text-emerald-300" /></Link>
              </div>
              <div className="absolute -bottom-6 -left-5 hidden items-center gap-3 rounded-2xl border border-white/60 bg-white p-3.5 text-slate-900 shadow-xl sm:flex"><span className="grid size-10 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><Store size={18} /></span><span><span className="block text-xs font-bold">Shop small</span><span className="text-[11px] text-slate-500">Find something special</span></span></div>
            </div>
          </div>
        </div>
      </section>

      <section className="border-y border-slate-200/70 bg-white py-14 sm:py-18">
        <div className="mx-auto max-w-7xl px-6 lg:px-10">
          <div className="mx-auto max-w-2xl text-center"><p className="text-xs font-bold uppercase tracking-[.2em] text-indigo-600">Made for independent sellers</p><h2 className="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Small shop spirit, all in one place.</h2><p className="mt-4 leading-7 text-slate-600">A welcoming way to discover local businesses and the products they love.</p></div>
          <div className="mt-11 grid gap-4 md:grid-cols-3">{features.map(({ icon: Icon, title, text }, index) => <article key={title} className="group rounded-2xl border border-slate-200/80 bg-[#fbfcff] p-6 transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:bg-white hover:shadow-xl hover:shadow-indigo-950/5"><div className="flex items-center justify-between"><span className={`grid size-12 place-items-center rounded-2xl ${index === 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700'}`}><Icon size={21} /></span><span className="text-xs font-bold tracking-widest text-slate-300">0{index + 1}</span></div><h3 className="mt-6 font-semibold text-slate-900">{title}</h3><p className="mt-2 text-sm leading-6 text-slate-600">{text}</p></article>)}</div>
        </div>
      </section>

      {!productsLoading && products.length > 0 && <section className="py-16 sm:py-20">
        <div className="mx-auto max-w-7xl px-6 lg:px-10">
          <div className="flex flex-wrap items-end justify-between gap-5"><div><p className="text-xs font-bold uppercase tracking-[.2em] text-indigo-600">A first look</p><h2 className="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">From the shared catalog</h2><p className="mt-3 max-w-2xl leading-7 text-slate-600">A few ideas from the collection shop owners can make their own.</p></div><Link to="/shops" className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:text-indigo-700">Explore shops <ArrowRight size={16} /></Link></div>
          <div className="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">{products.map((product) => <article key={product.id} className="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-900/10"><div className="relative grid h-56 place-items-center overflow-hidden bg-slate-100">{product.thumbnail_url ? <img src={product.thumbnail_url} alt={product.name} loading="lazy" className="size-full object-cover transition duration-500 group-hover:scale-105" /> : <Package size={38} className="text-slate-400" />}<span className="absolute left-3 top-3 rounded-full border border-white/70 bg-white/90 px-3 py-1.5 text-[11px] font-bold text-indigo-700 shadow-sm">Global product</span></div><div className="p-4"><h3 className="truncate font-semibold text-slate-900">{product.name}</h3><p className="mt-1 line-clamp-2 min-h-10 text-sm leading-5 text-slate-500">{product.description || 'Available in the shared catalog.'}</p></div></article>)}</div>
        </div>
      </section>}
    </main>
  )
}
