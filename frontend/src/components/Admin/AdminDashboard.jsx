import React from 'react'
import api from '../../services/api'

export default function AdminDashboard() {
  const [metrics, setMetrics] = React.useState(null)
  const [forecasts, setForecasts] = React.useState([])
  const [error, setError] = React.useState('')

  React.useEffect(() => {
    let isActive = true
    ;(async () => {
      try {
        const [m, f] = await Promise.all([
          api.get('/dashboard/metrics'),
          api.get('/analytics/forecasts?limit=6'),
        ])
        if (!isActive) return
        setMetrics(m.data)
        setForecasts(Array.isArray(f.data) ? f.data : [])
      } catch (e) {
        setError('Failed to load admin overview')
      }
    })()
    return () => { isActive = false }
  }, [])

  return (
    <div>
      <h3>Admin Dashboard</h3>
      {error && <div className="alert alert-danger">{error}</div>}

      {metrics && (
        <div className="row g-3 mb-4">
          <div className="col-md-3">
            <div className="card"><div className="card-body"><div className="small text-muted">Orders Today</div><div className="fs-4 fw-bold">{metrics.orders_today}</div></div></div>
          </div>
          <div className="col-md-3">
            <div className="card"><div className="card-body"><div className="small text-muted">Revenue This Month</div><div className="fs-4 fw-bold">${Number(metrics.revenue_month || 0).toFixed(2)}</div></div></div>
          </div>
          <div className="col-md-3">
            <div className="card"><div className="card-body"><div className="small text-muted">Batches In Progress</div><div className="fs-4 fw-bold">{metrics.batches_in_progress}</div></div></div>
          </div>
          <div className="col-md-3">
            <div className="card"><div className="card-body"><div className="small text-muted">Low Stock SKUs</div><div className="fs-4 fw-bold">{metrics.low_stock_count}</div></div></div>
          </div>
        </div>
      )}

      <div className="card">
        <div className="card-header d-flex justify-content-between align-items-center">
          <strong>Forecast & MRP Alerts</strong>
          <a className="btn btn-sm btn-outline-secondary" href="#/admin/reports">View Reports</a>
        </div>
        <div className="card-body">
          <div className="row g-3">
            {forecasts.map((row, idx) => (
              <div className="col-md-4" key={idx}>
                <div className="border rounded p-3 h-100">
                  <div className="d-flex justify-content-between align-items-center mb-1">
                    <div className="fw-semibold">{row.product?.name}</div>
                    <span className={`badge ${row.stock?.needReorder ? 'bg-danger' : 'bg-success'}`}>{row.stock?.needReorder ? 'Reorder' : 'OK'}</span>
                  </div>
                  <div className="small text-muted">SKU: {row.product?.sku}</div>
                  <div className="small mt-2">Demand (est.): <strong>{row.forecast?.daily_demand}</strong>/day</div>
                  <div className="small">Avail: <strong>{row.stock?.available}</strong> | ROP: <strong>{row.stock?.reorderPoint}</strong></div>
                  {row.stock?.needReorder && (
                    <div className="small text-danger mt-1">Suggested order: {row.stock?.recommendedOrderQty}</div>
                  )}
                </div>
              </div>
            ))}
            {forecasts.length === 0 && <div className="col-12 text-muted">No forecast data yet. Place orders to build a baseline.</div>}
          </div>
        </div>
      </div>
    </div>
  )
}