@extends('layouts.app')

@section('title', $viewingRequest->reference.' | SCi-Safety')

@section('content')
    @php
        $journey = $viewingRequest->journey();
        $reviewer = in_array(auth()->user()->role, [\App\Enums\UserRole::Director, \App\Enums\UserRole::AssociateDean, \App\Enums\UserRole::PdpaCoordinator], true);
        $calendarStart = $viewingRequest->appointment_at?->copy()->utc()->format('Ymd\THis\Z');
        $calendarEnd = $viewingRequest->appointment_at?->copy()->addHour()->utc()->format('Ymd\THis\Z');
        $calendarTitle = rawurlencode('นัดดูภาพ '.$viewingRequest->reference);
        $calendarPlace = rawurlencode($viewingRequest->appointment_place ?? 'ห้องควบคุมความปลอดภัย คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น');
        $mapsQuery = rawurlencode('คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น '.($viewingRequest->appointment_place ?? ''));
    @endphp
    <article class="card page">
        <a class="back" href="{{ route('requests.index') }}" style="color:var(--accent);font-weight:700;text-decoration:none">กลับคำขอของฉัน</a>
        <h1 style="margin-bottom:0.2rem">{{ $viewingRequest->reference }}</h1>
        <span class="pill pill-{{ $viewingRequest->status->tone() }}">{{ $viewingRequest->statusLabel() }}</span>
        <p>{{ $journey['next'] }}</p>
        <div class="stepper" aria-label="ความคืบหน้าของคำขอ">
            @foreach ($journey['steps'] as $step)
                <span class="{{ $step['state'] }}">{{ $step['label'] }}</span>
            @endforeach
        </div>

        @if ($reviewer)
            <section class="card" style="padding:1rem;box-shadow:none;margin:1rem 0;background:var(--sand)">
                <h2 style="margin-top:0">สรุปสำหรับผู้พิจารณา</h2>
                <p>{{ $viewingRequest->incident_type->label() }} ที่ {{ $viewingRequest->building }} {{ $viewingRequest->location }} ระหว่าง {{ $viewingRequest->started_at->format('d/m/Y H:i') }} ถึง {{ $viewingRequest->ended_at->format('d/m/Y H:i') }}</p>
                <p>ผู้ยื่นต้องการ: {{ $viewingRequest->purpose }}</p>
                <p>ผลตรวจของเจ้าหน้าที่: {{ $viewingRequest->technical_note ?: 'ยังไม่มีบันทึกผลตรวจ' }}</p>
            </section>
        @endif

        @if ($viewingRequest->appointment_at)
            <section class="card" style="padding:1rem;box-shadow:none;margin:1rem 0">
                <h2 style="margin-top:0">นัดดูภาพ</h2>
                <p>{{ $viewingRequest->appointment_at->format('d/m/Y H:i') }} ที่ {{ $viewingRequest->appointment_place }}</p>
                <p>ผู้ร่วม: {{ $viewingRequest->participants }}</p>
                <p>
                    <a class="btn btn-quiet" href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ $calendarTitle }}&dates={{ $calendarStart }}/{{ $calendarEnd }}&location={{ $calendarPlace }}">เพิ่มใน Google Calendar</a>
                    <a class="btn btn-quiet" href="https://outlook.office.com/calendar/0/deeplink/compose?subject={{ $calendarTitle }}&startdt={{ $viewingRequest->appointment_at->toIso8601String() }}&enddt={{ $viewingRequest->appointment_at->copy()->addHour()->toIso8601String() }}&location={{ $calendarPlace }}">เพิ่มใน Outlook</a>
                    <a class="btn btn-quiet" href="https://www.google.com/maps/search/?api=1&query={{ $mapsQuery }}" target="_blank" rel="noopener">เส้นทางห้องควบคุม</a>
                </p>
            </section>
        @endif

        <dl class="grid-2">
            <div><dt>ผู้ยื่น</dt><dd>{{ $viewingRequest->user->name }} · {{ $viewingRequest->requesterKindLabel() }}</dd></div>
            <div><dt>ติดต่อ</dt><dd>{{ $viewingRequest->phone }}</dd></div>
            <div><dt>ประเภท</dt><dd>{{ $viewingRequest->incident_type->label() }}</dd></div>
            <div><dt>ความเร่งด่วน</dt><dd>{{ $viewingRequest->urgencyLabel() }}</dd></div>
            <div><dt>ช่วงเวลา</dt><dd>{{ $viewingRequest->started_at->format('d/m/Y H:i') }} – {{ $viewingRequest->ended_at->format('d/m/Y H:i') }}</dd></div>
            <div><dt>สถานที่</dt><dd>{{ $viewingRequest->building }} {{ $viewingRequest->floor }} {{ $viewingRequest->location }} {{ $viewingRequest->landmark }}</dd></div>
            <div><dt>วัตถุประสงค์</dt><dd>{{ $viewingRequest->purpose }}</dd></div>
            <div><dt>รายละเอียด</dt><dd>{{ $viewingRequest->details }}</dd></div>
        </dl>
        @if ($viewingRequest->technical_note)
            <p><strong>บันทึกเจ้าหน้าที่:</strong> {{ $viewingRequest->technical_note }}</p>
        @endif
        <p>การอนุญาตหมายถึงให้นัดดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ</p>

        @if ($viewingRequest->user_id === auth()->id() && $viewingRequest->status === \App\Enums\RequestStatus::MoreInfo)
            <form method="POST" action="{{ route('requests.supplement', $viewingRequest) }}">
                @csrf
                <h2>ข้อมูลที่เจ้าหน้าที่ขอเพิ่ม</h2>
                <p>พิมพ์รายละเอียดหรือเลขที่เอกสาร เช่น ใบแจ้งความ ได้ที่นี่ โดยไม่ต้องแนบภาพหรือวิดีโอ</p>
                <label class="field">ข้อความถึงเจ้าหน้าที่<textarea name="supplement_note" required>{{ old('supplement_note') }}</textarea></label>
                <button class="btn btn-solid" type="submit">ส่งข้อมูลเพิ่ม</button>
            </form>
        @endif

        @if ($viewingRequest->user_id === auth()->id() && $viewingRequest->status === \App\Enums\RequestStatus::Scheduled && ! $viewingRequest->appointment_confirmed_at)
            <form method="POST" action="{{ route('requests.confirm', $viewingRequest) }}">
                @csrf
                <button class="btn btn-solid" type="submit">ยืนยันว่ามาตามนัดได้</button>
            </form>
        @endif

        @if (auth()->user()->hasRole(\App\Enums\UserRole::CctvAdmin) && $viewingRequest->status === \App\Enums\RequestStatus::Submitted)
            <form id="camera" method="POST" action="{{ route('requests.technical', $viewingRequest) }}">
                @csrf
                <h2>บันทึกผลกล้อง</h2>
                <label class="field">ผลที่พบนอกระบบ
                    <select name="technical_result" required>
                        @foreach ($technicalResults as $result)
                            <option value="{{ $result->value }}">{{ $result->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field">ความเห็น<textarea name="technical_note" required></textarea></label>
                <label class="field">หากไม่มีภาพ ให้ระบุความสำคัญของจุดนี้
                    <select name="gap_priority"><option value="medium">ปานกลาง</option><option value="high">สูง</option><option value="low">ต่ำ</option></select>
                </label>
                <label class="check"><input type="checkbox" name="high_risk" value="1"> <span>เรื่องนี้อาจกระทบบุคคลจำนวนมาก ส่งให้ผู้ประสานงานข้อมูลส่วนบุคคลดูก่อน</span></label>
                <label class="check"><input type="checkbox" name="send_to_dean" value="1"> <span>ส่งต่อผู้บริหารทันที เพราะเหตุเร่งด่วน</span></label>
                <button class="btn btn-solid" type="submit">บันทึกผลกล้อง</button>
            </form>
            <form method="POST" action="{{ route('requests.more', $viewingRequest) }}">
                @csrf
                <label class="field">หากข้อมูลยังไม่พอ บอกผู้ยื่นอย่างสุภาพ<textarea name="more_info_note" required></textarea></label>
                <button class="btn btn-quiet" type="submit">ขอข้อมูลเพิ่ม</button>
            </form>
        @endif

        @if (auth()->user()->role === \App\Enums\UserRole::Director && $viewingRequest->status === \App\Enums\RequestStatus::PendingDirector)
            @include('requests.partials.decision', ['allowEscalate' => true])
        @endif
        @if (auth()->user()->role === \App\Enums\UserRole::AssociateDean && $viewingRequest->status === \App\Enums\RequestStatus::PendingDean)
            @include('requests.partials.decision', ['allowEscalate' => false])
        @endif
        @if (auth()->user()->role === \App\Enums\UserRole::PdpaCoordinator && $viewingRequest->status === \App\Enums\RequestStatus::PendingPdpa)
            <form method="POST" action="{{ route('requests.pdpa', $viewingRequest) }}">
                @csrf
                <label class="field">ความเห็นด้านข้อมูลส่วนบุคคล<textarea name="pdpa_opinion" required></textarea></label>
                <button class="btn btn-solid" type="submit">บันทึกความเห็น</button>
            </form>
        @endif

        @if (auth()->user()->hasRole(\App\Enums\UserRole::CctvAdmin) && $viewingRequest->status === \App\Enums\RequestStatus::Approved)
            <form id="schedule" method="POST" action="{{ route('requests.schedule', $viewingRequest) }}">
                @csrf
                <h2>เปิดนัดดูภาพ</h2>
                <label class="field">วันเวลา<input type="datetime-local" name="appointment_at" required></label>
                <label class="field">ห้องควบคุม<input name="appointment_place" value="ห้องควบคุมความปลอดภัย คณะวิทยาศาสตร์" required></label>
                <label class="field">ผู้ที่จะเข้าห้อง<input name="participants" required></label>
                <button class="btn btn-solid" type="submit">บันทึกนัดหมาย</button>
            </form>
        @endif

        @if (auth()->user()->hasRole(\App\Enums\UserRole::CctvAdmin) && $viewingRequest->status === \App\Enums\RequestStatus::Scheduled && $viewingRequest->appointment_confirmed_at)
            <form method="POST" action="{{ route('requests.viewing', $viewingRequest) }}">
                @csrf
                <h2>บันทึกหลังการดูภาพ</h2>
                <div class="grid-2">
                    <label class="field">เริ่ม<input type="datetime-local" name="viewing_started_at" required></label>
                    <label class="field">สิ้นสุด<input type="datetime-local" name="viewing_ended_at" required></label>
                </div>
                <label class="field">ข้อสรุป<textarea name="viewing_summary" required></textarea></label>
                <label class="check"><input type="checkbox" name="controlled" value="1" required> <span>ยืนยันว่าควบคุมการดูภาพแล้ว ไม่มีการถ่ายภาพหน้าจอ อัดวิดีโอ หรือผู้ที่ไม่เกี่ยวข้อง</span></label>
                <button class="btn btn-solid" type="submit">บันทึกการดูภาพ</button>
            </form>
        @endif

        @if (auth()->user()->hasRole(\App\Enums\UserRole::CctvAdmin) && in_array($viewingRequest->status, [\App\Enums\RequestStatus::Viewed, \App\Enums\RequestStatus::NoFootage, \App\Enums\RequestStatus::Rejected], true))
            <form method="POST" action="{{ route('requests.close', $viewingRequest) }}">
                @csrf
                <button class="btn btn-quiet" type="submit">ปิดคำขอ</button>
            </form>
        @endif
    </article>
@endsection
