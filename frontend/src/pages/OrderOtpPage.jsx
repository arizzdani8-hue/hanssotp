import { useState, useEffect, useCallback } from 'react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import { useLang } from '../context/LangContext';
import { useSocketEvent, useOrderSocket } from '../hooks/useSocket';
import StatusBadge from '../components/StatusBadge';
import toast from 'react-hot-toast';

export default function OrderOtpPage() {
  const { t } = useLang();
  const { refreshUser } = useAuth();
  const [countries, setCountries] = useState([]);
  const [services, setServices] = useState([]);
  const [operators, setOperators] = useState([]);
  const [pricing, setPricing] = useState([]);
  const [selectedCountry, setSelectedCountry] = useState('');
  const [selectedService, setSelectedService] = useState('');
  const [selectedOperator, setSelectedOperator] = useState('');
  const [loading, setLoading] = useState(false);
  const [order, setOrder] = useState(null);

  useEffect(() => {
    api.get('/otp/countries').then(({ data }) => setCountries(data.data));
  }, []);

  useEffect(() => {
    if (selectedCountry) {
      api.get(`/otp/services?country_id=${selectedCountry}`).then(({ data }) => setServices(data.data));
      api.get(`/otp/operators?country_id=${selectedCountry}`).then(({ data }) => setOperators(data.data));
    } else {
      setServices([]);
      setOperators([]);
    }
    setSelectedService('');
    setSelectedOperator('');
  }, [selectedCountry]);

  useEffect(() => {
    if (selectedCountry && selectedService) {
      let url = `/otp/pricing?country_id=${selectedCountry}&service_id=${selectedService}`;
      if (selectedOperator) url += `&operator_id=${selectedOperator}`;
      api.get(url).then(({ data }) => setPricing(data.data));
    } else {
      setPricing([]);
    }
  }, [selectedCountry, selectedService, selectedOperator]);

  useOrderSocket(order?.id);

  const handleOtpReceived = useCallback((data) => {
    if (order && data.orderId === order.id) {
      setOrder((prev) => ({ ...prev, otp_code: data.otp_code, status: 'received' }));
      toast.success(`OTP diterima: ${data.otp_code}`);
      refreshUser();
    }
  }, [order, refreshUser]);

  const handleOrderUpdate = useCallback((data) => {
    if (order && data.orderId === order.id) {
      setOrder((prev) => ({ ...prev, status: data.status }));
      if (data.status === 'expired' || data.status === 'cancelled') {
        refreshUser();
      }
    }
  }, [order, refreshUser]);

  useSocketEvent('otp:received', handleOtpReceived);
  useSocketEvent('order:update', handleOrderUpdate);

  const createOrder = async (pricingItem) => {
    setLoading(true);
    try {
      const body = {
        country_id: parseInt(selectedCountry),
        service_id: parseInt(selectedService),
        ...(selectedOperator && { operator_id: parseInt(selectedOperator) }),
        provider_id: pricingItem.provider_id,
      };
      const { data } = await api.post('/otp/order', body);
      setOrder(data.data);
      refreshUser();
      toast.success('Order berhasil! Menunggu OTP...');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal membuat order');
    } finally {
      setLoading(false);
    }
  };

  const cancelOrder = async () => {
    if (!order) return;
    try {
      await api.post(`/otp/order/${order.id}/cancel`);
      setOrder(null);
      refreshUser();
      toast.success('Order dibatalkan, saldo dikembalikan');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal membatalkan order');
    }
  };

  const resendOtp = async () => {
    if (!order) return;
    try {
      const { data } = await api.post(`/otp/order/${order.id}/resend`);
      toast.success(data.message || 'Resend OTP berhasil');
    } catch (err) {
      toast.error(err.response?.data?.message || 'Gagal resend OTP');
    }
  };

  const checkStatus = async () => {
    if (!order) return;
    try {
      const { data } = await api.get(`/otp/order/${order.id}`);
      setOrder(data.data);
      if (data.data.otp_code) toast.success(`OTP: ${data.data.otp_code}`);
    } catch (err) {
      toast.error('Gagal cek status');
    }
  };

  const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    toast.success('Disalin!');
  };

  if (order) {
    return (
      <div className="max-w-2xl mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-2xl font-bold text-white">{t('order_title')}</h1>
          <p className="text-gray-500 text-sm mt-1">Detail order nomor virtual</p>
        </div>
        <div className="card space-y-5">
          <div className="flex justify-between items-center">
            <h3 className="text-lg font-semibold text-white">Order #{order.id}</h3>
            <StatusBadge status={order.status} />
          </div>
          <div className="bg-dark-900/50 rounded-xl p-5 space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-gray-400 text-sm">Nomor</span>
              <span className="font-mono text-lg text-white cursor-pointer hover:text-primary-400 transition-colors" onClick={() => copyToClipboard(order.phone_number)}>{order.phone_number}</span>
            </div>
            {order.otp_code ? (
              <div className="text-center py-6 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                <p className="text-sm text-gray-400 mb-2">Kode OTP:</p>
                <p className="text-5xl font-bold text-emerald-400 font-mono cursor-pointer hover:text-emerald-300 transition-colors" onClick={() => copyToClipboard(order.otp_code)}>{order.otp_code}</p>
                <p className="text-xs text-gray-500 mt-2">Klik untuk menyalin</p>
              </div>
            ) : order.status === 'waiting' ? (
              <div className="text-center py-6">
                <div className="animate-spin w-10 h-10 border-4 border-primary-500 border-t-transparent rounded-full mx-auto mb-3" />
                <p className="text-gray-400">{t('waiting_otp')}</p>
              </div>
            ) : null}
            <div className="flex justify-between text-sm">
              <span className="text-gray-400">Harga</span>
              <span className="text-white font-semibold">Rp {Number(order.price).toLocaleString('id-ID')}</span>
            </div>
            {order.expires_at && (
              <div className="flex justify-between text-sm">
                <span className="text-gray-400">Expired</span>
                <span className="text-gray-300">{new Date(order.expires_at).toLocaleString('id-ID')}</span>
              </div>
            )}
          </div>
          <div className="flex gap-3 flex-wrap">
            {order.status === 'waiting' && (
              <>
                <button onClick={checkStatus} className="btn-secondary">Cek Status</button>
                <button onClick={resendOtp} className="btn-primary">{t('resend')}</button>
                <button onClick={cancelOrder} className="btn-danger">{t('cancel')}</button>
              </>
            )}
            <button onClick={() => setOrder(null)} className="btn-secondary">Order Baru</button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-white">{t('order_title')}</h1>
        <p className="text-gray-500 text-sm mt-1">Pilih layanan dan order nomor virtual</p>
      </div>
      <div className="card space-y-4 mb-6">
        <div className="grid md:grid-cols-3 gap-4">
          <div>
            <label className="label">{t('select_country')}</label>
            <select className="input-field" value={selectedCountry} onChange={(e) => setSelectedCountry(e.target.value)}>
              <option value="">{t('select_country')}</option>
              {countries.map((c) => <option key={c.id} value={c.id}>{c.name} ({c.code})</option>)}
            </select>
          </div>
          <div>
            <label className="label">{t('select_service')}</label>
            <select className="input-field" value={selectedService} onChange={(e) => setSelectedService(e.target.value)} disabled={!selectedCountry}>
              <option value="">{t('select_service')}</option>
              {services.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
            </select>
          </div>
          <div>
            <label className="label">{t('select_operator')}</label>
            <select className="input-field" value={selectedOperator} onChange={(e) => setSelectedOperator(e.target.value)} disabled={!selectedCountry}>
              <option value="">Semua Operator</option>
              {operators.map((o) => <option key={o.id} value={o.id}>{o.name}</option>)}
            </select>
          </div>
        </div>
      </div>

      {pricing.length > 0 && (
        <div className="card">
          <h3 className="font-semibold text-white mb-4">Pilih Provider & Harga</h3>
          <div className="space-y-3">
            {pricing.map((p) => (
              <div key={p.id} className="flex items-center justify-between py-4 px-5 bg-dark-900/50 rounded-xl border border-gray-800/50 hover:border-primary-500/30 transition-all">
                <div>
                  <p className="font-medium text-white">{p.provider_name}</p>
                  <p className="text-sm text-gray-500">{p.service_name} - {p.country_name}</p>
                  {p.operator_name && <p className="text-xs text-gray-600">{p.operator_name}</p>}
                </div>
                <div className="flex items-center gap-4">
                  <p className="text-lg font-bold text-primary-400">Rp {Number(p.sell_price).toLocaleString('id-ID')}</p>
                  <button onClick={() => createOrder(p)} disabled={loading} className="btn-primary text-sm">
                    {loading ? '...' : t('order_now')}
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {selectedCountry && selectedService && pricing.length === 0 && (
        <div className="card text-center py-10">
          <p className="text-gray-500">{t('no_data')}</p>
        </div>
      )}
    </div>
  );
}
