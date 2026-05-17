import { useState, useEffect } from 'react';
import { adminApi } from '../../services/api';
import toast from 'react-hot-toast';

const settingsGroups = [
  {
    title: 'Website',
    keys: ['site_name', 'site_description', 'dark_mode_default'],
  },
  {
    title: 'Deposit',
    keys: ['min_deposit', 'max_deposit', 'currency', 'currency_symbol'],
  },
  {
    title: 'OTP Settings',
    keys: ['otp_expiry_minutes', 'otp_poll_interval', 'max_active_orders_default', 'max_orders_per_minute_default'],
  },
  {
    title: 'Payment - Pakasir',
    keys: ['pakasir_slug', 'pakasir_api_key', 'pakasir_mode', 'pakasir_callback_url'],
  },
  {
    title: 'OTP Provider - Hero SMS',
    keys: ['api_key_herosms'],
  },
  {
    title: 'Telegram',
    keys: ['telegram_bot_token', 'telegram_admin_chat_id'],
  },
  {
    title: 'Affiliate',
    keys: ['affiliate_enabled', 'affiliate_commission_deposit', 'affiliate_commission_order'],
  },
  {
    title: 'Auto Pricing',
    keys: ['auto_pricing_enabled', 'auto_pricing_demand_threshold', 'auto_pricing_increase_percent'],
  },
];

export default function AdminSettingsPage() {
  const [settings, setSettings] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    adminApi.get('/admin/settings').then(({ data }) => {
      setSettings(data.data);
    }).finally(() => setLoading(false));
  }, []);

  const handleChange = (key, value) => {
    setSettings({ ...settings, [key]: value });
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      await adminApi.put('/admin/settings', settings);
      toast.success('Settings saved');
    } catch (err) {
      toast.error('Failed to save');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <div className="flex justify-center py-20"><div className="animate-spin w-8 h-8 border-4 border-primary-500 border-t-transparent rounded-full" /></div>;

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold text-white">Settings</h1>
        <button onClick={handleSave} disabled={saving} className="btn-primary">
          {saving ? 'Saving...' : 'Save All'}
        </button>
      </div>

      <div className="space-y-6">
        {settingsGroups.map((group) => (
          <div key={group.title} className="card">
            <h3 className="text-lg font-semibold text-white mb-4">{group.title}</h3>
            <div className="space-y-3">
              {group.keys.map((key) => {
                const value = settings[key];
                const isBoolean = typeof value === 'boolean' || value === 'true' || value === 'false';
                const isApiKey = key.includes('api_key') || key.includes('token') || key.includes('private');

                return (
                  <div key={key} className="flex items-center gap-4">
                    <label className="w-48 text-sm font-medium text-gray-400 flex-shrink-0">
                      {key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase())}
                    </label>
                    {isBoolean ? (
                      <select className="input-field" value={String(value)} onChange={(e) => handleChange(key, e.target.value === 'true')}>
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                      </select>
                    ) : (
                      <input
                        type={isApiKey ? 'password' : 'text'}
                        className="input-field"
                        value={value ?? ''}
                        onChange={(e) => handleChange(key, e.target.value)}
                      />
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
