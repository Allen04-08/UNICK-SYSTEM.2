import React from 'react'
import api from '../../services/api'

export default function ProductCatalog() {
  const [products, setProducts] = React.useState([])
  const [isLoading, setIsLoading] = React.useState(false)

  React.useEffect(() => {
    let isActive = true
    setIsLoading(true)
    api.get('/products')
      .then(r => {
        if (!isActive) return
        const data = r.data?.data ?? r.data ?? []
        setProducts(Array.isArray(data) ? data : [])
      })
      .finally(() => isActive && setIsLoading(false))
    return () => { isActive = false }
  }, [])

  return (
    <div>
      <h3>Product Catalog</h3>
      {isLoading && <div>Loading...</div>}
      {!isLoading && (
        <div className="table-responsive">
          <table className="table table-striped align-middle">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Type</th>
                <th>Stock</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              {products.map(p => (
                <tr key={p.id}>
                  <td>{p.sku}</td>
                  <td>{p.name}</td>
                  <td>{p.type}</td>
                  <td>{p.stock_available}</td>
                  <td>{p.unit_price}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}