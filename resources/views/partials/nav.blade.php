@php
    $user = auth()->user();
    $link = function (string $route, string $icon, string $label, string $hint, array $active, string $group = 'คำขอส่วนตัว') {
        return compact('route', 'icon', 'label', 'hint', 'active', 'group');
    };
    $primary = [];
    $more = [];
    $showCreate = false;

    if ($user->role === \App\Enums\UserRole::SuperAdmin) {
        $primary[] = $link('admin.users', 'users', 'สิทธิ์', '', ['admin.users']);
        $more = [
            $link('admin.rules', 'route', 'เส้นทางคำขอ', 'กำหนดว่าคำขอแต่ละประเภทผ่านผู้ใด', ['admin.rules'], 'งานระบบ'),
            $link('admin.audit', 'document', 'บันทึกการใช้งาน', 'ตรวจย้อนหลังแบบอ่านอย่างเดียว', ['admin.audit'], 'งานระบบ'),
            $link('cameras.index', 'camera', 'ทะเบียนกล้อง', 'จุดติดตั้งและสถานะกล้อง', ['cameras.index'], 'งานระบบ'),
            $link('reports.index', 'chart', 'รายงาน', 'สถิติและจุดบกพร่องสำหรับที่ประชุม', ['reports.index'], 'งานระบบ'),
            $link('privacy.edit', 'shield', 'ประกาศความเป็นส่วนตัว', 'ตรวจและเผยแพร่รุ่นใหม่', ['privacy.edit'], 'งานระบบ'),
            $link('requests.create', 'help', 'แจ้งเรื่อง', 'ยื่นคำขอใหม่', ['requests.create']),
            $link('requests.index', 'clipboard', 'คำขอของฉัน', 'ติดตามเรื่องที่บัญชียื่นเอง', ['requests.index', 'requests.show']),
            $link('appointments.index', 'calendar', 'นัดหมาย', 'วันเวลาดูภาพภายใต้การควบคุม', ['appointments.index']),
        ];
    } elseif ($user->hasRole(\App\Enums\UserRole::Director, \App\Enums\UserRole::AssociateDean)) {
        $primary[] = $link('reviews', 'scale', 'พิจารณา', '', ['reviews']);
        $more = [
            $link('reports.index', 'chart', 'รายงาน', 'สถิติและจุดบกพร่องสำหรับที่ประชุม', ['reports.index'], 'งานพิจารณา'),
            $link('requests.create', 'help', 'แจ้งเรื่อง', 'ยื่นคำขอใหม่', ['requests.create']),
            $link('requests.index', 'clipboard', 'คำขอของฉัน', 'ติดตามเรื่องที่บัญชียื่นเอง', ['requests.index', 'requests.show']),
            $link('appointments.index', 'calendar', 'นัดหมาย', 'วันเวลาดูภาพภายใต้การควบคุม', ['appointments.index']),
        ];
    } elseif ($user->role === \App\Enums\UserRole::PdpaCoordinator) {
        $primary[] = $link('pdpa.inbox', 'shield', 'ความเห็นข้อมูลส่วนบุคคล', '', ['pdpa.inbox']);
        $more = [
            $link('privacy.edit', 'document', 'ประกาศความเป็นส่วนตัว', 'ตรวจและเผยแพร่รุ่นใหม่', ['privacy.edit'], 'งานข้อมูลส่วนบุคคล'),
            $link('requests.create', 'help', 'แจ้งเรื่อง', 'ยื่นคำขอใหม่', ['requests.create']),
            $link('requests.index', 'clipboard', 'คำขอของฉัน', 'ติดตามเรื่องที่บัญชียื่นเอง', ['requests.index', 'requests.show']),
            $link('appointments.index', 'calendar', 'นัดหมาย', 'วันเวลาดูภาพภายใต้การควบคุม', ['appointments.index']),
        ];
    } elseif ($user->hasRole(\App\Enums\UserRole::CctvAdmin)) {
        $primary[] = $link('queue', 'clipboard', 'คิวงาน', '', ['queue']);
        $showCreate = true;
        $more = [
            $link('cameras.index', 'camera', 'ทะเบียนกล้อง', 'ตรวจประจำเดือน งานซ่อม และจุดเสี่ยง', ['cameras.index'], 'งานกล้อง'),
            $link('requests.index', 'clipboard', 'คำขอของฉัน', 'ติดตามเรื่องที่บัญชียื่นเอง', ['requests.index', 'requests.show']),
            $link('appointments.index', 'calendar', 'นัดหมาย', 'วันเวลาดูภาพภายใต้การควบคุม', ['appointments.index']),
        ];
    } else {
        $primary[] = $link('requests.index', 'clipboard', 'คำขอของฉัน', '', ['requests.index', 'requests.show']);
        $primary[] = $link('appointments.index', 'calendar', 'นัดหมาย', '', ['appointments.index']);
        $showCreate = true;
    }

    $moreActive = collect($more)->contains(fn (array $item) => request()->routeIs(...$item['active']));
    $moreGroups = collect($more)->groupBy('group');
@endphp
<div class="header-tools">
    <nav class="primary-nav" aria-label="เมนูหลัก">
        <ul class="menu">
            @foreach ($primary as $item)
                <li>
                    <a href="{{ route($item['route']) }}" @if (request()->routeIs(...$item['active'])) aria-current="page" @endif>
                        <x-icon :name="$item['icon']" /> {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
    @if ($showCreate)
        <a class="btn btn-solid" href="{{ route('requests.create') }}" @if (request()->routeIs('requests.create')) aria-current="page" @endif><x-icon name="help" /> แจ้งเรื่อง</a>
    @endif
    @if ($more !== [])
        <details class="more" @if ($moreActive) open @endif>
            <summary @if ($moreActive) aria-current="page" @endif>เพิ่มเติม</summary>
            <div class="more-panel">
                <p class="more-kicker">เมนูเพิ่มเติม</p>
                @foreach ($moreGroups as $group => $items)
                    <p class="more-group">{{ $group }}</p>
                    @foreach ($items as $item)
                        <a class="more-item" href="{{ route($item['route']) }}" @if (request()->routeIs(...$item['active'])) aria-current="page" @endif>
                            <span class="more-ico"><x-icon :name="$item['icon']" /></span>
                            <span>
                                <strong>{{ $item['label'] }}</strong>
                                <small>{{ $item['hint'] }}</small>
                            </span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </details>
    @endif
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-quiet" type="submit"><x-icon name="logout" /> ออกจากระบบ</button>
    </form>
</div>
