@extends('layouts.app')

@section('title', 'เส้นทางคำขอ')

@section('content')
    <section class="page" style="padding:0">
        <h1>เส้นทางคำขอ</h1>
        <p style="color:var(--muted);max-width:42rem">เลือกได้ว่าเมื่อเจ้าหน้าที่พบภาพแล้ว เรื่องประเภทนั้นจะให้นัดดูได้เอง หรือต้องให้ผู้อำนวยการ หรือรองคณบดีฝ่ายบริหารเห็นชอบก่อน การเปลี่ยนค่านี้มีผลกับคำขอใหม่ ไม่ต้องแก้โค้ด</p>
        <div class="rule-list">
            @foreach ($rules as $rule)
                @php $incident = $rule->incident(); @endphp
                <article class="card rule-card">
                    <div class="rule-copy">
                        <div class="section-icon" style="width:2.6rem;height:2.6rem;border-radius:0.85rem;display:grid;place-items:center;background:var(--sand);color:var(--help)">
                            <x-icon :name="$incident?->icon() ?? 'clipboard'" />
                        </div>
                        <div>
                            <h2>{{ $incident?->label() ?? $rule->incident_type }}</h2>
                            <p>{{ $incident?->description() }}</p>
                            <p class="rule-example">{{ $incident?->example() }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.rules.update', $rule) }}">
                        @csrf
                        <label class="field">เมื่อพบภาพแล้ว ส่งต่อให้
                            <select name="approver">
                                <option value="admin" @selected($rule->approver === 'admin')>เจ้าหน้าที่นัดดูภาพได้เอง</option>
                                <option value="director" @selected($rule->approver === 'director')>ผู้อำนวยการกองบริหารงานคณะเห็นชอบก่อน</option>
                                <option value="dean" @selected($rule->approver === 'dean')>รองคณบดีฝ่ายบริหารเห็นชอบก่อน</option>
                            </select>
                        </label>
                        <p class="rule-now">ตอนนี้: {{ $rule->approverLabel() }}</p>
                        <button class="btn btn-solid" type="submit">บันทึกเส้นทางนี้</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>
@endsection

@push('styles')
<style>
    .rule-list { display: grid; gap: 0.8rem; }
    .rule-card {
        display: grid;
        grid-template-columns: 1.4fr 0.8fr;
        gap: 1.2rem;
        align-items: center;
        padding: 1.1rem 1.2rem;
    }
    .rule-copy { display: flex; gap: 0.85rem; align-items: flex-start; }
    .rule-copy h2 { margin: 0 0 0.25rem; font-size: 1.15rem; }
    .rule-copy p { margin: 0; color: var(--muted); }
    .rule-example { margin-top: 0.35rem !important; color: var(--help) !important; font-weight: 600; }
    .rule-now { margin: 0 0 0.7rem; color: var(--muted); font-size: 0.92rem; }
    @media (max-width: 800px) { .rule-card { grid-template-columns: 1fr; } }
</style>
@endpush
