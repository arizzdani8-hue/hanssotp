import { useState, useEffect } from 'react';
import api from '../services/api';
import { useLang } from '../context/LangContext';
import Pagination from '../components/Pagination';

export default function TransactionsPage() {
  const { t } = useLang();
  const [transactions, setTransactions] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [type, setType] = useState('');
  const limit = 20;

  useEffect(() => {
    let url = `/user/transactions?page=${page}&limit=${limit}`;
    if (type) url += `&type=${type}`;
    api.get(url).then(({ data }) => {
      setTransactions(data.data.transactions);
      setTotal(data.data.total);
    });
  }, [page, type]);

  return (
    <div className="max-w-7xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-white mb-6">{t('nav_transactions')}</h1>
      <div className="mb-4">
        <select className="input-field w-auto" value={type} onChange={(e) => { setType(e.target.value); setPage(1); }}>
          <option value="">Semua Tipe</option>
          <option value="deposit">Deposit</option>
          <option value="order">Order</option>
          <option value="refund">Refund</option>
          <option value="manual_add">Manual Add</option>
          <option value="manual_deduct">Manual Deduct</option>
          <option value="affiliate_commission">Affiliate Commission</option>
        </select>
      </div>
      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-800/50">
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">ID</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Tipe</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Jumlah</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Saldo Sebelum</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Saldo Sesudah</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Keterangan</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Waktu</th>
            </tr>
          </thead>
          <tbody>
            {transactions.map((tx) => (
              <tr key={tx.id} className="border-b border-gray-800/50 hover:bg-white/[0.02] transition-colors">
                <td className="py-3 px-2 text-gray-400">{tx.id}</td>
                <td className="py-3 px-2 capitalize text-gray-200">{tx.type}</td>
                <td className={`py-3 px-2 font-semibold ${parseFloat(tx.amount) >= 0 ? 'text-emerald-400' : 'text-red-400'}`}>
                  {parseFloat(tx.amount) >= 0 ? '+' : ''}Rp {Math.abs(parseFloat(tx.amount)).toLocaleString('id-ID')}
                </td>
                <td className="py-3 px-2 text-gray-300">Rp {Number(tx.balance_before).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2 text-gray-300">Rp {Number(tx.balance_after).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2 text-xs text-gray-400">{tx.description}</td>
                <td className="py-3 px-2 text-xs text-gray-500">{new Date(tx.created_at).toLocaleString('id-ID')}</td>
              </tr>
            ))}
            {!transactions.length && (
              <tr><td colSpan="7" className="py-8 text-center text-gray-500">{t('no_data')}</td></tr>
            )}
          </tbody>
        </table>
      </div>
      <Pagination page={page} total={total} limit={limit} onChange={setPage} />
    </div>
  );
}
