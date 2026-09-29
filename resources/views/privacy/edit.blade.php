@extends('layouts.app')

@section('title', 'แก้ไขประกาศความเป็นส่วนตัว')

@section('content')
    <section class="card page">
        <h1>ประกาศความเป็นส่วนตัว</h1>
        <p>การเผยแพร่จะสร้างรุ่นใหม่ รุ่นเดิมยังถูกเก็บไว้พร้อมผู้เผยแพร่และเวลา</p>
        <form method="POST" action="{{ route('privacy.store') }}">
            @csrf
            <label class="field">รหัสรุ่น<input name="version" required placeholder="เช่น 2026-10-01"></label>
            <label class="field">ข้อความ<textarea name="body" required>{{ old('body', $notice->body) }}</textarea></label>
            <button class="btn btn-solid" type="submit">เผยแพร่รุ่นใหม่</button>
        </form>
        <h2>รุ่นที่เผยแพร่แล้ว</h2>
        <ul>
            @foreach ($history as $item)
                <li>{{ $item->version }} · {{ $item->created_at }}</li>
            @endforeach
        </ul>
    </section>
@endsection
