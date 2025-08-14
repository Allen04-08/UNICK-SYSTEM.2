import React from 'react'
import { BrowserRouter, Routes, Route, Navigate, Link } from 'react-router-dom'
import 'bootstrap/dist/css/bootstrap.min.css'
import api from './services/api'

import AdminDashboard from './components/Admin/AdminDashboard'
import InventoryPage from './components/Admin/InventoryPage'
import OrderPage from './components/Admin/OrderPage'
import ProductionPage from './components/Admin/ProductionPage'
import Report from './components/Admin/Report'

import CustomerDashboard from './components/Customers/CustomerDashboard'
import ProductCatalog from './components/Customers/ProductCatalog'
import Cart from './components/Customers/Cart'

import Login from './components/Auth/Login'
import Register from './components/Auth/Register'

export default function App() {
  const [user, setUser] = React.useState(null)
  const [token, setToken] = React.useState(localStorage.getItem('token') || '')

  React.useEffect(() => {
    let isActive = true
    const check = async () => {
      if (!token) { setUser(null); return }
      try {
        const r = await api.get('/me')
        if (isActive) setUser(r.data)
      } catch {
        if (isActive) setUser(null)
      }
    }
    check()
    return () => { isActive = false }
  }, [token])

  const handleLogin = async (email, password) => {
    const r = await api.post('/auth/login', { email, password })
    const newToken = r.data?.token
    const newUser = r.data?.user
    if (newToken) localStorage.setItem('token', newToken)
    setToken(newToken || '')
    setUser(newUser || null)
  }

  const handleLogout = async () => {
    try { await api.post('/auth/logout') } catch (e) { console.debug('Logout failed:', e) }
    localStorage.removeItem('token')
    setToken('')
    setUser(null)
  }

  const isStaff = user && (user.role === 'admin' || user.role === 'staff')

  const RequireAuth = ({ children }) => {
    if (!user) return <Navigate to="/login" />
    return children
  }
  const RequireStaff = ({ children }) => {
    if (!isStaff) return <Navigate to="/login" />
    return children
  }

  return (
    <BrowserRouter>
      <nav className="navbar navbar-expand navbar-dark bg-dark">
        <div className="container-fluid">
          <Link className="navbar-brand" to="/">Unick</Link>
          <div className="navbar-nav">
            <Link className="nav-link" to="/">Catalog</Link>
            <Link className="nav-link" to="/cart">Cart</Link>
            <Link className="nav-link" to="/customer">My Dashboard</Link>
            {isStaff && <Link className="nav-link" to="/admin">Admin</Link>}
          </div>
          <div className="ms-auto navbar-nav">
            {!user && <Link className="nav-link" to="/login">Login</Link>}
            {!user && <Link className="nav-link" to="/register">Register</Link>}
            {user && <span className="navbar-text me-2">Hi, {user.name}</span>}
            {user && <button className="btn btn-sm btn-outline-light" onClick={handleLogout}>Logout</button>}
          </div>
        </div>
      </nav>
      <div className="container py-4">
        <Routes>
          <Route path="/" element={<ProductCatalog />} />
          <Route path="/cart" element={<Cart />} />
          <Route path="/customer" element={<RequireAuth><CustomerDashboard /></RequireAuth>} />

          <Route path="/login" element={!user ? <Login onLogin={handleLogin} /> : <Navigate to="/" />} />
          <Route path="/register" element={<Register />} />

          <Route path="/admin" element={<RequireStaff><AdminDashboard /></RequireStaff>} />
          <Route path="/admin/inventory" element={<RequireStaff><InventoryPage /></RequireStaff>} />
          <Route path="/admin/orders" element={<RequireStaff><OrderPage /></RequireStaff>} />
          <Route path="/admin/production" element={<RequireStaff><ProductionPage /></RequireStaff>} />
          <Route path="/admin/reports" element={<RequireStaff><Report /></RequireStaff>} />

          <Route path="*" element={<Navigate to="/" />} />
        </Routes>
      </div>
    </BrowserRouter>
  )
}
