@php
    $groups = [
        ['key' => 'action', 'label' => 'ต้องดำเนินการ', 'icon' => 'alert', 'match' => fn ($item) => in_array($item->status, [
            \App\Enums\RequestStatus::MoreInfo,
            \App\Enums\RequestStatus::Scheduled,
        ], true) && ! ($item->status === \App\Enums\RequestStatus::Scheduled && $item->appointment_confirmed_at)],
        ['key' => 'review', 'label' => 'อยู่ระหว่างดำเนินการ', 'icon' => 'scale', 'match' => fn ($item) => in_array($item->status, [
            \App\Enums\RequestStatus::Submitted,
            \App\Enums\RequestStatus::PendingDirector,
            \App\Enums\RequestStatus::PendingDean,
            \App\Enums\RequestStatus::Approved,
        ], true) || ($item->status === \App\Enums\RequestStatus::Scheduled && $item->appointment_confirmed_at)],
        ['key' => 'done', 'label' => 'เสร็จแล้ว', 'icon' => 'check', 'match' => fn ($item) => in_array($item->status, [
            \App\Enums\RequestStatus::Viewed,
            \App\Enums\RequestStatus::Closed,
        ], true)],
        ['key' => 'stop', 'label' => 'ไม่สามารถดูภาพ', 'icon' => 'camera', 'match' => fn ($item) => in_array($item->status, [
            \App\Enums\RequestStatus::Rejected,
            \App\Enums\RequestStatus::NoFootage,
        ], true)],
    ];

    $counts = [];
    foreach ($groups as $group) {
        $counts[$group['key']] = $requests->filter($group['match'])->count();
    }
@endphp

@push('styles')
<style>
    .track-page { padding: 0; }
    .track-hero {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1.35rem 1.45rem;
        border-radius: 16px;
        border: 1px solid #fecdd3;
        background:
            radial-gradient(circle at top right, rgba(251, 113, 133, 0.18), transparent 42%),
            linear-gradient(135deg, #fff1f2 0%, #ffffff 55%, #eff6ff 100%);
        margin-bottom: 1rem;
        box-shadow: 0 12px 28px rgba(159, 18, 57, 0.06);
    }
    .track-back {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--accent);
        font-weight: 700;
        text-decoration: none;
        margin-bottom: 0.55rem;
    }
    .track-back .icon { transform: scaleX(-1); width: 1.05rem; height: 1.05rem; }
    .track-hero h1 { margin: 0 0 0.35rem; font-size: 1.55rem; }
    .track-hero .lead { margin: 0; color: var(--muted); max-width: 36rem; }
    .track-hero-stat {
        min-width: 7.5rem;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #fecdd3;
        text-align: center;
        box-shadow: 0 8px 18px rgba(190, 18, 60, 0.08);
    }
    .track-hero-stat strong { display: block; font-size: 1.55rem; color: #be123c; line-height: 1.1; }
    .track-hero-stat span { color: var(--muted); font-size: 0.9rem; font-weight: 600; }

    .track-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .track-filter {
        display: flex;
        gap: 0.65rem;
        align-items: center;
        padding: 0.8rem 0.9rem;
        border-radius: 14px;
        border: 1px solid var(--line);
        background: #fff;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .track-filter:hover { transform: translateY(-1px); box-shadow: 0 10px 20px rgba(28, 25, 23, 0.06); }
    .track-filter-ico {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.75rem;
        display: grid;
        place-items: center;
        color: #fff;
        flex: none;
    }
    .track-filter strong { display: block; font-size: 0.95rem; }
    .track-filter small { color: var(--muted); }
    .track-filter[data-tone="action"] { border-color: #fdba74; background: linear-gradient(180deg, #fff7ed 0%, #fff 70%); }
    .track-filter[data-tone="action"] .track-filter-ico { background: #ea580c; }
    .track-filter[data-tone="review"] { border-color: #c7d2fe; background: linear-gradient(180deg, #eef2ff 0%, #fff 70%); }
    .track-filter[data-tone="review"] .track-filter-ico { background: #4f46e5; }
    .track-filter[data-tone="done"] { border-color: #a7f3d0; background: linear-gradient(180deg, #ecfdf5 0%, #fff 70%); }
    .track-filter[data-tone="done"] .track-filter-ico { background: #059669; }
    .track-filter[data-tone="stop"] { border-color: #cbd5e1; background: linear-gradient(180deg, #f8fafc 0%, #fff 70%); }
    .track-filter[data-tone="stop"] .track-filter-ico { background: #64748b; }

    .track-list { display: grid; gap: 0.85rem; }
    .track-card {
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
    .track-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--status-accent, var(--accent));
    }
    .track-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(28, 25, 23, 0.08);
    }
    .track-card-ico {
        width: 2.7rem;
        height: 2.7rem;
        border-radius: 0.9rem;
        display: grid;
        place-items: center;
        color: #fff;
        background: var(--status-accent, var(--accent));
        box-shadow: 0 8px 16px color-mix(in srgb, var(--status-accent, var(--accent)) 28%, transparent);
    }
    .track-card-body { min-width: 0; }
    .track-card-top {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem 0.7rem;
        align-items: center;
        margin-bottom: 0.35rem;
    }
    .track-card-top strong {
        font-size: 1.02rem;
        letter-spacing: 0.01em;
    }
    .track-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.7rem;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 700;
        background: color-mix(in srgb, var(--status-accent, var(--accent)) 14%, #fff);
        color: var(--status-ink, var(--help));
        border: 1px solid color-mix(in srgb, var(--status-accent, var(--accent)) 28%, #fff);
    }
    .track-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.85rem;
        color: var(--muted);
        margin: 0 0 0.45rem;
        font-size: 0.95rem;
    }
    .track-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    .track-meta .icon { width: 0.95rem; height: 0.95rem; }
    .track-hint {
        margin: 0;
        color: var(--status-ink, var(--help));
        font-weight: 600;
        line-height: 1.45;
    }
    .track-side {
        display: grid;
        gap: 0.45rem;
        justify-items: end;
        text-align: right;
    }
    .track-open {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: var(--status-ink, var(--accent));
        font-weight: 700;
        font-size: 0.92rem;
    }
    .track-open .icon { width: 1rem; height: 1rem; }
    .track-urgent {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: #fff1f2;
        color: #be123c;
        font-size: 0.82rem;
        font-weight: 700;
        border: 1px solid #fecdd3;
    }

    .track-card.accent-amber { --status-accent: #d97706; --status-ink: #b45309; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 58%); border-color: #fcd34d; }
    .track-card.accent-orange { --status-accent: #ea580c; --status-ink: #c2410c; background: linear-gradient(180deg, #fff7ed 0%, #ffffff 58%); border-color: #fdba74; }
    .track-card.accent-indigo { --status-accent: #4f46e5; --status-ink: #4338ca; background: linear-gradient(180deg, #eef2ff 0%, #ffffff 58%); border-color: #c7d2fe; }
    .track-card.accent-violet { --status-accent: #7c3aed; --status-ink: #6d28d9; background: linear-gradient(180deg, #f5f3ff 0%, #ffffff 58%); border-color: #ddd6fe; }
    .track-card.accent-teal { --status-accent: #0d9488; --status-ink: #0f766e; background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 58%); border-color: #99f6e4; }
    .track-card.accent-emerald { --status-accent: #059669; --status-ink: #047857; background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 58%); border-color: #a7f3d0; }
    .track-card.accent-sky { --status-accent: #0284c7; --status-ink: #0369a1; background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 58%); border-color: #bae6fd; }
    .track-card.accent-cyan { --status-accent: #0891b2; --status-ink: #0e7490; background: linear-gradient(180deg, #ecfeff 0%, #ffffff 58%); border-color: #a5f3fc; }
    .track-card.accent-slate { --status-accent: #475569; --status-ink: #334155; background: linear-gradient(180deg, #f8fafc 0%, #ffffff 58%); border-color: #cbd5e1; }
    .track-card.accent-rose { --status-accent: #e11d48; --status-ink: #be123c; background: linear-gradient(180deg, #fff1f2 0%, #ffffff 58%); border-color: #fecdd3; }
    .track-card.accent-stone { --status-accent: #78716c; --status-ink: #57534e; background: linear-gradient(180deg, #fafaf9 0%, #ffffff 58%); border-color: #d6d3d1; }

    .track-empty {
        padding: 1.4rem 1.2rem;
        border-radius: 16px;
        border: 1px dashed #fda4af;
        background: linear-gradient(180deg, #fff1f2 0%, #ffffff 70%);
        text-align: center;
    }
    .track-empty p { color: var(--muted); margin: 0 0 0.9rem; }

    @media (max-width: 900px) {
        .track-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 720px) {
        .track-card { grid-template-columns: auto 1fr; }
        .track-side { grid-column: 2; justify-items: start; text-align: left; }
        .track-filters { grid-template-columns: 1fr; }
    }
</style>
@endpush

<section class="page track-page">
    <header class="track-hero">
        <div>
            <a class="track-back" href="{{ route('home') }}"><x-icon name="arrow" /> กลับหน้าหลัก</a>
            <h1>{{ $title }}</h1>
            <p class="lead">{{ $lead }}</p>
        </div>
        <div class="track-hero-stat">
            <strong>{{ $requests->count() }}</strong>
            <span>คำขอทั้งหมด</span>
        </div>
    </header>

    @if ($requests->isEmpty())
        <div class="track-empty">
            <p>{{ $empty }}</p>
            <a class="btn btn-solid" href="{{ route('requests.create') }}"><x-icon name="help" /> แจ้งเรื่องที่ต้องการดูภาพ</a>
        </div>
    @else
        <div class="track-filters" aria-label="สรุปตามกลุ่มสถานะ">
            @foreach ($groups as $group)
                <div class="track-filter" data-tone="{{ $group['key'] }}">
                    <span class="track-filter-ico"><x-icon :name="$group['icon']" /></span>
                    <span>
                        <strong>{{ $counts[$group['key']] }} รายการ</strong>
                        <small>{{ $group['label'] }}</small>
                    </span>
                </div>
            @endforeach
        </div>

        <div class="track-list">
            @foreach ($requests as $requestItem)
                @php
                    $accent = $requestItem->status->accent();
                    $appointment = $requestItem->appointment_at
                        ? \App\Support\ThaiDate::short($requestItem->appointment_at)
                        : null;
                @endphp
                <a class="track-card accent-{{ $accent }}" href="{{ route('requests.show', $requestItem) }}">
                    <span class="track-card-ico"><x-icon :name="$requestItem->status->icon()" /></span>
                    <div class="track-card-body">
                        <div class="track-card-top">
                            <strong>{{ $requestItem->reference }}</strong>
                            <span class="track-status">{{ $requestItem->statusLabel() }}</span>
                            @if ($requestItem->urgency === 'urgent')
                                <span class="track-urgent"><x-icon name="alert" /> เร่งด่วน</span>
                            @endif
                        </div>
                        <div class="track-meta">
                            <span><x-icon name="flag" /> {{ $requestItem->incident_type->label() }}</span>
                            <span><x-icon name="building" /> {{ $requestItem->building }}{{ $requestItem->location ? ' · '.$requestItem->location : '' }}</span>
                            @if ($appointment)
                                <span><x-icon name="calendar" /> นัด {{ $appointment }}</span>
                            @endif
                        </div>
                        <p class="track-hint">{{ $requestItem->status->shortHint() }}</p>
                    </div>
                    <div class="track-side">
                        <span class="track-open">เปิดรายละเอียด <x-icon name="arrow" /></span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
