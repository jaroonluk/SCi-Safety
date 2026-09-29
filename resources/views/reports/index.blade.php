@extends('layouts.app')

@section('title', 'รายงาน')

@section('content')
    <section class="card page">
        <h1>รายงานสำหรับผู้บริหาร</h1>
        <div class="grid-2">
            <p>จำนวนคำขอทั้งหมด {{ $total }}</p>
            <p>คำขอค้างดำเนินการ {{ $pending }}</p>
            <p>สัดส่วนที่เห็นชอบให้นัดดูภาพ {{ $approvalRate }}%</p>
            <p>ระยะเวลาเฉลี่ยจนปิดเรื่อง {{ $averageDays }} วัน</p>
            <p>กล้องในทะเบียน {{ $cameras }} ตัว</p>
            <p>งานซ่อมที่ยังไม่เสร็จ {{ $openTickets }} งาน</p>
        </div>
        <h2>จุดบกพร่องของกล้องเพื่อประกอบคำของบประมาณ</h2>
        @forelse ($gaps as $gap)
            <p>{{ $gap->building }} {{ $gap->location }} · {{ $gap->reason }} · ความสำคัญ {{ $gap->priority }}</p>
        @empty
            <p>ยังไม่มีจุดบกพร่องที่บันทึกจากคำขอ</p>
        @endforelse
        <a class="btn btn-solid" href="{{ route('reports.export') }}">ส่งออก Excel โดยซ่อนข้อมูลส่วนบุคคล</a>
        <button class="btn btn-quiet" type="button" onclick="window.print()">พิมพ์รายงานเป็น PDF</button>
    </section>
@endsection
