import { useState, useEffect } from 'react';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import toast from 'react-hot-toast';

export default function ResellerApiPage() {
  const { user } = useAuth();
  const [showKey, setShowKey] = useState(false);

  const apiBase = window.location.origin + '/api/reseller';

  const copyKey = () => {
    navigator.clipboard.writeText(user.api_key || '');
    toast.success('API Key disalin!');
  };

  const endpoints = [
    { method: 'GET', path: '/balance', desc: 'Cek saldo', example: '{}' },
    { method: 'GET', path: '/services?country_id=1', desc: 'List layanan', example: '{}' },
    { method: 'POST', path: '/order', desc: 'Order OTP', example: '{ "pricing_id": 1 }' },
    { method: 'GET', path: '/order/:id', desc: 'Cek status order', example: '{}' },
    { method: 'POST', path: '/order/:id/cancel', desc: 'Cancel order', example: '{}' },
  ];

  return (
    <div className="max-w-4xl mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold text-white mb-6">API Reseller</h1>

      <div className="card mb-6">
        <h3 className="font-semibold text-white mb-3">API Key Anda</h3>
        <div className="flex items-center gap-3">
          <input type={showKey ? 'text' : 'password'} readOnly value={user?.api_key || 'N/A'}
            className="input-field flex-1 font-mono text-sm" />
          <button onClick={() => setShowKey(!showKey)} className="btn-secondary text-sm">
            {showKey ? 'Hide' : 'Show'}
          </button>
          <button onClick={copyKey} className="btn-primary text-sm">Copy</button>
        </div>
        <p className="text-xs text-gray-500 mt-2">Kirim API key melalui header: <code className="text-primary-400 bg-dark-900/50 px-1.5 py-0.5 rounded">X-API-Key: YOUR_KEY</code></p>
      </div>

      <div className="card mb-6">
        <h3 className="font-semibold text-white mb-3">Base URL</h3>
        <code className="text-sm bg-dark-900/50 text-primary-400 px-3 py-2 rounded-lg block border border-gray-700/50">{apiBase}</code>
      </div>

      <div className="card">
        <h3 className="font-semibold text-white mb-4">Endpoints</h3>
        <div className="space-y-4">
          {endpoints.map((ep, i) => (
            <div key={i} className="border border-gray-700/50 rounded-xl p-4 bg-dark-900/30">
              <div className="flex items-center gap-3 mb-2">
                <span className={`px-2.5 py-1 rounded-lg text-xs font-bold ${ep.method === 'GET' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-primary-500/10 text-primary-400'}`}>
                  {ep.method}
                </span>
                <code className="text-sm text-gray-200">{ep.path}</code>
              </div>
              <p className="text-sm text-gray-400 mb-2">{ep.desc}</p>
              {ep.method === 'POST' && (
                <div>
                  <p className="text-xs text-gray-500 mb-1">Request Body:</p>
                  <pre className="text-xs bg-dark-900/50 text-gray-300 p-2 rounded-lg border border-gray-700/50">{ep.example}</pre>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>

      <div className="card mt-6">
        <h3 className="font-semibold text-white mb-3">Rate Limit</h3>
        <p className="text-sm text-gray-400">
          Default rate limit: <strong>60 requests per menit</strong> per API key.
        </p>
      </div>
    </div>
  );
}
