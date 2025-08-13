import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter, Routes, Route, Navigate, Link } from 'react-router-dom'
import axios from 'axios'
import 'bootstrap/dist/css/bootstrap.min.css'

axios.defaults.baseURL = '/api/v1'
axios.defaults.withCredentials = true

function useAuth() {
  const [user, setUser] = React.useState(null)
  const [token, setToken] = React.useState(localStorage.getItem('token') || '')

  React.useEffect(() => {
    if (token) {
      axios.get('/me', { headers: { Authorization: `Bearer ${token}` } })
        .then(r => setUser(r.data))
        .catch(() => setUser(null))
    }
  }, [token])

  const login = async (email, password) => {
    const r = await axios.post('/auth/login', { email, password })
    localStorage.setItem('token', r.data.token)
    setToken(r.data.token)
    setUser(r.data.user)
  }
  const logout = async () => {
    try { await axios.post('/auth/logout', {}, { headers: { Authorization: `Bearer ${token}` } }) } catch {}
    localStorage.removeItem('token')
    setToken('')
    setUser(null)
  }
  return { user, token, login, logout }
}

function App() {
  const auth = useAuth()
  return (
    <BrowserRouter>
      <nav className="navbar navbar-expand navbar-dark bg-dark">
        <div className="container-fluid">
          <Link className="navbar-brand" to="/">Unick</Link>
          <div className="navbar-nav">
            <Link className="nav-link" to="/products">Products</Link>
            {auth.user && <Link className="nav-link" to="/orders">My Orders</Link>}
            {auth.user?.role !== 'customer' && auth.user && <Link className="nav-link" to="/dashboard">Dashboard</Link>}
          </div>
          <div className="ms-auto navbar-nav">
            {!auth.user && <Link className="nav-link" to="/login">Login</Link>}
            {auth.user && <button className="btn btn-sm btn-outline-light" onClick={auth.logout}>Logout</button>}
          </div>
        </div>
      </nav>
      <div className="container py-4">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login auth={auth} />} />
          <Route path="/products" element={<Products auth={auth} />} />
          <Route path="/orders" element={<RequireAuth auth={auth}><Orders auth={auth} /></RequireAuth>} />
          <Route path="/dashboard" element={<RequireStaff auth={auth}><Dashboard auth={auth} /></RequireStaff>} />
          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </div>
    </BrowserRouter>
  )
}

function RequireAuth({ auth, children }) {
  if (!auth.user) return <Navigate to="/login" />
  return children
}
function RequireStaff({ auth, children }) {
  if (!auth.user || (auth.user.role !== 'admin' && auth.user.role !== 'staff')) return <Navigate to="/login" />
  return children
}

function Home() {
  return <div className="p-4 bg-light border">Welcome to Unick Enterprises client.</div>
}

function Login({ auth }) {
  const [email, setEmail] = React.useState('admin@example.com')
  const [password, setPassword] = React.useState('password')
  const [error, setError] = React.useState('')
  const onSubmit = async e => {
    e.preventDefault()
    setError('')
    try { await auth.login(email, password) } catch (err) { setError('Login failed') }
  }
  return (
    <div className="row justify-content-center">
      <div className="col-md-4">
        <h3>Login</h3>
        {error && <div className="alert alert-danger">{error}</div>}
        <form onSubmit={onSubmit}>
          <div className="mb-3"><label className="form-label">Email</label><input className="form-control" value={email} onChange={e=>setEmail(e.target.value)} /></div>
          <div className="mb-3"><label className="form-label">Password</label><input type="password" className="form-control" value={password} onChange={e=>setPassword(e.target.value)} /></div>
          <button className="btn btn-primary w-100">Sign in</button>
        </form>
      </div>
    </div>
  )
}

function Products({ auth }) {
  const [products, setProducts] = React.useState([])
  React.useEffect(() => { axios.get('/products').then(r => setProducts(r.data.data || r.data)) }, [])
  return (
    <div>
      <h3>Products</h3>
      <table className="table table-striped">
        <thead><tr><th>SKU</th><th>Name</th><th>Type</th><th>Stock</th><th>Price</th></tr></thead>
        <tbody>
        {products.map(p => (
          <tr key={p.id}><td>{p.sku}</td><td>{p.name}</td><td>{p.type}</td><td>{p.stock_available}</td><td>{p.unit_price}</td></tr>
        ))}
        </tbody>
      </table>
    </div>
  )
}

function Orders({ auth }) {
  const [orders, setOrders] = React.useState([])
  React.useEffect(() => {
    axios.get('/orders', { headers: { Authorization: `Bearer ${auth.token}` } }).then(r => setOrders(r.data.data || r.data))
  }, [auth.token])
  return (
    <div>
      <h3>Orders</h3>
      <table className="table table-striped">
        <thead><tr><th>#</th><th>Status</th><th>Total</th></tr></thead>
        <tbody>
        {orders.map(o => (<tr key={o.id}><td>{o.order_number}</td><td>{o.status}</td><td>{o.total}</td></tr>))}
        </tbody>
      </table>
    </div>
  )
}

function Dashboard({ auth }) {
  const [metrics, setMetrics] = React.useState(null)
  React.useEffect(() => { axios.get('/dashboard/metrics', { headers: { Authorization: `Bearer ${auth.token}` } }).then(r => setMetrics(r.data)) }, [auth.token])
  if (!metrics) return <div>Loading...</div>
  return (
    <div className="row g-3">
      <div className="col-md-3"><div className="p-3 bg-light border">Orders today: <b>{metrics.orders_today}</b></div></div>
      <div className="col-md-3"><div className="p-3 bg-light border">Revenue month: <b>{metrics.revenue_month}</b></div></div>
      <div className="col-md-3"><div className="p-3 bg-light border">Batches in progress: <b>{metrics.batches_in_progress}</b></div></div>
      <div className="col-md-3"><div className="p-3 bg-light border">Low stock count: <b>{metrics.low_stock_count}</b></div></div>
    </div>
  )
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />)
