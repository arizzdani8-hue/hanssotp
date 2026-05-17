import { useState, useEffect } from 'react';
import { adminApi } from '../../services/api';
import toast from 'react-hot-toast';

export default function AdminServicesPage() {
  const [services, setServices] = useState([]);
  const [loading, setLoading] = useState(true);
  const [editId, setEditId] = useState(null);
  const [editForm, setEditForm] = useState({});

  const fetchServices = () => {
    adminApi.get('/admin/services').then(({ data }) => {
      setServices(data.data);
    }).finally(() => setLoading(false));
  };

  useEffect(fetchServices, []);

  const startEdit = (svc) => {
    setEditId(svc.id);
    setEditForm({
      cost_price: svc.cost_price,
      markup_percent: svc.markup_percent,
      is_active: svc.is_active,
      provider_service_code: svc.provider_service_code || '',
      provider_country_code: svc.provider_country_code || '',
    });
  };

  const saveEdit = async () => {
    try {
      await adminApi.put(`/admin/services/${editId}`, {
        cost_price: parseFloat(editForm.cost_price),
        markup_percent: parseFloat(editForm.markup_percent),
        is_active: editForm.is_active ? 1 : 0,
        provider_service_code: editForm.provider_service_code,
        provider_country_code: editForm.provider_country_code,
      });
      toast.success('Service updated');
      setEditId(null);
      fetchServices();
    } catch (err) {
      toast.error('Failed to update');
    }
  };

  const deleteService = async (id) => {
    if (!confirm('Hapus service ini?')) return;
    try {
      await adminApi.delete(`/admin/services/${id}`);
      toast.success('Service deleted');
      fetchServices();
    } catch (err) {
      toast.error('Failed to delete');
    }
  };

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;

  return (
    <div>
      <h1 className="text-2xl font-bold text-white mb-6">OTP Services / Pricing</h1>
      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-800/50">
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">ID</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Country</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Service</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Provider</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Cost</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Markup %</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Sell</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Active</th>
              <th className="text-left py-3 px-2 text-gray-400 font-medium text-xs uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {services.map((s) => (
              <tr key={s.id} className="border-b border-gray-800/50 hover:bg-white/[0.02] transition-colors">
                <td className="py-3 px-2 text-gray-400">{s.id}</td>
                <td className="py-3 px-2 text-gray-200">{s.country_name}</td>
                <td className="py-3 px-2 text-gray-200">{s.service_name}</td>
                <td className="py-3 px-2 text-gray-300">{s.provider_name}</td>
                <td className="py-3 px-2 text-gray-200">
                  {editId === s.id ? (
                    <input type="number" className="input-field w-20 text-xs" value={editForm.cost_price}
                      onChange={(e) => setEditForm({ ...editForm, cost_price: e.target.value })} />
                  ) : `Rp ${Number(s.cost_price).toLocaleString('id-ID')}`}
                </td>
                <td className="py-3 px-2 text-gray-200">
                  {editId === s.id ? (
                    <input type="number" className="input-field w-16 text-xs" value={editForm.markup_percent}
                      onChange={(e) => setEditForm({ ...editForm, markup_percent: e.target.value })} />
                  ) : `${s.markup_percent}%`}
                </td>
                <td className="py-3 px-2 font-semibold text-emerald-400">Rp {Number(s.sell_price).toLocaleString('id-ID')}</td>
                <td className="py-3 px-2">
                  {editId === s.id ? (
                    <input type="checkbox" checked={editForm.is_active}
                      onChange={(e) => setEditForm({ ...editForm, is_active: e.target.checked })} />
                  ) : (s.is_active ? <span className="text-emerald-400">Yes</span> : <span className="text-gray-500">No</span>)}
                </td>
                <td className="py-3 px-2 space-x-1">
                  {editId === s.id ? (
                    <>
                      <button onClick={saveEdit} className="text-xs px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 font-medium">Save</button>
                      <button onClick={() => setEditId(null)} className="text-xs px-2.5 py-1 rounded-lg bg-gray-500/10 text-gray-400 font-medium">Cancel</button>
                    </>
                  ) : (
                    <>
                      <button onClick={() => startEdit(s)} className="text-xs px-2.5 py-1 rounded-lg bg-primary-500/10 text-primary-400 font-medium">Edit</button>
                      <button onClick={() => deleteService(s.id)} className="text-xs px-2.5 py-1 rounded-lg bg-red-500/10 text-red-400 font-medium">Delete</button>
                    </>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
