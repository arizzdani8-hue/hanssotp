import { useState, useEffect } from 'react';
import { adminApi } from '../../services/api';
import StatusBadge from '../../components/StatusBadge';
import Pagination from '../../components/Pagination';

export default function AdminDepositsPage() {
  const [deposits, setDeposits] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState('');
  const limit = 20;

  useEffect(() => {
    let url = `/admin/deposits?page=${page}&limit=${limit}`;
    if (status) url += `&status=${status}`;
    adminApi.get(url).then(({ data }) => {
      setDeposits(data.data.deposits);
      setTotal(data.data.total);
    });
  }, [page, status]);

  return (
    <div>
      <h1 className="text-2xl font-bold text-white mb-6">Deposits</h1>
      <div className="mb-4">
        <select className="input-field w-auto" value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }}>
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="paid">Paid</option>
          <option value="failed">Failed</option>
          <option value="expired">Expired</option>
        </select>
      </div>
      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-800/50">
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">ID</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">User</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Reference</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Amount</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Fee</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Total</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Status</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Time</th>
            </tr>
          </thead>
          <tbody>
            {deposits.map((d) => (
              <tr key={d.id} className="border-b border-gray-800/50 hover:bg-white/[0.02] transition-colors">
                <td className="py-3 px-2 text-gray-400">{d.id}</td>
                <td className="py-3 px-2 text-gray-200">{d.username}</td>
                <td className="py-3 px-2 font-mono text-xs text-gray-400">{d.reference}</td>
                <td className="py-3 px-2 text-gray-200">Rp {Number(d.amount).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2 text-gray-400">Rp {Number(d.fee).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2 font-semibold text-emerald-400">Rp {Number(d.total_amount).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2"><StatusBadge status={d.status} /></td>
                <td className="py-3 px-2 text-xs text-gray-500">{new Date(d.created_at).toLocaleString('id-ID')}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <Pagination page={page} total={total} limit={limit} onChange={setPage} />
    </div>
  );
}
