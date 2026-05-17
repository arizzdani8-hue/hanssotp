import { useState, useEffect } from 'react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import toast from 'react-hot-toast';

export default function AffiliatePage() {
  const { user } = useAuth();
  const [affiliate, setAffiliate] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get('/user/profile').then(({ data }) => {
      setAffiliate(data.data.affiliate);
    }).finally(() => setLoading(false));
  }, []);

  const referralLink = `${window.location.origin}/register?ref=${user?.referral_code}`;

  const copyLink = () => {
    navigator.clipboard.writeText(referralLink);
    toast.success('Link referral disalin!');
  };

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-white mb-6">Affiliate / Referral</h1>

      <div className="grid md:grid-cols-3 gap-4 mb-6">
        <div className="stat-card text-center">
          <div className="absolute top-0 right-0 w-20 h-20 bg-primary-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Total Referral</p>
          <p className="text-2xl font-bold text-white mt-1">{affiliate?.total_referrals || 0}</p>
        </div>
        <div className="stat-card text-center">
          <div className="absolute top-0 right-0 w-20 h-20 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Total Komisi</p>
          <p className="text-2xl font-bold text-emerald-400 mt-1">Rp {Number(affiliate?.total_commission || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="stat-card text-center">
          <div className="absolute top-0 right-0 w-20 h-20 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Rate Komisi</p>
          <p className="text-lg font-bold text-white mt-1">
            Deposit: {affiliate?.commission_rate_deposit || 0}% | Order: {affiliate?.commission_rate_order || 0}%
          </p>
        </div>
      </div>

      <div className="card mb-6">
        <h3 className="font-semibold text-white mb-3">Link Referral Anda</h3>
        <div className="flex items-center gap-3">
          <input type="text" readOnly value={referralLink} className="input-field flex-1 text-sm" />
          <button onClick={copyLink} className="btn-primary text-sm">Copy</button>
        </div>
        <p className="text-xs text-gray-500 mt-2">Kode referral: <strong className="text-primary-400">{user?.referral_code}</strong></p>
      </div>

      <div className="card">
        <h3 className="font-semibold text-white mb-3">Cara Kerja</h3>
        <ul className="text-sm text-gray-400 space-y-2 list-disc list-inside">
          <li>Bagikan link referral Anda ke teman.</li>
          <li>Ketika teman mendaftar menggunakan link Anda, mereka menjadi referral Anda.</li>
          <li>Setiap kali referral melakukan deposit, Anda mendapat komisi {affiliate?.commission_rate_deposit || 0}%.</li>
          <li>Setiap kali referral melakukan order OTP, Anda mendapat komisi {affiliate?.commission_rate_order || 0}%.</li>
          <li>Komisi otomatis ditambahkan ke saldo Anda.</li>
        </ul>
      </div>
    </div>
  );
}
