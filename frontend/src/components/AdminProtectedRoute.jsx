import { Navigate } from 'react-router-dom';
import { useAdminAuth } from '../context/AdminAuthContext';

export default function AdminProtectedRoute({ children }) {
  const { admin, loading } = useAdminAuth();
  if (loading) return <div className="flex justify-center items-center min-h-screen"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;
  if (!admin) return <Navigate to="/admin/login" replace />;
  return children;
}
