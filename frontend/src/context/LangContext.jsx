import { createContext, useContext, useState } from 'react';

const translations = {
  id: {
    nav_home: 'Beranda',
    nav_dashboard: 'Dashboard',
    nav_deposit: 'Deposit',
    nav_order: 'Order OTP',
    nav_history: 'Riwayat Order',
    nav_transactions: 'Riwayat Transaksi',
    nav_api: 'API Reseller',
    nav_affiliate: 'Affiliate',
    nav_profile: 'Profil',
    nav_login: 'Login',
    nav_register: 'Daftar',
    nav_logout: 'Keluar',
    balance: 'Saldo',
    total_orders: 'Total Order',
    total_deposits: 'Total Deposit',
    active_orders: 'Order Aktif',
    deposit_title: 'Deposit Saldo',
    order_title: 'Order OTP',
    select_country: 'Pilih Negara',
    select_service: 'Pilih Layanan',
    select_operator: 'Pilih Operator',
    price: 'Harga',
    order_now: 'Order Sekarang',
    cancel: 'Batalkan',
    resend: 'Kirim Ulang',
    status: 'Status',
    phone_number: 'Nomor',
    otp_code: 'Kode OTP',
    waiting_otp: 'Menunggu OTP...',
    search: 'Cari',
    loading: 'Memuat...',
    no_data: 'Tidak ada data',
    success: 'Berhasil',
    error: 'Gagal',
    landing_hero_title: 'Virtual Number & OTP Terpercaya',
    landing_hero_subtitle: 'Dapatkan nomor virtual dan kode OTP untuk berbagai layanan dengan harga terjangkau',
    landing_cta: 'Mulai Sekarang',
    faq_title: 'Pertanyaan Umum',
    how_it_works: 'Cara Kerja',
    features: 'Keunggulan',
    pricing: 'Harga Layanan',
  },
  en: {
    nav_home: 'Home',
    nav_dashboard: 'Dashboard',
    nav_deposit: 'Deposit',
    nav_order: 'Order OTP',
    nav_history: 'Order History',
    nav_transactions: 'Transactions',
    nav_api: 'Reseller API',
    nav_affiliate: 'Affiliate',
    nav_profile: 'Profile',
    nav_login: 'Login',
    nav_register: 'Register',
    nav_logout: 'Logout',
    balance: 'Balance',
    total_orders: 'Total Orders',
    total_deposits: 'Total Deposits',
    active_orders: 'Active Orders',
    deposit_title: 'Deposit Balance',
    order_title: 'Order OTP',
    select_country: 'Select Country',
    select_service: 'Select Service',
    select_operator: 'Select Operator',
    price: 'Price',
    order_now: 'Order Now',
    cancel: 'Cancel',
    resend: 'Resend',
    status: 'Status',
    phone_number: 'Number',
    otp_code: 'OTP Code',
    waiting_otp: 'Waiting for OTP...',
    search: 'Search',
    loading: 'Loading...',
    no_data: 'No data',
    success: 'Success',
    error: 'Failed',
    landing_hero_title: 'Trusted Virtual Number & OTP',
    landing_hero_subtitle: 'Get virtual numbers and OTP codes for various services at affordable prices',
    landing_cta: 'Get Started',
    faq_title: 'FAQ',
    how_it_works: 'How It Works',
    features: 'Features',
    pricing: 'Pricing',
  },
};

const LangContext = createContext(null);

export function LangProvider({ children }) {
  const [lang, setLang] = useState(() => localStorage.getItem('lang') || 'id');

  const t = (key) => translations[lang]?.[key] || translations.id[key] || key;

  const switchLang = (newLang) => {
    setLang(newLang);
    localStorage.setItem('lang', newLang);
  };

  return (
    <LangContext.Provider value={{ lang, t, switchLang }}>
      {children}
    </LangContext.Provider>
  );
}

export function useLang() {
  const ctx = useContext(LangContext);
  if (!ctx) throw new Error('useLang must be used within LangProvider');
  return ctx;
}
