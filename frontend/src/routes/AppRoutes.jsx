import { Route, Routes } from 'react-router-dom'
import AdminLayout from '../layouts/AdminLayout.jsx'
import CustomerLayout from '../layouts/CustomerLayout.jsx'
import ShopOwnerLayout from '../layouts/ShopOwnerLayout.jsx'
import HomePage from '../pages/HomePage.jsx'
import LoginPage from '../pages/auth/LoginPage.jsx'
import NotFoundPage from '../pages/NotFoundPage.jsx'
import DashboardPage from '../pages/admin/DashboardPage.jsx'
import EntityListPage from '../pages/admin/EntityListPages.jsx'
import ShopFormPage from '../pages/admin/ShopFormPage.jsx'
import ShopDetailsPage from '../pages/admin/ShopDetailsPage.jsx'
import OwnerFormPage from '../pages/admin/OwnerFormPage.jsx'
import ProductFormPage from '../pages/admin/ProductFormPage.jsx'
import OwnerDashboardPage from '../pages/shop-owner/OwnerDashboardPage.jsx'
import MyShopsPage from '../pages/shop-owner/MyShopsPage.jsx'
import CatalogPage from '../pages/shop-owner/CatalogPage.jsx'
import GlobalProductFormPage from '../pages/shop-owner/GlobalProductFormPage.jsx'
import ProductDetailsPage from '../pages/shop-owner/ProductDetailsPage.jsx'
import AddCatalogProductPage from '../pages/shop-owner/AddCatalogProductPage.jsx'
import ShopListingsPage from '../pages/shop-owner/ShopListingsPage.jsx'
import EditListingPage from '../pages/shop-owner/EditListingPage.jsx'
import ShopListingPage from '../pages/customer/ShopListingPage.jsx'
import IndividualShopPage from '../pages/customer/IndividualShopPage.jsx'
import ShopProductListingPage from '../pages/customer/ShopProductListingPage.jsx'
import CustomerProductDetailsPage from '../pages/customer/ProductDetailsPage.jsx'
import AdminRoute from './AdminRoute.jsx'
import ShopOwnerRoute from './ShopOwnerRoute.jsx'

export default function AppRoutes() {
  return (
    <Routes>
      <Route element={<CustomerLayout />}>
        <Route index element={<HomePage />} />
        <Route path="shops" element={<ShopListingPage />} />
        <Route path="shop/:slug" element={<IndividualShopPage />} />
        <Route path="shop/:slug/products" element={<ShopProductListingPage />} />
        <Route path="shop/:slug/products/:productId" element={<CustomerProductDetailsPage />} />
      </Route>

      <Route path="/login" element={<LoginPage />} />

      <Route element={<AdminRoute />}>
        <Route path="/admin" element={<AdminLayout />}>
          <Route index element={<DashboardPage />} />
          <Route path="shops" element={<EntityListPage type="shops" />} />
          <Route path="shops/new" element={<ShopFormPage />} />
          <Route path="shops/:id" element={<ShopDetailsPage />} />
          <Route path="shops/:id/edit" element={<ShopFormPage />} />
          <Route path="users" element={<EntityListPage type="users" />} />
          <Route path="users/new" element={<OwnerFormPage />} />
          <Route path="users/:id/edit" element={<OwnerFormPage />} />
          <Route path="products" element={<EntityListPage type="products" />} />
          <Route path="products/new" element={<ProductFormPage />} />
          <Route path="products/:id/edit" element={<ProductFormPage />} />
          <Route path="listings" element={<EntityListPage type="listings" />} />
        </Route>
      </Route>

      <Route element={<ShopOwnerRoute />}>
        <Route path="/shop-owner" element={<ShopOwnerLayout />}>
          <Route index element={<OwnerDashboardPage />} />
          <Route path="shops" element={<MyShopsPage />} />
          <Route path="shops/:shopId/products" element={<ShopListingsPage />} />
          <Route path="shops/:shopId/products/:listingId/edit" element={<EditListingPage />} />
          <Route path="catalog" element={<CatalogPage />} />
          <Route path="catalog/:productId" element={<ProductDetailsPage />} />
          <Route path="catalog/:productId/add" element={<AddCatalogProductPage />} />
          <Route path="my-products" element={<CatalogPage mineOnly />} />
          <Route path="my-products/:id/edit" element={<GlobalProductFormPage />} />
          <Route path="products/new" element={<GlobalProductFormPage />} />
        </Route>
      </Route>

      <Route path="*" element={<NotFoundPage />} />
    </Routes>
  )
}
