import { useState, useEffect } from 'react';
import { adminApi } from '../../services/api';
import StatusBadge from '../../components/StatusBadge';
import Pagination from '../../components/Pagination';
import toast from 'react-hot-toast';

export default function AdminOrdersPage() {
  const [orders, setOrders] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const limit = 20;

  const fetchOrders = () => {
    let url = `/admin/orders?page=${page}&limit=${limit}`;
    if (status) url += `&status=${status}`;
    adminApi.get(url).then(({ data }) => {
      setOrders(data.data.orders);
      setTotal(data.data.total);
    });
  };

  useEffect(fetchOrders, [page, status]);

  const refund = async (orderId) => {
    const reason = prompt('Alasan refund:');
    if (!reason) return;
    try {
      await adminApi.post('/admin/refund', { order_id: orderId, reason });
      toast.success('Refund berhasil');
      fetchOrders();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Refund gagal');
    }
  };

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Orders</h1>
      <div className="mb-4">
        <select className="input-field w-auto" value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }}>
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="waiting">Waiting</option>
          <option value="received">Received</option>
          <option value="cancelled">Cancelled</option>
          <option value="expired">Expired</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-200 dark:border-gray-700">
              <th className="text-left py-3 px-2">ID</th>
              <th className="text-left py-3 px-2">User</th>
              <th className="text-left py-3 px-2">Service</th>
              <th className="text-left py-3 px-2">Provider</th>
              <th className="text-left py-3 px-2">Phone</th>
              <th className="text-left py-3 px-2">OTP</th>
              <th className="text-left py-3 px-2">Price</th>
              <th className="text-left py-3 px-2">Status</th>
              <th className="text-left py-3 px-2">Time</th>
              <th className="text-left py-3 px-2">Action</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((o) => (
              <tr key={o.id} className="border-b border-gray-100 dark:border-gray-700">
                <td className="py-2 px-2">{o.id}</td>
                <td className="py-2 px-2">{o.username}</td>
                <td className="py-2 px-2">{o.service_name}</td>
                <td className="py-2 px-2">{o.provider_name}</td>
                <td className="py-2 px-2 font-mono text-xs">{o.phone_number}</td>
                <td className="py-2 px-2 font-mono font-bold">{o.otp_code || '-'}</td>
                <td className="py-2 px-2">Rp {Number(o.price).toLocaleString('id-ID')}</td>
                <td className="py-2 px-2"><StatusBadge status={o.status} /></td>
                <td className="py-2 px-2 text-xs">{new Date(o.created_at).toLocaleString('id-ID')}</td>
                <td className="py-2 px-2">
                  {!o.refunded && ['received', 'waiting', 'cancelled', 'expired'].includes(o.status) && (
                    <button onClick={() => refund(o.id)} className="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700">Refund</button>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <Pagination page={page} total={total} limit={limit} onChange={setPage} />
    </div>
  );
}
