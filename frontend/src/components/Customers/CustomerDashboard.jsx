import React from 'react'
import api from '../../services/api'

export default function CustomerDashboard() {
  const [orders, setOrders] = React.useState([])
  const [error, setError] = React.useState('')

  React.useEffect(() => {
    let isActive = true
    ;(async () => {
      try {
        const r = await api.get('/orders')
        if (!isActive) return
        setOrders(Array.isArray(r.data?.data) ? r.data.data : [])
      } catch {
        setError('Failed to load orders')
      }
    })()
    return () => { isActive = false }
  }, [])

  return (
    <div>
      <h3>Customer Dashboard</h3>
      {error && <div className="alert alert-danger">{error}</div>}
      <div className="table-responsive">
        <table className="table align-middle">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Status</th>
              <th className="text-end">Total</th>
            </tr>
          </thead>
          <tbody>
            {orders.map(o => (
              <tr key={o.id}>
                <td>{o.order_number}</td>
                <td>{o.status}</td>
                <td className="text-end">${Number(o.total || 0).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}