import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAdminAuth } from '../../context/AdminAuthContext';
import toast from 'react-hot-toast';

export default function AdminLoginPage() {
  const { login } = useAdminAuth();
  const navigate = useNavigate();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await login(username, password);
      toast.success('Login admin berhasil');
      navigate('/admin');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Login gagal');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4">
      <div className="card w-full max-w-md">
        <h2 className="text-2xl font-bold text-center mb-6">Admin Login</h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="label">Username</label>
            <input type="text" className="input-field" value={username} onChange={(e) => setUsername(e.target.value)} required />
          </div>
          <div>
            <label className="label">Password</label>
            <input type="password" className="input-field" value={password} onChange={(e) => setPassword(e.target.value)} required />
          </div>
          <button type="submit" disabled={loading} className="btn-primary w-full">
            {loading ? 'Loading...' : 'Login'}
          </button>
        </form>
      </div>
    </div>
  );
}
