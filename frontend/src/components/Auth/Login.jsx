import React from 'react'
import { useNavigate } from 'react-router-dom'

export default function Login({ onLogin }) {
  const navigate = useNavigate()
  const [email, setEmail] = React.useState('admin@example.com')
  const [password, setPassword] = React.useState('password')
  const [error, setError] = React.useState('')
  const [isLoading, setIsLoading] = React.useState(false)

  const onSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setIsLoading(true)
    try {
      await onLogin(email, password)
      navigate('/')
    } catch (err) {
      setError('Login failed')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="row justify-content-center">
      <div className="col-md-4">
        <h3>Login</h3>
        {error && <div className="alert alert-danger">{error}</div>}
        <form onSubmit={onSubmit}>
          <div className="mb-3">
            <label className="form-label">Email</label>
            <input className="form-control" value={email} onChange={e=>setEmail(e.target.value)} />
          </div>
          <div className="mb-3">
            <label className="form-label">Password</label>
            <input type="password" className="form-control" value={password} onChange={e=>setPassword(e.target.value)} />
          </div>
          <button disabled={isLoading} className="btn btn-primary w-100">{isLoading ? 'Signing in...' : 'Sign in'}</button>
        </form>
      </div>
    </div>
  )
}