@extends('layouts.app')

@section('title', 'ทะเบียนกล้อง')

@section('content')
    <section class="card page">
        <h1>ทะเบียนกล้อง</h1>
        <form method="POST" action="{{ route('cameras.store') }}">
            @csrf
            <div class="grid-2">
                <label class="field">รหัส<input name="code" required></label>
                <label class="field">ชื่อ<input name="name" required></label>
                <label class="field">อาคาร<input name="building" required></label>
                <label class="field">ชั้น<input name="floor"></label>
                <label class="field">มุมที่ครอบคลุม<input name="coverage" required></label>
                <label class="field">เก็บภาพ (วัน)<input type="number" name="retention_days" value="30" min="1" required></label>
                <label class="field">สถานะ
                    <select name="status"><option value="normal">ปกติ</option><option value="broken">ชำรุด</option><option value="waiting_repair">รอซ่อม</option><option value="closed">ปิดปรับปรุง</option></select>
                </label>
            </div>
            <button class="btn btn-solid" type="submit">เพิ่มกล้อง</button>
        </form>
        <h2>จุดเสี่ยงที่ไม่มีกล้อง</h2>
        <form method="POST" action="{{ route('cameras.gaps') }}">
            @csrf
            <div class="grid-2">
                <label class="field">อาคาร<input name="building" required></label>
                <label class="field">บริเวณ<input name="location" required></label>
                <label class="field">เหตุผล<input name="reason" required></label>
                <label class="field">ความสำคัญ
                    <select name="priority"><option value="high">สูง</option><option value="medium">ปานกลาง</option><option value="low">ต่ำ</option></select>
                </label>
            </div>
            <button class="btn btn-quiet" type="submit">บันทึกจุดเสี่ยง</button>
        </form>
        @foreach ($gaps as $gap)
            <p>{{ $gap->building }} {{ $gap->location }} · {{ $gap->reason }} · {{ $gap->priority }}</p>
        @endforeach
        <table>
            @foreach ($cameras as $camera)
                <tr><td>{{ $camera->code }}</td><td>{{ $camera->name }}</td><td>{{ $camera->building }}</td><td>{{ $camera->statusLabel() }}</td><td>{{ $camera->retention_days }} วัน</td></tr>
            @endforeach
        </table>
    </section>
    @if (auth()->user()->hasRole(\App\Enums\UserRole::CctvAdmin))
        <section class="card page">
            <h2>ตรวจเช็กเดือน {{ now()->format('m/Y') }}</h2>
            @foreach ($inspections as $inspection)
                <form method="POST" action="{{ route('inspections.update', $inspection) }}">
                    @csrf
                    <strong>{{ $inspection->camera->code }}</strong> {{ $inspection->resultLabel() }}
                    <select name="result">
                        <option value="normal">ปกติ</option>
                        <option value="shifted">มุมเบี่ยง</option>
                        <option value="blur">เลนส์มัว</option>
                        <option value="no_signal">สัญญาณขาดหาย</option>
                    </select>
                    <input name="note" placeholder="หมายเหตุ">
                    <button class="btn btn-quiet" type="submit">บันทึกผลตรวจ</button>
                </form>
            @endforeach
            <h2>งานซ่อม</h2>
            @foreach ($tickets as $ticket)
                <form method="POST" action="{{ route('tickets.update', $ticket) }}">
                    @csrf
                    {{ $ticket->inspection->camera->code }} · {{ $ticket->statusLabel() }}
                    <select name="status"><option value="open">เปิดงาน</option><option value="in_progress">กำลังซ่อม</option><option value="done">ซ่อมเสร็จ</option></select>
                    <button class="btn btn-quiet" type="submit">อัปเดต</button>
                </form>
            @endforeach
        </section>
    @endif
@endsection
