import React from 'react'

export default function Cart() {
  const [items, setItems] = React.useState([])

  React.useEffect(() => {
    try {
      const data = JSON.parse(localStorage.getItem('cart_items') || '[]')
      setItems(Array.isArray(data) ? data : [])
    } catch {
      setItems([])
    }
  }, [])

  const total = items.reduce((sum, i) => sum + Number(i.price || 0) * Number(i.quantity || 1), 0)

  return (
    <div>
      <h3>Cart</h3>
      {items.length === 0 && <p>Your selected items will appear here.</p>}
      {items.length > 0 && (
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
      )}
    </div>
  )
}