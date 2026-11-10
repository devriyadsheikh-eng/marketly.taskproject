import { BarChart3, Package, Store, UserRound } from 'lucide-react'
import { Outlet } from 'react-router-dom'
import WorkspaceLayout from './WorkspaceLayout.jsx'

const links = [
  { to: '/shop-owner', label: 'Overview', icon: BarChart3 },
  { to: '/shop-owner/shops', label: 'My shops', icon: Store },
  { to: '/shop-owner/my-products', label: 'My products', icon: UserRound },
  { to: '/shop-owner/catalog', label: 'Global catalog', icon: Package },
]

export default function ShopOwnerLayout() {
  return <WorkspaceLayout title="Shop owner workspace" subtitle="Manage your storefronts" links={links}><Outlet /></WorkspaceLayout>
}
