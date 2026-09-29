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
        background: var(--rose);
        color: var(--help-deep);
        font-weight: 700;
    }
    .chip .icon { width: 1rem; height: 1rem; }
    .sections {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.9rem;
        margin-bottom: 1rem;
    }
    .section { padding: 1.15rem 1.15rem 1.25rem; }
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
        <article class="card section">
            <div class="section-icon"><x-icon name="help" /></div>
            <h2>แจ้งขอความช่วยเหลือ</h2>
            <p>ยื่นคำขอเมื่อทรัพย์สินสูญหาย เกิดอุบัติเหตุ หรือต้องการให้เจ้าหน้าที่ตรวจสอบเหตุการณ์</p>
        </article>
        <article class="card section">
            <div class="section-icon"><x-icon name="calendar" /></div>
            <h2>นัดหมายดูภาพ</h2>
            <p>หากได้รับอนุญาต จะดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ตามวัน เวลา และสถานที่ที่กำหนด</p>
        </article>
        <article class="card section">
            <div class="section-icon"><x-icon name="clipboard" /></div>
            <h2>ติดตามคำขอ</h2>
            <p>
                @if (auth()->user()->role === \App\Enums\UserRole::Requester)
                    ในฐานะ{{ auth()->user()->account_type->label() }} ท่านเห็นเฉพาะคำขอของตนเอง
                @else
                    ติดตามสถานะคำขอที่อยู่ในความรับผิดชอบ
                @endif
            </p>
        </article>
    </section>

    <div class="notice">
        <x-icon name="lock" />
        <span>ใช้สำหรับยื่นคำขอตรวจสอบหรือนัดหมายดูภาพภายใต้การควบคุมของเจ้าหน้าที่ ระบบไม่ได้เปิดไฟล์ภาพหรือวิดีโอจากกล้องวงจรปิด และไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ</span>
    </div>
@endsection
