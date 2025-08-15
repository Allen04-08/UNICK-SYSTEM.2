import React from 'react'
import api from '../../services/api'

export default function Report() {
  const [ordersSummary, setOrdersSummary] = React.useState(null)
  const [inventory, setInventory] = React.useState(null)
  const [error, setError] = React.useState('')

  React.useEffect(() => {
    let isActive = true
    ;(async () => {
      try {
        const [o, i] = await Promise.all([
          api.get('/reports/orders'),
          api.get('/reports/inventory'),
        ])
        if (!isActive) return
        setOrdersSummary(o.data)
        setInventory(i.data)
      } catch {
        setError('Failed to load reports')
      }
    })()
    return () => { isActive = false }
  }, [])

  return (
    <div>
      <h3>Reports</h3>
      {error && <div className="alert alert-danger">{error}</div>}

      <div className="row g-3">
        <div className="col-md-6">
          <div className="card h-100">
            <div className="card-header d-flex justify-content-between align-items-center">
              <strong>Orders</strong>
              <div className="btn-group">
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/orders">JSON</a>
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/orders.xlsx">Excel</a>
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/orders.csv">CSV</a>
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/orders.pdf">PDF</a>
              </div>
            </div>
            <div className="card-body">
              {ordersSummary && (
                <>
                  <div className="mb-2">By Status:</div>
                  <ul className="small">
                    {Object.entries(ordersSummary.by_status || {}).map(([k,v]) => (
                      <li key={k}><strong>{k}</strong>: {v}</li>
                    ))}
                  </ul>
                </>
              )}
            </div>
          </div>
        </div>
        <div className="col-md-6">
          <div className="card h-100">
            <div className="card-header d-flex justify-content-between align-items-center">
              <strong>Inventory</strong>
              <div className="btn-group">
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/inventory">JSON</a>
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/inventory.csv">CSV</a>
                <a className="btn btn-sm btn-outline-secondary" href="/api/v1/reports/inventory.xlsx">Excel</a>
              </div>
            </div>
            <div className="card-body">
              {inventory && (
                <>
                  <div className="mb-2">Low Stock:</div>
                  <ul className="small">
                    {(inventory.low_stock || []).map(p => (
                      <li key={p.id}>{p.sku} - {p.name} ({p.stock_on_hand} on hand)</li>
                    ))}
                  </ul>
                </>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}