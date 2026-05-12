import { useState, useEffect } from 'react';
import api from '../services/api';
import { useLang } from '../context/LangContext';
import StatusBadge from '../components/StatusBadge';
import Pagination from '../components/Pagination';

export default function OrderHistoryPage() {
  const { t } = useLang();
  const [orders, setOrders] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const limit = 20;

  useEffect(() => {
    let url = `/user/orders?page=${page}&limit=${limit}`;
    if (status) url += `&status=${status}`;
    api.get(url).then(({ data }) => {
      setOrders(data.data.orders);
      setTotal(data.data.total);
    });
  }, [page, status]);

  return (
    <div className="max-w-7xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">{t('nav_history')}</h1>
      <div className="mb-4">
        <select className="input-field w-auto" value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }}>
          <option value="">Semua Status</option>
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
              <th className="text-left py-3 px-2">Provider</th>
              <th className="text-left py-3 px-2">Layanan</th>
              <th className="text-left py-3 px-2">Negara</th>
              <th className="text-left py-3 px-2">Nomor</th>
              <th className="text-left py-3 px-2">OTP</th>
              <th className="text-left py-3 px-2">Harga</th>
              <th className="text-left py-3 px-2">Status</th>
              <th className="text-left py-3 px-2">Waktu</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((o) => (
              <tr key={o.id} className="border-b border-gray-100 dark:border-gray-700">
                <td className="py-2 px-2">{o.id}</td>
                <td className="py-2 px-2">{o.provider_name}</td>
                <td className="py-2 px-2">{o.service_name}</td>
                <td className="py-2 px-2">{o.country_name}</td>
                <td className="py-2 px-2 font-mono text-xs">{o.phone_number}</td>
                <td className="py-2 px-2 font-mono font-bold">{o.otp_code || '-'}</td>
                <td className="py-2 px-2">Rp {Number(o.price).toLocaleString('id-ID')}</td>
                <td className="py-2 px-2"><StatusBadge status={o.status} /></td>
                <td className="py-2 px-2 text-xs">{new Date(o.created_at).toLocaleString('id-ID')}</td>
              </tr>
            ))}
            {!orders.length && (
              <tr><td colSpan="9" className="py-8 text-center text-gray-500">{t('no_data')}</td></tr>
            )}
          </tbody>
        </table>
      </div>
      <Pagination page={page} total={total} limit={limit} onChange={setPage} />
    </div>
  );
}
