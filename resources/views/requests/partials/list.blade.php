@push('styles')
<style>
    .list-card { padding: 1.5rem 1.6rem; margin-bottom: 1rem; }
    .back { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--help); font-weight: 700; text-decoration: none; }
    h1 { margin: 0.7rem 0 0.3rem; }
    .lead { color: var(--muted); margin-top: 0; }
    .item {
        display: block;
        padding: 1rem 1.1rem;
        margin-bottom: 0.7rem;
        text-decoration: none;
        color: inherit;
    }
    .item strong { display: block; }
    .item span { color: var(--muted); }
    .empty { color: var(--muted); }
</style>
@endpush

<section class="card list-card">
    <a class="back" href="{{ route('home') }}"><x-icon name="arrow" style="transform: scaleX(-1)" /> กลับหน้าหลัก</a>
    <h1>{{ $title }}</h1>
    <p class="lead">{{ $lead }}</p>
    @if ($requests->isEmpty())
        <p class="empty">{{ $empty }}</p>
        <a class="btn btn-google" href="{{ route('requests.create') }}"><x-icon name="help" /> แจ้งขอความช่วยเหลือ</a>
    @else
        @foreach ($requests as $requestItem)
            <a class="card item" href="{{ route('requests.show', $requestItem) }}">
                <strong>{{ $requestItem->reference }} · {{ $requestItem->incident_type->label() }}</strong>
                <span class="pill pill-{{ $requestItem->status->tone() }}">{{ $requestItem->statusLabel() }}</span>
                <span>{{ $requestItem->building }} {{ $requestItem->location }}</span>
            </a>
        @endforeach
    @endif
</section>
