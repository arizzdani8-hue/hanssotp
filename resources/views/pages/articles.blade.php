@extends('layouts.app')
@section('title', 'Artikel')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold">Artikel</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($articles as $article)
        <a href="{{ route('articles.show', $article->slug) }}" class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
            @if($article->featured_image)<img src="{{ asset('storage/' . $article->featured_image) }}" class="w-full h-48 object-cover">@endif
            <div class="p-4">
                <h3 class="font-bold mb-2">{{ $article->title }}</h3>
                <p class="text-sm text-gray-500 line-clamp-2">{{ $article->excerpt }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $article->published_at?->format('d M Y') }}</p>
            </div>
        </a>
        @endforeach
    </div>
    <div>{{ $articles->links() }}</div>
</div>
@endsection
