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
      <h1 className="text-2xl font-bold mb-6">{t('nav_dashboard')}</h1>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div className="card text-center">
          <p className="text-sm text-gray-500 dark:text-gray-400">{t('balance')}</p>
          <p className="text-2xl font-bold text-green-600">Rp {Number(data?.balance || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="card text-center">
          <p className="text-sm text-gray-500 dark:text-gray-400">{t('total_orders')}</p>
          <p className="text-2xl font-bold">{data?.totalOrders || 0}</p>
        </div>
        <div className="card text-center">
          <p className="text-sm text-gray-500 dark:text-gray-400">{t('total_deposits')}</p>
          <p className="text-2xl font-bold">Rp {Number(data?.totalDeposits || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="card text-center">
          <p className="text-sm text-gray-500 dark:text-gray-400">{t('active_orders')}</p>
          <p className="text-2xl font-bold text-blue-600">{data?.activeOrders || 0}</p>
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-6">
        <div className="card">
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-semibold">Order Terbaru</h3>
            <Link to="/orders" className="text-sm text-primary-600 hover:underline">Lihat Semua</Link>
          </div>
          <div className="space-y-3">
            {data?.recentOrders?.length ? data.recentOrders.map((o) => (
              <div key={o.id} className="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                  <p className="text-sm font-medium">{o.service_name} - {o.country_name}</p>
                  <p className="text-xs text-gray-500">{o.phone_number}</p>
                </div>
                <StatusBadge status={o.status} />
              </div>
            )) : <p className="text-sm text-gray-500">{t('no_data')}</p>}
          </div>
        </div>

        <div className="card">
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-semibold">Transaksi Terbaru</h3>
            <Link to="/transactions" className="text-sm text-primary-600 hover:underline">Lihat Semua</Link>
          </div>
          <div className="space-y-3">
            {data?.recentTransactions?.length ? data.recentTransactions.map((tx) => (
              <div key={tx.id} className="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                  <p className="text-sm font-medium">{tx.description}</p>
                  <p className="text-xs text-gray-500">{new Date(tx.created_at).toLocaleString('id-ID')}</p>
                </div>
                <p className={`text-sm font-semibold ${parseFloat(tx.amount) >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {parseFloat(tx.amount) >= 0 ? '+' : ''}Rp {Math.abs(parseFloat(tx.amount)).toLocaleString('id-ID')}
                </p>
              </div>
            )) : <p className="text-sm text-gray-500">{t('no_data')}</p>}
          </div>
        </div>
      </div>

      <div className="mt-8 flex gap-4">
        <Link to="/deposit" className="btn-primary">{t('nav_deposit')}</Link>
        <Link to="/order" className="btn-success">{t('nav_order')}</Link>
      </div>
    </div>
  );
}
