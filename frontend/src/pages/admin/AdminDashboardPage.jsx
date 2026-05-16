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
      <h1 className="text-2xl font-bold mb-6">Dashboard</h1>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div className="card text-center">
          <p className="text-sm text-gray-500">Total Users</p>
          <p className="text-2xl font-bold">{data?.totalUsers || 0}</p>
        </div>
        <div className="card text-center">
          <p className="text-sm text-gray-500">Total Deposits</p>
          <p className="text-2xl font-bold text-green-600">Rp {Number(data?.totalDeposits || 0).toLocaleString('id-ID')}</p>
        </div>
        <div className="card text-center">
          <p className="text-sm text-gray-500">Total Orders</p>
          <p className="text-2xl font-bold">{data?.totalOrders || 0}</p>
        </div>
        <div className="card text-center">
          <p className="text-sm text-gray-500">Profit</p>
          <p className="text-2xl font-bold text-blue-600">Rp {Number(data?.profit || 0).toLocaleString('id-ID')}</p>
        </div>
      </div>

      <div className="grid md:grid-cols-2 gap-6">
        <div className="card">
          <h3 className="font-semibold mb-4">Recent Orders</h3>
          <div className="space-y-2">
            {data?.recentOrders?.map((o) => (
              <div key={o.id} className="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                  <p className="text-sm font-medium">{o.username} - {o.service_name}</p>
                  <p className="text-xs text-gray-500">{o.phone_number}</p>
                </div>
                <StatusBadge status={o.status} />
              </div>
            ))}
          </div>
        </div>
        <div className="card">
          <h3 className="font-semibold mb-4">Recent Deposits</h3>
          <div className="space-y-2">
            {data?.recentDeposits?.map((d) => (
              <div key={d.id} className="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <div>
                  <p className="text-sm font-medium">{d.username}</p>
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
