@extends('layouts.app')

@section('title', 'ตรวจสอบคำขอ | SCi-Safety')

@section('content')
    @php
        $filters = [
            ['key' => 'all', 'label' => 'ทั้งหมด', 'icon' => 'clipboard', 'tone' => 'slate'],
            ['key' => 'new', 'label' => 'เพิ่งยื่น', 'icon' => 'spark', 'tone' => 'amber'],
            ['key' => 'mine', 'label' => 'คิวของฉัน', 'icon' => 'camera', 'tone' => 'orange'],
            ['key' => 'director', 'label' => 'ค้างผอ.', 'icon' => 'scale', 'tone' => 'indigo'],
            ['key' => 'dean', 'label' => 'ค้างรองคณบดี', 'icon' => 'users', 'tone' => 'violet'],
            ['key' => 'urgent', 'label' => 'เร่งด่วน', 'icon' => 'alert', 'tone' => 'rose'],
            ['key' => 'close', 'label' => 'รอปิด', 'icon' => 'flag', 'tone' => 'cyan'],
            ['key' => 'closed', 'label' => 'ปิดแล้ว', 'icon' => 'check', 'tone' => 'slate'],
        ];

        $waitingLabel = function ($item) {
            return match ($item->status) {
                \App\Enums\RequestStatus::Submitted => 'ค้างที่เจ้าหน้าที่ · เพิ่งยื่นใหม่',
                \App\Enums\RequestStatus::MoreInfo => 'ค้างที่ผู้ยื่น · รอข้อมูลเพิ่ม',
                \App\Enums\RequestStatus::PendingDirector => 'ค้างที่ผู้อำนวยการกอง',
                \App\Enums\RequestStatus::PendingDean => 'ค้างที่รองคณบดีฝ่ายบริหาร',
                \App\Enums\RequestStatus::Approved => 'ค้างที่เจ้าหน้าที่ · รอนัดหมาย',
                \App\Enums\RequestStatus::Scheduled => $item->appointment_confirmed_at
                    ? 'ค้างที่เจ้าหน้าที่ · รอบันทึกหลังดูภาพ'
                    : 'ค้างที่ผู้ยื่น · รอยืนยันนัด',
                \App\Enums\RequestStatus::Viewed => 'ค้างที่เจ้าหน้าที่ · รอปิดคำขอ',
                \App\Enums\RequestStatus::Rejected, \App\Enums\RequestStatus::NoFootage => 'ค้างที่เจ้าหน้าที่ · รอปิดคำขอ',
                \App\Enums\RequestStatus::Closed => 'ปิดเรื่องแล้ว',
            };
        };

        $action = function ($item) {
            return match ($item->status) {
                \App\Enums\RequestStatus::Submitted => 'ตรวจและเห็นชอบ/ไม่เห็นชอบ',
                \App\Enums\RequestStatus::MoreInfo => 'รอผู้ยื่นส่งข้อมูล',
                \App\Enums\RequestStatus::PendingDirector => 'ติดตามผลผู้อำนวยการกอง',
                \App\Enums\RequestStatus::PendingDean => 'ติดตามผลรองคณบดี',
                \App\Enums\RequestStatus::Approved => 'เปิดฟอร์มนัดหมาย',
                \App\Enums\RequestStatus::Scheduled => $item->appointment_confirmed_at ? 'บันทึกหลังดูภาพ' : 'รอผู้ยื่นยืนยันนัด',
                \App\Enums\RequestStatus::Viewed, \App\Enums\RequestStatus::Rejected, \App\Enums\RequestStatus::NoFootage => 'ปิดคำขอ',
                \App\Enums\RequestStatus::Closed => 'ดูรายละเอียด',
            };
        };

        $anchor = function ($item) {
            return match ($item->status) {
                \App\Enums\RequestStatus::Approved => '#schedule',
                \App\Enums\RequestStatus::Submitted => '#camera',
                \App\Enums\RequestStatus::Viewed, \App\Enums\RequestStatus::Rejected, \App\Enums\RequestStatus::NoFootage => '#close-request',
                default => '',
            };
        };

        $officerActionable = [
            \App\Enums\RequestStatus::Submitted,
            \App\Enums\RequestStatus::Approved,
            \App\Enums\RequestStatus::Scheduled,
            \App\Enums\RequestStatus::Viewed,
            \App\Enums\RequestStatus::Rejected,
            \App\Enums\RequestStatus::NoFootage,
        ];
    @endphp

    <section class="queue-page">
        <header class="queue-hero">
            <span class="queue-hero-icon" aria-hidden="true"><x-icon name="clipboard" /></span>
            <div class="queue-hero-copy">
                <h1>ตรวจสอบคำขอ</h1>
                <p>ดูสถานะว่าค้างที่ใคร · เรียงจากล่าสุดก่อน · ใช้แถบสถานะด้านล่างเพื่อกรอง</p>
            </div>
            <div class="queue-hero-total" title="จำนวนคำขอทั้งหมด">
                <strong>{{ $counts['all'] }}</strong>
                <span>ทั้งหมด</span>
            </div>
        </header>

        <div class="queue-reassure">
            <span class="queue-reassure-icon"><x-icon name="camera" /></span>
            <span>
                @if ($inspectionTotal === 0)
                    รอบตรวจกล้องเดือน {{ $inspectionPeriod }} ยังไม่มีกล้องในทะเบียน
                @else
                    รอบตรวจกล้องเดือน {{ $inspectionPeriod }} ตรวจแล้ว {{ $inspectionDone }} จาก {{ $inspectionTotal }} ตัว
                @endif
            </span>
            <a class="btn btn-queue-link" href="{{ route('cameras.index') }}"><x-icon name="arrow" /> เปิดรายการตรวจ</a>
        </div>

        <form class="queue-toolbar" method="GET" action="{{ route('queue') }}">
            <div class="queue-filters" role="tablist" aria-label="กรองตามสถานะ">
                @foreach ($filters as $item)
                    <a
                        class="queue-filter tone-{{ $item['tone'] }} {{ $filter === $item['key'] ? 'is-on' : '' }}"
                        href="{{ route('queue', array_filter(['filter' => $item['key'] === 'all' ? null : $item['key'], 'q' => $search ?: null, 'per_page' => $perPage === 20 ? null : $perPage])) }}"
                        title="{{ $item['label'] }} {{ $counts[$item['key']] ?? 0 }} รายการ"
                    >
                        <span class="queue-filter-ico"><x-icon :name="$item['icon']" /></span>
                        <span class="queue-filter-label">{{ $item['label'] }}</span>
                        <span class="queue-filter-count">{{ $counts[$item['key']] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="queue-search-row">
                <label class="queue-search field">
                    <span class="field-label"><x-icon name="eye" /> ค้นหาเลขที่ / อาคาร / รายละเอียด</span>
                    <input type="search" name="q" value="{{ $search }}" placeholder="เช่น SCI-2026 หรือ SC.01">
                </label>
                @if ($filter !== 'all')
                    <input type="hidden" name="filter" value="{{ $filter }}">
                @endif
                <label class="queue-per-page field">
                    <span class="field-label">แสดงต่อหน้า</span>
                    <select name="per_page" onchange="this.form.submit()">
                        @foreach ([10, 20, 50, 100] as $size)
                            <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} รายการ</option>
                        @endforeach
                    </select>
                </label>
                <button class="btn btn-solid" type="submit"><x-icon name="clipboard" /> แสดงผล</button>
            </div>
        </form>

        <div class="queue-result-meta">
            <p>
                แสดง {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }}
                จาก {{ $requests->total() }} รายการ
                · เรียงจากล่าสุดก่อน
            </p>
        </div>

        <div class="queue-list">
            @forelse ($requests as $item)
                @php
                    $accent = $item->status->accent();
                    $canAct = in_array($item->status, $officerActionable, true)
                        && ! ($item->status === \App\Enums\RequestStatus::Scheduled && ! $item->appointment_confirmed_at)
                        && $item->status !== \App\Enums\RequestStatus::MoreInfo;
                    $waiting = $waitingLabel($item);
                    $submittedAt = \App\Support\ThaiDate::short($item->created_at);
                @endphp
                <a class="queue-card accent-{{ $accent }}" href="{{ route('requests.show', $item) }}{{ $anchor($item) }}">
                    <span class="queue-card-ico"><x-icon :name="$item->status->icon()" /></span>
                    <div class="queue-card-main">
                        <div class="queue-card-top">
                            <strong>{{ $item->reference }}</strong>
                            <span class="queue-status">{{ $item->statusLabel() }}</span>
                            @if ($item->urgency === 'urgent')
                                <span class="queue-urgent"><x-icon name="alert" /> เร่งด่วน</span>
                            @endif
                        </div>
                        <p class="queue-waiting"><x-icon name="route" /> {{ $waiting }}</p>
                        <div class="queue-meta">
                            <span><x-icon name="flag" /> {{ $item->incident_type->label() }}</span>
                            <span><x-icon name="building" /> {{ $item->building }}{{ $item->location ? ' · '.$item->location : '' }}</span>
                            <span><x-icon name="clock" /> ยื่น {{ $submittedAt }}</span>
                        </div>
                    </div>
                    <div class="queue-card-side">
                        <span class="queue-action {{ $canAct ? 'is-act' : 'is-wait' }}">
                            {{ $action($item) }}
                            <x-icon name="arrow" />
                        </span>
                    </div>
                </a>
            @empty
                <div class="queue-empty">
                    <p>ไม่พบรายการในตัวกรองนี้</p>
                    <a class="btn btn-quiet" href="{{ route('queue') }}">แสดงทั้งหมด</a>
                </div>
            @endforelse
        </div>

        @if ($requests->hasPages())
            <nav class="queue-pagination" aria-label="หน้าถัดไป">
                @if ($requests->onFirstPage())
                    <span class="is-disabled">ก่อนหน้า</span>
                @else
                    <a href="{{ $requests->previousPageUrl() }}">ก่อนหน้า</a>
                @endif

                @foreach ($requests->getUrlRange(max(1, $requests->currentPage() - 2), min($requests->lastPage(), $requests->currentPage() + 2)) as $page => $url)
                    @if ($page === $requests->currentPage())
                        <span aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                @if ($requests->hasMorePages())
                    <a href="{{ $requests->nextPageUrl() }}">ถัดไป</a>
                @else
                    <span class="is-disabled">ถัดไป</span>
                @endif
            </nav>
        @endif
    </section>

    @push('styles')
    <style>
        .queue-page { margin-bottom: 1.5rem; }
        .queue-hero {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem 1.25rem;
            align-items: flex-start;
            padding: 1.25rem 1.35rem;
            margin-bottom: 0.9rem;
            border-radius: 16px;
            border: 1px solid #fecdd3;
            background: linear-gradient(135deg, #fff1f2 0%, #ffffff 48%, #eef2ff 100%);
            box-shadow: var(--shadow);
        }
        .queue-hero-icon {
            width: 3.1rem; height: 3.1rem; border-radius: 1rem;
            display: grid; place-items: center; flex: none;
            background: linear-gradient(145deg, #e11d48, #9f1239); color: #fff;
            box-shadow: 0 10px 22px rgba(190, 18, 60, 0.2);
        }
        .queue-hero-icon .icon { width: 1.45rem; height: 1.45rem; }
        .queue-hero-copy { flex: 1 1 16rem; min-width: 0; }
        .queue-hero-copy h1 { margin: 0 0 0.3rem; font-size: 1.5rem; }
        .queue-hero-copy p { margin: 0; color: var(--muted); max-width: 40rem; }
        .queue-hero-total {
            margin-left: auto;
            min-width: 4.5rem;
            text-align: center;
            padding: 0.55rem 0.85rem;
            border-radius: 12px;
            border: 1px solid #fecdd3;
            background: #fff;
            color: #be123c;
        }
        .queue-hero-total strong { display: block; font-size: 1.35rem; line-height: 1.1; }
        .queue-hero-total span { font-size: 0.78rem; font-weight: 700; }

        .queue-reassure {
            display: flex; flex-wrap: wrap; gap: 0.7rem; align-items: center;
            padding: 0.85rem 1rem; margin-bottom: 1rem; border-radius: 14px;
            border: 1px solid #bfdbfe; background: linear-gradient(180deg, #eff6ff 0%, #ffffff 70%); color: #1e3a8a;
        }
        .queue-reassure-icon {
            width: 2.2rem; height: 2.2rem; border-radius: 0.7rem; display: grid; place-items: center;
            background: #2563eb; color: #fff; flex: none;
        }
        .queue-reassure > span:nth-child(2) { flex: 1 1 14rem; }
        .btn-queue-link { background: #2563eb; color: #fff; padding: 0.55rem 0.9rem; font-size: 0.95rem; }
        .btn-queue-link:hover { background: #1d4ed8; }

        .queue-toolbar { margin-bottom: 0.85rem; }
        .queue-filters {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.4rem;
            margin-bottom: 0.75rem;
            overflow-x: auto;
            padding-bottom: 0.15rem;
            -webkit-overflow-scrolling: touch;
        }
        .queue-filter {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex: 0 0 auto;
            padding: 0.35rem 0.55rem 0.35rem 0.35rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #fff;
            text-decoration: none;
            color: inherit;
            white-space: nowrap;
            line-height: 1;
            transition: transform 0.12s ease, box-shadow 0.12s ease, border-color 0.12s ease;
        }
        .queue-filter:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(28, 25, 23, 0.06); }
        .queue-filter.is-on {
            box-shadow: 0 0 0 2px currentColor inset;
            font-weight: 700;
        }
        .queue-filter-ico {
            width: 1.55rem;
            height: 1.55rem;
            border-radius: 999px;
            display: grid;
            place-items: center;
            color: #fff;
            flex: none;
        }
        .queue-filter-ico .icon { width: 0.85rem; height: 0.85rem; }
        .queue-filter-label {
            font-size: 0.84rem;
            font-weight: 700;
        }
        .queue-filter-count {
            min-width: 1.35rem;
            height: 1.35rem;
            padding: 0 0.4rem;
            border-radius: 999px;
            display: inline-grid;
            place-items: center;
            font-size: 0.78rem;
            font-weight: 800;
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid currentColor;
        }
        .queue-filter.tone-amber { border-color: #fcd34d; background: #fffbeb; color: #b45309; }
        .queue-filter.tone-amber .queue-filter-ico { background: #d97706; }
        .queue-filter.tone-orange { border-color: #fdba74; background: #fff7ed; color: #c2410c; }
        .queue-filter.tone-orange .queue-filter-ico { background: #ea580c; }
        .queue-filter.tone-indigo { border-color: #c7d2fe; background: #eef2ff; color: #4338ca; }
        .queue-filter.tone-indigo .queue-filter-ico { background: #4f46e5; }
        .queue-filter.tone-violet { border-color: #ddd6fe; background: #f5f3ff; color: #6d28d9; }
        .queue-filter.tone-violet .queue-filter-ico { background: #7c3aed; }
        .queue-filter.tone-rose { border-color: #fecdd3; background: #fff1f2; color: #be123c; }
        .queue-filter.tone-rose .queue-filter-ico { background: #e11d48; }
        .queue-filter.tone-cyan { border-color: #a5f3fc; background: #ecfeff; color: #0e7490; }
        .queue-filter.tone-cyan .queue-filter-ico { background: #0891b2; }
        .queue-filter.tone-slate { border-color: #cbd5e1; background: #f8fafc; color: #334155; }
        .queue-filter.tone-slate .queue-filter-ico { background: #475569; }

        .queue-search-row {
            display: grid;
            grid-template-columns: 1.6fr 0.55fr auto;
            gap: 0.7rem;
            align-items: end;
            padding: 0.9rem 1rem;
            border-radius: 14px;
            border: 1px solid #f3e4e1;
            background: #fff;
        }
        .queue-search-row .field { margin: 0; }
        .queue-result-meta { color: var(--muted); margin: 0 0 0.75rem; font-size: 0.92rem; }
        .queue-result-meta p { margin: 0; }

        .queue-list { display: grid; gap: 0.75rem; }
        .queue-card {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.9rem;
            align-items: start;
            padding: 1rem 1.05rem;
            border-radius: 16px;
            border: 1px solid var(--line);
            text-decoration: none;
            color: inherit;
            background: #fff;
            position: relative;
            overflow: hidden;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .queue-card::before {
            content: "";
            position: absolute; inset: 0 auto 0 0; width: 5px;
            background: var(--status-accent, var(--accent));
        }
        .queue-card:hover { transform: translateY(-2px); box-shadow: 0 14px 28px rgba(28, 25, 23, 0.08); }
        .queue-card-ico {
            width: 2.6rem; height: 2.6rem; border-radius: 0.85rem;
            display: grid; place-items: center; color: #fff;
            background: var(--status-accent, var(--accent)); flex: none;
        }
        .queue-card-top {
            display: flex; flex-wrap: wrap; gap: 0.4rem 0.65rem; align-items: center; margin-bottom: 0.35rem;
        }
        .queue-card-top strong { font-size: 1.02rem; }
        .queue-status {
            display: inline-flex; align-items: center; padding: 0.22rem 0.65rem; border-radius: 999px;
            font-size: 0.86rem; font-weight: 700;
            background: color-mix(in srgb, var(--status-accent, var(--accent)) 14%, #fff);
            color: var(--status-ink, var(--help));
            border: 1px solid color-mix(in srgb, var(--status-accent, var(--accent)) 28%, #fff);
        }
        .queue-urgent {
            display: inline-flex; align-items: center; gap: 0.25rem;
            padding: 0.18rem 0.55rem; border-radius: 999px;
            background: #e11d48; color: #fff; font-size: 0.78rem; font-weight: 800;
        }
        .queue-urgent .icon { width: 0.85rem; height: 0.85rem; }
        .queue-waiting {
            display: inline-flex; align-items: center; gap: 0.35rem;
            margin: 0 0 0.45rem; font-weight: 700; color: var(--status-ink, var(--help));
        }
        .queue-waiting .icon { width: 1rem; height: 1rem; }
        .queue-meta {
            display: flex; flex-wrap: wrap; gap: 0.35rem 0.85rem; color: var(--muted); font-size: 0.92rem;
        }
        .queue-meta span { display: inline-flex; align-items: center; gap: 0.3rem; }
        .queue-meta .icon { width: 0.95rem; height: 0.95rem; }
        .queue-card-side { display: grid; align-content: start; }
        .queue-action {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.55rem 0.75rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; white-space: nowrap;
        }
        .queue-action .icon { width: 1rem; height: 1rem; }
        .queue-action.is-act { background: var(--status-accent, var(--accent)); color: #fff; }
        .queue-action.is-wait {
            background: color-mix(in srgb, var(--status-accent, var(--accent)) 12%, #fff);
            color: var(--status-ink, var(--help));
            border: 1px solid color-mix(in srgb, var(--status-accent, var(--accent)) 30%, #fff);
        }

        .queue-card.accent-amber { --status-accent: #d97706; --status-ink: #b45309; background: linear-gradient(180deg, #fffbeb 0%, #fff 60%); border-color: #fcd34d; }
        .queue-card.accent-orange { --status-accent: #ea580c; --status-ink: #c2410c; background: linear-gradient(180deg, #fff7ed 0%, #fff 60%); border-color: #fdba74; }
        .queue-card.accent-indigo { --status-accent: #4f46e5; --status-ink: #4338ca; background: linear-gradient(180deg, #eef2ff 0%, #fff 60%); border-color: #c7d2fe; }
        .queue-card.accent-violet { --status-accent: #7c3aed; --status-ink: #6d28d9; background: linear-gradient(180deg, #f5f3ff 0%, #fff 60%); border-color: #ddd6fe; }
        .queue-card.accent-teal { --status-accent: #0d9488; --status-ink: #0f766e; background: linear-gradient(180deg, #f0fdfa 0%, #fff 60%); border-color: #99f6e4; }
        .queue-card.accent-emerald { --status-accent: #059669; --status-ink: #047857; background: linear-gradient(180deg, #ecfdf5 0%, #fff 60%); border-color: #a7f3d0; }
        .queue-card.accent-sky { --status-accent: #0284c7; --status-ink: #0369a1; background: linear-gradient(180deg, #f0f9ff 0%, #fff 60%); border-color: #bae6fd; }
        .queue-card.accent-cyan { --status-accent: #0891b2; --status-ink: #0e7490; background: linear-gradient(180deg, #ecfeff 0%, #fff 60%); border-color: #a5f3fc; }
        .queue-card.accent-slate { --status-accent: #475569; --status-ink: #334155; background: linear-gradient(180deg, #f8fafc 0%, #fff 60%); border-color: #cbd5e1; }
        .queue-card.accent-rose { --status-accent: #e11d48; --status-ink: #be123c; background: linear-gradient(180deg, #fff1f2 0%, #fff 60%); border-color: #fecdd3; }
        .queue-card.accent-stone { --status-accent: #78716c; --status-ink: #57534e; background: linear-gradient(180deg, #fafaf9 0%, #fff 60%); border-color: #d6d3d1; }

        .queue-empty {
            padding: 1.4rem; border-radius: 14px; border: 1px dashed #fda4af;
            background: #fff1f2; text-align: center; color: var(--muted);
        }
        .queue-pagination {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            justify-content: center;
        }
        .queue-pagination span,
        .queue-pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.4rem;
            padding: 0.45rem 0.7rem;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            text-decoration: none;
            color: inherit;
            font-weight: 600;
        }
        .queue-pagination [aria-current="page"] {
            background: var(--help);
            color: #fff;
            border-color: var(--help);
        }
        .queue-pagination .is-disabled { opacity: 0.45; }

        @media (max-width: 980px) {
            .queue-search-row { grid-template-columns: 1fr; }
            .queue-hero-total { margin-left: 0; }
        }
        @media (max-width: 720px) {
            .queue-card { grid-template-columns: auto 1fr; }
            .queue-card-side { grid-column: 2; }
            .queue-filter-label { font-size: 0.8rem; }
        }
    </style>
    @endpush
@endsection
