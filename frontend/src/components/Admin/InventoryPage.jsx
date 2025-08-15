import React from 'react'
import api from '../../services/api'

export default function InventoryPage() {
  const [items, setItems] = React.useState([])
  const [error, setError] = React.useState('')
  const [loading, setLoading] = React.useState(false)

  const load = async () => {
    setLoading(true)
    setError('')
    try {
      const r = await api.get('/products')
      const data = Array.isArray(r.data?.data) ? r.data.data : []
      setItems(data)
    } catch {
      setError('Failed to load inventory')
    } finally {
      setLoading(false)
    }
  }

  React.useEffect(() => { load() }, [])

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-3">
        <h3>Inventory</h3>
        <div className="btn-group">
          <a className="btn btn-outline-secondary" href="/api/v1/reports/inventory">JSON</a>
          <a className="btn btn-outline-secondary" href="/api/v1/reports/inventory.csv">CSV</a>
        </div>
      </div>
      {error && <div className="alert alert-danger">{error}</div>}
      {loading && <div>Loading...</div>}
      {!loading && (
        <div className="table-responsive">
          <table className="table table-striped align-middle">
            <thead>
              <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Type</th>
                <th className="text-end">On Hand</th>
                <th className="text-end">Allocated</th>
                <th className="text-end">Available</th>
                <th className="text-end">ROP</th>
                <th className="text-end">Lead (d)</th>
              </tr>
            </thead>
            <tbody>
              {items.map(p => (
                <tr key={p.id}>
                  <td>{p.sku}</td>
                  <td>{p.name}</td>
                  <td>{p.type}</td>
                  <td className="text-end">{p.stock_on_hand}</td>
                  <td className="text-end">{p.stock_allocated}</td>
                  <td className="text-end">{p.stock_available}</td>
                  <td className="text-end">{p.reorder_point}</td>
                  <td className="text-end">{p.lead_time_days}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}