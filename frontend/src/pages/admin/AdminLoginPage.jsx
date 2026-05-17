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
    <div className="min-h-screen flex items-center justify-center bg-dark-950 px-4 relative">
      <div className="absolute inset-0 bg-gradient-to-br from-dark-950 via-primary-950/20 to-dark-950" />
      <div className="absolute top-1/3 right-1/4 w-72 h-72 bg-primary-600/10 rounded-full blur-3xl" />
      <div className="relative w-full max-w-md">
        <div className="bg-dark-800/80 backdrop-blur-xl rounded-2xl border border-gray-700/50 p-8 shadow-2xl">
          <div className="text-center mb-8">
            <div className="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary-500 to-violet-600 rounded-2xl mb-4">
              <span className="text-white font-bold text-xl">N</span>
            </div>
            <h2 className="text-2xl font-bold text-white">Admin Login</h2>
            <p className="text-gray-500 text-sm mt-1">Masuk ke panel admin</p>
          </div>
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="label">Username</label>
              <input type="text" className="input-field" placeholder="admin" value={username} onChange={(e) => setUsername(e.target.value)} required />
            </div>
            <div>
              <label className="label">Password</label>
              <input type="password" className="input-field" placeholder="Masukkan password" value={password} onChange={(e) => setPassword(e.target.value)} required />
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full !py-3 text-base">
              {loading ? 'Loading...' : 'Login'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}
