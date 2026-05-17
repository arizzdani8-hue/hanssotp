import { useState } from 'react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import { useLang } from '../context/LangContext';
import { useSocketEvent } from '../hooks/useSocket';
import StatusBadge from '../components/StatusBadge';
import toast from 'react-hot-toast';

export default function DepositPage() {
  const { t, lang } = useLang();
  const { refreshUser } = useAuth();
  const [amount, setAmount] = useState('');
  const [loading, setLoading] = useState(false);
  const [deposit, setDeposit] = useState(null);

  const presets = [10000, 25000, 50000, 100000, 250000, 500000];

  useSocketEvent('deposit:update', (data) => {
    if (deposit && data.depositId === deposit.id) {
      setDeposit({ ...deposit, status: data.status || 'paid' });
      refreshUser();
      toast.success('Deposit berhasil!');
    }
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    const amt = parseInt(amount);
    if (!amt || amt < 10000) { toast.error('Minimum deposit Rp 10.000'); return; }
    setLoading(true);
    try {
      const { data } = await api.post('/deposits/create', { amount: amt });
      setDeposit(data.data);
      toast.success('Deposit dibuat! Silakan scan QRIS.');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal membuat deposit');
    } finally {
      setLoading(false);
    }
  };

  const checkStatus = async () => {
    if (!deposit) return;
    try {
      const { data } = await api.get(`/deposits/${deposit.id}`);
      setDeposit(data.data);
      if (data.data.status === 'paid') {
        toast.success('Pembayaran berhasil!');
        refreshUser();
      }
    } catch (err) {
      toast.error('Gagal cek status');
    }
  };

  return (
    <div className="max-w-2xl mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-white">{t('deposit_title')}</h1>
        <p className="text-gray-500 text-sm mt-1">Top up saldo via QRIS</p>
      </div>

      {!deposit ? (
        <div className="card">
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="label">Jumlah (Rp)</label>
              <input type="number" className="input-field" value={amount} onChange={(e) => setAmount(e.target.value)}
                min="10000" max="10000000" placeholder="Masukkan jumlah deposit" required />
            </div>
            <div className="flex flex-wrap gap-2">
              {presets.map((p) => (
                <button key={p} type="button" onClick={() => setAmount(String(p))}
                  className={`px-4 py-2 rounded-xl text-sm font-medium border transition-all ${amount === String(p) ? 'bg-primary-600/20 text-primary-400 border-primary-500/50' : 'border-gray-700/50 text-gray-400 hover:border-gray-600'}`}>
                  Rp {p.toLocaleString('id-ID')}
                </button>
              ))}
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full !py-3">
              {loading ? t('loading') : 'Buat Deposit'}
            </button>
          </form>
        </div>
      ) : (
        <div className="card text-center space-y-5">
          <h3 className="text-lg font-semibold text-white">Detail Deposit</h3>
          <div className="bg-dark-900/50 rounded-xl p-5 space-y-3">
            <div className="flex justify-between text-sm">
              <span className="text-gray-400">Reference</span>
              <span className="font-mono text-gray-200">{deposit.reference}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-gray-400">Jumlah</span>
              <span className="font-bold text-white">Rp {Number(deposit.amount).toLocaleString('id-ID')}</span>
            </div>
            {deposit.fee > 0 && (
              <div className="flex justify-between text-sm">
                <span className="text-gray-400">Fee</span>
                <span className="text-gray-300">Rp {Number(deposit.fee).toLocaleString('id-ID')}</span>
              </div>
            )}
            <div className="flex justify-between text-sm border-t border-gray-800 pt-3">
              <span className="text-gray-400">Total</span>
              <span className="font-bold text-lg text-primary-400">Rp {Number(deposit.total_amount).toLocaleString('id-ID')}</span>
            </div>
            <div className="flex justify-between text-sm items-center">
              <span className="text-gray-400">Status</span>
              <StatusBadge status={deposit.status} />
            </div>
          </div>

          {deposit.qr_url && deposit.status === 'pending' && (
            <div className="py-4">
              <p className="text-sm text-gray-500 mb-3">Scan QRIS untuk membayar:</p>
              <div className="bg-white rounded-2xl p-4 inline-block">
                <img src={deposit.qr_url} alt="QRIS" className="mx-auto max-w-xs rounded-lg" />
              </div>
            </div>
          )}

          {deposit.checkout_url && deposit.status === 'pending' && (
            <a href={deposit.checkout_url} target="_blank" rel="noopener noreferrer" className="btn-primary inline-block">
              Buka Halaman Pembayaran
            </a>
          )}

          <div className="flex gap-3 justify-center">
            {deposit.status === 'pending' && (
              <button onClick={checkStatus} className="btn-secondary">Cek Status</button>
            )}
            <button onClick={() => setDeposit(null)} className="btn-secondary">Deposit Baru</button>
          </div>
        </div>
      )}
    </div>
  );
}
