
import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/AdminDashboard.css";
import { 
    FaSignOutAlt, FaEdit, FaTrash, FaPlus, FaBoxOpen, 
    FaClipboardList, FaUsers, FaChartLine, FaCheckCircle, FaTruck, FaBan, FaUnlock 
} from "react-icons/fa";

const AdminDashboard = () => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState("inventory"); // Default to Inventory
  
  // --- PRODUCT STATE ---
  const [products, setProducts] = useState([]);
  const [editingId, setEditingId] = useState(null);
  const [sortOption, setSortOption] = useState("newest");
  
  // Form State (Image is now a string URL)
  const [form, setForm] = useState({
    name: "", brand: "", price: "", category: "", quantity: "", discount: "", image: "", description: ""
  });

  // --- ORDER, USER, ANALYTICS STATE ---
  const [orders, setOrders] = useState([]);
  const [users, setUsers] = useState([]); 
  const [analytics, setAnalytics] = useState({ total_revenue: 0, daily_sales: [] }); 
  const [loadingData, setLoadingData] = useState(false);

  const BASE_URL = 'http://localhost:8083'; 
  const API_URL = `${BASE_URL}/api`;
  const STORAGE_URL = `${BASE_URL}/storage/`;

  // --- HELPER: GET IMAGE URL ---
  // Smartly determines if the image is an external URL, a frontend asset, or a backend storage file
  const getImageUrl = (imagePath) => {
    if (!imagePath) return "https://placehold.co/50x50?text=No+Img";
    if (imagePath.startsWith('http')) return imagePath; // External link
    if (imagePath.startsWith('/')) return imagePath; // Frontend public folder (e.g. /img/...)
    return `${STORAGE_URL}${imagePath}`; // Backend storage upload (fallback)
  };

  // --- AUTH HELPER ---
  const getAuthHeaders = () => {
      const token = localStorage.getItem('auth_token');
      return {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
      };
  };

  // --- FETCH DATA ---
  const fetchProducts = () => {
    fetch(`${API_URL}/products?sort=${sortOption}`)
      .then(res => res.json())
      .then(data => setProducts(data))
      .catch(err => console.error("Error loading products:", err));
  };

  const fetchOrders = () => {
    setLoadingData(true);
    fetch(`${API_URL}/admin/orders`, { headers: getAuthHeaders() })
      .then(res => {
          if(res.status === 401) navigate('/login');
          return res.json();
      })
      .then(data => {
          setOrders(data);
          setLoadingData(false);
      })
      .catch(err => {
          console.error("Error loading orders:", err);
          setLoadingData(false);
      });
  };

  const fetchUsers = () => {
    setLoadingData(true);
    fetch(`${API_URL}/admin/users`, { headers: getAuthHeaders() })
      .then(res => res.json())
      .then(data => {
          setUsers(data);
          setLoadingData(false);
      })
      .catch(err => {
          console.error(err);
          setLoadingData(false);
      });
  };

  const fetchAnalytics = () => {
    fetch(`${API_URL}/admin/analytics`, { headers: getAuthHeaders() })
      .then(res => res.json())
      .then(data => setAnalytics(data))
      .catch(err => console.error(err));
  };

  useEffect(() => {
    if (activeTab === "inventory") fetchProducts();
    if (activeTab === "orders") fetchOrders();
    if (activeTab === "customers") fetchUsers();
    if (activeTab === "overview") fetchAnalytics();
  }, [activeTab, sortOption]);

  // --- PRODUCT HANDLERS ---
  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm({ ...form, [name]: value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!form.name || !form.brand || !form.price || !form.image || !form.description) {
        return alert("Please fill in all required fields.");
    }

    const url = editingId ? `${API_URL}/products/${editingId}` : `${API_URL}/products`;
    const method = editingId ? 'PUT' : 'POST';

    // Send as standard JSON since we are using Image URLs now
    fetch(url, {
      method: method,
      headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json' 
      }, 
      body: JSON.stringify({
          ...form,
          price: parseFloat(form.price),
          quantity: parseInt(form.quantity),
          discount: parseInt(form.discount || 0)
      })
    })
    .then(async (res) => {
      const data = await res.json();
      if (res.ok) {
        alert(editingId ? "Product updated!" : "Product added!");
        fetchProducts();
        resetForm();
      } else {
        alert("Error: " + (data.message || JSON.stringify(data)));
      }
    })
    .catch(err => alert("Network Error: " + err.message));
  };

  const handleDelete = (id) => {
    if (window.confirm("Delete this product?")) {
      fetch(`${API_URL}/products/${id}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' }
      }).then(res => {
        if(res.ok) fetchProducts();
        else alert("Error deleting product.");
      });
    }
  };

  const resetForm = () => {
    setEditingId(null);
    setForm({ name: "", brand: "", price: "", category: "", quantity: "", discount: "", image: "", description: "" });
  };

  const handleEdit = (product) => {
    setEditingId(product.id);
    setForm({ 
        name: product.name,
        brand: product.brand,
        price: product.price,
        category: product.category,
        quantity: product.quantity,
        discount: product.discount,
        image: product.image, // Just set the string URL directly
        description: product.description 
    }); 
  };

  // --- ORDER HANDLERS ---
  const handleStatusUpdate = (orderId, newStatus) => {
      if(!window.confirm(`Mark order #${orderId} as ${newStatus}?`)) return;
      fetch(`${API_URL}/admin/orders/${orderId}/status`, {
          method: 'PUT',
          headers: getAuthHeaders(),
          body: JSON.stringify({ status: newStatus })
      }).then(() => fetchOrders());
  };

  // --- CUSTOMER HANDLERS (IMPROVED ERROR HANDLING) ---
  const handleToggleBlock = async (userId, currentStatus) => {
      const action = currentStatus ? "unblock" : "block";
      if(!window.confirm(`Are you sure you want to ${action} this user?`)) return;

      try {
        const response = await fetch(`${API_URL}/admin/users/${userId}/block`, {
            method: 'PUT',
            headers: getAuthHeaders(),
            body: JSON.stringify({ is_blocked: !currentStatus })
        });

        const data = await response.json();

        if (response.ok) {
            alert(`User ${action}ed successfully!`);
            fetchUsers(); // Refresh list
        } else {
            // Show exact error from backend
            alert(`Error: ${data.message || "Failed to update user status"}`);
        }
      } catch (error) {
        console.error("Block User Error:", error);
        alert("Network Error: Could not connect to server.");
      }
  };

  const handleLogout = () => {
    if (window.confirm("Log out?")) {
        localStorage.removeItem('auth_token');
        navigate("/login");
    }
  };

  return (
    <>
      <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />

      <div className="admin-page">
        <div className="adminHeader">
          <div className="header-content">
            <div className="logo-area">
                <h1>Soleair <span className="admin-badge">Admin</span></h1>
            </div>
            
            <div className="admin-nav">
                <button className={`nav-item ${activeTab === 'overview' ? 'active' : ''}`} onClick={() => setActiveTab('overview')}>
                    <FaChartLine /> Overview
                </button>
                <button className={`nav-item ${activeTab === 'inventory' ? 'active' : ''}`} onClick={() => setActiveTab('inventory')}>
                    <FaBoxOpen /> Inventory
                </button>
                <button className={`nav-item ${activeTab === 'orders' ? 'active' : ''}`} onClick={() => setActiveTab('orders')}>
                    <FaClipboardList /> Orders
                </button>
                <button className={`nav-item ${activeTab === 'customers' ? 'active' : ''}`} onClick={() => setActiveTab('customers')}>
                    <FaUsers /> Customers
                </button>
            </div>

            <button className="logout-btn" onClick={handleLogout}>
                <FaSignOutAlt /> Logout
            </button>
          </div>
        </div>

        <div className="admin-content">
            
            {/* --- 1. OVERVIEW TAB --- */}
            {activeTab === 'overview' && (
                <div className="overview-panel">
                    <div className="stat-card total-revenue">
                        <h3>Total Revenue</h3>
                        <p>₱{Number(analytics.total_revenue || 0).toLocaleString()}</p>
                    </div>
                    
                    <div className="analytics-table-box">
                        <h3>Recent Sales Performance</h3>
                        <table className="admin-table">
                            <thead><tr><th>Date</th><th>Orders</th><th>Sales</th></tr></thead>
                            <tbody>
                                {analytics.daily_sales && analytics.daily_sales.length > 0 ? analytics.daily_sales.map((day, idx) => (
                                    <tr key={idx}>
                                        <td>{day.date}</td>
                                        <td>{day.order_count}</td>
                                        <td>₱{Number(day.total_sales).toLocaleString()}</td>
                                    </tr>
                                )) : (
                                    <tr><td colSpan="3" className="empty-state">No sales data yet.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* --- 2. INVENTORY TAB --- */}
            {activeTab === 'inventory' && (
                <div className="admin-split">
                    <div className="admin-form-section">
                        <div className="form-header">
                            <h3>{editingId ? "Edit" : "Add"} Product</h3>
                        </div>
                        <form onSubmit={handleSubmit} className="admin-form">
                            <div className="form-group"><label>Name</label><input name="name" value={form.name} onChange={handleChange} placeholder="Product Name"/></div>
                            <div className="form-row">
                                <div className="form-group"><label>Brand</label><input name="brand" value={form.brand} onChange={handleChange} placeholder="Brand"/></div>
                                <div className="form-group"><label>Category</label><select name="category" value={form.category} onChange={handleChange}><option value="">Select</option><option value="Men">Men</option><option value="Women">Women</option><option value="Kids">Kids</option></select></div>
                            </div>
                            <div className="form-row">
                                <div className="form-group"><label>Price</label><input type="number" name="price" value={form.price} onChange={handleChange} placeholder="0.00"/></div>
                                <div className="form-group"><label>Qty</label><input type="number" name="quantity" value={form.quantity} onChange={handleChange} placeholder="0"/></div>
                            </div>
                            <div className="form-group"><label>Discount (%)</label><input type="number" name="discount" value={form.discount} onChange={handleChange} placeholder="0"/></div>
                            
                            {/* REVERTED TO TEXT INPUT FOR URL */}
                            <div className="form-group">
                                <label>Image URL</label>
                                <input 
                                    type="text" 
                                    name="image" 
                                    value={form.image}
                                    onChange={handleChange} 
                                    placeholder="e.g. /img/shoe.png or https://..."
                                />
                                {form.image && (
                                    <div style={{marginTop: '10px', textAlign: 'center'}}>
                                        <img src={getImageUrl(form.image)} alt="Preview" style={{maxWidth:'80px', borderRadius: '8px', border: '1px solid #ddd'}} onError={(e)=>{e.target.src = "https://placehold.co/50x50?text=Invalid"}}/>
                                    </div>
                                )}
                            </div>
                            
                            <div className="form-group"><label>Desc</label><textarea name="description" value={form.description} onChange={handleChange} rows="2"/></div>
                            <button type="submit" className="save-btn">{editingId ? "Update" : "Add"}</button>
                            {editingId && <button type="button" className="cancel-btn" onClick={resetForm}>Cancel</button>}
                        </form>
                    </div>

                    <div className="scrollable-panel">
                        <div className="admin-table-section">
                            <div className="table-header-row">
                                <h3>Inventory</h3>
                                <div style={{display: 'flex', alignItems: 'center', gap: '15px'}}>
                                    <select value={sortOption} onChange={(e) => setSortOption(e.target.value)} className="admin-sort" style={{padding: '6px', borderRadius: '6px', border: '1px solid #ddd'}}>
                                        <option value="newest">Newest</option>
                                        <option value="price_asc">Price Low-High</option>
                                        <option value="price_desc">Price High-Low</option>
                                    </select>
                                    <span className="count-badge">{products.length} Items</span>
                                </div>
                            </div>
                            <table className="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Disc%</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {products.length > 0 ? (
                                        products.map((product) => (
                                            <tr key={product.id}>
                                                <td className="id-col">#{product.id}</td>
                                                <td className="product-col">
                                                    <div className="product-cell">
                                                        <img 
                                                            src={getImageUrl(product.image)} 
                                                            alt={product.name} 
                                                            className="product-img" 
                                                            onError={(e) => {e.target.src = 'https://placehold.co/50x50?text=No+Img'}} 
                                                        />
                                                        <div className="product-info">
                                                            <span className="p-name">{product.name}</span>
                                                            <span className="p-brand">{product.brand}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="price-col">₱{Number(product.price).toLocaleString()}</td>
                                                <td style={{color: product.discount > 0 ? '#d4483f' : '#999', fontWeight: 'bold'}}>
                                                    {product.discount > 0 ? `-${product.discount}%` : '-'}
                                                </td>
                                                <td>
                                                    <span className={`stock-badge ${product.quantity < 5 ? 'low' : ''}`}>
                                                        {product.quantity}
                                                    </span>
                                                </td>
                                                <td className="actions-col">
                                                  <button onClick={() => handleEdit(product)} className="edit-btn" title="Edit"><FaEdit/></button>
                                                  <button onClick={() => handleDelete(product.id)} className="delete-btn" title="Delete"><FaTrash/></button>
                                                </td>
                                              </tr>
                                        ))
                                      ) : (
                                        <tr><td colSpan="6" className="empty-state"><FaBoxOpen className="empty-icon"/><p>No products.</p></td></tr>
                                      )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}

            {/* --- 3. ORDERS TAB --- */}
            {activeTab === 'orders' && (
                <div className="orders-panel">
                    <div className="table-header-row"><h3>Manage Orders</h3></div>
                    <div className="admin-table-section">
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Products</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {orders.length > 0 ? orders.map(order => (
                                    <tr key={order.id}>
                                        <td className="id-col">#{order.id}</td>
                                        <td><div className="customer-info"><strong>{order.user?.name || "Guest"}</strong><span className="sub-text">{order.user?.email}</span></div></td>
                                        
                                        <td className="order-items-col">
                                            {order.items && order.items.map(item => (
                                                <div key={item.id} className="mini-order-item">
                                                    <img 
                                                        src={getImageUrl(item.product.image)} 
                                                        alt="" 
                                                        className="mini-img"
                                                        onError={(e) => {e.target.src = 'https://placehold.co/50x50?text=No+Img'}}
                                                    />
                                                    <div className="mini-info">
                                                        <span className="mini-name">{item.product.name}</span>
                                                        <span className="mini-brand">{item.product.brand}</span>
                                                        <span className="mini-qty">x{item.quantity} ({item.size})</span>
                                                    </div>
                                                </div>
                                            ))}
                                        </td>

                                        <td className="price-col">₱{Number(order.total_amount).toLocaleString()}</td>
                                        <td>
                                            <span className={`status-badge ${order.status}`}>
                                                {order.status}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="status-actions">
                                                {order.status !== 'shipped' && order.status !== 'delivered' && <button className="action-btn ship" title="Ship" onClick={() => handleStatusUpdate(order.id, 'shipped')}><FaTruck/></button>}
                                                {order.status !== 'delivered' && <button className="action-btn deliver" title="Deliver" onClick={() => handleStatusUpdate(order.id, 'delivered')}><FaCheckCircle/></button>}
                                            </div>
                                        </td>
                                    </tr>
                                )) : (
                                    <tr><td colSpan="6" className="empty-state">No orders found.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* --- 4. CUSTOMERS TAB --- */}
            {activeTab === 'customers' && (
                <div className="orders-panel">
                    <div className="table-header-row"><h3>Registered Customers</h3></div>
                    <div className="admin-table-section">
                        <table className="admin-table">
                            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                {users.map(u => (
                                    <tr key={u.id}>
                                        <td>#{u.id}</td>
                                        <td><strong>{u.name}</strong></td>
                                        <td>{u.email}</td>
                                        <td>{new Date(u.created_at).toLocaleDateString()}</td>
                                        <td>
                                            <span className={`status-badge ${u.is_blocked ? 'cancelled' : 'delivered'}`}>
                                                {u.is_blocked ? 'Blocked' : 'Active'}
                                            </span>
                                        </td>
                                        <td>
                                            <button 
                                                className={`action-btn ${u.is_blocked ? 'deliver' : 'delete-btn'}`} 
                                                title={u.is_blocked ? "Unblock User" : "Block User"}
                                                onClick={() => handleToggleBlock(u.id, u.is_blocked)}
                                                style={{width:'auto', padding:'5px 10px', gap: '5px'}}
                                            >
                                                {u.is_blocked ? <><FaUnlock/> Unblock</> : <><FaBan/> Block</>}
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

        </div>
      </div>
    </>
  );
};

export default AdminDashboard;
