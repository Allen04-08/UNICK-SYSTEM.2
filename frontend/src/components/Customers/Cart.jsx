import React from 'react'
import api from '../../services/api'

export default function Cart() {
  const [items, setItems] = React.useState([])
  const [shipping, setShipping] = React.useState('')
  const [billing, setBilling] = React.useState('')
  const [message, setMessage] = React.useState('')
  const [error, setError] = React.useState('')
  const [loading, setLoading] = React.useState(false)

  React.useEffect(() => {
    try {
      const data = JSON.parse(localStorage.getItem('cart_items') || '[]')
      setItems(Array.isArray(data) ? data : [])
    } catch {
      setItems([])
    }
  }, [])

  const total = items.reduce((sum, i) => sum + Number(i.price || 0) * Number(i.quantity || 1), 0)

  const placeOrder = async () => {
    setMessage('')
    setError('')
    setLoading(true)
    try {
      if (items.length === 0) throw new Error('Empty cart')
      const payload = {
        shipping_address: shipping || undefined,
        billing_address: billing || undefined,
        items: items.map(i => ({ product_id: i.id, quantity: i.quantity })),
      }
      await api.post('/orders', payload)
      setMessage('Order placed!')
      localStorage.removeItem('cart_items')
      setItems([])
    } catch (e) {
      setError('Failed to place order. Please login and try again.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <h3>Cart</h3>
      {message && <div className="alert alert-success">{message}</div>}
      {error && <div className="alert alert-danger">{error}</div>}
      {items.length === 0 && <p>Your selected items will appear here.</p>}
      {items.length > 0 && (
        <>
          <div className="table-responsive">
            <table className="table align-middle">
              <thead>
                <tr>
                  <th>Item</th>
                  <th style={{ width: 120 }}>Qty</th>
                  <th style={{ width: 160 }}>Price</th>
                </tr>
              </thead>
              <tbody>
                {items.map((i) => (
                  <tr key={i.id}>
                    <td>{i.name}</td>
                    <td>{i.quantity}</td>
                    <td>${Number(i.price || 0).toFixed(2)}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr>
                  <th colSpan={2} className="text-end">Total</th>
                  <th>${total.toFixed(2)}</th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div className="row g-3">
            <div className="col-md-6">
              <label className="form-label">Shipping Address</label>
              <textarea className="form-control" rows={3} value={shipping} onChange={e=>setShipping(e.target.value)} />
            </div>
            <div className="col-md-6">
              <label className="form-label">Billing Address</label>
              <textarea className="form-control" rows={3} value={billing} onChange={e=>setBilling(e.target.value)} />
            </div>
          </div>

          <div className="mt-3">
            <button className="btn btn-wood" disabled={loading} onClick={placeOrder}>{loading ? 'Placing...' : 'Place Order'}</button>
          </div>
        </>
      )}
    </div>
  )
}