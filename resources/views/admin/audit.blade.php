@extends('layouts.app')

@section('title', 'บันทึกการใช้งาน')

@section('content')
    <section class="card page">
        <h1>บันทึกการใช้งาน</h1>
        <p>อ่านได้อย่างเดียว ไม่มีทางแก้ไขหรือลบ รวมถึงผู้ดูแลระบบสูงสุด</p>
        <table>
            <tr><th>เวลา</th><th>ผู้ใช้</th><th>การกระทำ</th><th>รายละเอียด</th></tr>
            @foreach ($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->user->email ?? '-' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ json_encode($log->properties, JSON_UNESCAPED_UNICODE) }}</td>
                </tr>
            @endforeach
        </table>
    </section>
@endsection
