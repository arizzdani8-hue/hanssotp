<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Database Setup</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8">
        <h1 class="text-2xl font-bold mb-6">Setup Database</h1>
        @if($errors->any())<div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('installer.database.store') }}">
            @csrf
            <div class="space-y-4">
                <div><label class="block text-sm font-medium mb-1">DB Host</label><input type="text" name="db_host" value="127.0.0.1" required class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">DB Port</label><input type="text" name="db_port" value="3306" required class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">DB Name</label><input type="text" name="db_name" required class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">DB Username</label><input type="text" name="db_user" required class="w-full px-3 py-2 border rounded-lg"></div>
                <div><label class="block text-sm font-medium mb-1">DB Password</label><input type="password" name="db_pass" class="w-full px-3 py-2 border rounded-lg"></div>
            </div>
            <button type="submit" class="mt-6 w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium">Test & Migrate</button>
        </form>
    </div>
</body>
</html>
