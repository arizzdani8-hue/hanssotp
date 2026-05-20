<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Create Admin</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8">
        <h1 class="text-2xl font-bold mb-6">Buat Akun Admin</h1>
        @if($errors->any())<div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm">@foreach($errors->all() as $e){{ $e }}<br>@endforeach</div>@endif
        <form method="POST" action="{{ route('installer.admin.store') }}">
            @csrf
            <div class="space-y-4">
                <div><label class="block text-sm font-medium mb-1">Nama</label><input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">Email</label><input type="email" name="email" required class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">Password</label><input type="password" name="password" required minlength="8" class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">Konfirmasi Password</label><input type="password" name="password_confirmation" required class="w-full px-3 py-2 border rounded-lg"></div>
            </div>
            <button type="submit" class="mt-6 w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Buat Admin</button>
        </form>
    </div>
</body>
</html>
