@extends('layouts.app')

@section('title', 'หน้าหลัก | SCi-Safety')

@push('styles')
<style>
    .welcome { padding: 1.5rem 1.6rem; margin-bottom: 1rem; }
    .who { display: flex; gap: 1rem; align-items: center; }
    .avatar, .fallback {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 50%;
        object-fit: cover;
        background: var(--help);
        color: white;
        display: grid;
        place-items: center;
        font-weight: 700;
        flex: none;
        box-shadow: 0 0 0 3px var(--sand);
    }
    h1 { margin: 0; font-size: 1.55rem; }
    .meta { color: var(--muted); margin: 0.15rem 0 0; }
    .chips { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.32rem 0.75rem;
        background: var(--info-bg);
        color: var(--help);
        font-weight: 700;
    }
    .chip .icon { width: 1rem; height: 1rem; }
    .sections {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.9rem;
        margin-bottom: 1rem;
    }
    .section {
        display: block;
        padding: 1.15rem 1.15rem 1.25rem;
        color: inherit;
        text-decoration: none;
        transition: transform 0.15s ease, border-color 0.15s ease;
    }
    .section:hover { transform: translateY(-2px); border-color: var(--help); }
    .open {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.8rem;
        color: var(--help);
        font-weight: 700;
    }
    .section-icon {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.85rem;
        display: grid;
        place-items: center;
        background: var(--sand);
        color: var(--help);
        margin-bottom: 0.75rem;
    }
    .section h2 { margin: 0 0 0.35rem; font-size: 1.08rem; }
    .section p { margin: 0; color: var(--muted); }
    .notice {
        display: flex;
        gap: 0.7rem;
        align-items: flex-start;
        margin-bottom: 2rem;
        padding: 1rem 1.1rem;
        border-radius: 1rem;
        background: white;
        border: 1px solid var(--line);
        color: var(--ink);
    }
    .notice .icon { color: var(--gold-deep); margin-top: 0.15rem; }
    @media (max-width: 840px) {
        .sections { grid-template-columns: 1fr; }
        .logo { width: 3.4rem; height: 3.4rem; }
    }
</style>
@endpush

@section('content')
    <section class="card welcome">
        <div class="who">
            @if (auth()->user()->avatar)
                <img class="avatar" src="{{ auth()->user()->avatar }}" alt="">
            @else
                <div class="fallback">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            @endif
            <div>
                <h1>{{ auth()->user()->name }}</h1>
                <p class="meta">{{ auth()->user()->email }}</p>
            </div>
        </div>
        <div class="chips">
            <span class="chip"><x-icon name="person" /> {{ auth()->user()->account_type->label() }}</span>
            <span class="chip"><x-icon name="shield" /> {{ auth()->user()->role->label() }}</span>
        </div>
    </section>

    <section class="sections">
        <a class="card section" href="{{ route('requests.create') }}">
            <div class="section-icon"><x-icon name="help" /></div>
            <h2>แจ้งเรื่องที่ต้องการให้ช่วยดู</h2>
            <p>เล่าเหตุการณ์เป็นขั้นสั้น ๆ เจ้าหน้าที่จะรับเรื่องและติดต่อกลับบนหน้าติดตามสถานะ</p>
            <span class="open">เริ่มยื่นคำขอ <x-icon name="arrow" /></span>
        </a>
        <a class="card section" href="{{ route('appointments.index') }}">
            <div class="section-icon"><x-icon name="calendar" /></div>
            <h2>นัดหมายดูภาพ</h2>
            <p>หากได้รับอนุญาต จะดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ตามวัน เวลา และสถานที่ที่กำหนด</p>
            <span class="open">ดูสถานะนัดหมาย <x-icon name="arrow" /></span>
        </a>
        <a class="card section" href="{{ route('requests.index') }}">
            <div class="section-icon"><x-icon name="clipboard" /></div>
            <h2>ติดตามคำขอ</h2>
            <p>
                @if (auth()->user()->role === \App\Enums\UserRole::Requester)
                    ในฐานะ{{ auth()->user()->account_type->label() }} ท่านเห็นเฉพาะคำขอของตนเอง
                @else
                    ติดตามสถานะคำขอที่อยู่ในความรับผิดชอบ
                @endif
            </p>
            <span class="open">ดูคำขอของฉัน <x-icon name="arrow" /></span>
        </a>
    </section>

    @php
        $role = auth()->user()->role;
        $work = match ($role) {
            \App\Enums\UserRole::CctvAdmin => [
                ['queue', 'clipboard', 'คิวเจ้าหน้าที่', 'ตรวจคำขอ บันทึกผลกล้อง และนัดดูภาพภายใต้การควบคุม'],
                ['cameras.index', 'camera', 'ทะเบียนกล้อง', 'ตรวจเช็กรายเดือน งานซ่อม และจุดที่ยังไม่มีกล้อง'],
            ],
            \App\Enums\UserRole::Director, \App\Enums\UserRole::AssociateDean => [
                ['reviews', 'scale', 'พิจารณาคำขอ', 'เห็นชอบ ขอข้อมูลเพิ่ม หรือส่งต่อกรณีสำคัญ'],
                ['reports.index', 'chart', 'รายงาน', 'ดูสถิติและส่งออกโดยซ่อนข้อมูลส่วนบุคคล'],
            ],
            \App\Enums\UserRole::PdpaCoordinator => [
                ['pdpa.inbox', 'shield', 'ความเห็นข้อมูลส่วนบุคคล', 'ให้ความเห็นคำขอที่มีความเสี่ยงด้านสิทธิสูง'],
                ['privacy.edit', 'document', 'ประกาศความเป็นส่วนตัว', 'ตรวจและเผยแพร่รุ่นใหม่ของประกาศ'],
            ],
            \App\Enums\UserRole::SuperAdmin => [
                ['admin.users', 'users', 'กำหนดสิทธิ์', 'กำหนดบทบาทและเจ้าหน้าที่สำรองตามช่วงเวลา'],
                ['admin.rules', 'route', 'เส้นทางคำขอ', 'ปรับว่าคำขอประเภทใดต้องผ่านผู้ใด'],
                ['admin.audit', 'document', 'บันทึกการใช้งาน', 'ตรวจย้อนหลังแบบอ่านอย่างเดียว'],
                ['reports.index', 'chart', 'รายงาน', 'ดูภาพรวมและจุดบกพร่องเพื่อประกอบคำของบประมาณ'],
            ],
            default => [],
        };
    @endphp
    @if ($work !== [])
        <section class="sections">
            @foreach ($work as [$routeName, $icon, $heading, $text])
                <a class="card section" href="{{ route($routeName) }}">
                    <div class="section-icon"><x-icon :name="$icon" /></div>
                    <h2>{{ $heading }}</h2>
                    <p>{{ $text }}</p>
                    <span class="open">เปิดงาน <x-icon name="arrow" /></span>
                </a>
            @endforeach
        </section>
    @endif
    @if (auth()->user()->isActiveBackup())
        <section class="sections">
            <a class="card section" href="{{ route('queue') }}">
                <div class="section-icon"><x-icon name="shield" /></div>
                <h2>คิวในฐานะเจ้าหน้าที่สำรอง</h2>
                <p>ช่วงนี้ท่านได้รับมอบหมายให้ทำหน้าที่แทนเจ้าหน้าที่หลัก</p>
                <span class="open">เปิดคิว <x-icon name="arrow" /></span>
            </a>
        </section>
    @endif

    <div class="notice">
        <x-icon name="lock" />
        <span>ใช้สำหรับยื่นคำขอและนัดหมายดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ</span>
    </div>
@endsection
