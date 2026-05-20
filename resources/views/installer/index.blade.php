<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8 text-center">
        <h1 class="text-3xl font-bold mb-4">Selamat Datang</h1>
        <p class="text-gray-600 mb-6">Installer akan memandu Anda untuk mengatur website OTP.</p>
        <div class="space-y-3 text-left mb-6 text-sm">
            <div class="flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">1</span> Cek Requirements</div>
            <div class="flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">2</span> Setup Database</div>
            <div class="flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span> Buat Admin</div>
            <div class="flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">4</span> Setup Website</div>
        </div>
        <a href="{{ route('installer.requirements') }}" class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Mulai Install</a>
    </div>
</body>
</html>
