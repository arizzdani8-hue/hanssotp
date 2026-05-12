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
  const [gateway, setGateway] = useState('tripay');
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
      const { data } = await api.post('/deposits/create', { amount: amt, gateway });
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
      <h1 className="text-2xl font-bold mb-6">{t('deposit_title')}</h1>

      {!deposit ? (
        <div className="card">
          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="label">Jumlah (Rp)</label>
              <input type="number" className="input-field" value={amount} onChange={(e) => setAmount(e.target.value)}
                min="10000" max="10000000" placeholder="Masukkan jumlah deposit" required />
            </div>
            <div className="flex flex-wrap gap-2">
              {presets.map((p) => (
                <button key={p} type="button" onClick={() => setAmount(String(p))}
                  className={`px-3 py-1.5 rounded-lg text-sm border ${amount === String(p) ? 'bg-primary-600 text-white border-primary-600' : 'border-gray-300 dark:border-gray-600'}`}>
                  Rp {p.toLocaleString('id-ID')}
                </button>
              ))}
            </div>
            <div>
              <label className="label">Payment Gateway</label>
              <select className="input-field" value={gateway} onChange={(e) => setGateway(e.target.value)}>
                <option value="tripay">Tripay (QRIS)</option>
                <option value="qrispy">QRISPY (QRIS)</option>
              </select>
            </div>
            <button type="submit" disabled={loading} className="btn-primary w-full">
              {loading ? t('loading') : 'Buat Deposit'}
            </button>
          </form>
        </div>
      ) : (
        <div className="card text-center space-y-4">
          <h3 className="text-lg font-semibold">Detail Deposit</h3>
          <div className="space-y-2">
            <p>Reference: <span className="font-mono">{deposit.reference}</span></p>
            <p>Jumlah: <span className="font-bold">Rp {Number(deposit.amount).toLocaleString('id-ID')}</span></p>
            {deposit.fee > 0 && <p>Fee: Rp {Number(deposit.fee).toLocaleString('id-ID')}</p>}
            <p>Total: <span className="font-bold text-lg">Rp {Number(deposit.total_amount).toLocaleString('id-ID')}</span></p>
            <p>Status: <StatusBadge status={deposit.status} /></p>
          </div>

          {deposit.qr_url && deposit.status === 'pending' && (
            <div className="py-4">
              <p className="text-sm text-gray-500 mb-2">Scan QRIS untuk membayar:</p>
              <img src={deposit.qr_url} alt="QRIS" className="mx-auto max-w-xs rounded-lg" />
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
