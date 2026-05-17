import { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useLang } from '../context/LangContext';
import toast from 'react-hot-toast';

export default function RegisterPage() {
  const { register } = useAuth();
  const { t } = useLang();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const [form, setForm] = useState({ username: '', email: '', password: '', referral_code: searchParams.get('ref') || '' });
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      await register(form.username, form.email, form.password, form.referral_code);
      toast.success('Registrasi berhasil!');
      navigate('/dashboard');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Registrasi gagal');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4 relative">
      <div className="absolute inset-0 bg-gradient-to-br from-dark-950 via-primary-950/20 to-dark-950" />
      <div className="absolute bottom-1/3 right-1/4 w-72 h-72 bg-violet-600/10 rounded-full blur-3xl" />
      <div className="relative w-full max-w-md">
        <div className="bg-dark-800/80 backdrop-blur-xl rounded-2xl border border-gray-700/50 p-8 shadow-2xl">
          <div className="text-center mb-8">
            <div className="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-primary-500 to-violet-600 rounded-2xl mb-4">
              <span className="text-white font-bold text-xl">N</span>
            </div>
            <h2 className="text-2xl font-bold text-white">{t('nav_register')}</h2>
            <p className="text-gray-500 text-sm mt-1">Buat akun NyooApp gratis</p>
          </div>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="label">Username</label>
              <input type="text" className="input-field" placeholder="Masukkan username" value={form.username} onChange={(e) => setForm({ ...form, username: e.target.value })} required minLength={3} />
            </div>
            <div>
              <label className="label">Email</label>
              <input type="email" className="input-field" placeholder="nama@email.com" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
            </div>
            <div>
              <label className="label">Password</label>
              <input type="password" className="input-field" placeholder="Minimal 6 karakter" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required minLength={6} />
            </div>
            <div>
              <label className="label">Kode Referral (opsional)</label>
              <input type="text" className="input-field" placeholder="Masukkan kode referral" value={form.referral_code} onChange={(e) => setForm({ ...form, referral_code: e.target.value })} />
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full !py-3 text-base">
              {loading ? t('loading') : t('nav_register')}
            </button>
          </form>
          <p className="text-center mt-6 text-sm text-gray-500">
            Sudah punya akun? <Link to="/login" className="text-primary-400 hover:text-primary-300 font-medium">{t('nav_login')}</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
