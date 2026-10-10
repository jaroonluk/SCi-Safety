@php
    $journey = $viewingRequest->journey();
    $currentNumber = $journey['finished'] ? $journey['total'] : $journey['current'];
    $progressPercent = $journey['finished']
        ? 100
        : (int) round((max($currentNumber - 1, 0) / max($journey['total'] - 1, 1)) * 100);
@endphp

<section class="progress-board" aria-label="ความคืบหน้าของคำขอ">
    <div class="progress-board-head">
        <div>
            <p class="progress-kicker">ขั้นตอนการขอดูภาพ</p>
            <h2>
                @if ($journey['finished'])
                    ครบ {{ $journey['total'] }} ขั้นตอนแล้ว
                @elseif ($journey['stopped'])
                    หยุดที่ขั้นตอนที่ {{ $currentNumber }} จาก {{ $journey['total'] }}
                @else
                    ตอนนี้อยู่ขั้นตอนที่ {{ $currentNumber }} จาก {{ $journey['total'] }}
                @endif
            </h2>
            <p class="progress-next">{{ $journey['next'] }}</p>
        </div>
        <div class="progress-badge tone-{{ $viewingRequest->status->accent() }}">
            <span class="progress-badge-ico"><x-icon :name="$viewingRequest->status->icon()" /></span>
            <span>
                <strong>{{ $viewingRequest->statusLabel() }}</strong>
                <small>{{ $viewingRequest->status->shortHint() }}</small>
            </span>
        </div>
    </div>

    <div class="progress-meter" aria-hidden="true">
        <div class="progress-meter-bar" style="width: {{ $progressPercent }}%"></div>
    </div>

    <ol class="progress-steps">
        @foreach ($journey['steps'] as $step)
            <li class="progress-step is-{{ $step['state'] }}">
                <span class="progress-step-ico" aria-hidden="true">
                    @if ($step['state'] === 'done')
                        <x-icon name="check" />
                    @elseif ($step['state'] === 'stopped')
                        <x-icon name="alert" />
                    @else
                        <x-icon :name="$step['icon']" />
                    @endif
                </span>
                <span class="progress-step-copy">
                    <small>ขั้นที่ {{ $step['number'] }}</small>
                    <strong>{{ $step['label'] }}</strong>
                    <span>{{ $step['hint'] }}</span>
                </span>
                @if ($step['state'] === 'current')
                    <span class="progress-now">กำลังดำเนินการ</span>
                @elseif ($step['state'] === 'done')
                    <span class="progress-now is-done">เสร็จแล้ว</span>
                @elseif ($step['state'] === 'stopped')
                    <span class="progress-now is-stopped">หยุดที่ขั้นนี้</span>
                @else
                    <span class="progress-now is-wait">ยังไม่ถึง</span>
                @endif
            </li>
        @endforeach
    </ol>
</section>
