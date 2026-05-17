import { useState, useEffect } from 'react';
import { adminApi } from '../../services/api';
import StatusBadge from '../../components/StatusBadge';

export default function AdminDashboardPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminApi.get('/admin/dashboard').then(({ data }) => {
      setData(data.data);
    }).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;

  return (
    <div>
      <h1 className="text-2xl font-bold text-white mb-6">Dashboard</h1>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-primary-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Total Users</p>
          <p className="text-2xl font-bold text-white mt-1">{data?.totalUsers || 0}</p>
        </div>
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Total Deposits</p>
          <p className="text-2xl font-bold text-emerald-400 mt-1">Rp {Number(data?.totalDeposits || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-violet-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Total Orders</p>
          <p className="text-2xl font-bold text-white mt-1">{data?.totalOrders || 0}</p>
        </div>
        <div className="stat-card">
          <div className="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-full -translate-y-1/2 translate-x-1/2" />
          <p className="text-sm text-gray-400">Profit</p>
          <p className="text-2xl font-bold text-blue-400 mt-1">Rp {Number(data?.profit || 0).toLocaleString('id-ID')}</p>
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-6">
        <div className="card">
          <h3 className="font-semibold text-white mb-4">Recent Orders</h3>
          <div className="space-y-2">
            {data?.recentOrders?.map((o) => (
              <div key={o.id} className="flex justify-between items-center py-3 border-b border-gray-800/50 last:border-0">
                <div>
                  <p className="text-sm font-medium text-gray-200">{o.username} - {o.service_name}</p>
                  <p className="text-xs text-gray-500 font-mono">{o.phone_number}</p>
                </div>
                <StatusBadge status={o.status} />
              </div>
            ))}
          </div>
        </div>
        <div className="card">
          <h3 className="font-semibold text-white mb-4">Recent Deposits</h3>
          <div className="space-y-2">
            {data?.recentDeposits?.map((d) => (
              <div key={d.id} className="flex justify-between items-center py-3 border-b border-gray-800/50 last:border-0">
                <div>
                  <p className="text-sm font-medium text-gray-200">{d.username}</p>
                  <p className="text-xs text-gray-500">Rp {Number(d.amount).toLocaleString('id-ID')}</p>
                </div>
                <StatusBadge status={d.status} />
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
