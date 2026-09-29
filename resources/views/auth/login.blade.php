@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ | SCi-Safety')

@push('styles')
<style>
    .login-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1.15rem;
        align-items: stretch;
        padding-bottom: 2.5rem;
    }
    .intro, .panel { padding: 1.7rem 1.8rem 1.8rem; }
    h1 { font-size: clamp(1.7rem, 3vw, 2.35rem); line-height: 1.3; margin: 0.8rem 0 0.7rem; }
    .lead { font-size: 1.05rem; color: var(--muted); margin: 0 0 1.2rem; }
    .rules { display: grid; gap: 0.7rem; margin: 0; padding: 0; list-style: none; }
    .rules li {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        background: var(--sand);
        border-radius: 0.95rem;
        padding: 0.8rem 0.9rem;
    }
    .rule-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.7rem;
        display: grid;
        place-items: center;
        background: white;
        color: var(--help);
        flex: none;
    }
    .panel h2 { margin: 0 0 0.35rem; font-size: 1.4rem; }
    .panel > p { color: var(--muted); margin-top: 0; }
    .domains { display: grid; gap: 0.6rem; margin: 1rem 0 1.15rem; }
    .domain {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        padding: 0.75rem 0.85rem;
        border: 1px solid var(--line);
        border-radius: 0.95rem;
        background: white;
    }
    .domain-label { display: flex; align-items: center; gap: 0.55rem; font-weight: 600; }
    .domain-label .icon { color: var(--help); }
    .domain b { color: var(--help-deep); text-align: right; }
    .fine { font-size: 0.92rem; color: var(--muted); margin: 0.9rem 0 0; }
    @media (max-width: 840px) {
        .login-grid { grid-template-columns: 1fr; }
        .intro, .panel { padding: 1.25rem; }
        .logo { width: 3.4rem; height: 3.4rem; }
    }
</style>
@endpush

@section('content')
    <main class="login-grid">
        <section class="card intro">
            <div class="pill"><x-icon name="help" /> จุดรับแจ้งเมื่อต้องการความช่วยเหลือ</div>
            <h1>ระบบรับคำขอตรวจสอบและนัดหมายดูภาพจากกล้องวงจรปิด</h1>
            <p class="lead">คณะวิทยาศาสตร์ มหาวิทยาลัยขอนแก่น พร้อมรับเรื่องเมื่อทรัพย์สินสูญหาย เกิดอุบัติเหตุ หรือต้องการตรวจสอบเหตุการณ์ ใช้สำหรับยื่นคำขอและนัดหมายดูภาพเท่านั้น ไม่มีการดาวน์โหลดหรือส่งมอบไฟล์ภาพ</p>
            <ul class="rules">
                <li>
                    <span class="rule-icon"><x-icon name="eye" /></span>
                    <span>การดูภาพทำได้ภายใต้การควบคุมของเจ้าหน้าที่ ณ สถานที่หรือช่องทางที่คณะวิทยาศาสตร์กำหนด</span>
                </li>
                <li>
                    <span class="rule-icon"><x-icon name="calendar" /></span>
                    <span>การอนุมัติคำขอเป็นการอนุญาตให้นัดหมายดูภาพ ไม่ใช่การอนุญาตให้นำไฟล์ออก</span>
                </li>
                <li>
                    <span class="rule-icon"><x-icon name="shield" /></span>
                    <span>นักศึกษาใช้อีเมล @kkumail.com บุคลากรใช้อีเมล @kku.ac.th และบุคคลภายนอกใช้บัญชี Google ที่ติดต่อได้</span>
                </li>
            </ul>
        </section>
        <section class="card panel">
            <h2>เข้าสู่ระบบ</h2>
            <p>เลือกบัญชี Google ให้ตรงกับสถานะของท่าน</p>
            @if (session('error'))
                <div class="alert"><x-icon name="info" /> <span>{{ session('error') }}</span></div>
            @endif
            <div class="domains">
                <div class="domain">
                    <span class="domain-label"><x-icon name="student" /> นักศึกษา มข.</span>
                    <b>@kkumail.com</b>
                </div>
                <div class="domain">
                    <span class="domain-label"><x-icon name="staff" /> บุคลากร มข.</span>
                    <b>@kku.ac.th</b>
                </div>
                <div class="domain">
                    <span class="domain-label"><x-icon name="person" /> บุคคลภายนอก</span>
                    <b>บัญชี Google</b>
                </div>
            </div>
            <a class="btn btn-google" href="{{ route('auth.google') }}"><x-icon name="help" /> เข้าสู่ระบบด้วย Google</a>
            <p class="fine">ระบบใช้ Google เพื่อยืนยันตัวตน นักศึกษาและบุคลากรโปรดใช้บัญชีมหาวิทยาลัย เพื่อให้คำขอถูกบันทึกตามประเภทผู้ยื่นที่ถูกต้อง</p>
        </section>
    </main>
@endsection
