/**
 * Digital Store - Main JavaScript
 */

// Toggle mobile menu
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Toggle dropdown
function toggleDropdown(btn) {
    const dropdown = btn.nextElementSibling;
    document.querySelectorAll('.dropdown-menu').forEach(d => {
        if (d !== dropdown) d.classList.add('hidden');
    });
    dropdown.classList.toggle('hidden');
}

// Close dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick*="toggleDropdown"]') && !e.target.closest('.dropdown-menu')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.add('hidden'));
    }
});

// Copy to clipboard
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Tersalin!';
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-600');
        }, 2000);
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Tersalin!';
        setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
    });
}

// Countdown timer
function startCountdown(elementId, expiry) {
    const el = document.getElementById(elementId);
    if (!el || !expiry) return;

    const expiryTime = new Date(expiry).getTime();

    const interval = setInterval(() => {
        const now = new Date().getTime();
        const diff = expiryTime - now;

        if (diff <= 0) {
            el.innerHTML = '<span class="text-red-400 font-bold">Kadaluarsa</span>';
            clearInterval(interval);
            setTimeout(() => location.reload(), 2000);
            return;
        }

        const minutes = Math.floor(diff / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        el.innerHTML = `<span class="text-2xl font-bold text-yellow-400">${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}</span>`;
    }, 1000);
}

// Check payment status polling
function pollPaymentStatus(invoice, interval) {
    interval = interval || 5000;
    const poll = setInterval(() => {
        fetch(window.location.origin + '/api/check-payment.php?invoice=' + encodeURIComponent(invoice))
            .then(r => r.json())
            .then(data => {
                if (data.status === 'paid' || data.status === 'completed') {
                    clearInterval(poll);
                    const statusEl = document.getElementById('paymentStatus');
                    if (statusEl) {
                        statusEl.innerHTML = '<div class="text-center p-6"><div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-check-circle text-3xl text-green-400"></i></div><h3 class="text-xl font-bold text-green-400 mb-2">Pembayaran Berhasil!</h3><p class="text-gray-400">Pesanan Anda sedang diproses...</p></div>';
                    }
                    setTimeout(() => {
                        window.location.href = window.location.origin + '/pages/order-detail.php?invoice=' + encodeURIComponent(invoice);
                    }, 3000);
                } else if (data.status === 'expired' || data.status === 'failed') {
                    clearInterval(poll);
                    const statusEl = document.getElementById('paymentStatus');
                    if (statusEl) {
                        statusEl.innerHTML = '<div class="text-center p-6"><div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4"><i class="fas fa-times-circle text-3xl text-red-400"></i></div><h3 class="text-xl font-bold text-red-400 mb-2">Pembayaran Gagal</h3><p class="text-gray-400">Silakan coba lagi.</p></div>';
                    }
                }
            })
            .catch(() => {});
    }, interval);
}

// Confirm action
function confirmAction(message, url) {
    if (confirm(message)) {
        window.location.href = url;
    }
}

// Format currency
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Toast notification
function showToast(message, type) {
    type = type || 'info';
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-indigo-500'
    };
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-[9999] px-6 py-3 rounded-xl text-white text-sm font-medium shadow-2xl ${colors[type]} fade-in`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Image preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// Quantity controls
function changeQty(productId, delta) {
    const input = document.getElementById('qty-' + productId);
    if (!input) return;
    let val = parseInt(input.value) + delta;
    const min = parseInt(input.min) || 1;
    const max = parseInt(input.max) || 99;
    if (val < min) val = min;
    if (val > max) val = max;
    input.value = val;
}

// Smooth scroll to element
function scrollToElement(id) {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Debounce helper
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Search input handler
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function() {
        const form = this.closest('form');
        if (form) form.submit();
    }, 500));
}
