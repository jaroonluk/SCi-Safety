@extends('layouts.app')

@section('title', 'ประกาศความเป็นส่วนตัว | SCi-Safety')

@section('content')
    <article class="card page">
        <div class="pill">รุ่น {{ $notice->version }}</div>
        <h1>ประกาศความเป็นส่วนตัว</h1>
        <div style="white-space:pre-wrap">{{ $notice->body }}</div>
    </article>
@endsection
