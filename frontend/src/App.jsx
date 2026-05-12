import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { Toaster } from 'react-hot-toast';
import { AuthProvider } from './context/AuthContext';
import { AdminAuthProvider } from './context/AdminAuthContext';
import { ThemeProvider } from './context/ThemeContext';
import { LangProvider } from './context/LangContext';
import MainLayout from './layouts/MainLayout';
import AdminLayout from './layouts/AdminLayout';
import ProtectedRoute from './components/ProtectedRoute';
import AdminProtectedRoute from './components/AdminProtectedRoute';

import LandingPage from './pages/LandingPage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import DashboardPage from './pages/DashboardPage';
import DepositPage from './pages/DepositPage';
import OrderOtpPage from './pages/OrderOtpPage';
import OrderHistoryPage from './pages/OrderHistoryPage';
import TransactionsPage from './pages/TransactionsPage';
import ResellerApiPage from './pages/ResellerApiPage';
import AffiliatePage from './pages/AffiliatePage';
import ProfilePage from './pages/ProfilePage';

import AdminLoginPage from './pages/admin/AdminLoginPage';
import AdminDashboardPage from './pages/admin/AdminDashboardPage';
import AdminUsersPage from './pages/admin/AdminUsersPage';
import AdminServicesPage from './pages/admin/AdminServicesPage';
import AdminOrdersPage from './pages/admin/AdminOrdersPage';
import AdminDepositsPage from './pages/admin/AdminDepositsPage';
import AdminSettingsPage from './pages/admin/AdminSettingsPage';

export default function App() {
  return (
    <ThemeProvider>
      <LangProvider>
        <AuthProvider>
          <AdminAuthProvider>
            <BrowserRouter>
              <Toaster position="top-right" toastOptions={{ duration: 3000 }} />
              <Routes>
                {/* Public + User routes */}
                <Route element={<MainLayout />}>
                  <Route path="/" element={<LandingPage />} />
                  <Route path="/login" element={<LoginPage />} />
                  <Route path="/register" element={<RegisterPage />} />
                  <Route path="/dashboard" element={<ProtectedRoute><DashboardPage /></ProtectedRoute>} />
                  <Route path="/deposit" element={<ProtectedRoute><DepositPage /></ProtectedRoute>} />
                  <Route path="/order" element={<ProtectedRoute><OrderOtpPage /></ProtectedRoute>} />
                  <Route path="/orders" element={<ProtectedRoute><OrderHistoryPage /></ProtectedRoute>} />
                  <Route path="/transactions" element={<ProtectedRoute><TransactionsPage /></ProtectedRoute>} />
                  <Route path="/reseller-api" element={<ProtectedRoute><ResellerApiPage /></ProtectedRoute>} />
                  <Route path="/affiliate" element={<ProtectedRoute><AffiliatePage /></ProtectedRoute>} />
                  <Route path="/profile" element={<ProtectedRoute><ProfilePage /></ProtectedRoute>} />
                </Route>

                {/* Admin routes */}
                <Route path="/admin/login" element={<AdminLoginPage />} />
                <Route path="/admin" element={<AdminProtectedRoute><AdminLayout /></AdminProtectedRoute>}>
                  <Route index element={<AdminDashboardPage />} />
                  <Route path="users" element={<AdminUsersPage />} />
                  <Route path="services" element={<AdminServicesPage />} />
                  <Route path="orders" element={<AdminOrdersPage />} />
                  <Route path="deposits" element={<AdminDepositsPage />} />
                  <Route path="settings" element={<AdminSettingsPage />} />
                </Route>
              </Routes>
            </BrowserRouter>
          </AdminAuthProvider>
        </AuthProvider>
      </LangProvider>
    </ThemeProvider>
  );
}
