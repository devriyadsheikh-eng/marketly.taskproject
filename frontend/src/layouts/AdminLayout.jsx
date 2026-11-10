import { BarChart3, LayoutList, Package, Store, Users } from 'lucide-react'
import { Outlet } from 'react-router-dom'
import WorkspaceLayout from './WorkspaceLayout.jsx'

const links = [
  { to: '/admin', label: 'Overview', icon: BarChart3 },
  { to: '/admin/shops', label: 'Shops', icon: Store },
  { to: '/admin/users', label: 'Shop owners', icon: Users },
  { to: '/admin/products', label: 'Global products', icon: Package },
  { to: '/admin/listings', label: 'Product listings', icon: LayoutList },
]

export default function AdminLayout() {
  return <WorkspaceLayout title="Admin workspace" subtitle="Manage your marketplace" links={links}><Outlet /></WorkspaceLayout>
}
