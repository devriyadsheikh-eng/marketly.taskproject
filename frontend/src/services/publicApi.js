import api from './api.js'

const unwrap = (response) => response.data
export const publicApi = {
  catalogProducts: (params = {}) => api.get('/public/products', { params }).then(unwrap),
  shops: (params = {}) => api.get('/public/shops', { params }).then(unwrap),
  shop: (slug) => api.get(`/public/shops/${encodeURIComponent(slug)}`).then(unwrap),
  products: (slug, params = {}) => api.get(`/public/shops/${encodeURIComponent(slug)}/products`, { params }).then(unwrap),
  product: (slug, productId) => api.get(`/public/shops/${encodeURIComponent(slug)}/products/${productId}`).then(unwrap),
}
