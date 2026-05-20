<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Install Complete</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-bold mb-4">Instalasi Selesai!</h1>
        <p class="text-gray-600 mb-6">Website OTP Anda siap digunakan.</p>
        <div class="space-y-3 text-sm text-left bg-gray-50 p-4 rounded-lg mb-6">
            <p><strong>Setup Cron Job:</strong></p>
            <code class="block bg-gray-100 p-2 rounded text-xs">* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1</code>
            <p class="mt-2"><strong>Setup Queue Worker:</strong></p>
            <code class="block bg-gray-100 p-2 rounded text-xs">php artisan queue:work --sleep=3 --tries=3</code>
        </div>
        <div class="flex gap-3">
            <a href="/" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Ke Website</a>
            <a href="/admin" class="flex-1 bg-gray-200 py-3 rounded-lg hover:bg-gray-300 font-medium">Admin Panel</a>
        </div>
    </div>
</body>
</html>
