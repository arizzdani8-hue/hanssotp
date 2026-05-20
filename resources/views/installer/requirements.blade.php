<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Requirements Check</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg max-w-lg w-full p-8">
        <h1 class="text-2xl font-bold mb-6">Cek Requirements</h1>
        <h3 class="font-bold mb-3">PHP Extensions</h3>
        <div class="space-y-2 mb-6">
            @foreach($requirements as $name => $passed)
            <div class="flex justify-between items-center text-sm">
                <span>{{ $name }}</span>
                <span class="{{ $passed ? 'text-green-600' : 'text-red-600' }}">{{ $passed ? '✓' : '✗' }}</span>
            </div>
            @endforeach
        </div>
        <h3 class="font-bold mb-3">Directory Permissions</h3>
        <div class="space-y-2 mb-6">
            @foreach($permissions as $dir => $writable)
            <div class="flex justify-between items-center text-sm">
                <span>{{ $dir }}</span>
                <span class="{{ $writable ? 'text-green-600' : 'text-red-600' }}">{{ $writable ? 'Writable' : 'Not Writable' }}</span>
            </div>
            @endforeach
        </div>
        @if(collect($requirements)->every(fn($v) => $v) && collect($permissions)->every(fn($v) => $v))
            <a href="{{ route('installer.database') }}" class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 font-medium text-center">Lanjut: Setup Database</a>
        @else
            <p class="text-red-600 text-sm">Mohon perbaiki requirements di atas sebelum melanjutkan.</p>
        @endif
    </div>
</body>
</html>
