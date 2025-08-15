import React from 'react'
import { Link } from 'react-router-dom'

export default function Footer() {
  return (
    <footer className="footer-wood mt-5">
      <div className="container py-4">
        <div className="row g-3 align-items-center">
          <div className="col-md-6">
            <strong>Unick Woodcraft</strong>
            <div className="small opacity-75">Handcrafted furniture made to order</div>
          </div>
          <div className="col-md-6 text-md-end">
            <Link className="text-light me-3" to="/">Catalog</Link>
            <Link className="text-light me-3" to="/cart">Cart</Link>
            <Link className="text-light" to="/customer">My Dashboard</Link>
          </div>
        </div>
      </div>
    </footer>
  )
}