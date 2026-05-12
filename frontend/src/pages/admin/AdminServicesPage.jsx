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
      <h1 className="text-2xl font-bold mb-6">OTP Services / Pricing</h1>
      <div className="card overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-200 dark:border-gray-700">
              <th className="text-left py-3 px-2">ID</th>
              <th className="text-left py-3 px-2">Country</th>
              <th className="text-left py-3 px-2">Service</th>
              <th className="text-left py-3 px-2">Provider</th>
              <th className="text-left py-3 px-2">Cost</th>
              <th className="text-left py-3 px-2">Markup %</th>
              <th className="text-left py-3 px-2">Sell</th>
              <th className="text-left py-3 px-2">Active</th>
              <th className="text-left py-3 px-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {services.map((s) => (
              <tr key={s.id} className="border-b border-gray-100 dark:border-gray-700">
                <td className="py-2 px-2">{s.id}</td>
                <td className="py-2 px-2">{s.country_name}</td>
                <td className="py-2 px-2">{s.service_name}</td>
                <td className="py-2 px-2">{s.provider_name}</td>
                <td className="py-2 px-2">
                  {editId === s.id ? (
                    <input type="number" className="input-field w-20 text-xs" value={editForm.cost_price}
                      onChange={(e) => setEditForm({ ...editForm, cost_price: e.target.value })} />
                  ) : `Rp ${Number(s.cost_price).toLocaleString('id-ID')}`}
                </td>
                <td className="py-2 px-2">
                  {editId === s.id ? (
                    <input type="number" className="input-field w-16 text-xs" value={editForm.markup_percent}
                      onChange={(e) => setEditForm({ ...editForm, markup_percent: e.target.value })} />
                  ) : `${s.markup_percent}%`}
                </td>
                <td className="py-2 px-2 font-semibold">Rp {Number(s.sell_price).toLocaleString('id-ID')}</td>
                <td className="py-2 px-2">
                  {editId === s.id ? (
                    <input type="checkbox" checked={editForm.is_active}
                      onChange={(e) => setEditForm({ ...editForm, is_active: e.target.checked })} />
                  ) : (s.is_active ? 'Yes' : 'No')}
                </td>
                <td className="py-2 px-2 space-x-1">
                  {editId === s.id ? (
                    <>
                      <button onClick={saveEdit} className="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Save</button>
                      <button onClick={() => setEditId(null)} className="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">Cancel</button>
                    </>
                  ) : (
                    <>
                      <button onClick={() => startEdit(s)} className="text-xs px-2 py-1 rounded bg-blue-100 text-blue-700">Edit</button>
                      <button onClick={() => deleteService(s.id)} className="text-xs px-2 py-1 rounded bg-red-100 text-red-700">Delete</button>
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
