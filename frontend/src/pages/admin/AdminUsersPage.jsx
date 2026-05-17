import { useState, useEffect } from 'react';
import { adminApi } from '../../services/api';
import Pagination from '../../components/Pagination';
import toast from 'react-hot-toast';

export default function AdminUsersPage() {
  const [users, setUsers] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [balanceModal, setBalanceModal] = useState(null);
  const [balanceForm, setBalanceForm] = useState({ amount: '', type: 'add', description: '' });
  const limit = 20;

  const fetchUsers = () => {
    let url = `/admin/users?page=${page}&limit=${limit}`;
    if (search) url += `&search=${search}`;
    adminApi.get(url).then(({ data }) => {
      setUsers(data.data.users);
      setTotal(data.data.total);
    });
  };

  useEffect(fetchUsers, [page, search]);

  const toggleBan = async (userId, isBanned) => {
    const reason = isBanned ? '' : prompt('Alasan ban:');
    try {
      await adminApi.patch(`/admin/users/${userId}/ban`, { is_banned: !isBanned, ban_reason: reason });
      toast.success(isBanned ? 'User di-unban' : 'User dibanned');
      fetchUsers();
    } catch (err) {
      toast.error('Gagal');
    }
  };

  const adjustBalance = async (e) => {
    e.preventDefault();
    try {
      await adminApi.patch(`/admin/users/${balanceModal}/balance`, {
        amount: parseFloat(balanceForm.amount),
        type: balanceForm.type,
        description: balanceForm.description,
      });
      toast.success('Saldo diubah');
      setBalanceModal(null);
      setBalanceForm({ amount: '', type: 'add', description: '' });
      fetchUsers();
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal');
    }
  };

  return (
    <div>
      <h1 className="text-2xl font-bold text-white mb-6">Users</h1>
      <div className="mb-4">
        <input type="text" className="input-field w-full md:w-80" placeholder="Cari username/email..."
          value={search} onChange={(e) => { setSearch(e.target.value); setPage(1); }} />
      </div>
      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-800/50">
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">ID</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Username</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Email</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Saldo</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Status</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {users.map((u) => (
              <tr key={u.id} className="border-b border-gray-800/50 hover:bg-white/[0.02] transition-colors">
                <td className="py-3 px-2 text-gray-400">{u.id}</td>
                <td className="py-3 px-2 text-gray-200">{u.username}</td>
                <td className="py-3 px-2 text-gray-300">{u.email}</td>
                <td className="py-3 px-2 text-emerald-400 font-medium">Rp {Number(u.balance).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2">{u.is_banned ? <span className="text-red-400 bg-red-500/10 px-2 py-0.5 rounded-lg text-xs font-medium">Banned</span> : <span className="text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg text-xs font-medium">Active</span>}</td>
                <td className="py-3 px-2 space-x-2">
                  <button onClick={() => toggleBan(u.id, u.is_banned)} className={`text-xs px-2.5 py-1 rounded-lg font-medium ${u.is_banned ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'}`}>
                    {u.is_banned ? 'Unban' : 'Ban'}
                  </button>
                  <button onClick={() => setBalanceModal(u.id)} className="text-xs px-2.5 py-1 rounded-lg bg-primary-500/10 text-primary-400 font-medium">Saldo</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <Pagination page={page} total={total} limit={limit} onChange={setPage} />

      {balanceModal && (
        <div className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center px-4">
          <div className="card w-full max-w-md">
            <h3 className="text-lg font-semibold text-white mb-4">Adjust Saldo User #{balanceModal}</h3>
            <form onSubmit={adjustBalance} className="space-y-4">
              <div>
                <label className="label">Jumlah</label>
                <input type="number" className="input-field" value={balanceForm.amount} onChange={(e) => setBalanceForm({ ...balanceForm, amount: e.target.value })} required min="1" />
              </div>
              <div>
                <label className="label">Tipe</label>
                <select className="input-field" value={balanceForm.type} onChange={(e) => setBalanceForm({ ...balanceForm, type: e.target.value })}>
                  <option value="add">Tambah</option>
                  <option value="deduct">Kurangi</option>
                </select>
              </div>
              <div>
                <label className="label">Keterangan</label>
                <input type="text" className="input-field" value={balanceForm.description} onChange={(e) => setBalanceForm({ ...balanceForm, description: e.target.value })} />
              </div>
              <div className="flex gap-3">
                <button type="submit" className="btn-primary">Simpan</button>
                <button type="button" onClick={() => setBalanceModal(null)} className="btn-secondary">Batal</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
