import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { useLang } from '../context/LangContext';
import { HiMenu, HiX, HiMoon, HiSun, HiGlobe } from 'react-icons/hi';
import { useState } from 'react';

export default function MainLayout() {
  const { user, logout } = useAuth();
  const { darkMode, toggleDarkMode } = useTheme();
  const { t, lang, switchLang } = useLang();
  const navigate = useNavigate();
  const [menuOpen, setMenuOpen] = useState(false);

  const handleLogout = () => { logout(); navigate('/'); };

  return (
    <div className="min-h-screen flex flex-col">
      <nav className="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16 items-center">
            <Link to="/" className="text-xl font-bold text-primary-600">HanssOTP</Link>
            <div className="hidden md:flex items-center gap-4">
              {user ? (
                <>
                  <Link to="/dashboard" className="text-sm hover:text-primary-600">{t('nav_dashboard')}</Link>
                  <Link to="/deposit" className="text-sm hover:text-primary-600">{t('nav_deposit')}</Link>
                  <Link to="/order" className="text-sm hover:text-primary-600">{t('nav_order')}</Link>
                  <Link to="/orders" className="text-sm hover:text-primary-600">{t('nav_history')}</Link>
                  <Link to="/transactions" className="text-sm hover:text-primary-600">{t('nav_transactions')}</Link>
                  <Link to="/reseller-api" className="text-sm hover:text-primary-600">{t('nav_api')}</Link>
                  <Link to="/affiliate" className="text-sm hover:text-primary-600">{t('nav_affiliate')}</Link>
                  <Link to="/profile" className="text-sm hover:text-primary-600">{t('nav_profile')}</Link>
                  <span className="text-sm font-semibold text-green-600">Rp {Number(user.balance).toLocaleString('id-ID')}</span>
                  <button onClick={handleLogout} className="text-sm text-red-500 hover:text-red-700">{t('nav_logout')}</button>
                </>
              ) : (
                <>
                  <Link to="/login" className="text-sm hover:text-primary-600">{t('nav_login')}</Link>
                  <Link to="/register" className="btn-primary text-sm">{t('nav_register')}</Link>
                </>
              )}
              <button onClick={toggleDarkMode} className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                {darkMode ? <HiSun className="w-5 h-5" /> : <HiMoon className="w-5 h-5" />}
              </button>
              <button onClick={() => switchLang(lang === 'id' ? 'en' : 'id')} className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <HiGlobe className="w-5 h-5" />
              </button>
            </div>
            <button onClick={() => setMenuOpen(!menuOpen)} className="md:hidden p-2">
              {menuOpen ? <HiX className="w-6 h-6" /> : <HiMenu className="w-6 h-6" />}
            </button>
          </div>
        </div>
        {menuOpen && (
          <div className="md:hidden border-t border-gray-200 dark:border-gray-700 pb-4 px-4 space-y-2">
            {user ? (
              <>
                <Link to="/dashboard" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_dashboard')}</Link>
                <Link to="/deposit" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_deposit')}</Link>
                <Link to="/order" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_order')}</Link>
                <Link to="/orders" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_history')}</Link>
                <Link to="/transactions" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_transactions')}</Link>
                <Link to="/reseller-api" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_api')}</Link>
                <Link to="/affiliate" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_affiliate')}</Link>
                <Link to="/profile" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_profile')}</Link>
                <button onClick={handleLogout} className="block py-2 text-sm text-red-500">{t('nav_logout')}</button>
              </>
            ) : (
              <>
                <Link to="/login" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_login')}</Link>
                <Link to="/register" onClick={() => setMenuOpen(false)} className="block py-2 text-sm">{t('nav_register')}</Link>
              </>
            )}
            <div className="flex gap-2 pt-2">
              <button onClick={toggleDarkMode} className="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                {darkMode ? <HiSun className="w-5 h-5" /> : <HiMoon className="w-5 h-5" />}
              </button>
              <button onClick={() => switchLang(lang === 'id' ? 'en' : 'id')} className="p-2 rounded-lg bg-gray-100 dark:bg-gray-700">
                <HiGlobe className="w-5 h-5" />
              </button>
            </div>
          </div>
        )}
      </nav>
      <main className="flex-1">
        <Outlet />
      </main>
      <footer className="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-6 text-center text-sm text-gray-500">
        &copy; {new Date().getFullYear()} HanssOTP. All rights reserved.
      </footer>
    </div>
  );
}
