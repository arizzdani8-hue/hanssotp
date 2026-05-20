@extends('layouts.app')
@section('title', $article->meta_title ?? $article->title)
@section('meta_description', $article->meta_description ?? $article->excerpt)
@section('content')
<article class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-4">{{ $article->title }}</h1>
    <p class="text-gray-500 mb-6">{{ $article->published_at?->format('d M Y') }}</p>
    @if($article->featured_image)<img src="{{ asset('storage/' . $article->featured_image) }}" class="w-full rounded-xl mb-6">@endif
    <div class="prose dark:prose-invert max-w-none">{!! $article->content !!}</div>
</article>
@endsection
