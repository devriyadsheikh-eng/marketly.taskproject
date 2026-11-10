import ProtectedRoute from './ProtectedRoute.jsx'

export default function ShopOwnerRoute() {
  return <ProtectedRoute roles={['shop_owner']} />
}
