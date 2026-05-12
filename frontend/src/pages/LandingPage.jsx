import { Link } from 'react-router-dom';
import { useLang } from '../context/LangContext';
import { HiShieldCheck, HiLightningBolt, HiCurrencyDollar, HiGlobe, HiClock, HiSupport } from 'react-icons/hi';

const features = [
  { icon: HiShieldCheck, titleId: 'Aman & Terpercaya', titleEn: 'Safe & Trusted', descId: 'Transaksi aman dengan enkripsi data', descEn: 'Secure transactions with data encryption' },
  { icon: HiLightningBolt, titleId: 'Cepat & Realtime', titleEn: 'Fast & Realtime', descId: 'OTP diterima secara realtime via WebSocket', descEn: 'OTP received in realtime via WebSocket' },
  { icon: HiCurrencyDollar, titleId: 'Harga Terjangkau', titleEn: 'Affordable Prices', descId: 'Harga kompetitif untuk semua layanan', descEn: 'Competitive prices for all services' },
  { icon: HiGlobe, titleId: 'Multi Negara', titleEn: 'Multi Country', descId: 'Tersedia nomor dari berbagai negara', descEn: 'Numbers available from various countries' },
  { icon: HiClock, titleId: '24/7 Service', titleEn: '24/7 Service', descId: 'Layanan tersedia 24 jam non-stop', descEn: 'Service available 24/7 non-stop' },
  { icon: HiSupport, titleId: 'API Reseller', titleEn: 'Reseller API', descId: 'API lengkap untuk reseller', descEn: 'Complete API for resellers' },
];

const steps = [
  { num: '1', titleId: 'Daftar Akun', titleEn: 'Register', descId: 'Buat akun gratis dalam hitungan detik', descEn: 'Create a free account in seconds' },
  { num: '2', titleId: 'Isi Saldo', titleEn: 'Top Up', descId: 'Deposit saldo via QRIS dengan mudah', descEn: 'Deposit balance easily via QRIS' },
  { num: '3', titleId: 'Pilih Layanan', titleEn: 'Choose Service', descId: 'Pilih negara dan layanan yang diinginkan', descEn: 'Select the desired country and service' },
  { num: '4', titleId: 'Terima OTP', titleEn: 'Receive OTP', descId: 'OTP akan muncul otomatis secara realtime', descEn: 'OTP will appear automatically in realtime' },
];

const faqs = [
  { qId: 'Apa itu virtual number?', qEn: 'What is a virtual number?', aId: 'Virtual number adalah nomor telepon sementara yang bisa digunakan untuk menerima SMS/OTP tanpa perlu kartu SIM fisik.', aEn: 'A virtual number is a temporary phone number that can be used to receive SMS/OTP without a physical SIM card.' },
  { qId: 'Bagaimana cara deposit?', qEn: 'How to deposit?', aId: 'Anda bisa deposit melalui QRIS. Cukup scan QR code dan saldo akan otomatis masuk setelah pembayaran berhasil.', aEn: 'You can deposit via QRIS. Simply scan the QR code and balance will be automatically added after successful payment.' },
  { qId: 'Berapa lama OTP masuk?', qEn: 'How long for OTP to arrive?', aId: 'Biasanya OTP masuk dalam 1-5 menit. Jika tidak masuk dalam 15 menit, order akan otomatis dibatalkan dan saldo dikembalikan.', aEn: 'Usually OTP arrives within 1-5 minutes. If not received within 15 minutes, the order will be auto-cancelled and balance refunded.' },
  { qId: 'Apakah ada API untuk reseller?', qEn: 'Is there a reseller API?', aId: 'Ya! Kami menyediakan Public API lengkap untuk reseller dengan dokumentasi dan rate limiting.', aEn: 'Yes! We provide a complete Public API for resellers with documentation and rate limiting.' },
];

export default function LandingPage() {
  const { lang, t } = useLang();
  const isId = lang === 'id';

  return (
    <div>
      <section className="bg-gradient-to-br from-primary-600 to-primary-800 text-white py-20 md:py-32">
        <div className="max-w-7xl mx-auto px-4 text-center">
          <h1 className="text-4xl md:text-6xl font-bold mb-6">{t('landing_hero_title')}</h1>
          <p className="text-lg md:text-xl text-primary-100 mb-8 max-w-2xl mx-auto">{t('landing_hero_subtitle')}</p>
          <div className="flex gap-4 justify-center">
            <Link to="/register" className="bg-white text-primary-700 font-semibold py-3 px-8 rounded-lg hover:bg-gray-100 transition-colors">{t('landing_cta')}</Link>
            <Link to="/login" className="border-2 border-white text-white font-semibold py-3 px-8 rounded-lg hover:bg-white/10 transition-colors">{t('nav_login')}</Link>
          </div>
        </div>
      </section>

      <section className="py-16 md:py-24 bg-white dark:bg-gray-800">
        <div className="max-w-7xl mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">{t('features')}</h2>
          <div className="grid md:grid-cols-3 gap-8">
            {features.map((f, i) => (
              <div key={i} className="text-center p-6">
                <div className="inline-flex items-center justify-center w-16 h-16 bg-primary-100 dark:bg-primary-900 rounded-2xl mb-4">
                  <f.icon className="w-8 h-8 text-primary-600" />
                </div>
                <h3 className="text-lg font-semibold mb-2">{isId ? f.titleId : f.titleEn}</h3>
                <p className="text-gray-600 dark:text-gray-400">{isId ? f.descId : f.descEn}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 md:py-24">
        <div className="max-w-7xl mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">{t('how_it_works')}</h2>
          <div className="grid md:grid-cols-4 gap-8">
            {steps.map((s) => (
              <div key={s.num} className="text-center">
                <div className="inline-flex items-center justify-center w-14 h-14 bg-primary-600 text-white rounded-full text-xl font-bold mb-4">{s.num}</div>
                <h3 className="text-lg font-semibold mb-2">{isId ? s.titleId : s.titleEn}</h3>
                <p className="text-gray-600 dark:text-gray-400">{isId ? s.descId : s.descEn}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 md:py-24 bg-white dark:bg-gray-800">
        <div className="max-w-3xl mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">{t('faq_title')}</h2>
          <div className="space-y-4">
            {faqs.map((faq, i) => (
              <details key={i} className="card cursor-pointer group">
                <summary className="font-semibold list-none flex justify-between items-center">
                  {isId ? faq.qId : faq.qEn}
                  <span className="text-gray-400 group-open:rotate-180 transition-transform">&#9660;</span>
                </summary>
                <p className="mt-3 text-gray-600 dark:text-gray-400">{isId ? faq.aId : faq.aEn}</p>
              </details>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 bg-primary-600 text-white text-center">
        <h2 className="text-3xl font-bold mb-4">{isId ? 'Siap Memulai?' : 'Ready to Start?'}</h2>
        <p className="mb-8 text-primary-100">{isId ? 'Daftar sekarang dan dapatkan layanan OTP terbaik' : 'Register now and get the best OTP service'}</p>
        <Link to="/register" className="bg-white text-primary-700 font-semibold py-3 px-8 rounded-lg hover:bg-gray-100 transition-colors">{t('nav_register')}</Link>
      </section>
    </div>
  );
}
