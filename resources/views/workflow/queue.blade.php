@extends('layouts.app')

@section('title', 'คิวเจ้าหน้าที่ | SCi-Safety')

@section('content')
    @php
        $urgent = $requests->filter(fn ($item) => $item->urgency === 'urgent' && in_array($item->status, [\App\Enums\RequestStatus::Submitted, \App\Enums\RequestStatus::MoreInfo, \App\Enums\RequestStatus::Approved, \App\Enums\RequestStatus::Scheduled], true));
        $checking = $requests->filter(fn ($item) => $item->urgency !== 'urgent' && in_array($item->status, [\App\Enums\RequestStatus::Submitted, \App\Enums\RequestStatus::MoreInfo], true));
        $appointing = $requests->filter(fn ($item) => $item->urgency !== 'urgent' && in_array($item->status, [\App\Enums\RequestStatus::Approved, \App\Enums\RequestStatus::Scheduled], true));
        $closed = $requests->filter(fn ($item) => in_array($item->status, [\App\Enums\RequestStatus::Viewed, \App\Enums\RequestStatus::Closed, \App\Enums\RequestStatus::Rejected, \App\Enums\RequestStatus::NoFootage], true));
        $lanes = [
            ['ต้องเร่งด่วน', 'help', $urgent],
            ['รอเช็กกล้อง', 'camera', $checking],
            ['รอนัดหมาย', 'calendar', $appointing],
            ['เคสปิดแล้ว', 'clipboard', $closed],
        ];
    @endphp
    <section class="page" style="padding:0">
        <h1>คิวเจ้าหน้าที่</h1>
        <p style="color:var(--muted)">เลือกการ์ดใบที่ต้องทำต่อได้เลย ไม่มีการเปิดไฟล์ภาพในหน้านี้</p>
        <div class="reassure">
            <x-icon name="clipboard" />
            <span>
                @if ($inspectionTotal === 0)
                    รอบตรวจกล้องเดือน {{ $inspectionPeriod }} ยังไม่มีกล้องในทะเบียน
                @else
                    รอบตรวจกล้องเดือน {{ $inspectionPeriod }} ตรวจแล้ว {{ $inspectionDone }} จาก {{ $inspectionTotal }} ตัว
                @endif
                <a href="{{ route('cameras.index') }}">เปิดรายการตรวจ</a>
            </span>
        </div>
        <div class="kanban">
            @foreach ($lanes as [$heading, $icon, $items])
                <section class="lane">
                    <h2><x-icon name="{{ $icon }}" /> {{ $heading }} · {{ $items->count() }}</h2>
                    @forelse ($items as $item)
                        @php
                            $action = match ($item->status) {
                                \App\Enums\RequestStatus::Approved => 'เปิดฟอร์มนัดหมาย',
                                \App\Enums\RequestStatus::Submitted => 'บันทึกผลกล้อง',
                                default => 'เปิดเรื่อง',
                            };
                            $anchor = $item->status === \App\Enums\RequestStatus::Approved ? '#schedule' : '#camera';
                        @endphp
                        <a class="card mini" href="{{ route('requests.show', $item) }}{{ $anchor }}">
                            <strong>{{ $item->reference }}</strong>
                            <span class="pill pill-{{ $item->status->tone() }}">{{ $item->statusLabel() }}</span>
                            <p style="margin:0.35rem 0 0;color:var(--muted)">{{ $item->incident_type->label() }} · {{ $item->building }}</p>
                            <span class="open" style="color:var(--accent);font-weight:700">{{ $action }}</span>
                        </a>
                    @empty
                        <p style="color:var(--muted)">ไม่มีเรื่องในกลุ่มนี้</p>
                    @endforelse
                </section>
            @endforeach
        </div>
    </section>
@endsection
