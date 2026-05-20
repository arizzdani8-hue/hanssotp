<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Site Setup</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8">
        <h1 class="text-2xl font-bold mb-6">Setup Website</h1>
        <form method="POST" action="{{ route('installer.site.store') }}">
            @csrf
            <div class="space-y-4">
                <div><label class="block text-sm font-medium mb-1">Nama Website</label><input type="text" name="site_name" required class="w-full px-3 py-2 border rounded-lg" placeholder="OTP Service"></div>
                <div><label class="block text-sm font-medium mb-1">URL Website</label><input type="url" name="site_url" required class="w-full px-3 py-2 border rounded-lg" placeholder="https://example.com"></div>
            </div>
            <button type="submit" class="mt-6 w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Selesai</button>
        </form>
    </div>
</body>
</html>
