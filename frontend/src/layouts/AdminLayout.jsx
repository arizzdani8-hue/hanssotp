import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAdminAuth } from '../context/AdminAuthContext';
import { HiHome, HiUsers, HiCollection, HiCreditCard, HiShoppingCart, HiCog, HiLogout, HiMenu, HiX } from 'react-icons/hi';
import { useState } from 'react';

const menuItems = [
  { to: '/admin', icon: HiHome, label: 'Dashboard' },
  { to: '/admin/users', icon: HiUsers, label: 'Users' },
  { to: '/admin/services', icon: HiCollection, label: 'Services' },
  { to: '/admin/orders', icon: HiShoppingCart, label: 'Orders' },
  { to: '/admin/deposits', icon: HiCreditCard, label: 'Deposits' },
  { to: '/admin/settings', icon: HiCog, label: 'Settings' },
];

export default function AdminLayout() {
  const { logout } = useAdminAuth();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = () => { logout(); navigate('/admin/login'); };

  return (
    <div className="min-h-screen flex bg-dark-950">
      <aside className={`fixed inset-y-0 left-0 z-50 w-64 bg-dark-900 border-r border-gray-800/50 text-white transform ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} md:translate-x-0 md:static transition-transform duration-200`}>
        <div className="p-5 border-b border-gray-800/50">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-sm">N</span>
            </div>
            <span className="text-lg font-bold bg-gradient-to-r from-primary-400 to-primary-300 bg-clip-text text-transparent">Admin</span>
          </div>
        </div>
        <nav className="p-3 space-y-1">
          {menuItems.map((item) => (
            <Link key={item.to} to={item.to} onClick={() => setSidebarOpen(false)}
              className="flex items-center gap-3 px-3 py-2.5 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all text-sm font-medium">
              <item.icon className="w-5 h-5" />
              {item.label}
            </Link>
          ))}
          <div className="pt-4 mt-4 border-t border-gray-800/50">
            <button onClick={handleLogout}
              className="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-red-500/10 transition-all text-sm w-full text-left text-red-400">
              <HiLogout className="w-5 h-5" />
              Logout
            </button>
          </div>
        </nav>
      </aside>
      {sidebarOpen && <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden" onClick={() => setSidebarOpen(false)} />}
      <div className="flex-1 flex flex-col min-w-0">
        <header className="bg-dark-900/80 backdrop-blur-xl border-b border-gray-800/50 h-16 flex items-center px-4 md:px-6 sticky top-0 z-30">
          <button onClick={() => setSidebarOpen(true)} className="md:hidden p-2 mr-3 text-gray-400">
            <HiMenu className="w-6 h-6" />
          </button>
          <h2 className="text-lg font-semibold text-white">Admin Panel</h2>
        </header>
        <main className="flex-1 p-4 md:p-6 overflow-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
