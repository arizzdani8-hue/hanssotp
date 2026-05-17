import { Link } from 'react-router-dom';
import { useLang } from '../context/LangContext';
import { HiShieldCheck, HiLightningBolt, HiCurrencyDollar, HiGlobe, HiClock, HiSupport } from 'react-icons/hi';

const features = [
  { icon: HiLightningBolt, titleId: 'Instan & Cepat', titleEn: 'Instant & Fast', descId: 'Nomor virtual siap dalam hitungan detik. OTP masuk langsung ke dashboard.', descEn: 'Virtual numbers ready in seconds. OTP arrives directly to your dashboard.', color: 'from-yellow-500 to-orange-500' },
  { icon: HiShieldCheck, titleId: 'Aman & Privasi', titleEn: 'Safe & Private', descId: 'Data dilindungi enkripsi end-to-end. 100% privasi terjamin.', descEn: 'Data protected with end-to-end encryption. 100% privacy guaranteed.', color: 'from-emerald-500 to-teal-500' },
  { icon: HiGlobe, titleId: '200+ Layanan', titleEn: '200+ Services', descId: 'Dukungan untuk WhatsApp, Telegram, Facebook, dan aplikasi populer lainnya.', descEn: 'Support for WhatsApp, Telegram, Facebook, and other popular apps.', color: 'from-blue-500 to-cyan-500' },
  { icon: HiCurrencyDollar, titleId: 'Harga Terjangkau', titleEn: 'Affordable Prices', descId: 'Mulai dari Rp 1.200 per nomor. Tanpa biaya tersembunyi.', descEn: 'Starting from Rp 1,200 per number. No hidden fees.', color: 'from-primary-500 to-violet-500' },
  { icon: HiSupport, titleId: 'Support 24/7', titleEn: '24/7 Support', descId: 'Tim support siap membantu kapan saja via live chat dan Telegram.', descEn: 'Support team ready to help anytime via live chat and Telegram.', color: 'from-pink-500 to-rose-500' },
  { icon: HiClock, titleId: 'Auto Refund', titleEn: 'Auto Refund', descId: 'Tidak menerima SMS? Saldo otomatis dikembalikan 100%.', descEn: 'Didn\'t receive SMS? Balance automatically refunded 100%.', color: 'from-indigo-500 to-purple-500' },
];

const steps = [
  { num: '01', titleId: 'Daftar Akun', titleEn: 'Register', descId: 'Buat akun gratis dalam hitungan detik', descEn: 'Create a free account in seconds' },
  { num: '02', titleId: 'Isi Saldo', titleEn: 'Top Up', descId: 'Deposit via QRIS, cepat & otomatis', descEn: 'Deposit via QRIS, fast & automatic' },
  { num: '03', titleId: 'Pilih Layanan', titleEn: 'Choose Service', descId: 'Pilih negara & aplikasi yang diinginkan', descEn: 'Select country & desired application' },
  { num: '04', titleId: 'Terima OTP', titleEn: 'Receive OTP', descId: 'Kode OTP muncul realtime di dashboard', descEn: 'OTP code appears realtime in dashboard' },
];

const faqs = [
  { qId: 'Apa itu virtual number?', qEn: 'What is a virtual number?', aId: 'Virtual number adalah nomor telepon sementara yang bisa digunakan untuk menerima SMS/OTP tanpa perlu kartu SIM fisik.', aEn: 'A virtual number is a temporary phone number that can be used to receive SMS/OTP without a physical SIM card.' },
  { qId: 'Bagaimana cara deposit?', qEn: 'How to deposit?', aId: 'Anda bisa deposit melalui QRIS. Cukup scan QR code dan saldo akan otomatis masuk setelah pembayaran berhasil.', aEn: 'You can deposit via QRIS. Simply scan the QR code and balance will be automatically added after successful payment.' },
  { qId: 'Berapa lama OTP masuk?', qEn: 'How long for OTP to arrive?', aId: 'Biasanya OTP masuk dalam 1-5 menit. Jika tidak masuk dalam 15 menit, order akan otomatis dibatalkan dan saldo dikembalikan.', aEn: 'Usually OTP arrives within 1-5 minutes. If not received within 15 minutes, the order will be auto-cancelled and balance refunded.' },
  { qId: 'Apakah ada API untuk reseller?', qEn: 'Is there a reseller API?', aId: 'Ya! Kami menyediakan Public API lengkap untuk reseller dengan dokumentasi dan rate limiting.', aEn: 'Yes! We provide a complete Public API for resellers with documentation and rate limiting.' },
  { qId: 'Layanan apa saja yang tersedia?', qEn: 'What services are available?', aId: 'Tersedia 200+ layanan termasuk WhatsApp, Telegram, Gmail, Instagram, TikTok, Shopee, dan masih banyak lagi dari 10+ negara.', aEn: 'Over 200+ services including WhatsApp, Telegram, Gmail, Instagram, TikTok, Shopee, and many more from 10+ countries.' },
];

const pricing = [
  { name: 'WhatsApp', country: 'Indonesia', price: 'Rp 2.250', popular: true, emoji: '💬' },
  { name: 'Telegram', country: 'Indonesia', price: 'Rp 1.800', popular: false, emoji: '✈️' },
  { name: 'Gmail', country: 'Indonesia', price: 'Rp 2.250', popular: false, emoji: '📧' },
  { name: 'Instagram', country: 'Indonesia', price: 'Rp 1.800', popular: true, emoji: '📸' },
  { name: 'TikTok', country: 'Indonesia', price: 'Rp 2.250', popular: false, emoji: '🎵' },
  { name: 'WhatsApp', country: 'United States', price: 'Rp 7.500', popular: false, emoji: '🇺🇸' },
];

const stats = [
  { value: '10K+', labelId: 'Pengguna Aktif', labelEn: 'Active Users' },
  { value: '50K+', labelId: 'SMS Terkirim', labelEn: 'SMS Delivered' },
  { value: '99.9%', labelId: 'Uptime', labelEn: 'Uptime' },
  { value: '10+', labelId: 'Negara', labelEn: 'Countries' },
];

export default function LandingPage() {
  const { lang, t } = useLang();
  const isId = lang === 'id';

  return (
    <div className="-mt-16">
      {/* Hero */}
      <section className="relative min-h-screen flex items-center overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-dark-950 via-primary-950/30 to-dark-950" />
        <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-primary-600/10 rounded-full blur-3xl" />
        <div className="absolute bottom-1/4 right-1/4 w-80 h-80 bg-violet-600/10 rounded-full blur-3xl" />
        <div className="relative max-w-7xl mx-auto px-4 pt-32 pb-20 text-center z-10">
          <div className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-500/10 border border-primary-500/20 text-primary-400 text-sm font-medium mb-8">
            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
            {isId ? 'Platform Nomor Virtual #1 Indonesia' : '#1 Virtual Number Platform in Indonesia'}
          </div>
          <h1 className="text-5xl md:text-7xl font-extrabold mb-6 leading-tight">
            <span className="bg-gradient-to-r from-white via-gray-100 to-gray-300 bg-clip-text text-transparent">
              {isId ? 'Verifikasi Akun' : 'Verify Accounts'}
            </span>
            <br />
            <span className="bg-gradient-to-r from-primary-400 via-violet-400 to-primary-300 bg-clip-text text-transparent">
              {isId ? 'Dengan Nomor Virtual' : 'With Virtual Numbers'}
            </span>
          </h1>
          <p className="text-lg md:text-xl text-gray-400 mb-10 max-w-2xl mx-auto leading-relaxed">
            {isId
              ? 'Platform terpercaya untuk mendapatkan nomor virtual SMS & OTP. Cepat, aman, dan harga terjangkau.'
              : 'Trusted platform to get virtual SMS & OTP numbers. Fast, secure, and affordable prices.'}
          </p>
          <div className="flex gap-4 justify-center flex-wrap">
            <Link to="/register" className="bg-gradient-to-r from-primary-600 to-violet-600 hover:from-primary-500 hover:to-violet-500 text-white font-bold py-4 px-10 rounded-2xl transition-all duration-300 shadow-2xl shadow-primary-600/25 hover:shadow-primary-500/40 text-lg">
              {isId ? 'Mulai Sekarang' : 'Get Started'}
            </Link>
            <Link to="/login" className="border border-gray-700 text-gray-300 font-semibold py-4 px-10 rounded-2xl hover:bg-white/5 hover:border-gray-600 transition-all duration-300 text-lg">
              {t('nav_login')}
            </Link>
          </div>

          {/* Stats */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-20 max-w-3xl mx-auto">
            {stats.map((s, i) => (
              <div key={i} className="bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-4">
                <p className="text-2xl md:text-3xl font-bold bg-gradient-to-r from-primary-400 to-violet-400 bg-clip-text text-transparent">{s.value}</p>
                <p className="text-sm text-gray-500 mt-1">{isId ? s.labelId : s.labelEn}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="py-20 md:py-28 relative">
        <div className="max-w-7xl mx-auto px-4">
          <div className="text-center mb-16">
            <p className="text-primary-400 font-semibold text-sm uppercase tracking-wider mb-3">{isId ? 'Keunggulan Kami' : 'Our Advantages'}</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white">{isId ? 'Kenapa Memilih NyooApp?' : 'Why Choose NyooApp?'}</h2>
          </div>
          <div className="grid md:grid-cols-3 gap-6">
            {features.map((f, i) => (
              <div key={i} className="group bg-dark-800/50 backdrop-blur-sm border border-gray-800/50 rounded-2xl p-8 hover:border-primary-500/30 transition-all duration-300 hover:-translate-y-1">
                <div className={`inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br ${f.color} rounded-xl mb-5 shadow-lg`}>
                  <f.icon className="w-7 h-7 text-white" />
                </div>
                <h3 className="text-lg font-bold text-white mb-2">{isId ? f.titleId : f.titleEn}</h3>
                <p className="text-gray-400 leading-relaxed">{isId ? f.descId : f.descEn}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* How It Works */}
      <section className="py-20 md:py-28 relative">
        <div className="absolute inset-0 bg-gradient-to-b from-transparent via-primary-950/10 to-transparent" />
        <div className="relative max-w-7xl mx-auto px-4">
          <div className="text-center mb-16">
            <p className="text-primary-400 font-semibold text-sm uppercase tracking-wider mb-3">{isId ? 'Mudah & Cepat' : 'Easy & Fast'}</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white">{isId ? 'Cara Menggunakan' : 'How It Works'}</h2>
          </div>
          <div className="grid md:grid-cols-4 gap-8">
            {steps.map((s, i) => (
              <div key={s.num} className="relative text-center group">
                {i < steps.length - 1 && (
                  <div className="hidden md:block absolute top-8 left-1/2 w-full h-px bg-gradient-to-r from-primary-500/30 to-transparent" />
                )}
                <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary-600 to-violet-600 text-white rounded-2xl text-xl font-bold mb-5 shadow-xl shadow-primary-600/20 group-hover:shadow-primary-500/40 transition-all">
                  {s.num}
                </div>
                <h3 className="text-lg font-bold text-white mb-2">{isId ? s.titleId : s.titleEn}</h3>
                <p className="text-gray-400">{isId ? s.descId : s.descEn}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Pricing Preview */}
      <section className="py-20 md:py-28">
        <div className="max-w-7xl mx-auto px-4">
          <div className="text-center mb-16">
            <p className="text-primary-400 font-semibold text-sm uppercase tracking-wider mb-3">{isId ? 'Harga Transparan' : 'Transparent Pricing'}</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white">{isId ? 'Layanan Populer' : 'Popular Services'}</h2>
          </div>
          <div className="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            {pricing.map((p, i) => (
              <div key={i} className={`relative bg-dark-800/50 border rounded-2xl p-6 transition-all duration-300 hover:-translate-y-1 ${p.popular ? 'border-primary-500/50 shadow-lg shadow-primary-500/10' : 'border-gray-800/50'}`}>
                {p.popular && (
                  <div className="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-gradient-to-r from-primary-600 to-violet-600 rounded-full text-xs font-bold text-white">
                    POPULER
                  </div>
                )}
                <div className="text-3xl mb-3">{p.emoji}</div>
                <h3 className="text-lg font-bold text-white">{p.name}</h3>
                <p className="text-sm text-gray-500 mb-4">{p.country}</p>
                <p className="text-2xl font-bold bg-gradient-to-r from-primary-400 to-violet-400 bg-clip-text text-transparent">{p.price}</p>
                <div className="mt-4 space-y-2 text-sm text-gray-400">
                  <p>&#10003; Auto refund</p>
                  <p>&#10003; Instan & cepat</p>
                  <p>&#10003; Durasi 15 menit</p>
                </div>
              </div>
            ))}
          </div>
          <div className="text-center mt-10">
            <Link to="/register" className="text-primary-400 hover:text-primary-300 font-semibold transition-colors">
              {isId ? 'Lihat Semua Layanan →' : 'View All Services →'}
            </Link>
          </div>
        </div>
      </section>

      {/* FAQ */}
      <section className="py-20 md:py-28">
        <div className="max-w-3xl mx-auto px-4">
          <div className="text-center mb-16">
            <p className="text-primary-400 font-semibold text-sm uppercase tracking-wider mb-3">FAQ</p>
            <h2 className="text-3xl md:text-4xl font-bold text-white">{isId ? 'Pertanyaan Umum' : 'Frequently Asked Questions'}</h2>
          </div>
          <div className="space-y-4">
            {faqs.map((faq, i) => (
              <details key={i} className="group bg-dark-800/50 border border-gray-800/50 rounded-2xl cursor-pointer hover:border-primary-500/30 transition-all">
                <summary className="font-semibold list-none flex justify-between items-center p-6 text-white">
                  {isId ? faq.qId : faq.qEn}
                  <span className="text-gray-500 group-open:rotate-180 transition-transform duration-300 ml-4 flex-shrink-0">&#9660;</span>
                </summary>
                <p className="px-6 pb-6 text-gray-400 leading-relaxed -mt-2">{isId ? faq.aId : faq.aEn}</p>
              </details>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-20 relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-primary-900/50 via-violet-900/50 to-primary-900/50" />
        <div className="absolute top-0 left-1/3 w-96 h-96 bg-primary-600/20 rounded-full blur-3xl" />
        <div className="relative max-w-4xl mx-auto px-4 text-center">
          <h2 className="text-3xl md:text-5xl font-bold text-white mb-4">{isId ? 'Siap Memulai?' : 'Ready to Start?'}</h2>
          <p className="text-lg text-gray-400 mb-10 max-w-xl mx-auto">
            {isId ? 'Daftar sekarang dan dapatkan layanan nomor virtual & OTP terbaik di Indonesia.' : 'Register now and get the best virtual number & OTP service in Indonesia.'}
          </p>
          <Link to="/register" className="bg-gradient-to-r from-primary-600 to-violet-600 hover:from-primary-500 hover:to-violet-500 text-white font-bold py-4 px-12 rounded-2xl transition-all duration-300 shadow-2xl shadow-primary-600/25 hover:shadow-primary-500/40 text-lg inline-block">
            {isId ? 'Daftar Gratis Sekarang' : 'Register Free Now'}
          </Link>
        </div>
      </section>
    </div>
  );
}
