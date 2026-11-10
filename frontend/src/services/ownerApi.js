import api from './api.js'

const unwrap = (response) => response.data
export const ownerApi = {
  dashboard: () => api.get('/owner/dashboard').then(unwrap),
  shops: (params = {}) => api.get('/owner/shops', { params }).then(unwrap),
  catalog: (params = {}) => api.get('/owner/catalog', { params }).then(unwrap),
  product: (id) => api.get(`/owner/catalog/${id}`).then(unwrap),
  createProduct: (body) => api.post('/owner/products', body).then(unwrap),
  updateProduct: (id, body, multipart = false) => multipart ? api.post(`/owner/products/${id}`, body).then(unwrap) : api.put(`/owner/products/${id}`, body).then(unwrap),
  listings: (shopId, params = {}) => api.get(`/owner/shops/${shopId}/products`, { params }).then(unwrap),
  addListing: (shopId, body) => api.post(`/owner/shops/${shopId}/products`, body).then(unwrap),
  updateListing: (shopId, listingId, body, multipart = false) => multipart ? api.post(`/owner/shops/${shopId}/products/${listingId}`, body).then(unwrap) : api.put(`/owner/shops/${shopId}/products/${listingId}`, body).then(unwrap),
  removeListing: (shopId, listingId) => api.delete(`/owner/shops/${shopId}/products/${listingId}`).then(unwrap),
}

export async function allPages(fetchPage, params = {}) {
  const first = await fetchPage({ ...params, per_page: 100, page: 1 })
  const records = [...(first.data || [])]
  for (let page = 2; page <= (first.meta?.last_page || 1); page += 1) {
    const next = await fetchPage({ ...params, per_page: 100, page })
    records.push(...(next.data || []))
  }
  return records
}
