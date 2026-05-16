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
    <div className="min-h-screen flex bg-gray-100 dark:bg-gray-900">
      <aside className={`fixed inset-y-0 left-0 z-50 w-64 bg-gray-800 text-white transform ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} md:translate-x-0 md:static transition-transform duration-200`}>
        <div className="p-4 border-b border-gray-700">
          <h1 className="text-lg font-bold">NyooApp Admin</h1>
        </div>
        <nav className="p-4 space-y-1">
          {menuItems.map((item) => (
            <Link key={item.to} to={item.to} onClick={() => setSidebarOpen(false)}
              className="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors text-sm">
              <item.icon className="w-5 h-5" />
              {item.label}
            </Link>
          ))}
          <button onClick={handleLogout}
            className="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-700 transition-colors text-sm w-full text-left text-red-400">
            <HiLogout className="w-5 h-5" />
            Logout
          </button>
        </nav>
      </aside>
      {sidebarOpen && <div className="fixed inset-0 bg-black/50 z-40 md:hidden" onClick={() => setSidebarOpen(false)} />}
      <div className="flex-1 flex flex-col min-w-0">
        <header className="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16 flex items-center px-4 md:px-6">
          <button onClick={() => setSidebarOpen(true)} className="md:hidden p-2 mr-3">
            <HiMenu className="w-6 h-6" />
          </button>
          <h2 className="text-lg font-semibold">Admin Panel</h2>
        </header>
        <main className="flex-1 p-4 md:p-6 overflow-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
