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
    .panel-title {
        margin: 0.35rem 0 0.75rem;
        font-size: 1.05rem;
        color: var(--ink);
    }
    .panel-title small {
        display: block;
        margin-top: 0.2rem;
        color: var(--muted);
        font-weight: 500;
        font-size: 0.92rem;
    }
    .sections {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.9rem;
        margin-bottom: 1.15rem;
    }
    .sections.sections-work {
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
    .section {
        display: block;
        padding: 1.2rem 1.2rem 1.3rem;
        color: inherit;
        text-decoration: none;
        border: 1px solid var(--line);
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .section:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(28, 25, 23, 0.08);
    }
    .open {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        margin-top: 0.9rem;
        font-weight: 700;
    }
    .section-icon {
        width: 2.7rem;
        height: 2.7rem;
        border-radius: 0.9rem;
        display: grid;
        place-items: center;
        margin-bottom: 0.8rem;
    }
    .section h2 { margin: 0 0 0.35rem; font-size: 1.1rem; }
    .section p { margin: 0; color: var(--muted); line-height: 1.55; }

    .tone-red {
        background: linear-gradient(180deg, #fff1f2 0%, #ffffff 58%);
        border-color: #fecdd3;
    }
    .tone-red:hover { border-color: #e11d48; }
    .tone-red .section-icon { background: #e11d48; color: #fff; }
    .tone-red .open { color: #be123c; }

    .tone-green {
        background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 58%);
        border-color: #a7f3d0;
    }
    .tone-green:hover { border-color: #059669; }
    .tone-green .section-icon { background: #059669; color: #fff; }
    .tone-green .open { color: #047857; }

    .tone-rose {
        background: linear-gradient(180deg, #fff1f2 0%, #ffffff 60%);
        border-color: #fecdd3;
    }
    .tone-rose:hover { border-color: #9f1239; }
    .tone-rose .section-icon { background: #9f1239; color: #fff; }
    .tone-rose .open { color: #9f1239; }

    .tone-blue {
        background: linear-gradient(180deg, #eff6ff 0%, #ffffff 60%);
        border-color: #bfdbfe;
    }
    .tone-blue:hover { border-color: #2563eb; }
    .tone-blue .section-icon { background: #2563eb; color: #fff; }
    .tone-blue .open { color: #1d4ed8; }

    .tone-teal {
        background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 60%);
        border-color: #99f6e4;
    }
    .tone-teal:hover { border-color: #0d9488; }
    .tone-teal .section-icon { background: #0d9488; color: #fff; }
    .tone-teal .open { color: #0f766e; }

    .tone-amber {
        background: linear-gradient(180deg, #fffbeb 0%, #ffffff 60%);
        border-color: #fde68a;
    }
    .tone-amber:hover { border-color: #d97706; }
    .tone-amber .section-icon { background: #d97706; color: #fff; }
    .tone-amber .open { color: #b45309; }

    .tone-slate {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 60%);
        border-color: #e2e8f0;
    }
    .tone-slate:hover { border-color: #475569; }
    .tone-slate .section-icon { background: #475569; color: #fff; }
    .tone-slate .open { color: #334155; }

    .tone-indigo {
        background: linear-gradient(180deg, #eef2ff 0%, #ffffff 60%);
        border-color: #c7d2fe;
    }
    .tone-indigo:hover { border-color: #4f46e5; }
    .tone-indigo .section-icon { background: #4f46e5; color: #fff; }
    .tone-indigo .open { color: #4338ca; }

    .personal-panel {
        margin-top: 0.35rem;
        padding-top: 0.35rem;
        border-top: 1px dashed var(--line);
    }
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
    @php
        $role = auth()->user()->role;
        $isStaffWorkspace = in_array($role, [
            \App\Enums\UserRole::CctvAdmin,
            \App\Enums\UserRole::Director,
            \App\Enums\UserRole::AssociateDean,
            \App\Enums\UserRole::PdpaCoordinator,
            \App\Enums\UserRole::SuperAdmin,
        ], true);

        $work = match ($role) {
            \App\Enums\UserRole::CctvAdmin => [
                ['queue', 'clipboard', 'ตรวจสอบคำขอ', 'ตรวจคำขอ บันทึกผลกล้อง และนัดดูภาพภายใต้การควบคุม', 'tone-amber'],
                ['cameras.index', 'camera', 'ทะเบียนกล้อง', 'ตรวจเช็กรายเดือน งานซ่อม และจุดที่ยังไม่มีกล้อง', 'tone-blue'],
            ],
            \App\Enums\UserRole::Director, \App\Enums\UserRole::AssociateDean => [
                ['reviews', 'scale', 'พิจารณาคำขอ', 'เห็นชอบ ขอข้อมูลเพิ่ม หรือส่งต่อกรณีสำคัญ', 'tone-blue'],
                ['reports.index', 'chart', 'รายงาน', 'ดูสถิติและส่งออกโดยซ่อนข้อมูลส่วนบุคคล', 'tone-amber'],
            ],
            \App\Enums\UserRole::PdpaCoordinator => [
                ['privacy.edit', 'document', 'ประกาศความเป็นส่วนตัว', 'ตรวจและเผยแพร่รุ่นใหม่ของประกาศ', 'tone-slate'],
            ],
            \App\Enums\UserRole::SuperAdmin => [
                ['admin.users', 'users', 'กำหนดสิทธิ์', 'กำหนดบทบาทและเจ้าหน้าที่สำรองตามช่วงเวลา', 'tone-rose'],
                ['admin.rules', 'route', 'เส้นทางคำขอ', 'ปรับว่าคำขอประเภทใดต้องผ่านผู้ใด', 'tone-blue'],
                ['admin.audit', 'document', 'บันทึกการใช้งาน', 'ตรวจย้อนหลังแบบอ่านอย่างเดียว', 'tone-slate'],
                ['reports.index', 'chart', 'รายงาน', 'ดูภาพรวมและจุดบกพร่องเพื่อประกอบคำของบประมาณ', 'tone-amber'],
            ],
            default => [],
        };
    @endphp

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

    @if ($isStaffWorkspace)
        <h2 class="panel-title">
            งานหลักของคุณ
            <small>เริ่มจากงานตามสิทธิ์ก่อน แล้วค่อยใช้เมนูคำขอส่วนตัวด้านล่าง</small>
        </h2>
        <section class="sections sections-work">
            @foreach ($work as [$routeName, $icon, $heading, $text, $tone])
                <a class="card section {{ $tone }}" href="{{ route($routeName) }}">
                    <div class="section-icon"><x-icon :name="$icon" /></div>
                    <h2>{{ $heading }}</h2>
                    <p>{{ $text }}</p>
                    <span class="open">เปิดงาน <x-icon name="arrow" /></span>
                </a>
            @endforeach
        </section>

        @if (auth()->user()->isActiveBackup())
            <section class="sections sections-work">
                <a class="card section tone-rose" href="{{ route('queue') }}">
                    <div class="section-icon"><x-icon name="shield" /></div>
                    <h2>คิวในฐานะเจ้าหน้าที่สำรอง</h2>
                    <p>ช่วงนี้ท่านได้รับมอบหมายให้ทำหน้าที่แทนเจ้าหน้าที่หลัก</p>
                    <span class="open">เปิดคิว <x-icon name="arrow" /></span>
                </a>
            </section>
        @endif

        <div class="personal-panel">
            <h2 class="panel-title">
                คำขอส่วนตัว
                <small>ใช้เมื่อต้องการยื่นคำขอหรือติดตามนัดดูภาพของบัญชีนี้</small>
            </h2>
            <section class="sections">
                <a class="card section tone-red" href="{{ route('requests.create') }}">
                    <div class="section-icon"><x-icon name="help" /></div>
                    <h2>แจ้งเรื่องที่ต้องการดูภาพ</h2>
                    <p>ยื่นคำขอให้เจ้าหน้าที่ช่วยตรวจภาพ จากนั้นติดตามสถานะและวันนัดได้ด้านขวา</p>
                    <span class="open">เริ่มแจ้งเรื่อง <x-icon name="arrow" /></span>
                </a>
                <a class="card section tone-green" href="{{ route('requests.index') }}">
                    <div class="section-icon"><x-icon name="clipboard" /></div>
                    <h2>ติดตามคำขอและนัดดูภาพ</h2>
                    <p>ดูสถานะคำขอของบัญชีนี้ รวมถึงวัน เวลา และห้องควบคุมเมื่อได้รับนัด</p>
                    <span class="open">เปิดรายการคำขอ <x-icon name="arrow" /></span>
                </a>
            </section>
        </div>
    @else
        <section class="sections">
            <a class="card section tone-red" href="{{ route('requests.create') }}">
                <div class="section-icon"><x-icon name="help" /></div>
                <h2>แจ้งเรื่องที่ต้องการดูภาพ</h2>
                <p>ยื่นคำขอให้เจ้าหน้าที่ช่วยตรวจภาพ จากนั้นติดตามสถานะและวันนัดดูภาพได้ในหน้าติดตามคำขอ</p>
                <span class="open">เริ่มแจ้งเรื่อง <x-icon name="arrow" /></span>
            </a>
            <a class="card section tone-green" href="{{ route('requests.index') }}">
                <div class="section-icon"><x-icon name="clipboard" /></div>
                <h2>ติดตามคำขอและนัดดูภาพ</h2>
                <p>ดูสถานะคำขอของท่าน รวมถึงวัน เวลา และห้องควบคุมเมื่อได้รับนัดดูภาพ</p>
                <span class="open">เปิดรายการคำขอ <x-icon name="arrow" /></span>
            </a>
        </section>

        @if (auth()->user()->isActiveBackup())
            <section class="sections sections-work">
                <a class="card section tone-rose" href="{{ route('queue') }}">
                    <div class="section-icon"><x-icon name="shield" /></div>
                    <h2>คิวในฐานะเจ้าหน้าที่สำรอง</h2>
                    <p>ช่วงนี้ท่านได้รับมอบหมายให้ทำหน้าที่แทนเจ้าหน้าที่หลัก</p>
                    <span class="open">เปิดคิว <x-icon name="arrow" /></span>
                </a>
            </section>
        @endif
    @endif

    <div class="notice">
        <x-icon name="lock" />
        <span>ใช้สำหรับแจ้งเรื่องที่ต้องการดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ</span>
    </div>
@endsection
