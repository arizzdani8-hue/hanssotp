import { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import { useLang } from '../context/LangContext';
import { useSocketEvent } from '../hooks/useSocket';
import StatusBadge from '../components/StatusBadge';
import toast from 'react-hot-toast';

export default function DashboardPage() {
  const { t } = useLang();
  const { refreshUser } = useAuth();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchDashboard = useCallback(async () => {
    try {
      const { data: res } = await api.get('/user/dashboard');
      setData(res.data);
    } catch (err) {
      toast.error('Gagal memuat dashboard');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchDashboard(); }, [fetchDashboard]);

  useSocketEvent('balance:update', useCallback(() => {
    fetchDashboard();
    refreshUser();
  }, [fetchDashboard, refreshUser]));

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;

  return (
    <div className="max-w-7xl mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-bold text-white">{t('nav_dashboard')}</h1>
          <p className="text-gray-500 text-sm mt-1">Selamat datang di NyooApp</p>
        </div>
        <div className="flex gap-3">
          <Link to="/deposit" className="btn-primary text-sm">{t('nav_deposit')}</Link>
          <Link to="/order" className="btn-success text-sm">{t('nav_order')}</Link>
        </div>
      </div>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">{t('balance')}</p>
          <p className="text-2xl font-bold text-emerald-400 mt-1">Rp {Number(data?.balance || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-primary-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">{t('total_orders')}</p>
          <p className="text-2xl font-bold text-white mt-1">{data?.totalOrders || 0}</p>
        </div>
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-violet-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">{t('total_deposits')}</p>
          <p className="text-2xl font-bold text-white mt-1">Rp {Number(data?.totalDeposits || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">{t('active_orders')}</p>
          <p className="text-2xl font-bold text-blue-400 mt-1">{data?.activeOrders || 0}</p>
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-6">
        <div className="card">
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-semibold text-white">Order Terbaru</h3>
            <Link to="/orders" className="text-sm text-primary-400 hover:text-primary-300">Lihat Semua</Link>
          </div>
          <div className="space-y-3">
            {data?.recentOrders?.length ? data.recentOrders.map((o) => (
              <div key={o.id} className="flex items-center justify-between py-3 border-b border-gray-800/50 last:border-0">
                <div>
                  <p className="text-sm font-medium text-gray-200">{o.service_name} - {o.country_name}</p>
                  <p className="text-xs text-gray-500 font-mono">{o.phone_number}</p>
                </div>
                <StatusBadge status={o.status} />
              </div>
            )) : <p className="text-sm text-gray-500">{t('no_data')}</p>}
          </div>
        </div>

        <div className="card">
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-semibold text-white">Transaksi Terbaru</h3>
            <Link to="/transactions" className="text-sm text-primary-400 hover:text-primary-300">Lihat Semua</Link>
          </div>
          <div className="space-y-3">
            {data?.recentTransactions?.length ? data.recentTransactions.map((tx) => (
              <div key={tx.id} className="flex items-center justify-between py-3 border-b border-gray-800/50 last:border-0">
                <div>
                  <p className="text-sm font-medium text-gray-200">{tx.description}</p>
                  <p className="text-xs text-gray-500">{new Date(tx.created_at).toLocaleString('id-ID')}</p>
                </div>
                <p className={`text-sm font-semibold ${parseFloat(tx.amount) >= 0 ? 'text-emerald-400' : 'text-red-400'}`}>
                  {parseFloat(tx.amount) >= 0 ? '+' : ''}Rp {Math.abs(parseFloat(tx.amount)).toLocaleString('id-ID')}
                </p>
              </div>
            )) : <p className="text-sm text-gray-500">{t('no_data')}</p>}
          </div>
        </div>
      </div>
    </div>
  );
}
