@extends('layouts.app')
@section('title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? '')
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">{{ $page->title }}</h1>
    <div class="prose dark:prose-invert max-w-none">{!! $page->content !!}</div>
</div>
@endsection
