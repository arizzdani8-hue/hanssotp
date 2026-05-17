import { Link, Outlet, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { useLang } from '../context/LangContext';
import { HiMenu, HiX, HiGlobe } from 'react-icons/hi';
import { useState } from 'react';

export default function MainLayout() {
  const { user, logout } = useAuth();
  const { darkMode } = useTheme();
  const { t, lang, switchLang } = useLang();
  const navigate = useNavigate();
  const location = useLocation();
  const [menuOpen, setMenuOpen] = useState(false);
  const isLanding = location.pathname === '/';

  const handleLogout = () => { logout(); navigate('/'); };

  const navLinks = user ? [
    { to: '/dashboard', label: t('nav_dashboard') },
    { to: '/deposit', label: t('nav_deposit') },
    { to: '/order', label: t('nav_order') },
    { to: '/orders', label: t('nav_history') },
    { to: '/transactions', label: t('nav_transactions') },
    { to: '/profile', label: t('nav_profile') },
  ] : [];

  return (
    <div className="min-h-screen flex flex-col bg-dark-950">
      <nav className={`sticky top-0 z-50 transition-all duration-300 ${isLanding ? 'bg-transparent' : 'bg-dark-900/80 backdrop-blur-xl border-b border-gray-800/50'}`}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16 items-center">
            <Link to="/" className="flex items-center gap-2">
              <div className="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-sm">N</span>
              </div>
              <span className="text-xl font-bold bg-gradient-to-r from-primary-400 to-primary-300 bg-clip-text text-transparent">NyooApp</span>
            </Link>
            <div className="hidden md:flex items-center gap-1">
              {navLinks.map((link) => (
                <Link key={link.to} to={link.to}
                  className={`px-3 py-2 rounded-lg text-sm font-medium transition-all ${location.pathname === link.to ? 'bg-primary-600/20 text-primary-400' : 'text-gray-400 hover:text-white hover:bg-white/5'}`}>
                  {link.label}
                </Link>
              ))}
              {user ? (
                <>
                  <div className="ml-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                    <span className="text-sm font-semibold text-emerald-400">Rp {Number(user.balance).toLocaleString('id-ID')}</span>
                  </div>
                  <button onClick={handleLogout} className="ml-1 px-3 py-2 text-sm text-gray-400 hover:text-red-400 transition-colors">{t('nav_logout')}</button>
                </>
              ) : (
                <>
                  <Link to="/login" className="px-4 py-2 text-sm text-gray-300 hover:text-white transition-colors">{t('nav_login')}</Link>
                  <Link to="/register" className="btn-primary text-sm !py-2 !px-5">{t('nav_register')}</Link>
                </>
              )}
              <button onClick={() => switchLang(lang === 'id' ? 'en' : 'id')} className="ml-1 p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                <HiGlobe className="w-5 h-5" />
              </button>
            </div>
            <button onClick={() => setMenuOpen(!menuOpen)} className="md:hidden p-2 text-gray-400">
              {menuOpen ? <HiX className="w-6 h-6" /> : <HiMenu className="w-6 h-6" />}
            </button>
          </div>
        </div>
        {menuOpen && (
          <div className="md:hidden bg-dark-900/95 backdrop-blur-xl border-t border-gray-800/50 pb-4 px-4 space-y-1">
            {navLinks.map((link) => (
              <Link key={link.to} to={link.to} onClick={() => setMenuOpen(false)}
                className={`block py-2.5 px-3 rounded-lg text-sm ${location.pathname === link.to ? 'bg-primary-600/20 text-primary-400' : 'text-gray-400'}`}>
                {link.label}
              </Link>
            ))}
            {user ? (
              <>
                <div className="px-3 py-2 text-sm text-emerald-400 font-semibold">Rp {Number(user.balance).toLocaleString('id-ID')}</div>
                <button onClick={handleLogout} className="block py-2.5 px-3 text-sm text-red-400">{t('nav_logout')}</button>
              </>
            ) : (
              <>
                <Link to="/login" onClick={() => setMenuOpen(false)} className="block py-2.5 px-3 text-sm text-gray-400">{t('nav_login')}</Link>
                <Link to="/register" onClick={() => setMenuOpen(false)} className="block py-2.5 px-3 text-sm text-primary-400">{t('nav_register')}</Link>
              </>
            )}
          </div>
        )}
      </nav>
      <main className="flex-1">
        <Outlet />
      </main>
      <footer className="border-t border-gray-800/50 py-8 mt-auto">
        <div className="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex items-center gap-2">
            <div className="w-6 h-6 bg-gradient-to-br from-primary-500 to-primary-700 rounded-md flex items-center justify-center">
              <span className="text-white font-bold text-xs">N</span>
            </div>
            <span className="text-sm text-gray-500">&copy; {new Date().getFullYear()} NyooApp. All rights reserved.</span>
          </div>
          <div className="flex gap-6 text-sm text-gray-500">
            <a href="#" className="hover:text-gray-300 transition-colors">Terms</a>
            <a href="#" className="hover:text-gray-300 transition-colors">Privacy</a>
            <a href="#" className="hover:text-gray-300 transition-colors">Contact</a>
          </div>
        </div>
      </footer>
    </div>
  );
}
