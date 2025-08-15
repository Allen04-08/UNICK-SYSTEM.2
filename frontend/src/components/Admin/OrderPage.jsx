import React from 'react'
import api from '../../services/api'

export default function OrderPage() {
  const [orders, setOrders] = React.useState([])
  const [error, setError] = React.useState('')

  const load = async () => {
    try {
      const r = await api.get('/orders')
      const data = Array.isArray(r.data?.data) ? r.data.data : []
      setOrders(data)
    } catch {
      setError('Failed to load orders')
    }
  }

  React.useEffect(() => { load() }, [])

  return (
    <div>
      <h3>Orders</h3>
      {error && <div className="alert alert-danger">{error}</div>}
      <div className="table-responsive">
        <table className="table align-middle">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Status</th>
              <th className="text-end">Total</th>
            </tr>
          </thead>
          <tbody>
            {orders.map(o => (
              <tr key={o.id}>
                <td>{o.order_number}</td>
                <td>{o.user?.name}</td>
                <td><span className="badge bg-secondary">{o.status}</span></td>
                <td className="text-end">${Number(o.total || 0).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}