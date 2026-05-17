import { useState, useEffect } from 'react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import { useLang } from '../context/LangContext';
import toast from 'react-hot-toast';

export default function ProfilePage() {
  const { user, refreshUser } = useAuth();
  const { lang, switchLang } = useLang();
  const [profile, setProfile] = useState(null);
  const [phone, setPhone] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/user/profile').then(({ data }) => {
      setProfile(data.data.user);
      setPhone(data.data.user.phone || '');
    }).finally(() => setLoading(false));
  }, []);

  const handleUpdate = async (e) => {
    e.preventDefault();
    try {
      await api.put('/user/profile', { phone, language: lang });
      toast.success('Profil diperbarui');
      refreshUser();
    } catch (err) {
      toast.error('Gagal update profil');
    }
  };

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;

  return (
    <div className="max-w-2xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-white mb-6">Profil</h1>

      <div className="card mb-6">
        <div className="space-y-3">
          <div className="flex justify-between"><span className="text-gray-400">Username</span><span className="font-medium text-gray-200">{profile?.username}</span></div>
          <div className="flex justify-between"><span className="text-gray-400">Email</span><span className="font-medium text-gray-200">{profile?.email}</span></div>
          <div className="flex justify-between"><span className="text-gray-400">Saldo</span><span className="font-bold text-emerald-400">Rp {Number(profile?.balance || 0).toLocaleString('id-ID')}</span></div>
          <div className="flex justify-between"><span className="text-gray-400">Referral Code</span><span className="font-mono text-primary-400">{profile?.referral_code}</span></div>
          <div className="flex justify-between"><span className="text-gray-400">Member Since</span><span className="text-gray-300">{new Date(profile?.created_at).toLocaleDateString('id-ID')}</span></div>
        </div>
      </div>

      <div className="card">
        <h3 className="font-semibold text-white mb-4">Edit Profil</h3>
        <form onSubmit={handleUpdate} className="space-y-4">
          <div>
            <label className="label">Nomor Telepon</label>
            <input type="text" className="input-field" value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="+62xxx" />
          </div>
          <div>
            <label className="label">Bahasa</label>
            <select className="input-field" value={lang} onChange={(e) => switchLang(e.target.value)}>
              <option value="id">Bahasa Indonesia</option>
              <option value="en">English</option>
            </select>
          </div>
          <button type="submit" className="btn-primary">Simpan</button>
        </form>
      </div>
    </div>
  );
}
