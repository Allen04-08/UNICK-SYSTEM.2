import React from 'react'
import api from '../../services/api'

export default function ProductionPage() {
  const [batches, setBatches] = React.useState([])
  const [error, setError] = React.useState('')
  const [loading, setLoading] = React.useState(false)

  const load = async () => {
    setLoading(true)
    setError('')
    try {
      const r = await api.get('/batches')
      const data = Array.isArray(r.data?.data) ? r.data.data : []
      setBatches(data)
    } catch {
      setError('Failed to load production batches')
    } finally {
      setLoading(false)
    }
  }
  React.useEffect(() => { load() }, [])

  const complete = async (id) => {
    const qty = Number(prompt('Quantity completed?') || 0)
    if (!qty) return
    try {
      await api.post(`/batches/${id}/complete`, { quantity_completed: qty })
      await load()
    } catch {
      alert('Failed to complete batch')
    }
  }

  return (
    <div>
      <div className="d-flex justify-content-between align-items-center mb-3">
        <h3>Production</h3>
        <div className="btn-group">
          <a className="btn btn-outline-secondary" href="/api/v1/reports/production">JSON</a>
          <a className="btn btn-outline-secondary" href="/api/v1/reports/production.csv">CSV</a>
        </div>
      </div>
      {error && <div className="alert alert-danger">{error}</div>}
      {loading && <div>Loading...</div>}

      {!loading && (
        <div className="table-responsive">
          <table className="table align-middle">
            <thead>
              <tr>
                <th>Batch #</th>
                <th>Product</th>
                <th className="text-end">Planned</th>
                <th className="text-end">Completed</th>
                <th>Status</th>
                <th>Start</th>
                <th>Due</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {batches.map(b => (
                <tr key={b.id}>
                  <td>{b.batch_number}</td>
                  <td>{b.product?.name}</td>
                  <td className="text-end">{b.quantity_planned}</td>
                  <td className="text-end">{b.quantity_completed}</td>
                  <td>{b.status}</td>
                  <td>{b.start_date || '-'}</td>
                  <td>{b.due_date || '-'}</td>
                  <td className="text-end">
                    <button className="btn btn-sm btn-primary" onClick={() => complete(b.id)}>Complete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}