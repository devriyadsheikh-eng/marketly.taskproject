import api from './api.js'

const unwrap = (response) => response.data
export const adminApi = {
  dashboard: () => api.get('/admin/dashboard').then(unwrap),
  shops: (params = {}) => api.get('/admin/shops', { params }).then(unwrap),
  shop: (id) => api.get(`/admin/shops/${id}`).then(unwrap),
  createShop: (data) => api.post('/admin/shops', data).then(unwrap),
  updateShop: (id, data, multipart = false) => multipart
    ? api.post(`/admin/shops/${id}`, data).then(unwrap)
    : api.put(`/admin/shops/${id}`, data).then(unwrap),
  deleteShop: (id, deleteListings = false) => api.delete(`/admin/shops/${id}`, { params: deleteListings ? { delete_listings: true } : {} }).then(unwrap),
  owners: (params = {}) => api.get('/admin/users', { params }).then(unwrap),
  owner: (id) => api.get(`/admin/users/${id}`).then(unwrap),
  createOwner: (data) => api.post('/admin/users', data).then(unwrap),
  updateOwner: (id, data) => api.put(`/admin/users/${id}`, data).then(unwrap),
  deleteOwner: (id) => api.delete(`/admin/users/${id}`).then(unwrap),
  products: (params = {}) => api.get('/admin/products', { params }).then(unwrap),
  product: (id) => api.get(`/admin/products/${id}`).then(unwrap),
  createProduct: (data) => api.post('/admin/products', data).then(unwrap),
  updateProduct: (id, data, multipart = false) => multipart
    ? api.post(`/admin/products/${id}`, data).then(unwrap)
    : api.put(`/admin/products/${id}`, data).then(unwrap),
  deleteProduct: (id) => api.delete(`/admin/products/${id}`).then(unwrap),
  listings: (params = {}) => api.get('/admin/shop-listings', { params }).then(unwrap),
}
