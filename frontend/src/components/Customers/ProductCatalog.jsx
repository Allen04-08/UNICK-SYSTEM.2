import React from 'react'
import api from '../../services/api'

export default function ProductCatalog() {
  const [products, setProducts] = React.useState([])
  const [links, setLinks] = React.useState({ prev: null, next: null })
  const [isLoading, setIsLoading] = React.useState(false)
  const [error, setError] = React.useState('')

  const toRelativeApiPath = (url) => {
    if (!url) return null
    try {
      const u = new URL(url, window.location.origin)
      const pathWithQuery = `${u.pathname}${u.search}`
      return pathWithQuery.replace(/^\/api\/v1/, '')
    } catch {
      return url.replace(/^https?:\/\/[^/]+/, '').replace(/^\/api\/v1/, '')
    }
  }

  const loadPage = React.useCallback(async (url = '/products') => {
    setIsLoading(true)
    setError('')
    try {
      const r = await api.get(url)
      const payload = r.data || {}
      const data = Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : [])
      setProducts(data)
      if (payload.links && (payload.links.prev !== undefined || payload.links.next !== undefined)) {
        setLinks({ prev: toRelativeApiPath(payload.links.prev), next: toRelativeApiPath(payload.links.next) })
      } else if (payload.meta && typeof payload.meta.current_page === 'number' && typeof payload.meta.last_page === 'number') {
        const current = payload.meta.current_page
        const last = payload.meta.last_page
        setLinks({
          prev: current > 1 ? `/products?page=${current - 1}` : null,
          next: current < last ? `/products?page=${current + 1}` : null,
        })
      } else {
        setLinks({ prev: null, next: null })
      }
    } catch (e) {
      setError('Failed to load products')
    } finally {
      setIsLoading(false)
    }
  }, [])

  React.useEffect(() => {
    let isActive = true
    ;(async () => {
      if (!isActive) return
      await loadPage('/products')
    })()
    return () => { isActive = false }
  }, [loadPage])

  const formatCurrency = (value) => {
    const num = Number(value || 0)
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD', maximumFractionDigits: 2 }).format(num)
  }

  const addToCart = (product) => {
    try {
      const key = 'cart_items'
      const current = JSON.parse(localStorage.getItem(key) || '[]')
      const existingIndex = current.findIndex((i) => i.id === product.id)
      if (existingIndex >= 0) {
        current[existingIndex].quantity = (current[existingIndex].quantity || 1) + 1
      } else {
        current.push({ id: product.id, name: product.name, price: product.unit_price, quantity: 1 })
      }
      localStorage.setItem(key, JSON.stringify(current))
    } catch {/* no-op */}
  }

  return (
    <div>
      <div className="hero-wood mb-4">
        <div className="row align-items-center g-3">
          <div className="col-lg-8">
            <h2 className="mb-1">Handcrafted Wood Furniture</h2>
            <p className="mb-0">Timeless pieces built to last. Sustainably sourced. Custom finishes available.</p>
          </div>
          <div className="col-lg-4 text-lg-end">
            <a className="btn btn-wood" href="#catalog">Shop Collection</a>
          </div>
        </div>
      </div>

      {error && <div className="alert alert-danger">{error}</div>}
      {isLoading && <div className="text-center py-5"><div className="spinner-border text-secondary" role="status" /></div>}

      {!isLoading && (
        <div id="catalog">
          <div className="d-flex align-items-baseline mb-3">
            <h3 className="me-3 mb-0">Product Catalog</h3>
            <span className="text-muted">{products.length} items</span>
          </div>

          <div className="row g-4">
            {products.map((p) => (
              <div className="col-12 col-sm-6 col-md-4 col-lg-3" key={p.id}>
                <div className="card product-card h-100">
                  <div className="ratio ratio-4x3 product-media"></div>
                  <div className="card-body d-flex flex-column">
                    <div className="d-flex justify-content-between align-items-start mb-2">
                      <h6 className="mb-0">{p.name}</h6>
                      {p.type && <span className="badge badge-type ms-2">{p.type}</span>}
                    </div>
                    <div className="small text-muted mb-2">SKU: {p.sku}</div>
                    <div className="d-flex align-items-center justify-content-between mt-auto">
                      <div className="price">{formatCurrency(p.unit_price)}</div>
                      <small className={p.stock_available > 0 ? 'text-success' : 'text-danger'}>
                        {p.stock_available > 0 ? `${p.stock_available} in stock` : 'Out of stock'}
                      </small>
                    </div>
                    <button
                      className="btn btn-wood w-100 mt-3"
                      onClick={() => addToCart(p)}
                      disabled={!p.stock_available}
                    >
                      {p.stock_available ? 'Add to Cart' : 'Unavailable'}
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>

          <div className="d-flex justify-content-between mt-4">
            <button className="btn btn-outline-secondary" disabled={!links.prev} onClick={() => loadPage(links.prev || '/products')}>Previous</button>
            <button className="btn btn-outline-secondary" disabled={!links.next} onClick={() => loadPage(links.next || '/products')}>Next</button>
          </div>
        </div>
      )}
    </div>
  )
}